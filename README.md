# AwsSesTransport plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require tyrellsys/cakephp3-aws-ses-transport
```

## Configuration

app/config/app.php EmailTransport

```
    'EmailTransport' => [
        'default' => [
            'className' => 'CakePHP3AwsSesTransport\Mailer\Transport\AwsSesTransport',
            'region' => 'us-east-1', // optional
            'version' => 'latest', // optional
            'aws_access_key_id' => 'xxxx', // optional if EC2InstanceRole
            'aws_access_secret_key' => 'xxxx', // optional if EC2InstanceRole
        ],
        :
        :
```
