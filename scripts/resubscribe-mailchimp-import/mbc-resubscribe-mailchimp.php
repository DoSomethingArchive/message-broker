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

class MBI_MailchimpResubscribe {
 
 /**
  * AMQPConnection
  */
  public $messageBroker = NULL;
  
  /**
   * Setting from external services - Mailchimp.
   *
   * @var array
   */
  private $settings;
 
  public function __construct($settings) {
   $this->settings = $settings;
  }

  /**
   * Submit user campaign activity to the UserAPI
   *
   * @param array $payload
   *   The contents of the queue entry
   */
  public function consumerQueue($message) {

    echo '------- mbc-resubscribe-mailchimp MBI_MailchimpResubscribe consumerQueue START' . date('D M j G:i:s T Y') . ' -------', PHP_EOL;
    
    $resubscribe = unserialize($message->body);
    

    // Submit subscription to Mailchimp
    $mc = new \Drewm\MailChimp($this->settings['mailchimp_apikey']);
    
    // Debugging
    //$results1 = $mc->call("lists/list", array());
    //$results2 = $mc->call("lists/interest-groupings", array('id' => 'f2fab1dfd4'));  // DoSomething Members f2fab1dfd4, Old People a27895fe0c

    $results = $mc->call("lists/subscribe", array(
      'id' => 'a27895fe0c',
      'email' => array(
        'email' => $resubscribe['email']
        ),
      'merge_vars' =>  array(
        'groupings' => array(
          0 => array(
            'id' => $this->settings['mailchimp_grouping_id'], // Campaigns2013 (10621), Campaigns2014 (10637), Old People ()
          )
        ),
        'FNAME' => $resubscribe['FNAME'],
        'MMERGE10' => isset($resubscribe['birthdate']) ? $resubscribe['birthdate'] : '',
      ),
      'double_optin' => FALSE,
      'update_existing' => TRUE,
      'replace_interests' => FALSE,
      'send_welcome' => FALSE,
    ));

    echo '-------  mbc-resubscribe-mailchimp MBI_MailchimpResubscribe consumerQueue - END' . date('D M j G:i:s T Y') . ' -------', PHP_EOL;
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
  'mailchimp_grouping_id' => 333,
  'stathat_ez_key' => getenv("STATHAT_EZKEY"),
);
 
 // Kick off
 $mb = new MessageBroker($credentials, $config);
 $mb->consumeMessage(array(new MBI_MailchimpResubscribe($settings), 'consumerQueue'));

