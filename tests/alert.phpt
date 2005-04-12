--TEST--
This file tests connectivity to services known not to be working on example.com - DNS, FTP, and HTTPS and attempts to send an SMTP alert to foo@example.com.
--FILE--
<?PHP
/**
 *
 * This file tests connectivity to services known
 * not to be working on example.com - DNS, FTP, and HTTPS.
 *
 * @package Net_Monitor
 */
/**
 *
 */
require_once('Net/Monitor.php');
error_reporting(E_ALL);
$services = array('example.com'=>array('DNS','FTP','HTTPS'));
$alerts = array('foo' => array('SMTP' => 'foo@example.com'));
$options = array('state_file' => 'Net_Monitor_TestSuite');
$monitor = new Net_Monitor($services,$alerts,$options);
$monitor->resetState();
$monitor->checkAll();
$monitor->resetState();
?>
--EXPECT--
