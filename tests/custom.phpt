--TEST--
This file tests connectivity to services known not to be working on example.com and formats output using a custom, user-specified format.
--FILE--
<?PHP
/**
 *
 * This file tests connectivity to services known
 * not to be working on example.com and formats output
 * using a custom, user-specified format.
 *
 * Expected output to STDOUT is:
 * <pre>
 * Got no response from example.com checking DNS - code 0
 * Got Connection to host failed from example.com checking FTP - code 0
 * </pre>
 *
 * @package Net_Monitor
 */
/**
 *
 */
require_once('Net/Monitor.php');
$services = array('example.com'=>array('DNS','FTP'));
$alerts = array(); //use this to output to STDOUT
$options = array('state_file' => 'Net_Monitor_TestSuite', 'subject_line' => 'Custom subject','alert_line' => 'Got %m from %h checking %s - code %c');
$monitor = new Net_Monitor($services,$alerts,$options);
$monitor->resetState();
$monitor->checkAll();
$monitor->resetState();
?>
--EXPECT--
Got no response from example.com checking DNS - code 0
Got Connection to host failed from example.com checking FTP - code 0
