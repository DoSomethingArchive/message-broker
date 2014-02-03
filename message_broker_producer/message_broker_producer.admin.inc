<?php

/**
 * @file
 * Content administration and module settings UI.
 */

/**
 * List RabbitMQ status
 */
function message_broker_producer_status() {

  $output = '';

  // List queues
  /* @todo: permission access issues for www user
   *
   * http://unix.stackexchange.com/questions/15264/executing-a-shell-command-from-php-with-shell-exec
   * Added:
   * http    ALL=(username) /usr/local/sbin/rabbitmqctl
   * to /etc/sudoers
   *
   * Program Execution in PHP: exec, system, passthru, and shell_exec, oh my!
   * http://chipmunkninja.com/Program-Execution-in-PHP%3A-exec-m@
   *
   * works
   * $directoryListing = shell_exec('ls -la /usr/local/sbin');
   */
  $queues = shell_exec('/usr/local/sbin/rabbitmqctl list_queues');
  if ($queues == NULL) {
    $output['queues'] = 'Queue Listing - No RabbitMQ queues found, check that RabbitMQ is running.';
  }
  else {
    $output['queues'] = print_r($queues, TRUE);
  }

  // List exchanges
  $exchanges = shell_exec('/usr/local/sbin/rabbitmqctl list_exchanges');
  if ($exchanges == NULL) {
    $output['exchanges'] = 'Exchange Listing - No RabbitMQ exchanges found, check that RabbitMQ is running.';
  }

  // List bindings
  $bindings = shell_exec('rabbitmqctl list_exchanges');
  if ($bindings == NULL) {
    $output['bindings'] = 'Bindings Listing - No RabbitMQ bindings found, check that RabbitMQ is running.';
  }

  return theme('message_broker_producer_status', array('output' => $output));
}
