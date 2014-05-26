message-broker v0.1.2
==============

A system to manage communication / messages sent between applications and DoSomething.org members.
The system is loosely coupled between producers:

- Drupal 7 website (primary producer)
- Other web applications

and consumers that issue requests to vendors (APIs) both internal (MB User API and MB Campaigns API). Mandrill and Mailchimp are external vendors for sending messages:

- Mandrill: https://mandrillapp.com
- Mailchimp: http://mailchimp.com/

RabbitMQ (http://www.rabbitmq.com) is used to manage the communication between the producer and consumer applications. RabbitMQ acts as a communication bus that uses the AMQP (http://amqp.org) protocall. AMQP is an open, general-purpose protocol for
messaging.

Sponsored by DoSomething.org


Development Plan
=================
The current development plan (2014-05-26 - Newman v 0.5.0) diagram with related notes:
https://raw.githubusercontent.com/DoSomething/message-broker/master/resources/0.5.0-Newman-140526.jpg
