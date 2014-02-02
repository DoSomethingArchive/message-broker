<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

$bla = TRUE;
if ($bla) {
  $bla = FALSE;
}

include('config.inc');
$mandrill = new Mandrill();

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');

// Create a channel, where most of the API for getting things done resides
$channel = $connection->channel();

// @todo: Need to get queue name out of setting file. Ideally it's in sync with
// the Drupal settings that are used to produce the entry. Hard coded for now
$queueName = 'transactionals';

// See queue_declare comments in MessageBrokerObjectLibrary - produce function
// - Declare settings must be the same.
// Note that the queue declared in both the producer and receiver code.
// It's possible to start the receiver before the sender.
$channel->queue_declare($queueName, false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

// @todo: Mandrill functionality will be in callback from basic_consume
// Messages are delivered asynchronously, a callback in the form of an object
// will buffer the messages until we're ready to use them. That is what
// QueueingConsumer does.
$callback = function($payload){
  echo " [x] Received ", $payload->body, "\n";
  // Mandrill stub
  echo " [x] Done", "\n";
  $payload->delivery_info['channel']->basic_ack($payload->delivery_info['delivery_tag']);
};

// Fair dispatch
// Don't give more than one message to a worker at a time. Don't dispatch a new
// message to a worker until it has processed and acknowledged the previous one.
// Instead, it will dispatch it to the next worker that is not still busy.
// AKA: unlimited number of workers with even distribution of tasks based on
// completion
// prefetch_count = 1
$channel->basic_qos(null, 1, null);

// Message acknowledgments are turned off by default.  Fourth parameter in
// basic_consume to false (true means no ack). This will send an acknowledgment
// from the worker once the task is complete.
$channel->basic_consume($queueName, '', false, false, false, false, $callback);

// To see message that have not been "unack"ed.
// $ rabbitmqctl list_queues name messages_ready messages_unacknowledged

// The code will block while $channel has callbacks. Whenever a message is
// received the $callback function will be passed the received message.
while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>