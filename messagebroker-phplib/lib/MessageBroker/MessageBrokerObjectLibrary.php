<?php

/*
 * Message Broker class library
 */

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageBroker
{
  public $connection = NULL;

  /**
    * Constructor
    *
    * @param array $config Configuration variables
    * @return object
    */
    public function __construct($credentials = array()) {

      // Cannot continue if the library wasn't loaded.
      if (!class_exists('AMQPConnection')) {
        throw new Exception("Could not find php-amqplib. Please download and
          install from https://github.com/videlalvaro/php-amqplib/tree/v1.0. See
          rabbitmq INSTALL file for more details.");
      }

      if (empty($credentials['host']) || empty($credentials['port']) ||
          empty($credentials['username']) || empty($credentials['password'])) {

        // @todo: These values should be pulled out of a config file. Using variable_get() is a Drupal thingy.
        $credentials = array(
          'host' => variable_get('message_broker_producer_host', 'localhost'),
          'port' => variable_get('message_broker_producer_port', '5672'),
          'username' => variable_get('message_broker_producer_username', 'guest'),
          'password' => variable_get('message_broker_producer_password', 'guest'),
        );

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