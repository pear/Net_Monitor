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
 * Net_Monitor
 *
 * A unified interface for checking the availability services on external 
 * servers and sending meaningful alerts through a variety of media if a 
 * service becomes unavailable.
 *
 * @package Net_Monitor
 * @author Robert Peake <robert@peakepro.com>
 * @copyright 2004
 * @license http://www.php.net/license/3_0.txt
 * @version 0.0.7
 * 
 */
/**
 * Requires the main Pear class
 */
require_once 'PEAR.php';

/**  
 * class Net_Monitor
 *
 * @access public
 * @package Net_Monitor
 */
class Net_Monitor 
{
    /**
     * Array of services to check
     *
     * @access private
     * @var array $_services
     */
    var $_services = array();
    /**
     * Array of alerts to be sent
     *
     * @access private
     * @var array $_alerts
     */
    var $_alerts = array();
    /**
     * Array of options to be used in the current monitoring session
     *
     * @access private
     * @var array $_options
     */
    var $_options = array('state_directory' => '/tmp/',
                          'state_file'      => 'Net_Monitor_State',
                          'subject_line'    => 'Net_Monitor Alert',
                          'alert_line'      => '%h: %s: %m',
                          'notify_change'   => 1,
                          'notify_ok'       => 1
                         );
    /**
     * Array of client objects to be used when testing a service
     *
     * @access private
     * @var array $_clients
     */
    var $_clients = array();
    /**
     * Array of alerter objects to be used when sending alerts
     *
     * @access private
     * @var array $_alerters
     */
    var $_alerters = array();
    /**
     * Array of results from most recent service check
     *
     * @access private
     * @var array $_results
     */
    var $_results = array();
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
     * @access public
     * @param array $services
     * @param array $alerts
     * @return void
     */
    function Net_Monitor($services=array(),$alerts=array(),$options=array()) 

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
     * @access public
     * @param array $options
     * @return void
     */
    function setOptions($options) 

