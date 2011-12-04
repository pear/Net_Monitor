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
 * @copyright 2004-2007 Robert Peake
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_Monitor
 * @since     File available since Release 0.0.6
 */
require_once 'Net/Monitor/Exception.php';

/**
 * class Net_Monitor
 *
 * @category Net
 * @package  Net_Monitor
 * @author   Robert Peake <cyberscribe@php.net>
 * @author   Bertrand Gugger <bertrand@toggg.com>
 * @license  http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link     http://pear.php.net/package/Net_Monitor
 * @access   public
 */
class Net_Monitor
{
    /**
     * Array of services to check 'url' => array('services')
     *
     * @access protected
     * @var array $_services
     */
    protected $_services = array();

    /**
     * Array of alerts to be sent organized by alerter protocols
     *
     * @access protected
     * @var array $_alerts
     */
    protected $_alerts = array();

    /**
     * Array of options to be used in the current monitoring session
     *
     * @access protected
     * @var array $_options
     */
    protected $_options = array('state_directory' => '/tmp/',
                          'state_file'      => 'Net_Monitor_State',
                          'subject_line'    => 'Net_Monitor Alert',
                          'alert_line'      => '%h: %s: %m',
                          'notify_change'   => 1,
                          'notify_ok'       => 1,
                          'smtp_debug'      => false);
    /**
     * Array of client objects to be used when testing a service
     *
     * @access protected
     * @var array $_clients
     */
    protected $_clients = array();

    /**
     * Array of alerter objects to be used when sending alerts
     *
     * @access protected
     * @var array $_alerters
     */
    protected $_alerters = array();

    /**
     * Array of results from most recent service check
     *
     * @access protected
     * @var array $_results
     */
    protected $_results = array();

    /**
     * Array of differences in results between previous session and this session
     *
     * @access private
     * @var array $_results_diff
     */
    var $_results_diff = array();

    /**
     * function Net_Monitor
     *
     * @param array $services services to check 'url' => array('services')
     * example: array('example.com' => array('SMTP', 'HTTP', 'HTTPS'),
     *                'example.net' => array('DNS', 'FTP'))
     *
     * @param array $alerts   alerts to be sent organized by alerter protocols
     * as array('SMTP' => array(SMTP_adressees), 'SMS' => array(SMS_adressees))
     * If this array is empty, alerts will be only printed and nothing sent
     *
     * SMTP_adressees is a simple string email, then $options['SMTP_default']
     * will be used to configure the SMTP sender, or can itself be an array of
     * options for a prioritary adressee (useful to monitor SMTP itself)
     * in this case, the adressee is the 'email' => 'email@adress.com' element.
     * see Mail for these options
     *
     * SMS_adressees is a simple string phone number, then $options['SMS_default']
     * will be used to configure the SMS sender, or can itself be an array of
     * options for a prioritary adressee (useful to specify alternate provider)
     * in this case, the adressee is the 'phone_number' => '0123456789' element.
     * see Net_SMS for these options
     *
     * @param array $options  options to be used in the current monitoring session
     * Options (default) are:
     * + state_directory - the directory where the state file gets saved ('/tmp/')
     * + state_file - the name of the state file ('Net_Monitor_State')
     * + subject_line - the subject line of the alert message ('Net_Monitor Alert')
     * + alert_line - the format string for the alert ('%h: %s: %m') where:
     *     %h = host
     *     %s = service
     *     %m = message
     *     %c = code
     * + notify_change - send alerts only on state change (1)
     * + notify_ok - send an alert when a service returns to a code 200 state (1)
     * + smtp_debug - send debugging output to STDOUT for the SMTP alert (false)
     * + from_email - who is the sender for the SMTP alert ('pear.Net_Monitor')
     * + SMTP_default - array of options for Mail_SMTP used for normal adressees (array())
     * + sms_debug - send debugging output to STDOUT for the SMS alert (false)
     * + sms_from - who is the sender for the SMS alert, ('Net_Monitor')
     * + SMS_default - array of options for Net_SMS used for normal adressees (array())
     *
     * @access public
     * @return void
     */
    public function __construct($services = array(), $alerts = array(), $options = array())
    {
        if (is_array($options) && sizeof($options) > 0) {
            $this->setOptions($options);
        }

        if (is_array($services) && sizeof($services) > 0) {
            $this->setServices($services);
        }

        if (is_array($alerts) && sizeof($alerts) > 0) {
            $this->setAlerts($alerts);
        }
    }

