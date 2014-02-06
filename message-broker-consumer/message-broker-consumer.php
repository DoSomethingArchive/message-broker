<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

$bla = TRUE;
if ($bla) {
  $bla = FALSE;
}

// Load settings based on arguments passed to sript
$useProductiontKey = $argv[1];
require_once __DIR__ . 'config.inc';

print('credentials<pre>');
print_r($credentials);
print('</pre>');

// Create objects
$mandrill = new Mandrill();
$connection = new AMQPConnection($credentials['host'], $credentials['port'], $credentials['username'], $credentials['password']);

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
  
  echo " [x] Received payload: ", $payload->body, "\n";
  
  // Assemble message details
  $payloadDetails = unseralize($payload->body);
  $targetEmail = $payloadDetails[''];
  list($templateName, $templateContent, $message) = BuildMessage($targetEmail);
  
  echo " [x] Built message contents...\n";

  // Send message
  $mandrillResults = $mandrill->messages->sendTemplate($templateName, $templateContent, $message);
  
  $mandrillResults = print_r($mandrillResults, TRUE);

  echo " [x] Sent message via Mandrill:\n";
  echo $mandrillResults;

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

/*
 * Assembly of message based on Mandrill API: Send-Template
 * https://mandrillapp.com/api/docs/messages.JSON.html#method=send-template
 */
function BuildMessage($targetEmail) {
  
  $message = array(
    'subject' => 'Test message',
    'from_email' => $targetEmail,
    'html' => '<p>this is a test message with Mandrill\'s PHP wrapper!.</p>',
    'to' => array(array('email' => $targetEmail, 'name' => 'Recipient 1')),
    'merge_vars' => array(array(
        'rcpt' => $targetEmail,
        'vars' =>
        array(
            array(
                'name' => 'FIRSTNAME',
                'content' => 'Recipient 1 first name'),
            array(
                'name' => 'LASTNAME',
                'content' => 'Last name')
    ))));

  $templateName = 'Stationary';

  $templateContent = array(
    array(
        'name' => 'main',
        'content' => 'Hi *|FIRSTNAME|* *|LASTNAME|*, thanks for signing up.'),
  );

  return array($templateName, $templateContent, $message);

}