--TEST--
This file tests connectivity to services known not to be working on example.com - DNS, FTP, and HTTPS.
--FILE--
<?PHP
/**
 *
 * This file tests connectivity to services known
 * not to be working on example.com - DNS, FTP, and HTTPS.
 *
 * Expected output to STDOUT is:
 * <pre>
 * example.com: DNS: no response
 * example.com: FTP: Connection to host failed
 * example.com: HTTPS: Operation timed out
 * </pre>
 *
 * @package Net_Monitor
 */
/**
 *
 */
require_once('Net/Monitor.php');
error_reporting(E_ALL);
$services = array('example.com'=>array('DNS','FTP','HTTPS'));
$alerts = array(); //use this to output to STDOUT
$options = array('state_file' => 'Net_Monitor_TestSuite');
$monitor = new Net_Monitor($services,$alerts,$options);
$monitor->resetState();
$monitor->checkAll();
$monitor->resetState();
?>
--EXPECT--
example.com: DNS: no response
example.com: FTP: Connection to host failed
example.com: HTTPS: Connection refused
