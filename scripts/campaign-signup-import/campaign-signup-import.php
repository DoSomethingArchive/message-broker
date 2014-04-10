<?php
/*
 * User campaign signup import script from csv file.
 */

 // Load up the Composer autoload magic
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration settings common to the Message Broker system
// symlinks in the project directory point to the actual location of the files
require __DIR__ . '/mb-secure-config.inc';
require __DIR__ . '/mb-config.inc';

class MBI_ProduceCampaignActivity {
 
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

    $targetCSVFile = __DIR__ . '/' . $targetCSVFile;
    $data = file($targetCSVFile);
    
    $signups = preg_split('/\n|\r/', $data[0], -1, PREG_SPLIT_NO_EMPTY);
  
    $count = 0;
    foreach ($signups as $signupCount => $signup) {
     
      $count++;
     
      // Skip column titles
      if ($signupCount > 0) {
    
        $signupData = explode(',', $signup);
        
        $data = array(
         'activity' => 'campaign_signup',
         'email' => $signupData[3],
         'uid' => $signupData[2],
         'event_id' => $signupData[0],
         'activity_timestamp' => time(),
         'application_id' => 2,
        );
         
        switch ($signupData[0]) {
         
         case 362:
          
           $data['mailchimp_grouping_id'] = 10637;
           $data['mailchimp_group_name'] = 'ComebackClothes2014';
           break;
         
         case 850:
          
           $data['mailchimp_grouping_id'] = 10621;
           $data['mailchimp_group_name'] = 'MindOnMyMoney2013';
           break;
         
         case 955:
          
           $data['mailchimp_grouping_id'] = 10637;
           $data['mailchimp_group_name'] = 'PBJamSlam2014';
           break;
  
        }
        
        $payload = serialize($data);
        $status = $this->messageBroker->publishMessage($payload);
  
      }

    }

  }

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
    'userAPICampaignActivity' => array(
      'name' => getenv("MB_USER_API_CAMPAIGN_ACTIVITY_QUEUE"),
      'passive' => getenv("MB_USER_API_CAMPAIGN_ACTIVITY_QUEUE_PASSIVE"),
      'durable' => getenv("MB_USER_API_CAMPAIGN_ACTIVITY_QUEUE_DURABLE"),
      'exclusive' => getenv("MB_USER_API_CAMPAIGN_ACTIVITY_QUEUE_EXCLUSIVE"),
      'auto_delete' => getenv("MB_USER_API_CAMPAIGN_ACTIVITY_QUEUE_AUTO_DELETE"),
      'bindingKey' => getenv("MB_USER_API_CAMPAIGN_ACTIVITY_QUEUE_TOPIC_MB_TRANSACTIONAL_EXCHANGE_PATTERN"),
    ),
    'campaign_signups' => array(
      'name' => getenv("MB_MAILCHIMP_CAMPAIGN_SIGNUP_QUEUE"),
      'passive' => getenv("MB_MAILCHIMP_CAMPAIGN_SIGNUP_QUEUE_PASSIVE"),
      'durable' => getenv("MB_MAILCHIMP_CAMPAIGN_SIGNUP_QUEUE_DURABLE"),
      'exclusive' => getenv("MB_MAILCHIMP_CAMPAIGN_SIGNUP_QUEUE_EXCLUSIVE"),
      'auto_delete' => getenv("MB_MAILCHIMP_CAMPAIGN_SIGNUP_QUEUE_AUTO_DELETE"),
      'bindingKey' => getenv("MB_MAILCHIMP_CAMPAIGN_SIGNUP_QUEUE_TOPIC_MB_TRANSACTIONAL_EXCHANGE_PATTERN"),
    ),
  ),
  'routingKey' => 'campaign.signup.import',
);

$targetFile = 'emails.csv';

// Kick off
$mbi = new MBI_ProduceCampaignActivity($credentials, $config);
$mbi->produceFromCSV($targetFile);
