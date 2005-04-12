<?php
/**
 *
 * Example of use
 *
 * @package Net_Monitor
 */
/**
 *
 */
require_once('Net/Monitor.php');
error_reporting(E_ALL);
//PEAR::setErrorHandling (PEAR_ERROR_DIE);
$services = array('example.com'=>array('DNS','FTP','HTTPS'));
$alerts = array(
'chine' => array('SMS' => array( 'SMS_provider' => 'sms2email_http',
								 'base_url' => 'horde.sms2email.com/horde/',
								 'ssl' => true,
                         		 'username' => 'xxxxx',
								 'password' => 'yyyyyyyy',
								 'phone_number' => '1234567890' )),
'bertrand' => array( 'SMS' => '9876543210', 'SMTP' => 'foo@bar.com'));
$options = array('state_file' => 'Net_Monitor_TestSuite',
	'SMS_default' => array( 'SMS_provider' => 'clickatell_http',
							'username' => 'zzzzz',
							'password' => 'yyyyy',
							'api_id' => '123456' ),
	'SMTP_default' => array( 'host' => 'smtp.example.com'),
	'sms_debug' => false );
$monitor = new Net_Monitor($services,$alerts,$options);
$monitor->resetState();
$monitor->checkAll();
$monitor->resetState();
?>
