<?php
/*
 * Drupal records from users table import script from csv file.
 */

 // Load up the Composer autoload magic
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration settings common to the Message Broker system
// symlinks in the project directory point to the actual location of the files
require __DIR__ . '/mb-secure-config.inc';
require __DIR__ . '/mb-config.inc';

class MBI_ProduceMailchimpImport {

 /**
  * AMQPConnection
  */
  public $messageBroker = NULL;

  public function __construct($credentials, $config) {
   $this->messageBroker = new MessageBroker($credentials, $config);
  }

  /**
   * Submit user campaign activity to the UserAPI
   *
   * @param array $payload
   *   The contents of the queue entry
   *
   * @param int $subscribed
   *   A flag to be passed as a part of the payload about the email
   *   subscription status 
   */
  public function produceFromCSV($targetCSVFile, $subscribed) {

    echo '------- mailchimp-import MBI_ProduceMailchimpImport produceFromCSV START: ' . date('D M j G:i:s T Y') . ' -------', "\n";

    $targetCSVFile = __DIR__ . '/' . $targetCSVFile;
    $data = file($targetCSVFile);

    echo '------- mailchimp-import MBI_ProduceMailchimpImport produceFromCSV: ' . $targetCSVFile . ' loaded - ' . date('D M j G:i:s T Y') . ' -------' .  "\n";

    // Was there a file found
    if ($data != FALSE) {

      $count = 0;
      foreach ($data as $userCount => $user) {

        $count++;

        // Skip column titles
        if ($userCount > 0) {

          echo '------- mailchimp-import MBI_ProduceMailchimpImport user: ' . print_r($user, TRUE) . ' - ' . date('D M j G:i:s T Y') . ' -------', "\n";

          $userData = explode(',', $user);

          $payload = array(
            'activity' => 'user_register',
            'email' => str_replace('"', '', $userData[0]),
            'application_id' => 0,
            'subscribed' => $subscribed,
          );

          $payload = serialize($payload);

          echo '------- mailchimp-import MBI_ProduceMailchimpImport publishMessage #' . $count . ' START: ' . date('D M j G:i:s T Y') . ' -------', "\n";
          $this->messageBroker->publishMessage($payload);
          echo '------- mailchimp-import MBI_ProduceMailchimpImport publishMessage #' . $count . ' END: ' . date('D M j G:i:s T Y') . ' -------', "\n\n";

        }

      }
 
    }
    else {
      trigger_error('Invalid file ' . $targetCSVFile, E_USER_WARNING);
      return FALSE;
    }

    echo $count . ' "user_register" submitted to User API.', "\n";
    echo '------- mailchimp-import MBI_ProduceMailchimpImport produceFromCSV END' . date('D M j G:i:s T Y') . ' -------', "\n";
  }

}

if (isset($argv[1]) && $argv[1] != '') {
  $targetFile = $argv[1];
  if (isset($argv[2])) {
    $subscribed = $argv[2];
  }
  else {
    $subscribed = 0;
  }
  

  // Settings
  $credentials = array(
    'host' =>  getenv("RABBITMQ_HOST"),
    'port' => getenv("RABBITMQ_PORT"),
    'username' => getenv("RABBITMQ_USERNAME"),
    'password' => getenv("RABBITMQ_PASSWORD"),
    'vhost' => getenv("RABBITMQ_VHOST"),
  );

  $config = array(
    'exchange' => array(
      'name' => getenv("MB_TRANSACTIONAL_EXCHANGE"),
      'type' => getenv("MB_TRANSACTIONAL_EXCHANGE_TYPE"),
      'passive' => getenv("MB_TRANSACTIONAL_EXCHANGE_PASSIVE"),
      'durable' => getenv("MB_TRANSACTIONAL_EXCHANGE_DURABLE"),
      'auto_delete' => getenv("MB_TRANSACTIONAL_EXCHANGE_AUTO_DELETE"),
    ),
    'queue' => array(
      array(
        'name' => getenv("MB_USER_API_REGISTRATION_QUEUE"),
        'passive' => getenv("MB_USER_API_REGISTRATION_QUEUE_PASSIVE"),
        'durable' => getenv("MB_USER_API_REGISTRATION_QUEUE_DURABLE"),
        'exclusive' => getenv("MB_USER_API_REGISTRATION_QUEUE_EXCLUSIVE"),
        'auto_delete' => getenv("MB_USER_API_REGISTRATION_QUEUE_AUTO_DELETE"),
        'bindingKey' => getenv("MB_USER_API_REGISTRATION_QUEUE_TOPIC_MB_TRANSACTIONAL_EXCHANGE_PATTERN"),
      ),
    ),
    'routingKey' => 'user.registration.transactional.import',
  );

  // Kick off
  $mbi = new MBI_ProduceMailchimpImport($credentials, $config);
  $mbi->produceFromCSV($targetFile, $subscribed);
}
else {
  echo('Target file not defined.' . "\n\n");
}