    /**
     * function setOptions
     *
     * <p>Sets additional options for the class</p>
     * <p>Merges input array ($options) with $this->_options</p>
     *
     * @param array $options Options to set
     *
     * @access public
     * @return void
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $this->_options[$key] = $value;
        }
    }

    /**
     * function setServices
     *
     * <p>Sets the services to monitor for the class</p>
     * <p>Overwrites $_services with input array ($services)</p>
     * <p>Net_Monitor_Services are of the form: <br />
     * <pre>
     * $services = array('foo.example.com'=>array('SMTP','DNS'),
     *                   'bar.example.com'=>array('HTTP','FTP','DNS'));
     * </pre>
     *
     * @param array $services Services per domain
     *
     * @access public
     * @return void
     */
    public function setServices($services)
    {
        $this->_services = $services;
    }

    /**
     * function setAlerts
     *
     * <p>Sets the alerts for the class</p>
     * <p>Overwrites $_alerts with input array ($alerts)</p>
     * <p>Net_Monitor_Alerts are of the form: <br />
     * <pre>
     * $alerts = array('User1' => array('SMTP'=>'user1@example.com'),
     *                 'User2' => array('SMTP'=>'user2@example.com'));
     * </pre>
     *
     * @param array $alerts Alerts
     *
     * @return void
     */
    public function setAlerts($alerts)
    {
        foreach ($alerts as $user => $parAlert) {
            foreach ($parAlert as $proto => $param) {
                if (!isset($this->_alerts[$proto])) {
                    $this->_alerts[$proto] = array();
                }
                $this->_alerts[$proto][$user] = $param;
            }
        }
    }

    /**
     * function checkAll
     *
     * Checks all services and sends all alerts.
     *
     * @access public
     * @return void
     */
    public function checkAll()
    {
        //initialize the _results and _results_diff arrays
        $this->_results      = array();
        $this->_results_diff = array();

        //check all services and populate the _results array
        if (is_array($this->_services) && sizeof($this->_services) > 0) {

            //load client objects once and only once per service
            $this->loadClients(); 

            foreach ($this->_services as $server => $services) {
                foreach ($services as $service) {
                    $result = $this->check($server, $service);
                    if ($result) {
                        $this->_results[$server][$service] = $result;
                    }
                }
            }
        } else {
            throw new Net_Monitor_Excepton('No services found to check.');
        }

        if (is_array($this->_results) && sizeof($this->_results) > 0) {
            $last_state = $this->getState();
            $this->saveState();
            $this->_results_diff = $this->stateDiff($last_state);
            /* UNCOMMENT THE FOLLOWING TO DEBUG DIFFERENTIALS
            //  print "Last state: \n\n";
            //  print_r($last_state);
            //  print "Current state: \n\n";
            //  print_r($this->_results);
            //  print "Difference: \n\n";
            //  print_r($this->_results_diff);
            */

        }

        if (is_array($this->_results_diff) && sizeof($this->_results_diff) > 0) {
            if (is_array($this->_alerts) && sizeof($this->_alerts) > 0) {
                $this->loadAlerters();
                //loop through alerts, sending the result message
                foreach ($this->_alerts as $method => $alert_array) {
                    $this->alert($method, $alert_array);
                }
            } else {
                //nobody to alert? print the result to STDOUT
                foreach ($this->_results_diff as $host=>$results) {
                    foreach ($results as $service=>$result) {
                        $this->printAlert($host, $service, $result);
                    }
                }
            }
            return true;
        }

        return false;

    }

