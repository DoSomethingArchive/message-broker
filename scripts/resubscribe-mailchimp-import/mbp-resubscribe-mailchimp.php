<?php
/*
 * Resubscribe email address based on csv file entries.
 */

 // Load up the Composer autoload magic
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration settings common to the Message Broker system
// symlinks in the project directory point to the actual location of the files
require __DIR__ . '/mb-secure-config.inc';
require __DIR__ . '/mb-config.inc';

class MBI_ImportCSV {
 
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

    echo '------- resubscribe-mailchimp-import MBI_ImportCS produceFromCSV START' . date('D M j G:i:s T Y') . ' -------', PHP_EOL;
    
    // First Name,Last Name,Email Address,Graduation Year,Birthday,Phone Number,Full Name,Birthday,Completion URL,Untitled,Date of Birth,Relationship to DoSomething,Marketing,Partners,Events,General,Campaigns,Salesforce,MEMBER_RATING,OPTIN_TIME,OPTIN_IP,CONFIRM_TIME,CONFIRM_IP,LATITUDE,LONGITUDE,GMTOFF,DSTOFF,TIMEZONE,CC,REGION,LAST_CHANGED,LEID,EUID,NOTES

    $resubscribeCount = 0;

    $targetCSVFile = __DIR__ . '/' . $targetCSVFile;
    echo 'targetCSVFile: ' . $targetCSVFile, PHP_EOL;
    $resubscribeFile = file($targetCSVFile);
    $resubscribeUsers = explode("\r", $resubscribeFile[0]);
 
    foreach ($resubscribeUsers as $resubscribeCount => $resubscribeUser) {
      if ($resubscribeCount > 0) {
        $resubscribeData = explode(',', $resubscribeUser);
        $data = array(
          'email' => $resubscribeData[2],
          'FNAME' => $resubscribeData[0],
          'birthdate' => $resubscribeData[4],
        );
        $payload = serialize($data);
        $status = $this->messageBroker->publishMessage($payload);
      }
    }

    echo '------- resubscribe-mailchimp-import MBI_ImportCS produceFromCSV produceFromCSV : ' . $resubscribeCount . ' added/updated... - END' . date('D M j G:i:s T Y') . ' -------', PHP_EOL;
  }

}


// Kick off
$targetFile = '';
if (isset($argv[1]) && $argv[1] != '') {
  $targetFile = $argv[1];
}
elseif (isset($_GET['targetFile'])) {
  $targetFile = $_GET['targetFile'];
}

if ($targetFile != '') {

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
    'name' => getenv("MB_SCRIPT_EXCHANGE"),
    'type' => getenv("MB_SCRIPT_EXCHANGE_TYPE"),
    'passive' => getenv("MB_SCRIPT_EXCHANGE_PASSIVE"),
    'durable' => getenv("MB_SCRIPT_EXCHANGE_DURABLE"),
    'auto_delete' => getenv("MB_SCRIPT_EXCHANGE_AUTO_DELETE"),
  ),
  'queue' => array(
    'mailchimpResubscribeQueue' => array(
      'name' => getenv("MB_MAILCHIMP_RESUBSCRIBE_QUEUE"),
      'passive' => getenv("MB_MAILCHIMP_RESUBSCRIBE_QUEUE_PASSIVE"),
      'durable' => getenv("MB_MAILCHIMP_RESUBSCRIBE_QUEUE_DURABLE"),
      'exclusive' => getenv("MB_MAILCHIMP_RESUBSCRIBE_QUEUE_EXCLUSIVE"),
      'auto_delete' => getenv("MB_MAILCHIMP_RESUBSCRIBE_QUEUE_AUTO_DELETE"),
      'bindingKey' => getenv("MB_MAILCHIMP_RESUBSCRIBE_QUEUE_BINDING_KEY"),
    ),
  ),
  'routingKey' => 'old-people.mailchimp.resubscribe',
);
$settings = array(
  'mailchimp_apikey' => getenv("MAILCHIMP_APIKEY"),
  'mailchimp_list_id' => getenv("MAILCHIMP_LIST_ID"),
  'stathat_ez_key' => getenv("STATHAT_EZKEY"),
);
 
  // Kick off
  $mb = new MBI_ImportCSV($credentials, $config);
  $mb->produceFromCSV($targetFile);

}
else {
  echo('Target file needs to be provided as a parameter (?targetFile=).' . "\n\n");
}
