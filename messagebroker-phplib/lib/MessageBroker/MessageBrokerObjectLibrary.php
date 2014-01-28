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
      
$bla = TRUE;
if ($bla) {
  $bla = TRUE;
}
      if (empty($credentials['host']) || empty($credentials['port']) || empty($credentials['username']) || empty($credentials['password'])) {
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
   * Produce
   * @see http://
   *
   * @param object $connection
   * @param string $queueName
   * @param string $payload
   */
  public function produce($script) {
    
$bla = TRUE;
if ($bla) {
  $bla = TRUE;
}
    $connection = $this->connection;
    $channel = $connection->channel();
    $channel->queue_declare($script['queueName'], false, true, false, false);
    
    $msg = new AMQPMessage($script['payload'], array('delivery_mode' => 2));
    $channel->basic_publish($msg, '', $script['queueName']);
    
    $channel->close();
    $connection->close();

  }
  
}


/**
 * RabbitMQ connection class.
 */

 

class RabbitMQConnection {

  static protected $connection;

  /**
   * Get a configured connection to RabbitMQ.
   */
  static public function get() {
    if (!self::$connection) {
      
/*
      $search_paths = array(dirname(__FILE__) . '/php-amqplib');

      // Load up the php-amqplib library.
      if (function_exists('libraries_get_path')) {
        array_push($search_paths, libraries_get_path('php-amqplib'));
      }

      // Search for the AMQP php library.
      while ($search_path = array_pop($search_paths)) {
        if (file_exists($search_path . '/amqp.inc')) {
          require_once $search_path . '/amqp.inc';
          break;
        }
      }

      // Cannot continue if the library wasn't loaded.
      if (!class_exists('AMQPConnection')) {
        throw new Exception("Could not find php-amqplib. Please download and install from https://github.com/videlalvaro/php-amqplib/tree/v1.0. See rabbitmq INSTALL file for more details.");
      }
      
*/

      $credentials = array(
        'host' => variable_get('message_broker_producer_host', 'localhost'),
        'port' => variable_get('message_broker_producer_port', '5672'),
        'username' => variable_get('message_broker_producer_username', 'guest'),
        'password' => variable_get('message_broker_producer_password', 'guest'),
      );
      $connection = new AMQPConnection($credentials['host'], $credentials['port'], $credentials['username'], $credentials['password']);

/*
      // Ensure the connection is closed when PHP exits.
      register_shutdown_function(function () use ($connection) {
        $connection->close();
      });
*/
      
      self::$connection = $connection;
    }

    return self::$connection;
  }
}