    /**
     * function check
     *
     * Check a single service ($service) on a single server ($server)
     *
     * @param mixed  $server  Host
     * @param string $service Service
     *
     * @access public
     * @return mixed
     */
    public function check($server, $service)
    {
        $client = $this->_clients[$service];
        return $client->check($server);
    }

    /**
     * function loadClients
     *
     * Load all clients into the Net_Monitor object so that
     * each type of service client is only instantiated once.
     *
     * @access public
     * @return void
     */
    public function loadClients()
    {
        $services_array = array_values($this->_services);
        $types_array    = array_keys($this->_clients);

        foreach ($services_array as $sub_array) {
            foreach ($sub_array as $type) {
                if (!in_array($type, $types_array)) {
                    $types_array[] = $type;

                    $this->_clients[$type] = $this->getClient($type);
                }
            }
        }
    }

    /**
     * function loadAlerters
     *
     * Load alert objects into Net_Monitor so that each type of alert
     * is only used once.
     *
     * @access public
     * @return void
     */
    public function loadAlerters()
    {
        $alerts_array = array_keys($this->_alerts);
        foreach ($alerts_array as $alert_type) {
            if (!isset($this->_alerters[$alert_type])) {
                $this->_alerters[$alert_type] = &$this->getAlerter($alert_type);
            }
        }
    }

    /**
     * function getClient
     *
     * Returns a client of the type specified in $type. <em>Note:
     * does not check to see if this client has already been
     * loaded into Net_Monitor::clients - that is handled elsewhere.</em>
     *
     * @param string $type Class / Type of service to fetch
     *
     * @access public
     * @return object
     */
    public function getClient($type)
    {
        include_once "Net/Monitor/Service/$type.php";
        $service = "Net_Monitor_Service_$type";
        return new $service();
    }

    /**
     * function getAlerter
     *
     * Returns an alerter of the type specified by $type. <em>Note:
     * does not check to see if this alerter has already been
     * loaded into Net_Monitor::alerters - that is handled elsewhere.</em>
     *
     * @param string $type Class / Type of alerter to fetch
     *
     * @access public
     * @return object
     */
    public function getAlerter($type)
    {
        include_once "Net/Monitor/Alert/$type.php";
        $alerter = "Net_Monitor_Alert_$type";
        return new $alerter();
    }

    /**
     * function alert
     *
     * Send a single alert specified in $method to the server specified in $server
     *
     * @param string $method Method
     * @param mixed  $server Server
     *
     * @return mixed
     * @access public
     */
    public function alert($method, $server)
    {
        $alerter = $this->_alerters[$method];

        // don't die on error but send a message
        $ret = $alerter->alert($server, $this->_results_diff, $this->_options);

        return $ret;
    }

    /**
     * function saveState
     *
     * Saves the current $_results array to the directory specified in
     * $_options['state_directory'] as a file named $_options['state_file'].
     *
     * If an array ($results) is passed to the function, that array is saved as state,
     * otherwise this function acts upon $_results.
     *
     * @param array $results Results
     *
     * @access public
     * @return void
     */
    public function saveState($results = null)
    {
        $options = $this->_options;

        $path = $options['state_directory'];
        $file = $options['state_file'];

        if (!is_writable($path)) {
            throw new Net_Monitor_Exception($path.' is not writeable');
        }

        if (file_exists($path.$file)) {
            if (!is_writable($path.$file)) {
                throw new Net_Monitor_Exception($path.$file.' exists but is not writeable');
            }
        }

        $fp = @fopen($path.$file, 'w');
        if (is_null($results)) {
            $results = $this->_results;
        }

        $line = serialize($results);

        @fwrite($fp, $line);
        @fclose($fp);
    }

