--TEST--
This file tests connectivity to known working services on php.net - SMTP and HTTP and pear.php.net - HTTP.
--FILE--
<?PHP
/**
 * This file tests connectivity to known working services on php.net
 * - SMTP and HTTP and pear.php.net - HTTP
 *
 * Expected output to STDOUT is blank.
 *
 * @package Net_Monitor
 */
/**
 *
 */
require_once('Net/Monitor.php');
$services = array('www.php.net'=>array('SMTP','HTTP'),'pear.php.net'=>array('HTTP'));
$alerts = array(); //use this to output to STDOUT
$options = array('state_file' => 'Net_Monitor_TestSuite');
$monitor = new Net_Monitor($services,$alerts,$options);
$monitor->resetState();
$monitor->checkAll();
$monitor->resetState();
?>
--EXPECT--