    {
        foreach($options as $key => $value) {
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
     * @access public
     * @param array $services
     * @return void
     */
    function setServices($services) 

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
     * @param array $alerts
     * @return void
     */
    function setAlerts($alerts) 

    {
        $this->_alerts = $alerts;
    }
    /** 
     * function checkAll
     *
     * Checks all services and sends all alerts. 
     *
     * @access public
     * @return void
     */
    function checkAll() 

    {
        //initialize the _results and _results_diff arrays
        $this->_results = array();
        $this->_results_diff = array();
        //check all services and populate the _results array
        if (is_array($this->_services) && sizeof($this->_services) > 0) {
            $this->loadClients(); //load client objects once and only once per service
            foreach($this->_services as $server => $services) {
                for ($i=0; $i<sizeof($services); $i++) {
                    $service = $services[$i];
                    $result = $this->check($server,$service);
                    if ($result) {
                        $this->_results[] = $result;
                    }
                }
            }
        } else {
            pear::raiseError('No services found to check.');
        }
        if (is_array($this->_results) && sizeof($this->_results) > 0) {
            $last_state = $this->getState();
            $this->saveState();
            $this->_results_diff = $this->stateDiff($this->_results,$last_state);
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
                foreach($this->_alerts as $user => $alert_array) {
                    foreach($alert_array as $method => $server) {
                        $this->alert($method,$server);
                    }
                }
            } else {
                //nobody to alert? print the result to STDOUT
                $results = $this->_results_diff;
                $sizeof_results = sizeof($results);
                for($i=0; $i < $sizeof_results; $i++) {
                    $this->printAlert($results[$i]);
                }
            }
            return true;
        } else {
            return false;
        }
    }
    /** 
     * function check
     *
     * Check a single service ($service) on a single server ($server)
     *
     * @access public
     * @param mixed $server
     * @param string $service
     * @return mixed 
     */
    function check($server,$service) 

    {
        $client =& $this->_clients[$service];
        if (!is_object($client)) { //if an client has not yet been created for this service, create one
            $this->client[$service] =& $this->getClient($service);
            $client =& $this->_clients[$service];
        }
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
    function loadClients() 

    {
        $services_array = array_values($this->_services);
        $types_array = array_keys($this->_clients);
        for ($i=0;$i<sizeof($services_array);$i++) {
            $sub_array = $services_array[$i];
            for ($j=0; $j<sizeof($sub_array); $j++) {
                $type = $sub_array[$j];
                if (!in_array($type,$types_array)) {
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
    function loadAlerters() 

    {
        $alerts_array = array_values($this->_alerts);
        $current_alerters = array_keys($this->_alerters);
        for ($i=0; $i<sizeof($alerts_array); $i++) {
            $sub_array = $alerts_array[$i];
            foreach($sub_array as $alert_type => $method) {
                if (!in_array($alert_type,$current_alerters)) {
                    $current_alerters[] = $alert_type;
                    $this->_alerters[$alert_type] = $this->getAlerter($alert_type);
                 }
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
     * @access private
     * @param string $type
     * @return object
     */
    function &getClient($type) 

    {
        require_once "Net/Monitor/Service/$type.php";
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
     * @access private
     * @param string $type
     * @return object
     *
     */
    function &getAlerter($type) 

    {
        require_once "Net/Monitor/Alert/$type.php";
        $alerter = "Net_Monitor_Alert_$type";
        return new $alerter();
    }
    /** 
     * function alert
     *
     * Send a single alert specified in $method to the server specified in $server
     *
     * @access private
     * @param string $method
     * @param mixed $server
     *
     */
    function alert($method,$server) 

    {
        $alerter =& $this->_alerters[$method];
        if (!is_object($alerter)) { //if an alerter for this method has not already been created, create one
            $this->_alerters[$method] =& $this->getAlerter($method);
            $alerter =& $this->_alerters[$method];
        }
        return $alerter->alert($server,$this->_results_diff,$this->_options);
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
     * @access public
     * @param array $results
     * @return void
     *
     */
    function saveState($results = null)

    {
        $options = $this->_options;
        $path = $options['state_directory'];
        $file = $options['state_file'];
        if (!is_writable($path)) {
            PEAR::raiseError($path.' is not writeable');
        }
        if (file_exists($path.$file)) {
            if (!is_writable($path.$file)) {
                PEAR::raiseError($path.$file.' exists but is not writeable');
            }
        }
        $fp = @fopen($path.$file,'w');
        if (is_null($results)) {
            $results = $this->_results;
        }
        $line = serialize($results);
        @fwrite($fp,$line);
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
     *
     */
    function getState() 

    {
        $my_line = "";
        $options = $this->_options;
        $path = $options['state_directory'];
        $file = $options['state_file'];
        if (file_exists($path.$file)) {
            if (!is_readable($path.$file)) {
                PEAR::raiseError($path.$file.' exists but is not readable');
            }
        } else {
            return array();
        }
        $fp = @fopen($path.$file,'r');
        while(!feof($fp)) {
           $my_line .= fgets($fp,4096);
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
     * @access private
     * @param array primary
     * @param array secondary
     * @return array
     */
    function stateDiff($primary,$secondary) 

    {
        $return_array = array();
        $sizeof_primary = sizeof($primary);
        $sizeof_secondary = sizeof($secondary);
        //loop through primary array
        for($i=0; $i < $sizeof_primary; $i++) {
            $primary_sub = $primary[$i];
            $ps_host = $primary_sub['host'];
            $ps_service = $primary_sub['service'];
            $ps_code = $primary_sub['code'];
            //reindex secondary in case it has had values removed
            $secondary = array_values($secondary);
            $sizeof_secondary = sizeof($secondary);
            //reset primary flags
            $primary_sub_is_unique = true;
            $primary_sub_changed = false;
            //for each primary value, loop through secondary array
            for($j = 0; $j < $sizeof_secondary; $j++) {
                $secondary_sub = $secondary[$j];
                $ss_host = $secondary_sub['host'];
                $ss_service = $secondary_sub['service'];
                $ss_code = $secondary_sub['code'];
                if ($ss_host == $ps_host && $ss_service == $ps_service) {
                    //host and service identical in secondary and primary
                    if ($ss_code != $ps_code) {
                         //different codes 
                         if ($this->_options['notify_change']) {
                             //notify_change on; move to return
                             $return_array[] = $primary_sub;
                         }
                         //flag this primary_sub as a change, not a unique
                         $primary_sub_changed = true;
                    } else {
                         //code also identical in secondary and primary
                         //therefore this primary_sub is not unique
                         $primary_sub_is_unique = false;
                    }
                    //remove from secondary
                    unset($secondary[$j]);
                }
            }
            if ($primary_sub_is_unique && !$primary_sub_changed) {
                //after all that checking, primary_sub is unique
                //but not because it represents a host/service that has
                //changed from one down state to another; move to return.
                $return_array[] = $primary_sub;
            }
        }
        if ($this->_options['notify_ok'] && sizeof($secondary) > 0) {
            //remaining states in secondary are marked OK and added to return
            reset($secondary);
            $sizeof_secondary = sizeof($secondary);
            for($k = 0; $k < $sizeof_secondary; $k++) {
                $secondary[$k]['code'] = 200;
                $secondary[$k]['message'] = 'OK';
                $return_array[] = $secondary[$k];
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
    function resetState() 

    {
         $this->_results = array();
         $this->_results_diff = array();
         $options = $this->_options;
         $path = $options['state_directory'];
         $file = $options['state_file'];
         if (file_exists($path.$file)) {
             if (!is_writable($path.$file)) {
                 PEAR::raiseError($path.$file.' exists but is not writeable');
             }
             return unlink($path.$file);
         } else {
             return true;
         }
    }
    /** 
     * function resetHostState
     *
     * Resets the state for a single host ($host). Optionally takes in a
     * second parameter, $service, whereby the function only
     * resets the results for that particular host/service test.
     *
     * @param string $host
     * @param string $service
     * @return void
     * @access public
     */
    function resetHostState($host,$service = null) 

    {
         $last_state = $this->getState();
         $sizeof_last_state = sizeof($last_state);
         for($i=0; $i < $sizeof_last_state; $i++) {
             $working_state = $last_state[$i];
             if ($working_state['host'] == $host) {
                 if ($service != null && $working_state['service'] != $service) {
                     //hosts match but services don't match? place in new array
                     $new_array[] = $working_state;
                 }
             } else {
                 //different hosts? place in new array
                 $new_array[] = $working_state;
             }
         }
         $this->saveState($new_array);
     }
     /** 
      * function printAlert
      *
      * Prints the alert specified in the associative array ($array)
      * to STDOUT. Formats the alert according to $_options['alert_line'].
      *
      * @param array array
      * @return void
      * @access public
      */
     function printAlert($array) 

     {
         if ($this->_options['alert_line']) {
             $alert_line = $this->_options['alert_line'];
         } else {
             $alert_line = '%h: %s: %m';
         }
         $alert_line = str_replace('%h',$array['host'],$alert_line);
         $alert_line = str_replace('%s',$array['service'],$alert_line);
         $alert_line = str_replace('%m',$array['message'],$alert_line);
         $alert_line = str_replace('%c',$array['code'],$alert_line);
         print $alert_line."\r\n";
     }
}
?>
