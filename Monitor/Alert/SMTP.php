<?php
/**
 * Network service monitoring package
 *
 * A unified interface for checking the availability services on external 
 * servers and sending meaningful alerts through a variety of media if a 
 * service becomes unavailable.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Net
 * @package   Net_Monitor
 * @author    Robert Peake <cyberscribe@php.net>
 * @author    Bertrand Gugger <bertrand@toggg.com>
 * @copyright 2004-2007 Robert Peake
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_Monitor
 * @since     File available since Release 0.0.6
 */
/**
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/**
 * requires and uses the Mail class to send SMTP alerts
 * FATAL if Mail is not installed
 */
require_once 'Mail.php';
/** 
 * Net_Monitor_Alert_SMTP
 *
 * A class for sending alerts via SMTP (email)
 * It uses the global Net_Monitor options (default):
 * + smtp_debug - send debugging output to STDOUT for the SMTP alert (false)
 * + from_email - who is the sender for the SMTP alert ('pear.Net_Monitor')
 * + SMTP_default - array of options for Mail_SMTP used for normal adressees (array())
 *
 * @category Net
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Alert
 * @see Mail
 */
class Net_Monitor_Alert_SMTP extends Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'SMTP';
    /**
     * The alert object to be used
     *
     * @var object $_alert
     * @access private
     */
    var $_alert = null;
    /** 
     * function Net_Monitor_Alert_SMTP
     *
     * @access public
     */
    function Net_Monitor_Alert_SMTP()

    {
        
    }
    /** 
     * function alert
     *
     * Sends the alerts to the specified SMTP servers
     * First each dedicaded prioritary user (with own smtp parameters as array)
     * + $server is an array of user=>parameter
     *      where parameter is either an array or string.
     *      A string should be a fully qualified email address (common user)
     *      in this case global option SMTP_default array will be used to configure SMTP
     *      An array describes the SMTP server as required my Mail/smtp.php
     *      and must contain an extra 'email' key (prioritary user)
     *      the SMTP server description contains optionally (refer to Mail):
     *      + host - The server to connect. Default is localhost
     *      + port - The port to connect. Default is 25
     *      + auth - Whether or not to use SMTP authentication. Default is FALSE
     *      + username - The username to use for SMTP authentication.
     *      + password - The password to use for SMTP authentication.
     * + $results is the array of results to send
     * 
     * Returns true on success, PEAR_Error object on failure
     *
     * @access private
     * @param mixed server
     * @param array results
     * @return mixed
     */
    function alert($server, $result_array, $options=array()) 

    {
        //construct $email_message from $result_array
        $email_message = '';
        if (isset($options['alert_line'])) {
            $model_line = $options['alert_line'];
        } else {
            $model_line = '%h: %s: %m';
        }
        foreach ($result_array as $host=>$services) {
        	foreach ($services as $service=>$result) {
                $email_message .= str_replace(
                    array('%h', '%s',    '%c',      '%m'),
                    array($host, $service, $result[0], $result[1]),
                    $model_line)."\r\n";
            }
        }
        if (!$email_message) { // nothing to do
            return true;
        }

        //construct header's subject and from
        $headers = array(); 
        if (!empty($options['subject_line'])) {
            $headers['Subject'] = $options['subject_line'];
        } else {
            PEAR::raiseError("Using default subject\n",
                 null, PEAR_ERROR_PRINT, E_USER_WARNING);
            $headers['Subject'] = 'Net_Monitor Alert';
        }
        if (!empty($options['from_email'])) {
            $headers['From'] = $options['from_email'];
        } else {
            PEAR::raiseError("Using default email 'from': pear.Net_Monitor\n",
                 null, PEAR_ERROR_PRINT, E_USER_WARNING);
            $headers['From'] = 'pear.Net_Monitor';
        }
        
        //parse $server to proceed prioritary ones and cumulate the others
        $common = array();
        $email = '';
        foreach ($server as $user=>$where) {
            // only email as string specified ? => to cumulate
            if (is_string($where)) {
                $common[] = $where;
                continue;
            }
            if (!is_array($where)) {
                PEAR::raiseError(
                    '$server is not a string or array -- unable to send alert');
                return false;
            }
            $params = array();
            $email = $where['email'];
            if (isset($where['host'])) {
                $params['host'] = $where['host'];
            }
            if (isset($where['port'])) {
                $params['port'] = $where['port'];
            }
            if (isset($where['auth'])) {
                $params['auth'] = $where['auth'];
                if (isset($where['username'])) {
                    $params['username'] = $where['username'];
                }
                if (isset($where['password'])) {
                    $params['password'] = $where['password'];
                }
            }
            if (sizeof($params) > 0) {
                if (isset($where['smtp_debug'])) {
                    $params['debug'] = $where['smtp_debug'];
                }
            	//send message
                $e = $this->sendAlert($email, $params, $headers, $email_message);
            } else {
                // it was only 'email' given => as normal user
                $common[] = $email;
            }
        }
    	
    	if (!$common) { // nothing left to do
    	   return true;
    	}
    	//send message to normal users and return result
    	$params = array();
        if (isset($options['SMTP_default'])) {
            $params = $options['SMTP_default'];
        }
        if (isset($options['smtp_debug'])) {
            $params['debug'] = $options['smtp_debug'];
        }
        return $this->sendAlert($common, $params, $headers, $email_message);
    }
    /** 
     * function sendAlert
     *
     * Sends the specified results to the specified SMTP server
     * Returns true on success, PEAR_Error object on failure
     *
     * @access private
     * @param mixed email(s) address(es)
     * @param array smtp server parameters
     * @param array headers
     * @param string message
     * @return mixed
     */
    function sendAlert($email, $params, $headers, $email_message) 

    {
        $mailer =& Mail::factory('smtp', $params);
        if (PEAR::isError($mailer))   {
            return $mailer;
        }
        $headers['To'] = $email;
        $smtp_client =& $this->_alert;
    	
    	//send message and return result
    	return $mailer->send($email, $headers, $email_message);
    }
}
