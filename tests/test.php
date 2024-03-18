<?php
declare(strict_types=1);

use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use CakePHP3AwsSesTransport\Mailer\Transport\AwsSesTransport;

/**
 * Usage: test.php to-address from-address
 */
require dirname(__DIR__) . '/vendor/autoload.php';

TransportFactory::setConfig([
    'ses' => [
        'className' => AwsSesTransport::class,
        'region' => 'us-east-1',
        'version' => 'latest',
    ],
]);

$mailer = new Mailer();
$mailer
    ->setTransport('ses')
    ->setTo($argv[1])
    ->setFrom($argv[2])
    ->setSubject('test cake aws ses transport')
    ->deliver('test cake aws ses transport');
