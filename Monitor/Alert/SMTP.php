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
 * @version 0.2.0
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
     * Sends the alerts to the specified SMTP servers
     * First each dedicaded prioritary user (with own smtp parameters as array)
     * <li> $server is an array of user=>parameter
     *      where parameter is either an array or string.
     *      A string should be a fully qualified email address (common user)
     *      An array describes the SMTP server as required my Mail/smtp.php
     *      and must contain an extra 'email' key (prioritary user)
     *      the SMTP server description contains optionally:
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
                    array($host,$service,$result[0],$result[1]),
                    $model_line)."\r\n";
            }
        }
        if (!$email_message) { // nothing to do
            return true;
        }

        //construct header's subject and from
        $headers = array(); 
        if ($options['subject_line']) {
            $headers['Subject'] = $options['subject_line'];
        } else {
            PEAR::raiseError('Using default subject',
                 null, PEAR_ERROR_EXCEPTION, E_USER_WARNING);
            $headers['Subject'] = 'Net_Monitor Alert';
        }
        if (isset($options['from_email'])) {
            $headers['From'] = $options['from_email'];
        } else {
            $headers['From'] = '';
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
            if (is_string($where['host'])) {
                $params['host'] = $where['host'];
            }
            if (is_int($where['port'])) {
                $params['port'] = $where['port'];
            }
            if ($where['auth']) {
                $params['auth'] = $where['auth'];
                if (is_string($where['username'])) {
                    $params['username'] = $where['username'];
                }
                if (is_string($where['password'])) {
                    $params['password'] = $where['password'];
                }
            }
            if (sizeof($params) > 0) {
            	//send message and return result error if any
                $e = $this->sendAlert($email, $params, $headers, $email_message);
                if (PEAR::isError($e)) {
                    return $e;
                }            
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
        $mailer =& Mail::factory('smtp',$params);
        if (PEAR::isError($mailer))   {
            return $mailer;
        }
        $headers['To'] = $email;
        $smtp_client =& $this->_alert;
    	
    	//send message and return result
    	return $mailer->send($email, $headers, $email_message);
    }
}
?>
