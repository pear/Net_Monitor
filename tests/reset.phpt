--TEST--
This file tests services known not to be operating at example.com. This file also resets one of the failed states so that the warning is sent for this service (DNS) a second time.
--FILE--
<?PHP
/**
 *
 * This file tests services known not to be operating at example.com
 * This file also resets one of the failed states so that the warning
 * is sent for this service (DNS) a second time.
 *
 * Expected output to STDOUT is:
 * <pre>
 * example.com: DNS: no response
 * example.com: FTP: Connection to host failed
 * example.com: DNS: no response
 * </pre>
 *
 * @package Net_Monitor
 */
/**
 *
 */
require_once('Net/Monitor.php');
$services = array('example.com'=>array('DNS','FTP'));
$alerts = array();
$options = array('state_file' => 'Net_Monitor_TestSuite');
$monitor = new Net_Monitor($services,$alerts,$options);
$monitor->resetState();
$monitor->checkAll();
$monitor->resetHostState('example.com','DNS');
$monitor->checkAll();
$monitor->resetState();
?>
--EXPECT--
example.com: DNS: no response
example.com: FTP: Connection to host failed
example.com: DNS: no response
