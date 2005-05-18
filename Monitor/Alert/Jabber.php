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
// Remote service monitor alerter thru Jabber - WARNING: THIS IS AN EXPERIMENTAL PROOF OF CONCEPT SO FAR. THIS METHOD SENDS THE LOGIN AND PASSWORD AS PLAIN TEXT AND SHOULD NOT BE CONSIDERED SECURE.
/**
 * @package Net_Monitor
 * @author Robert Peake <robert@peakepro.com>
 * @copyright 2005
 * @license http://www.php.net/license/3_0.txt
 * @version 0.1.1
 */
/**
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/** 
 * class Net_Monitor_Alert_Jabber
 *
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Alert
 */
class Net_Monitor_Alert_Jabber extends Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'Jabber';
    /**
     * The alert object to be used (if any)
     *
     * @var object $_alert
     * @access private
     */
    var $_alert = null;
    /**
     * The default port to be used
     *
     * @var int $_port
     * @access private
     */
    var $_port = 5222;
    /**
     * The resource name to specify to the server
     *
     * @var string $_resource
     * @access private
     */
    var $_resource = 'Net_Monitor_Alert_Jabber';
    /**
     * Any socket error numbers
     *
     * @var int $_sockErr
     * @access private
     */
    var $_sockErr = 0;
    /**
     * Any socket error messages
     *
     * @var string $_sockErrMsg
     * @access private
     */
    var $_sockErrMsg = false;
    /**
     * Max expected server response length
     *
     * @var string $_maxResponseLength
     * @access private
     */
    var $_maxResponseLength = 4096;
    /** 
     * function Net_Monitor_Alert_Jabber
     *
     * @access public
     */
    function Net_Monitor_Alert_Jabber()

    {
        //nothing to initialize
    }
    /** 
     * function alert
     *
     * Sends the alerts thru the specified Jabber servers and accounts
     * <li> $server is an array of key=>value
     *      where value is a string.
     *      Server defines these keys:
     *      <ul>
     *      <li> server - The server to connect to. Mandatory.
     *      <li> recipient - The recipient of the message. Mandatory.
     *      <li> login - The login to use for Jabber authentication. Mandatory.
     *      <li> password - The password to use for Jabber authentication. Mandatory.
     *      </ul>
     * <li> $results is the array of results to send
     * </ul>
     * Returns true on success, PEAR_Error object on failure
     *
     * @access private
     * @param array server
     * @param array results
     * @param array options
     * @return mixed true or PEAR_Error
     */
    function alert($server,$result_array,$options=array()) 
    {
        $im_message = '';
        if (isset($options['alert_line'])) {
            $model_line = $options['alert_line'];
        } else {
            $model_line = '%h: %s: %m';
        }
        foreach ($result_array as $host=>$services) {
            foreach ($services as $service=>$result) {
            $im_message .= str_replace(
                array('%h', '%s',    '%c',      '%m'),
                array($host,$service,$result[0],$result[1]),
                $model_line)."\r\n";
            }
        }
        foreach ($server as $user=>$where) {
            if (!is_array($where)) {
                PEAR::raiseError(
                    'server paramaters are not in an array -- unable to send alert');
                return false;
            }
            if (is_string($where['server'])) {
                $server_addr = $where['server'];
            }
            if (is_string($where['recipient'])) {
                $recipient = $where['recipient'];
            }
            if (is_string($where['login'])) {
                $login = $where['login'];
            }
            if (is_string($where['password'])) {
                $password = $where['password'];
            }
            $e = $this->sendAlert($server_addr, $recipient, $login, $password, $im_message);
            if (Pear::isError($e)) {
                return $e;
            }
        }
        return true;
    } // alert()
    /**
     * function sendAlert
     *
     * Sends the specified results to the specified SMTP server
     * Returns true on success, PEAR_Error object on failure
     *
     * @access private
     * @param string server
     * @param string recipient
     * @param string login
     * @param string password
     * @param string message
     * @return mixed
     */
    function sendAlert($server, $recipient, $login, $password, $message)
    {
        if (!is_string($server) || !is_string($recipient) || !is_string($login) || !is_string($password) ||!is_string($message)) {
            return new Pear_Error('Net_Monitor_Alert_Jabber received incorrect arguments. server is '.gettype($server).', recipient is '.gettype($recipient).', login is '.gettype($login).', password is '.gettype($password).', message = '.gettype($message));
        } else if ((strlen($server) == 0) || (strlen($recipient) == 0) || (strlen($login) == 0) || (strlen($password) == 0) || (strlen($message) == 0)) {
            return new Pear_Error("Net_Monitor_Alert_Jabber received insufficeint arguments. server = $server, recipient = $recipient, login = $login, password = $password, message = $message");
        } else {
            //send Jabber alert here
            $init = '<?xml version=\'1.0\'?>';
            $init .= '<stream:stream to=\'jabber.org\' xmlns=\'jabber:client\' xmlns:stream=\'http://etherx.jabber.org/streams\' version=\'1.0\'>';
            $auth_format = '<iq type=\'set\' id=\'%s\'><query xmlns=\'jabber:iq:auth\'><username>%s</username><password>%s</password><resource>%s %s</resource></query></iq>';
            $message_format = '<message id=\'%s\' to=\'%s\' type=\'normal\' xml:lang=\'en\'><body>%s</body></message>';
            $starttls_format = '<starttls xmlns=\'%s\'/>';
            $sockErr = false;
            $sockErrMsg = false;
            $fp = fsockopen('tcp://'.$server, $this->_port, $sockErr, $sockErrMsg);
            fwrite($fp, $init);
            sleep(1); //give it time to respond to the init
            if ($sockErr || $sockErrMsg) {
                $this->_sockErr = $sockErr;
                $this->_sockErrMsg = $sockErrMsg;
                return new Pear_Error("Socket error $sockErr: $sockErrMsg");
            }
            $response .= fread($fp,$this->_maxResponseLength);
            if ($response) {
                preg_match("/id='(\w+)'.*<starttls xmlns='(\S+)'/",$response,$response_array);
                $id = $response_array[1];
                $xmlns = $response_array[2];
                if (!$id) {
                    $id = md5(time().$resource);
                }
                if (strstr($xmlns, 'tls') && version_compare(phpversion(),'5.1.0','ge')) {
                    //attempt EXPERIMENTAL tls negotiation
                    $starttls = sprintf($starttls_format, $xmlns);
                    fwrite($fp, $starttls);
                    stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                }
                $auth = sprintf($auth_format, $id, $login, $password, $resource, md5($resource));
                fwrite($fp, $auth);
                sleep(1); //give it some time to digest the authorization
                $auth_response = fread($fp,$this->_maxResponseLength);
                $response .= $auth_response;
                if (strstr($xmlns, 'tls') && version_compare(phpversion(),'5.1.0','ge')) {
                    stream_socket_enable_crypto($fp, false, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                }
                if (preg_match('/<iq.*type=\'result\'/',$auth_response)) {
                    $message = sprintf($message_format, $id, $recipient, $message);
                    fwrite($fp, $message);
                    $message_response = fread($fp,$this->_maxResponseLength);
                    if ($message_response) {
                        //panic, this is probably an error
                        fwrite($fp, '</stream:stream>');
                        fclose($fp);
                        return new Pear_Error($message_response);
                    }
                }
            } else {
                return new Pear_Error('No response from '.$server);
            }
            fwrite($fp, '</stream:stream>');
            $response .= fread($fp,$this->_maxResponseLength);
            fclose($fp);
        }
    }
} // end class Net_Monitor_Alert_Jabber
?>
