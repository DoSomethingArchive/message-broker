<?php

/**
 * @file
 * Content administration and module settings UI.
 */

/**
 * System settings form for basic configurations settings
 */
function message_broker_producer_config_form($form, &$form_state)  {

  $form['authentication'] = array(
    '#type' => 'fieldset',
    '#title' => t('Authentication')
  );

  $form['authentication']['message_broker_producer_host'] = array(
    '#type' => 'textfield',
    '#title' => t('host'),
    '#required' => TRUE,
    '#default_value' => variable_get('message_broker_producer_host', 'localhost'),
  );
  
  $form['authentication']['message_broker_producer_port'] = array(
    '#type' => 'textfield',
    '#title' => t('port'),
    '#required' => TRUE,
    '#default_value' => variable_get('message_broker_producer_port', '5672'),
  );
  
  $form['authentication']['message_broker_producer_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#required' => TRUE,
    '#default_value' => variable_get('message_broker_producer_username', 'guest'),
  );
  
  $form['authentication']['message_broker_producer_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Password'),
    '#required' => TRUE,
    '#default_value' => variable_get('message_broker_producer_password', 'guest'),
  );

  return system_settings_form($form);
}
