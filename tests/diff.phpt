--TEST--
This file tests the alerting mechanism by simulating a variety of circumstances where the previous state and current state differ in some aspects and are the same in others. This file also tests the notify_ok and notify_change options in relation to these circumstances.
--FILE--
<?PHP
/**
 * This file tests the alerting mechanism by simulating a variety of
 * circumstances where the previous state and current state differ
 * in some aspects and are the same in others. This file also tests the
 * notify_ok and notify_change options in relation to these circumstances.
 * See comments throughout the file for expected outputs.
 * 
 * @package Net_Monitor
 */
/**
 *
 */
require_once 'Net/Monitor.php';
error_reporting(E_ALL);

$primary = array(
    'foo.example.com'=>array(
        'SMTP'=>array( 0, 'Operation timed out' )
    )
    ,
    'bar.example.com'=>array(
        'HTTP'=>array( 404, 'File Not Found' )
    )
);
$secondary = array(
    'foo.example.com'=>array(
    	'DNS'=>array(0, 'Operation timed out')
    	,
        'SMTP'=>array(0, 'Operation timed out')
    )
    ,
    'bar.example.com'=>array(
    	'HTTP'=>array(0, 'Operation timed out')
    )
);
/* 
 * In this scenario:
 * foo.example.com SMTP is still down (no notice)
 * foo.example.com DNS came back up (send notice)
 * bar.example.com HTTP changed from down to 404 (send notice)
 */
$monitor = new Net_Monitor();
$monitor->_results = $primary;
$result = $monitor->stateDiff($secondary);
print_r($result);
/* Should produce:
 *
 * Array
 * (
 *     [0] => Array
 *         (
 *             [host] => bar.example.com
 *             [service] => HTTP
 *             [message] => File Not Found
 *             [code] => 404
 *         )
 * 
 *     [1] => Array
 *         (
 *             [host] => foo.example.com
 *             [service] => DNS
 *             [message] => OK
 *             [code] => 200
 *         )
 * )
 */
$monitor->setOptions(array('notify_ok' => 0));
$monitor->_results = $primary;
$result = $monitor->stateDiff($secondary);
print_r($result);
/* Should produce:
 *
 * Array
 * (
 *     [0] => Array
 *         (
 *             [host] => bar.example.com
 *             [service] => HTTP
 *             [message] => File Not Found
 *             [code] => 404
 *         )
 * )
 */
$monitor->setOptions(array('notify_ok' => 1, 'notify_change' => 0));
$monitor->_results = $primary;
$result = $monitor->stateDiff($secondary);
print_r($result);
 /* Should produce: 
  *
  * Array
  * (
  *     [0] => Array
  *         (
  *             [host] => foo.example.com
  *             [service] => DNS
  *             [message] => OK
  *             [code] => 200
  *         )
  * )
 */
?>
--EXPECT--
Array
(
    [bar.example.com] => Array
        (
            [HTTP] => Array
                (
                    [0] => 404
                    [1] => File Not Found
                )

        )

    [foo.example.com] => Array
        (
            [DNS] => Array
                (
                    [0] => 200
                    [1] => Operation timed out
                )

        )

)
Array
(
    [bar.example.com] => Array
        (
            [HTTP] => Array
                (
                    [0] => 404
                    [1] => File Not Found
                )

        )

)
Array
(
    [foo.example.com] => Array
        (
            [DNS] => Array
                (
                    [0] => 200
                    [1] => Operation timed out
                )

        )

)
