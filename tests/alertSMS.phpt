--TEST--
This file tests connectivity to services known not to be working on example.com - DNS, FTP, and HTTPS and attempts to send an SMS alert to 012345678 by clickatem
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
$alerts = array('foo' => array('SMS' => '012345678'));
$options = array('state_file' => 'Net_Monitor_TestSuite',
	'SMS_default' => array( 'SMS_provider' => 'clickatell_http',
							'username' => 'pique',
							'password' => 'robert',
							'api_id' => 'x.y.z' ),
	'sms_debug' => true );
$monitor = new Net_Monitor($services,$alerts,$options);
$monitor->resetState();
$monitor->checkAll();
$monitor->resetState();
?>
--EXPECT--
pique by clickatell_http
Array
(
    [from] => Net_Monitor
    [to] => Array
        (
            [0] => 012345678
        )

)
Array
(
    [password] => robert
    [api_id] => x.y.z
    [user] => pique
)