    /**
     * function getState
     *
     * Retrieves previous state information from the directory specified in
     * $_options['state_directory'] via a  file named $_options['state_file']
     *
     * @access public
     * @return array
     */
    public function getState()
    {
        $my_line = "";
        $options = $this->_options;

        $path = $options['state_directory'];
        $file = $options['state_file'];

        if (file_exists($path . $file)) {
            if (!is_readable($path.$file)) {
                throw new Net_Monitor_Exception($path.$file.' exists but is not readable');
            }
        } else {
            return array();
        }

        $fp = @fopen($path.$file, 'r');

        while (!feof($fp)) {
            $my_line .= fgets($fp, 4096);
        }

        $return_array = unserialize($my_line);
        @fclose($fp);
        return $return_array;
    }

    /**
     * function stateDiff
     *
     * Computes the difference between the $primary and $secondary
     * arrays representing state, i.e. all values in primary that
     * are not already in secondary.
     *
     * Also returns an OK status for values in secondary that are
     * not in primary unless
     * $_options['notify_ok'] is set to false.
     *
     * Returns values in primary whose code value differs
     * from values in secondary unless
     * $_options['notify_change'] is set to false.
     *
     * @param array $secondary states to compare to current
     *
     * @access private
     * @return array
     */
    public function stateDiff($secondary)
    {
        $return_array = array();

        foreach ($this->_results as $host => $services) {
            foreach ($services as $service => $result) {

                // it's a new host/service so to be announced
                if (!isset($secondary[$host][$service])) {
                    $return_array[$host][$service] = $result;
                    continue;
                }

                // host and service identical in current and secondary
                if ($result[0] !== $secondary[$host][$service][0] && $this->_options['notify_change']) {
                    //notify_change on; move to return
                    $return_array[$host][$service] = $result;

                    continue;
                }


                // anyway unset so ok to withdrawn services to the end
                unset($secondary[$host][$service]);
            }
        }

        if ($this->_options['notify_ok']) {
            foreach ($secondary as $host => $services) {
                foreach ($services as $service => $result) {
                    //remaining states in secondary added OK to return
                    $return_array[$host][$service] = array(200, $result[1]);
                }
            }
        }

        return $return_array;
    }

    /**
     * function resetState
     *
     * Resets the results and results differential arrays
     * and deletes the state file.
     *
     * Returns true if the file has been deleted or never existed
     * in the first place; false otherwise.
     *
     * @access public
     * @return boolean
     */
    public function resetState()
    {
        $this->_results      = array();
        $this->_results_diff = array();

        $options = $this->_options;

        $path = $options['state_directory'];
        $file = $options['state_file'];

        if (file_exists($path.$file)) {
            if (!is_writable($path.$file)) {
                throw new Net_Monitor_Exception($path.$file.' exists but is not writeable');
            }
            return unlink($path.$file);
        }

        return true;        
    }

    /**
     * function resetHostState
     *
     * Resets the state for a single host ($host). Optionally takes in a
     * second parameter, $service which maybe an array, whereby the function only
     * resets the results for that/those particular host/service test.
     *
     * @param string $host    Host
     * @param mixed  $service Service
     *
     * @return void
     * @access public
     */
    public function resetHostState($host, $service = null)
    {
        $last_state = $this->getState();
        if (!isset($last_state[$host])) {
            return;
        }

        if ($service != null) {
            if (is_array($service)) {
                foreach ($service as $serelt) {
                    unset($last_state[$host][$serelt]);
                }
            } else {
                unset($last_state[$host][$service]);
            }
        } else {
            unset($last_state[$host]);
        }

        $this->saveState($last_state);
    }

    /**
     * function printAlert
     *
     * Prints the alert for a host/service
     * to STDOUT. Formats the alert according to $_options['alert_line'].
     *
     * @param string $host    Host name
     * @param string $service Service
     * @param array  $result  (code, message)
     *
     * @return void
     * @access public
     */
    public function printAlert($host, $service, $result)
    {
        $alert_line = '%h: %s: %m';

        if ($this->_options['alert_line']) {
            $alert_line = $this->_options['alert_line'];
        }

        print str_replace(array('%h', '%s',    '%c',      '%m'),
                          array($host, $service, $result[0], $result[1]),
                          $alert_line)."\r\n";
    }
}
