<?php

/*
 * Message Broker class library
 */

class MessageBroker
{
  /**
    * Constructor
    *
    * @param array $config Configuration variables
    * @return object
    */
    public function __construct($credentials = array())
    {
      if (is_null($credentials)) {
        $credentials = array(
          'host' => variable_get('message_broker_producer_host', 'localhost'),
          'port' => variable_get('message_broker_producer_port', '5672'),
          'username' => variable_get('message_broker_producer_username', 'guest'),
          'password' => variable_get('message_broker_producer_password', 'guest'),
        );
      }
      $connection = new AMQPConnection($credentials['host'], $credentials['port'], $credentials['username'], $credentials['password']);
      
      return $connection;

    }
    
  /**
   * Produce
   * @see http://
   *
   * @param object $connection
   * @param string $queueName
   * @param string $payload
   */
  public function Produce($connection, $queueName, $payload) {
    $channel = CreateChannel($connection);
    $channel = QueueDeclare($channel, $queueName);
    
    $msg = new AMQPMessage($payload);
    $channel->basic_publish($msg, '', $queueName);
    
    $channel->close();
    $connection->close();

  }
    
  /**
   * Create Channel
   * @see http://
   *
   * @param object $connection
   * @return object
   */
  public function CreateChannel($connection) {
    $channel = $connection->channel();
    return $channel;
  }
  
  /**
   *
   * Queue Declare
    * @see http://
    *
    * @param object $connection
    * @param string $queueName
    * @return object
    */
  public function QueueDeclare($channel, $queueName = NULL) {
    $channel->queue_declare($queueName, false, false, false, false);
    return $channel;
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