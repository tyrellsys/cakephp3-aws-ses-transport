<?php
/**
 * Usage: test.php to-address from-address
 */
require dirname(__DIR__) . '/vendor/autoload.php';

\Cake\Mailer\TransportFactory::setConfig([
    'ses' => [
        'className' => \CakePHP3AwsSesTransport\Mailer\Transport\AwsSesTransport::class,
        'region' => 'us-east-1',
        'version' => 'latest',
    ],
]);

$mailer = new \Cake\Mailer\Mailer();
$mailer
    ->setTransport('ses')
    ->setTo($argv[1])
    ->setFrom($argv[2])
    ->setSubject('test cake aws ses transport')
    ->deliver('test cake aws ses transport');
