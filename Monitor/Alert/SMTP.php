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
 * @version 0.0.7
 */
/**
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/**
 * requires and uses the Net_SMTP class to send SMTP alerts
 */
require_once 'Net/SMTP.php';
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
        $this->_alert = new Net_SMTP();
    }
    /** 
     * function alert
     *
     * Sends the specified results to the specified SMTP server
     * <ul>
     * <li> $server is the SMTP server
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
        if ($options['subject_line']) {
            $subject = $options['subject_line'];
        } else {
            PEAR::raiseError('Using default subject', null, PEAR_ERROR_EXCEPTION, E_USER_WARNING);
            $subject = 'Net_Monitor Alert';
        }
        $server_array = explode('@',$server);
        $rcpt = $server_array[0];
        $server_addr = $server_array[1];
        $this->_client = new Net_SMTP($server_addr);
        $smtp_client = $this->_client;
        if ($options['smtp_debug']) {
            $smtp_client->setDebug(true);
        }
    	$sizeof_result_array = sizeof($result_array);
    	for($i=0;$i<$sizeof_result_array;$i++) {
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
    	$smtp_client->quotedata(&$email_message);
        $e = $smtp_client->connect();
        if (!PEAR::isError($e)) {
            $e = $smtp_client->mailFrom($server);
            $e = $smtp_client->rcptTo($server);
            $e = $smtp_client->data('Subject: '.$subject."\r\n\r\n".$email_message);
            $smtp_client->disconnect();
        }
        if (PEAR::isError($e)) {
            return $e;
        } else { 
            return true;
    	}
    }
}
?>
