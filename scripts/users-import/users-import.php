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

class MBI_ProduceUsersImport {

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
   */
  public function produceFromCSV($targetCSVFile) {

    echo '------- users-import-import MBI_ProduceUsersImport produceFromCSV START: ' . date('D M j G:i:s T Y') . ' -------', "\n";

    $targetCSVFile = __DIR__ . '/' . $targetCSVFile;
    $data = file($targetCSVFile);

    echo '------- users-import-import MBI_ProduceUsersImport produceFromCSV: ' . $targetCSVFile . ' loaded - ' . date('D M j G:i:s T Y') . ' -------' .  "\n";

    // Was there a file found
    if ($data != FALSE) {

      $count = 0;
      foreach ($data as $userCount => $user) {

        $count++;

        // Skip column titles
        if ($userCount > 0) {

          echo '------- users-import->MBI_ProduceUsersImport user: ' . print_r($user, TRUE) . ' - ' . date('D M j G:i:s T Y') . ' -------', "\n";

          $userData = explode(',', $user);

          // First Name - remove \N character when blank
          if ($userData[4] == "\N") {
           $firstname = NULL;
          }
          else {
            $firstname = str_replace('"', '', $userData[4]);
          }

          // Last Name - remove \N character when blank
          if ($userData[5] == "\N") {
           $lastname = NULL;
          }
          else {
            $lastname = str_replace('"', '', $userData[5]);
          }

          // Mobile
          if ($userData[6] != "\\N") {
            $mobile = str_replace('"', '', $userData[6]);
          }
          else {
            $mobile = NULL;
          }

          // Birthdate
          if ($userData[7] != "\\N\n") {
            $userData[7] = str_replace('"', '', $userData[7]);
            $userData[7] = str_replace("\n", '', $userData[7]);
            $birthdate = strtotime($userData[7]);
          }
          else {
            $birthdate = NULL;
          }

          $payload = array(
            'activity' => 'user_register',
            'email' => str_replace('"', '', $userData[2]),
            'mobile' => $mobile,
            'uid' => (int) str_replace('"', '', $userData[0]),
            'birthdate' => $birthdate,
            'merge_vars' => array(
              'FNAME' => $firstname,
              'LNAME' => $lastname,
            ),
            'activity_timestamp' => (int) str_replace('"', '', $userData[3]),
            'application_id' => 0,
          );

          $payload = serialize($payload);

          echo '------- users-import->MBI_ProduceUsersImport publishMessage #' . $count . ' START: ' . date('D M j G:i:s T Y') . ' -------', "\n";
          $this->messageBroker->publishMessage($payload);
          echo '------- users-import->MBI_ProduceUsersImport publishMessage #' . $count . ' END: ' . date('D M j G:i:s T Y') . ' -------', "\n\n";

        }

      }
 
    }
    else {
      trigger_error('Invalid file ' . $targetCSVFile, E_USER_WARNING);
      return FALSE;
    }

    echo $count . ' "user_register" submitted to User API.', "\n";
    echo '------- users-import MBI_ProduceUsersImport produceFromCSV END' . date('D M j G:i:s T Y') . ' -------', "\n";
  }

}

if (isset($argv[1]) && $argv[1] != '') {
  $targetFile = $argv[1];

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
  $mbi = new MBI_ProduceUsersImport($credentials, $config);
  $mbi->produceFromCSV($targetFile);
}
else {
  echo('Target file not defined.' . "\n\n");
}
