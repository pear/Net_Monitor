===========
Net_Monitor
===========

Purpose

To provide a unified interface for checking the availability services on external servers and sending meaningful alerts through a variety of media if a service becomes unavailable.

Elements

The service monitoring aspect of this package is essentially a unified interface to a number of packages designed to interact with Internet services. Initially, this list includes:

    * Net_SMTP
    * HTTP_Request
    * Net_DNS
    * Net_FTP

And would also include the Net_Telnet package that is currently being proposed.

The alert generation aspect is represented entirely through the use of PEAR_Error objects.

The alert sending aspect is currently a unified interface to Net_SMTP, with plans for extending this to encompass Net_SMS, whereby messages can be generated and sent directly from the package. This makes this package more robust in the event that the monitored servce that goes down is the SMTP relay for the package itself -- the package can still generate and send an SMTP message to an external address.

Structure

The package object inputs and stores two arrays -- one representing the person(s) to be notified in the event of a failure and the other representing the servers to be monitored.

Each person can be assigned a notification type (currently SMTP, SMS, or both). Each server can be assigned any number and combination of services to be monitored (currently SMTP, HTTP, DNS, and FTP).

Application

The most common application for this package is a simple monitoring script that cna be run at a scheduled interval to check remote services and send an alert if a service is down.


<?php
require('Net/Monitor.php');
$options = array('state_file' => 'Net_Monitor_TestSuite',
	'SMS_default' => array( 'SMS_provider' => 'clickatell_http',
							'username' => 'zzzzz',
							'password' => 'yyyyy',
							'api_id' => '123456' ),
	'SMTP_default' => array( 'host' => 'smtp.example.com'),
	'sms_debug' => false );
$monitor = new Net_Monitor($options);
$alerts = array(
	'User1' => array('SMTP'=>'user1@example.com', 'SMS'=>'1234567890'),
    'User2' => array('SMTP'=>'user2@example.com'));
$services = array('foo.example.com'=>array('SMTP','DNS'),
                 'bar.example.com'=>array('HTTP','FTP','DNS'));
$monitor->setAlerts($alerts);
$monitor->setServices($services);
$monitor->checkAll();
?>
NOTA: instead of a scalar mail or phone number string, the parameter associated with an alerter (SMTP or SMS) can be an array, in which case this array gives the parameters for establishing the connection for the alert.
This feature must be used in case the default alerter is itself under monitoring.
(see doc/examples/complex.php et al.)

Possible Extensions

Connection Time Monitoring

One proposed extension is the ability to monitor the time it takes to connect to a service and return a value. This could be accomplished using the Benchmark package. By logging this data to a database, graphs of server response over time could be generated. This idea is currently under review, mostly to ascertain how accurate the connection time values would be given that the connections depend upon external classes with variable overhead.

Document Change Monitoring

Another proposed extension is the ability to check an HTTP or FTP file against a previously stored md5 sum of the file to see if it has changed. This feature would be the responsibility of the HTTP/FTP service clients (wrappers to HTTP_Request and Net_FTP) to accept an array of arguments containing the server fqdn and the file to check instead of just a string of the server fqdn.

It is unclear if similar behavior extended to SMTP or DNS would prove useful. Ideas include issuing a VRFY command over SMTP to verify that an account exists on the server or checking that either a zone file has changed or a particular fqdn's IP address has changed on the DNS server.

Dependencies

    * Net_SMTP
    * HTTP_Request
    * Net_DNS
    * Net_FTP
