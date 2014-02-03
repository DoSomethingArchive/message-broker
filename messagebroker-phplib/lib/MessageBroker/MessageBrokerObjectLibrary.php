<?php

/*
 * Message Broker class library
 */

include(__DIR__ . 'config.inc');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageBroker
{
  public $connection = NULL;
  public $credentials;

  /**
    * Constructor
    *
    * @param array $config Configuration variables
    * @return object
    */
    public function __construct() {

      // Cannot continue if the library wasn't loaded.
      if (!class_exists('AMQPConnection')) {
        throw new Exception("Could not find php-amqplib. Please download and
          install from https://github.com/videlalvaro/php-amqplib/tree/v1.0. See
          rabbitmq INSTALL file for more details.");
      }

      $this->connection = new AMQPConnection($credentials['host'], $credentials['port'], $credentials['username'], $credentials['password']);
    }

  /**
   * Produce - called to trigger production of an entry in an exchange / queue
   *
   * @param array $script
   */
  public function produce($script) {

    $connection = $this->connection;
    $channel = $connection->channel();

    // Declare queue as durable, pass third parameter to queue_declare as true
    // Flag needs to be set to true to both the producer and consumer queue_declare
    $channel->queue_declare($script['queueName'], false, true, false, false);

    // Mark messages as persistent by setting the delivery_mode = 2 message property
    // Supported message properties: https://github.com/videlalvaro/php-amqplib/blob/master/doc/AMQPMessage.md
    $payload = new AMQPMessage($script['payload'], array('delivery_mode' => 2));

    $channel->basic_publish($payload, '', $script['queueName']);

    $channel->close();
    $connection->close();

  }

}