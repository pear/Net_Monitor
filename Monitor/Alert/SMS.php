<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2005 The PHP Group                                |
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
// Remote service monitor alerter thru SMS
/**
 * @package Net_Monitor
 * @author Robert Peake <robert@peakepro.com>
 * @copyright 2005
 * @license http://www.php.net/license/3_0.txt
 * @version 0.2.0
 */
/**
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/**
 * requires and uses the Net_SMS class for SMS alerts
 */
require_once 'Net/SMS.php';
/** 
 * class Net_Monitor_Alert_SMS
 *
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Alert
 */
class Net_Monitor_Alert_SMS extends Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'SMS';
    /**
     * The alert object to be used
     *
     * @var object $_alert
     * @access private
     */
    var $_alert = null;
    /** 
     * function Net_Monitor_Alert_SMS
     *
     * @access public
     */
    function Net_Monitor_Alert_SMS()

    {
        $this->_alert = new Net_SMS();
    }
    /** 
     * function alert
     *
     * Sends the alerts thru the specified SMS servers and accounts
     * <li> $server is an array of user=>parameter
     *      where parameter is either an array or string.
     *      A string should be a phone number (common user)
     *      An array describes the SMS server as required by Net/SMS.php
     *      and must contains an extra 'phone_number' key (prioritary user)
     *      SMS server description (extra or common option['SMS_default']) contains:
     *      <ul>
     *      <li> SMS_provider - The server to connect. Mandatory.
     *      <li> username - The username to use for SMS authentication. Mandatory.
     *      <li> password - The password to use for SMS authentication.
     *      <li> ... more, depending upon provider
     *      </ul>
     * <li> $results is the array of results to send
     * </ul>
     * Returns true on success, PEAR_Error object on failure
     *
     * @access private
     * @param mixed server
     * @param array results
     * @param array options
     * @return mixed true or PEAR_Error
     */
    function alert($server,$result_array,$options=array()) 

    {
        // max size of a message
        $max = 160;
        // construct $SMS_message from $result_array
        // prepare once the template
        if (isset($options['sms_line'])) {
            $alert_line = $options['sms_line'];
        } else {
            $alert_line = '%h(%s)>%c ';
        }
        // store each message case total > $max
        $SMS_array = array();
        // buid one message
        $SMS_message = '';
    	foreach ($result_array as $host=>$services) {
        	foreach ($services as $service=>$result) {
	    	    // insert the values
                $out = str_replace(
                    array('%h', '%s',    '%c',      '%m'),
                    array($host,$service,$result[0],$result[1]),
                    $alert_line)."\r\n";
	            // result line too long, store old, cut to max and store
	            if (strlen($out) >= $max) {
	                if ($SMS_message) {
	                    $SMS_array[] = $SMS_message;
	                    $SMS_message = '';
	                }
	                $SMS_array[] = substr($out, 0, $max);
	            } else {
	                // it will become too long, sore old first
	                if ((strlen($SMS_message) + strlen($out)) > $max) {
	                    $SMS_array[] = $SMS_message;
	                    $SMS_message = $out;
	                } else {
	                    $SMS_message .= $out;
	                }
	            }
            }
        }
        // rest in buffer ?
        if ($SMS_message) {
            $SMS_array[] = $SMS_message;
        }
        if (!$SMS_array) { // nothing to do
            return true;
        }

        //parse $server to group by SMS provider/subscriber and prepare params
        $toSend = $accPar = array();
        foreach ($server as $where) {
            // only phone as string specified ? => to cumulate
            if (is_string($where)) {
                $where = array_merge($options['SMS_default'],
                                     array('phone_number' => $where));
            } elseif (!is_array($where)) {
                PEAR::raiseError(
                    'user param is not a string or array -- unable to send alert');
                return false;
            }
            $SMS_provider = $where['SMS_provider'];
            $username = $where['username'];
            // first time for this provider
            if (!array_key_exists ($SMS_provider, $toSend)) {
                $toSend[$SMS_provider] = array();
                $accPar[$SMS_provider] = array();
            }
            // first time for this subscriber by this provider
            if (!array_key_exists ($username, $toSend[$SMS_provider])) {
                $toSend[$SMS_provider][$username] = array();
                // to ensure future compatibility with Net_SMS take everything
                $accPar[$SMS_provider][$username] = $where;
                unset($accPar[$SMS_provider][$username]['phone_number']);
                unset($accPar[$SMS_provider][$username]['SMS_provider']);
                unset($accPar[$SMS_provider][$username]['username']);
                $accPar[$SMS_provider][$username]['user'] = $username;
            }
            // store the SMS destination
            $toSend[$SMS_provider][$username][] = $where['phone_number'];
        }
    	$SMS = array();
        if (isset($options['sms_from'])) {
            $SMS['from'] = $options['sms_from'];
        } else {
            $SMS['from'] = 'Net_Monitor';
        }
        // loop on providers
        foreach ($toSend as $SMS_provider => $sublist) {
            // loop on subscribers
            foreach ($sublist as $username => $toList) {
                $SMS['to'] = $toList;
		        if (isset($options['sms_debug']) and $options['sms_debug']) {
					echo "{$username} by {$SMS_provider}\n";
					print_r($SMS);
					print_r($accPar[$SMS_provider][$username]);
					continue;
		        }
                $sender =& Net_SMS::factory($SMS_provider,
                                    $accPar[$SMS_provider][$username]);
                if (PEAR::isError($sender))   {
                    return $sender;
                }
                $i = 1;
                foreach ($SMS_array as $SMS_message) {
                    $SMS['id'] = $i;
                    $SMS['text'] = $SMS_message;
                	//send message and return result
                	$e = $sender->send($SMS);
                    if (PEAR::isError($e))   {
                        return $e;
                    }
                }
                
            }
        }
        return true;
    } // alert()
} // end class Net_Monitor_Alert_SMS
?>
