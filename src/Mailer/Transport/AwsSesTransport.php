<?php
namespace CakePHP3AwsSesTransport\Mailer\Transport;

use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;
use Aws\Ses\SesClient;
use Cake\Network\Exception\SocketException;

/**
 * Send mail using AWS SES API
 */
class AwsSesTransport extends AbstractTransport
{

    /**
     * Default config for this class
     *
     * @var array
     */
    protected $_defaultConfig = [
        'region' => 'us-east-1',
        'version' => 'latest',
        'aws_access_key_id' => '',
        'aws_access_secret_key' => '',
        'timeout' => 0,
        'connect_timeout' => 0,
    ];


    /**
     * @var Aws\Ses\SesClient
     */
    protected $_ses = null;


    /** @var Aws\Result */
    protected $_lastResponse = null;


    /**
     * Returns the response of the last sent AWS SES API.
     *
     * @return Aws\Result
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    /**
     * create instance the Aws\Ses\SesClient
     *
     * @return void
     */
    protected function _connect()
    {
        if ($this->_ses != null) {
            return;
        }

        $options = [
            'region' => $this->_config['region'],
            'version' => $this->_config['version'],
            'http' => [
                'connect_timeout' => $this->_config['connect_timeout'],
                'timeout' => $this->_config['timeout'],
            ],
        ];

        if (!empty($this->_config['aws_access_key_id']) && !empty($this->_config['aws_access_secret_key'])) {
            $options['credentials'] = [
                'key' => $this->_config['aws_access_key_id'],
                'secret' => $this->_config['aws_access_secret_key']
            ];
        }

        $this->_ses = new SesClient($options);
    }

    /**
     * destroy the Aws\Ses\SesClient
     */
    protected function _disconnect()
    {
        unset($this->_ses);
        $this->_ses = null;
    }


    /**
     * Send mail
     *
     * @param \Cake\Mailer\Message $message Message instance
     * @return array
     * @throws \Cake\Network\Exception\SocketException
     */
    public function send(Message $message): array
    {
        $this->_connect();

        $headers = $message->getHeaders(['X-BounceTo']);
        if (!empty($headers["X-BounceTo"])){
            $message->setReturnPath($headers["X-BounceTo"]);
            unset($headers['X-BounceTo']);
            $message->setHeaders($headers);
        }

        $header = $message->getHeadersString([
            'from',
            'sender',
            'replyTo',
            'readReceipt',
            'to',
            'cc',
            'bcc',
            'subject',
            'returnPath',
        ]);
        $body = $message->getBodyString();

        $raw = $header . "\r\n\r\n" . $body;

        $args = [
            'RawMessage' => [
                'Data' => $raw
            ],
        ];

        try {
            $result = $this->_ses->sendRawEmail($args);
        } catch (\Exception $e) {
            throw new SocketException($e->getMessage());
        }
        
        if(empty($result)) {
            throw new SocketException();
        }

        $this->_lastResponse = $result;
        $results = $result->toArray();
        if(!isset($results['@metadata']['statusCode']) || ($results['@metadata']['statusCode'] != 200)) {
            throw new SocketException();
        }

        return ['headers' => $headers, 'message' => $message, 'messageId' => $results['MessageId']];
    }
}
