<?php
/**
 * Network service monitoring package
 *
 * A unified interface for checking the availability services on external 
 * servers and sending meaningful alerts through a variety of media if a 
 * service becomes unavailable.
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
 * @author    Bertrand Gugger <bertrand@toggg.com>
 * @copyright 2004-2007 Bertrand Gugger
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
 * requires and uses the Net_SMS class for SMS alerts
 * FATAL if Net_SMS is not installed
 */
require_once 'Net/SMS.php';
/** 
 * Net_Monitor_Alert_SMS
 *
 * It uses the global Net_Monitor options (default):
 * + sms_debug - send debugging output to STDOUT for the SMS alert (false)
 * + sms_from - who is the sender for the SMS alert, ('Net_Monitor')
 * + SMS_default - array of options for Net_SMS used for normal adressees (array())
 *
 * @category Net
 * @package  Net_Monitor
 * @author   Robert Peake <cyberscribe@php.net>
 * @author   Bertrand Gugger <bertrand@toggg.com>
 * @license  http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link     http://pear.php.net/package/Net_Monitor
 * @access   public
 * @see      Net_Monitor_Alert
 * @see      Net_SMS
 */
class Net_Monitor_Alert_SMS extends Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access protected
     */
    protected $_service = 'SMS';

    /**
     * The alert object to be used
     *
     * @var object $_alert
     * @access protected
     */
    protected $_alert = null;

    /** 
     * function Net_Monitor_Alert_SMS
     *
     * @access public
     */
    public function __construct()
    {
        $this->_alert = new Net_SMS();
    }

    /** 
     * function alert
     *
     * Sends the alerts thru the specified SMS servers and accounts
     * + $server is an array of user=>parameter
     *      where parameter is either an array or string.
     *      A string should be a phone number (common user)
     *      in this case global option SMS_default array will be used to configure Net_SMS
     *      An array describes the SMS server as required by Net/SMS.php
     *      and must contains an extra 'phone_number' key (prioritary user)
     *      SMS server description (extra or common option['SMS_default']) contains:
     *      + SMS_provider - The server to connect. Mandatory.
     *      + username - The username to use for SMS authentication. Mandatory.
     *      + password - The password to use for SMS authentication.
     *      + ... more, depending upon provider
     * + $results is the array of results to send
     * Returns true on success, PEAR_Error object on failure
     *
     * @param mixed $server       Server
     * @param array $result_array Results
     * @param array $options      Options
     *
     * @access public
     * @return mixed true or PEAR_Error
     */
    public function alert($server, $result_array, $options=array()) 
    {
        // max size of a message
        $max = 160;

        // construct $SMS_message from $result_array
        // prepare once the template
        $alert_line = $this->determineAlertLine($options);

        // store each message case total > $max
        $SMS_array = array();
        // buid one message
        $SMS_message = '';
        foreach ($result_array as $host => $services) {
            foreach ($services as $service => $result) {
                // insert the values
                $out = $this->parseResult($host, $service, $result, $alert_line);
                $this->bufferSMS($out, $max, $SMS_message, $SMS_array);
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
        foreach ($server as $where) {
            if (!is_string($where) && !is_array($where)) {
                throw new Net_Monitor_Exception('user param is not a string or array -- unable to send alert');
            }
        }

        $items = array();
        foreach ($server as $where) {
            // only phone as string specified ? => to cumulate
            if (is_string($where)) {
                $where = array_merge($options['SMS_default'],
                                     array('phone_number' => $where));
            }

            $items[] = $where;
        }

        list($toSend, $accPar) = $this->buildToSendList($items);


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

                $sender = $this->factory($SMS_provider,
                                    $accPar[$SMS_provider][$username]);

                if (PEAR::isError($sender)) {
                    return $sender;
                }

                $i = 1;
                foreach ($SMS_array as $SMS_message) {
                    $SMS['id']   = $i;
                    $SMS['text'] = $SMS_message;
                    //send message and return result
                    $sender->send($SMS);
                }
                
            }
        }
        return true;
    } // alert()

    protected function determineAlertLine($options) {
        if (isset($options['sms_line'])) {
            return $options['sms_line'];
        }

        return '%h(%s)>%c ';
    }

    protected function parseResult($host, $service, $result, $alert_line) {
        return str_replace(array('%h', '%s',    '%c',      '%m'),
                                   array($host, $service, $result[0], $result[1]),
                                   $alert_line)."\r\n";
    }

    protected function buildToSendList($items) {
        $toSend = array();
        $accPar = array();

        foreach ($items as $where) {
            if (empty($where['SMS_provider'])) {
                continue;
            }

            $SMS_provider = $where['SMS_provider'];

            // first time for this provider
            if (!array_key_exists($SMS_provider, $toSend)) {
                $toSend[$SMS_provider] = array();
                $accPar[$SMS_provider] = array();
            }
        }

        foreach ($items as $where) {
            if (empty($where['SMS_provider'])) {
                continue;
            }

            $SMS_provider = $where['SMS_provider'];

            if (empty($where['username'])) {
                continue;
            }

            $username     = $where['username'];

            // first time for this subscriber by this provider
            if (!array_key_exists($username, $toSend[$SMS_provider])) {
                $toSend[$SMS_provider][$username] = array();
                // to ensure future compatibility with Net_SMS take everything
                $accPar[$SMS_provider][$username] = $where;
                unset($accPar[$SMS_provider][$username]['phone_number']);
                unset($accPar[$SMS_provider][$username]['SMS_provider']);
                unset($accPar[$SMS_provider][$username]['username']);
                $accPar[$SMS_provider][$username]['user'] = $username;
            }
        }

         // store the SMS destination
        foreach ($items as $where) {
            if (empty($where['SMS_provider'])) {
                continue;
            }

            if (empty($where['username'])) {
                continue;
            }

            if (empty($where['phone_number'])) {
                continue;

            }
            $SMS_provider = $where['SMS_provider'];
            $username     = $where['username'];

            $toSend[$SMS_provider][$username][] = $where['phone_number'];
        }

        return array($toSend, $accPar);
    }

    /** @todo Refactor this to be 1 method, 1 purpose */
    protected function bufferSMS($out, $max, &$SMS_message, &$SMS_array) {
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

    /**
     * A quick and dirty way to allow testability.
     */
    protected function factory($SMS_provider, $username) {
        return Net_SMS::factory($SMS_provider, $username);
    }
} // end class Net_Monitor_Alert_SMS
