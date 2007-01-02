<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Remote service monitor alerter thru Growl
 *
 * WARNING: THIS IS AN EXPERIMENTAL PROOF OF CONCEPT SO FAR.
 * 
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * 
 * @package Net_Monitor
 * @author Robert Peake <robert@peakepro.com>
 * @copyright 2005-2007
 * @license http://www.php.net/license/3_0.txt
 * @version 0.0.1
 */
/**
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/**
 * requires and uses the Net_Growl class to send Growl alerts
 * FATAL if Net_Growl is not installed
 */
require_once 'Net/Growl.php';
/** 
 * class Net_Monitor_Alert_Growl
 *
 * @package Net_Monitor
 * @see Net_Monitor_Alert
 * @access public
 */
class Net_Monitor_Alert_Growl extends Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'Growl';

    /**
     * Array of alert objects to be used
     *
     * in the format $messenger => $object
     *
     * @var array $_alert
     * @access private
     */
    var $_alert = array();

    /**
     * The default port to be used
     *
     * @var int $_port
     * @access private
     */
    var $_port = null;

    /**
     * The default protocol to be used
     *
     * @var int $_protocol
     * @access private
     */
    var $_protocol = 'udp';

    /**
     * Net_Growl specific options
     *
     * @var int $_options
     * @access private
     */
    var $_options = array();

    /**
     * The resource name to specify to the messenger
     *
     * @var string $_resource
     * @access private
     */
    var $_resource = 'Net_Monitor_Alert_Growl';

    /** 
     * constructor
     *
     * @access public
     */
    function Net_Monitor_Alert_Growl()

    {
        //nothing to initialize
    }
    /** 
     * Sends the alerts thru the specified Growl server with optional password
     *
     * <li> $server is an array of key=>value
     *      where value is a string.
     *      Server defines these keys:
     *      <ul>
     *      <li> server - The server to connect to. Mandatory.
     *      <li> password - The password to use for Growl authentication. Mandatory.
     *      </ul>
     * <li> $results is the array of results to send
     * </ul>
     * Returns true on success, PEAR_Error object on failure
     *
     * @param array $server Growl server connection/authentication options
     * @param array $results results to send
     * @param array $options standard Net_Monitor options
     * @return mixed true or PEAR_Error
     * @access private
     */
    function alert($server, $result_array, $options=array()) 
    {
        $message = '';
        if (isset($options['alert_line'])) {
            $model_line = $options['alert_line'];
        } else {
            $model_line = '%h: %s: %m';
        }
        if (isset($options['subject_line'])) {
            $subject = $options['subject_line'];
        }
        if (isset($options['priority'])) {
            $this->_options['priority'] =  $options['priority'];
        }
        if (isset($options['sticky'])) {
            $this->_options['sticky'] =  $options['sticky'];
        }
        foreach ($result_array as $host=>$services) {
            foreach ($services as $service=>$result) {
            $message .= str_replace(
                array('%h', '%s',    '%c',      '%m'),
                array($host, $service, $result[0], $result[1]),
                $model_line)."\r\n";
            }
        }
        foreach ($server as $messenger=>$where) {
            if (!is_array($where)) {
                PEAR::raiseError(
                    'server paramaters are not in an array -- unable to send alert');
                return false;
            }
            if (is_string($where['server'])) {
                $server_addr = $where['server'];
            }
            if (is_string($where['password'])) {
                $password = $where['password'];
            }
            $e = $this->sendAlert($message, $messenger, $server_addr, $password, $subject);
            if (Pear::isError($e)) {
                return $e;
            }
        }
        return true;
    } // alert()
    /**
     * Sends the specified results to the specified Growl server
     *
     * Returns true on success, PEAR_Error object on failure
     *
     * @param string $message message to send
     * @param string $server Growl server
     * @param string $password Growl server password
     * @param string $subject optional subject line
     * @return mixed
     * @access private
     */
    function sendAlert($message, $messenger, $server = '', $password = '', $subject = '')
    {
        if (!is_string($message) || strlen($message) == 0) {
            return new Pear_Error('Net_Monitor_Alert_Growl requires a message.');
        } else {
            //send Growl alert here
            if (!$subject) $subject = 'Net_Monitor Alert';
            if (empty($this->_alert) || !is_object($this->_alert[$messenger])) {
                if (!is_string($server) || strlen($server) == 0) {
                    $server = '127.0.0.1';
                }
                $server_array = array($server);
                if ($this->_port) {
                    $server_array[] = $this->_port;
                }
                if ($this->_protocol) {
                    $server_array[] = $this->_protocol;
                }
                $application = 'PEAR::Net_Monitor for '.$messenger;
                $this->_alert[$messenger] = new Net_Growl($application, array($messenger), $password, $server_array);
            }
            $growl =& $this->_alert[$messenger];
            return $growl->notify($messenger, $subject, $message, $this->_options);
        }
    }
} // end class Net_Monitor_Alert_Growl
