<?php

/*
if (!function_exists('php-amqplib')) {
    throw new Exception('Message Broker needs the AMQP PHP extension.');
}
*/

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

require(dirname(__FILE__) . '/MessageBroker/MessageBroker.php');