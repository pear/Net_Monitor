<?php

// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Robert Peake <robert@peakepro.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//
// Remote service monitor
/**
 * @package Net_Monitor
 * @author Robert Peake <robert@peakepro.com>
 * @copyright 2004
 * @license http://www.php.net/license/3_0.txt
 * @version 0.1.0
 */
/**
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/**
 * requires and uses the Mail class to send SMTP alerts
 */
require_once 'Mail.php';
/** 
 * class Net_Monitor_Alert_SMTP
 *
 * A class for sending alerts via SMTP (email)
 *
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Alert
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
     * Sends the specified results to the specified SMTP server
     * <ul>
     * <li> $server is the SMTP server as either an array or string.
     *      A string should be a fully qualified email address
     *      An array must contain an 'email' key and, optionally:
     *      <ul>
     *      <li> host - The server to connect. Default is localhost
     *      <li> port - The port to connect. Default is 25
     *      <li> auth - Whether or not to use SMTP authentication. Default is FALSE
     *      <li> username - The username to use for SMTP authentication.
     *      <li> password - The password to use for SMTP authentication.
     *      </ul>
     * <li> $results is the array of results to send
     * </ul>
     * Returns true on success, PEAR_Error object on failure
     *
     * @access private
     * @param mixed server
     * @param array results
     * @return mixed
     */
    function alert($server,$result_array,$options=array()) 

    {
        $email = '';
        $email_message = '';
        $headers = array(); 
        
        //construct subject
        if ($options['subject_line']) {
            $subject = $options['subject_line'];
        } else {
            PEAR::raiseError('Using default subject', null, PEAR_ERROR_EXCEPTION, E_USER_WARNING);
            $subject = 'Net_Monitor Alert';
        }
        $headers['Subject'] = $subject;
        
        //parse $server into $email and $params and
        //set $this->_alert to the new Mail::factory() object
        if (is_string($server)) {
            $email = $server;
            $this->_alert =& Mail::factory('smtp');
        } else if (is_array($server)) {
            $params = array();
            $email = $server['email'];
            if (is_string($server['host'])) {
                $params['host'] = $server['host'];
            }
            if (is_int($server['port'])) {
                $params['port'] = $server['port'];
            }
            if ($server['auth']) {
                $params['auth'] = $server['auth'];
                if (is_string($server['username'])) {
                    $params['username'] = $server['username'];
                }
                if (is_string($server['password'])) {
                    $params['password'] = $server['password'];
                }
            }
            if (sizeof($params) > 0) {
                $this->_alert =& Mail::factory('smtp',$params);
            } else {
                $this->_alert =& Mail::factory('smtp');
            }
        } else {
            PEAR::raiseEror('$server is not a string or array -- unable to send alert');
            return false;
        }
        $headers['To'] = $email;
        $headers['From'] = $email;
        $smtp_client =& $this->_alert;
        
        //construct $email_message from $result_array
    	$sizeof_result_array = sizeof($result_array);
    	for ($i=0;$i<$sizeof_result_array;$i++) {
            $result = $result_array[$i];
            $host = $result['host'];
            $service = $result['service'];
            $message = $result['message'];
            $code = $result['code'];
            if ($options['alert_line']) {
                $alert_line = $options['alert_line'];
            } else {
                $alert_line = '%h: %s: %m';
            }
            $alert_line = str_replace('%h',$host,$alert_line);
            $alert_line = str_replace('%s',$service,$alert_line);
            $alert_line = str_replace('%m',$message,$alert_line);
            $alert_line = str_replace('%c',$code,$alert_line);
            $email_message .= $alert_line."\r\n";
        }
    	
    	//send message and retun result
    	$e = $smtp_client->send($email, $headers, $email_message);
        if (PEAR::isError($e)) {
            return $e;
        } else { 
            return true;
    	}
    }
}
?>
