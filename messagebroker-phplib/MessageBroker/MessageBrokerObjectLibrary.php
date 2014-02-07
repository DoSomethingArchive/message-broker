<?php

/*
 * Message Broker class library
 */

// Use AMQP
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageBroker
{
  public $connection = NULL;

  /**
    * Constructor
    *
    * @param array $credentials
    *   RabbitMQ connection details
    *   
    * @return object
    */
    public function __construct($credentials = array()) {
      
$bla = TRUE;
if ($bla) {
  $bla = TRUE;
}
      // Cannot continue if the library wasn't loaded.
      if (!class_exists('PhpAmqpLib\Connection\AMQPConnection') || !class_exists('PhpAmqpLib\Message\AMQPMessage')) {
        throw new Exception("Could not find php-amqplib. Please download and
          install from https://github.com/videlalvaro/php-amqplib/tree/v1.0. See
          rabbitmq INSTALL file for more details.");
      }

      // Use enviroment values set in config.inc if credentials not set
      if (empty($credentials['host']) || empty($credentials['port']) || empty($credentials['username']) || empty($credentials['password'])) {
        require_once(dirname(dirname(dirname(__FILE__))) . '/config.inc');
        $credentials['host'] = RABBITMQ_HOST;
        $credentials['port'] = RABBITMQ_PORT;
        $credentials['username'] = RABBITMQ_USERNAME;
        $credentials['password'] = RABBITMQ_PASSWORD;
      }

      // Connect
      $this->connection = new AMQPConnection($credentials['host'], $credentials['port'], $credentials['username'], $credentials['password']);
    }

  /**
   * produceTransactional - called to trigger production of a transactional
   * entry in an exchange / queue.
   *
   * @param array $param
   *  Values used to generate a production entry
   * 
   */
  public function produceTransactional($param) {
    
$bla = TRUE;
if ($bla) {
  $bla = TRUE;
}

    $connection = $this->connection;
    $channel = $connection->channel();
    
    $queueName = 'transactional';

    // @todo: Move to function to allow producers and consumers to create the
    // same queue. Declare queue as durable, pass third parameter to
    // queue_declare as true. Needs to be set to true to both the producer and
    // consumer queue_declare
    $channel->queue_declare($queueName, false, true, false, false);

    // Mark messages as persistent by setting the delivery_mode = 2 message property
    // Supported message properties: https://github.com/videlalvaro/php-amqplib/blob/master/doc/AMQPMessage.md
    $payload = new AMQPMessage($param, array('delivery_mode' => 2));

    $channel->basic_publish($payload, '', $queueName);

    $channel->close();
    $connection->close();

  }
  
}