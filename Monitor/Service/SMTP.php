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
 * @version 0.1.0
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
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Service
 */
class Net_Monitor_Service_SMTP extends Net_Monitor_Service
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'SMTP';
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
     * function Net_Monitor_Service_SMTP
     *
     * @access public
     */
    function Net_Monitor_Service_SMTP()

    {
        $this->_client = new Net_SMTP();
    }
    /** 
     * function check
     * 
     * Checks the specified SMTP server ($host) for availability.
     * Returns false on success, or a notification array on failure.
     *
     * @param mixed host
     * @return mixed
     */
    function check($host) 

    {
        $response = 0;
        $this->_client = new Net_SMTP($host);
        $c = $this->_client;
        $e = $c->connect();
        if (PEAR::isError($e)) { 
            //return connection-specific error string
            $this->_last_code = $response;
            return array('host' => $host, 'service' => $this->_service, 'message' => $e->getMessage(), 'code' => $response);
        } else { 
            //everything is OK
            $c->disconnect();
            $this->_last_code = 200; //set last code to 200
            return false; //false signifies no problem
        }
    }
}
?>
