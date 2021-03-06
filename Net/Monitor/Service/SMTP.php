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
/**
 * require and extend the Net_Monitor_Service class
 */
require_once 'Net/Monitor/Service.php';
/**
 * reqire and use the Net_SMTP class to check SMTP services
 */
require_once 'Net/SMTP.php';
/** 
 * class Net_Monitor_Service_SMTP
 *
 * A class for checking SMTP (email) services
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
class Net_Monitor_Service_SMTP extends Net_Monitor_Service
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access protected
     */
    protected $_service = 'SMTP';

    /**
     * The client object used for testing
     *
     * @var object $_client
     * @access protected
     */
    protected $_client = null;

    /**
     * The last response code received
     *
     * @var int $_last_code
     * @access protected
     */
    protected $_last_code = -1;

    /** 
     * function Net_Monitor_Service_SMTP
     *
     * @access public
     */
    public function __construct()
    {
        $this->_client = new Net_SMTP();
    }

    /** 
     * function check
     * 
     * Checks the specified SMTP server ($host) for availability.
     * Returns false on success, or a notification array on failure.
     *
     * @param mixed $host SMTP server
     *
     * @return mixed
     */
    public function check($host) 
    {
        $response = 0;

        $this->_client = new Net_SMTP($host);

        $c = $this->_client;
        $e = $c->connect();

        if (PEAR::isError($e)) { 
            //return connection-specific error string
            $this->_last_code = $response;
            return array($response, $e->getMessage());
        }

        //everything is OK
        $c->disconnect();

        $this->_last_code = 200; //set last code to 200

        return false; //false signifies no problem       
    }
}
