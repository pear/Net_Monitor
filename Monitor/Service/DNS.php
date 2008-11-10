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
 * require and extend the Net_Monitor_Service class
 */
require_once 'Net/Monitor/Service.php';
/**
 * require and use the Net_DNS class to check DNS service
 */
require_once 'Net/DNS.php';
/** 
 * class Net_Monitor_Service_DNS
 * 
 * A class for checking DNS services
 *
 * @category Net
 * @package  Net_Monitor
 * @author   Robert Peake <cyberscribe@php.net>
 * @author   Bertrand Gugger <bertrand@toggg.com>
 * @license  http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link     http://pear.php.net/package/Net_Monitor
 * @access   public
 * @see      Net_Monitor_Service
 */
class Net_Monitor_Service_DNS extends Net_Monitor_Service
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'DNS';

    /**
     * The client object used for testing
     *
     * @var object $_client
     * @access private
     */
    var $_client = null;

    /**
     * The last response code received
     *
     * @var int $_last_code
     * @access private
     */
    var $_last_code = -1;

    /** 
     * function Net_Monitor_Service_DNS
     *
     * @access public
     */
    function Net_Monitor_Service_DNS()
    {
        $this->_client = new Net_DNS_Resolver();
    }

    /** 
     * function check
     * 
     * Checks the specified DNS server ($host) for availability.
     * Returns false on success, or a notification array on failure.
     *
     * @param mixed $host DNS server
     *
     * @return mixed
     */
    function check($host) 
    {
        $c = $this->_client;

        $c->nameservers = array($host);

        $e = $c->search($host);

        if (is_object($e) && $e->answer) {
            return false;
        }

        $this->_last_code = 0;
        return array(0, 'no response');      
    }
}
