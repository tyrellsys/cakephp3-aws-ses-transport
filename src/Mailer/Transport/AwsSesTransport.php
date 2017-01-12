<?php
namespace CakePHP3AwsSesTransport\Mailer\Transport\AwsSesTransport;

use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Email;
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
        'aws_access_secret_key' => ''
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
    public function getLastResponse() {
        return $this->_lastResponse;
    }

    /**
     * create instance the Aws\Ses\SesClient
     *
     * @return Aws\Ses\SesClient
     */
    protected function _connect()
    {
        if ($this->_ses != null) {
            return;
        }

        $options = [
            'region' => $this->_config['region'],
            'version' => $this->_config['version']
        ];

        if (!empty($this->_config['aws_access_key_id']) && !empty($this->config['aws_access_secret_key'])) {
            $options['credentials'] = [
                'key' => $this->_config['aws_access_key_id'],
                'secret' => $this->_config['aws_access_secret_key']
            ];
        }

        $this->_ses = new Aws\Ses\SesClient($options);
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
     * @param \Cake\Mailer\Email $email Email instance
     * @return array
     * @throws \Cake\Network\Exception\SocketException
     */
    public function send(Email $email)
    {
        $this->_connect();

        $headers = $this->_headersToString(
            $email->getHeaders([
                'from',
                'sender',
                'replyTo',
                'readReceipt',
                'returnPath',
                'to',
                'cc',
                'bcc',
                'subject'
            ])
        );
        $message = implode("\r\n", (array)$email->message());

        $raw = $headers . "\r\n\r\n" . $message;
        $options = array(
            'RawMessage' => array(
                'Data' => $raw,
            ),
        );

        $result = $this->ses->sendRawEmail($options);
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
