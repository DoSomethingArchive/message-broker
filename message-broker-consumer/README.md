message-broker
==============

A system to manage communication / messages sent to DoSomething.org members.
The system is loosely coupled between producers:

- Drupal 7 websites, New and Old world: http://dosomething.org
- Other web applications

and consumers:

- Mandrill: https://mandrillapp.com


Message Broker Producer
=================

A dripal 7.x module that provides producer access to the Message Broker system.
* Wrapper function for Object Library calls
* Simple administration page for authentication details

Requirements:
Message Broker PHP Library - https://github.com/DoSomething/message-broker

Sponsored by DoSomething.org
