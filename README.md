message-broker v0.1.2
==============

A system to manage communication / messages sent to DoSomething.org members.
The system is loosely coupled between producers:

- Drupal 7 websites, New and Old world: http://dosomething.org
- Other web applications

and consumers that issue email requests to vendors. Currently Mandrill is the
only vendor for sending messages that's supported:

- Mandrill: https://mandrillapp.com

RabbitMQ () is used to manage the communication between the producer and
consumers. RabbitMQ acts as a communication bus that uses the AMQP
(http://amqp.org) protocall. AMQP is an open, general-purpose protocol for
messaging.


Message Broker Producer
=================

A dripal 7.x module that provides producer access to the Message Broker system.
* Wrapper function for Object Library calls
* Simple administration page for authentication details

Requirements:
Message Broker PHP Library - https://github.com/DoSomething/message-broker

Sponsored by DoSomething.org


Development Plan
=================
The current development plan (2014-02-06 - Cliff v 0.1.0) diagram with related notes:
https://raw.github.com/DoSomething/message-broker/master/resources/MessageBrokerDevPlan-Cliffv1_0_0-140206.jpg
