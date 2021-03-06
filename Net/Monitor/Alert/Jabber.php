<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Remote service monitor alerter thru Jabber
 *
 * WARNING: THIS IS AN EXPERIMENTAL PROOF OF CONCEPT SO FAR. THIS METHOD SENDS 
 * THE LOGIN AND PASSWORD AS PLAIN TEXT AND SHOULD NOT BE CONSIDERED SECURE.
 * 
 * PHP version 5
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
 * @copyright 2005-2007 Robert Peake
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_Monitor
 */
/**
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/** 
 * class Net_Monitor_Alert_Jabber
 *
 * @category Net
 * @package  Net_Monitor
 * @author   Robert Peake <cyberscribe@php.net>
 * @author   Bertrand Gugger <bertrand@toggg.com>
 * @license  http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link     http://pear.php.net/package/Net_Monitor
 * @access   public
 */
class Net_Monitor_Alert_Jabber extends Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access protected
     */
    protected $_service = 'Jabber';

    /**
     * The alert object to be used (if any)
     *
     * @var object $_alert
     * @access protected
     */
    protected $_alert = null;

    /**
     * The default port to be used
     *
     * @var int $_port
     * @access protected
     */
    protected $_port = 5222;

    /**
     * The resource name to specify to the server
     *
     * @var string $_resource
     * @access protected
     */
    protected $_resource = 'Net_Monitor_Alert_Jabber';

    /**
     * Any socket error numbers
     *
     * @var int $_sockErr
     * @access protected
     */
    protected $_sockErr = 0;

    /**
     * Any socket error messages
     *
     * @var string $_sockErrMsg
     * @access protected
     */
    protected $_sockErrMsg = false;

    /**
     * Max expected server response length
     *
     * @var string $_maxResponseLength
     * @access protected
     */
    protected $_maxResponseLength = 4096;

    /** 
     * Sends the alerts thru the specified Jabber servers and accounts
     *
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
     * @param array $server       Jabber server connection/authentication options
     * @param array $result_array results to send
     * @param array $options      standard Net_Monitor options
     *
     * @return mixed true or PEAR_Error
     * @access public
     */
    public function alert($server, $result_array, $options=array()) 
    {
        $im_message = '';
        if (isset($options['alert_line'])) {
            $model_line = $options['alert_line'];
        } else {
            $model_line = '%h: %s: %m';
        }

        foreach ($result_array as $host=>$services) {
            foreach ($services as $service=>$result) {
                $im_message .= str_replace(array('%h', '%s',    '%c',      '%m'),
                                           array($host, $service, $result[0], $result[1]),
                                           $model_line)."\r\n";
            }
        }

        foreach ($server as $user=>$where) {
            if (!is_array($where)) {
                throw new Net_Monitor_Exception('server paramaters are not in an array -- unable to send alert');
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
            $this->sendAlert($server_addr, $recipient, $login, $password, $im_message);
        }

        return true;
    } // alert()

    /**
     * Sends the specified results to the specified Jabber server
     *
     * Returns true on success, PEAR_Error object on failure
     *
     * @param string $server    Jabber server
     * @param string $recipient Jabber recipient
     * @param string $login     Jabber sender login
     * @param string $password  Jabber sender password
     * @param string $message   message to send
     *
     * @return mixed
     * @access public
     */
    public function sendAlert($server, $recipient, $login, $password, $message)
    {
        if (!is_string($server) || !is_string($recipient) || !is_string($login) || !is_string($password) ||!is_string($message)) {
            throw new Net_Monitor_Exception('Net_Monitor_Alert_Jabber received incorrect arguments. server is '.gettype($server).', recipient is '.gettype($recipient).', login is '.gettype($login).', password is '.gettype($password).', message = '.gettype($message));
        }

        if ((strlen($server) == 0) || (strlen($recipient) == 0) || (strlen($login) == 0) || (strlen($password) == 0) || (strlen($message) == 0)) {
            throw new Net_Monitor_Exception("Net_Monitor_Alert_Jabber received insufficeint arguments. server = $server, recipient = $recipient, login = $login, password = $password, message = $message");
        }

        //send Jabber alert here
        $init  = '<?xml version=\'1.0\'?>';
        $init .= '<stream:stream to=\'jabber.org\' xmlns=\'jabber:client\' xmlns:stream=\'http://etherx.jabber.org/streams\' version=\'1.0\'>';

        $auth_format = '<iq type=\'set\' id=\'%s\'><query xmlns=\'jabber:iq:auth\'><username>%s</username><password>%s</password><resource>%s %s</resource></query></iq>';

        $message_format = '<message id=\'%s\' to=\'%s\' type=\'normal\' xml:lang=\'en\'><body>%s</body></message>';

        $starttls_format = '<starttls xmlns=\'%s\'/>';

        $sockErr    = false;
        $sockErrMsg = false;

        $fp = fsockopen('tcp://'.$server, $this->_port, $sockErr, $sockErrMsg);
        fwrite($fp, $init);
        sleep(1); //give it time to respond to the init

        if ($sockErr || $sockErrMsg) {
            $this->_sockErr    = $sockErr;
            $this->_sockErrMsg = $sockErrMsg;
            throw new Net_Monitor_Exception("Socket error $sockErr: $sockErrMsg");
        }

        $response = fread($fp, $this->_maxResponseLength);
        if ($response) {
            preg_match("/id='(\w+)'.*<starttls xmlns='(\S+)'/", $response, $response_array);

            list(, $id, $xmlns) = $response_array;

            if (!$id) {
                $id = md5(time() . $resource);
            }

            if (strstr($xmlns, 'tls') && version_compare(phpversion(), '5.1.0', 'ge')) {
                //attempt EXPERIMENTAL tls negotiation
                $starttls = sprintf($starttls_format, $xmlns);
                fwrite($fp, $starttls);
                stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }

            $auth = sprintf($auth_format, $id, $login, $password, $resource, md5($resource));
            fwrite($fp, $auth);
            sleep(1); //give it some time to digest the authorization

            $auth_response = fread($fp, $this->_maxResponseLength);

            $response .= $auth_response;

            if (strstr($xmlns, 'tls') && version_compare(phpversion(), '5.1.0', 'ge')) {
                stream_socket_enable_crypto($fp, false, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }

            if (preg_match('/<iq.*type=\'result\'/', $auth_response)) {
                $message = sprintf($message_format, $id, $recipient, $message);
                fwrite($fp, $message);

                $message_response = fread($fp, $this->_maxResponseLength);
                if ($message_response) {
                    //panic, this is probably an error
                    fwrite($fp, '</stream:stream>');
                    fclose($fp);
                    throw new Net_Monitor_Exception($message_response);
                }
            }
        } else {
            throw new Net_Monitor_Exception('No response from '.$server);
        }
        fwrite($fp, '</stream:stream>');
        $response .= fread($fp, $this->_maxResponseLength);
        fclose($fp);
    }
} // end class Net_Monitor_Alert_Jabber
