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
 * @version 0.0.6 (proposal)
 */
/**
 * require and extend the Net_Monitor_Service class
 */
require_once 'Net/Monitor/Service.php';
/**
 * require and use the HTTP_Request class to check HTTP services
 */
require_once 'HTTP/Request.php';
/** 
 * class Net_Monitor_Service_HTTP
 *
 * A class to check HTTP (web) services
 *
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Service
 */
class Net_Monitor_Service_HTTP extends Net_Monitor_Service
{
    /**
     * @var string _service
     * @access private
     */
    var $_service = 'HTTP';
    /**
     * @var object _client
     * @access private
     */
    var $_client = NULL;
    /**
     * @var int _last_code
     * @access private
     */
    var $_last_code = -1;
    /**
     * @var string _prefix
     * @access private
     */
    var $_prefix = 'http://';
    /** 
     * function Net_Monitor_Service_HTTP
     *
     * @access public
     */
    function Net_Monitor_Service_HTTP()
    {
        $this->_client = new HTTP_Request();
    }
    /** 
     * function check
     * 
     * Checks the specified HTTP (web) server for availability.
     * Returns false on success, or a notification array on failure.
     *
     * @param mixed host
     * @return mixed
     */
    function check($host) 
    {
        $response = 0;
        $full_host = $this->_prefix.$host;
    	$this->_client = new HTTP_Request($full_host);  
        $http_client = $this->_client;
        $e = $http_client->sendRequest();
        if (!PEAR::isError($e)) {
            $e = $http_client->getResponseBody();
            if (!PEAR::isError($e)) {
                $response = $http_client->getResponseCode();
                if ($response != 200) {
                    $e = new PEAR_Error($response);
                } else {
                    $return = false;
                }
            }
        }
        if (PEAR::isError($e)) {
            $msg = $e->getMessage();
            if (!$msg) {
    		    $msg = 'host not found';
            }
    		$return = array('host' => $host, 'service' => $this->_service,'message' => $msg, 'code' => $response);
        }
    	$this->_last_code = $response;
        return $return;
    }
}
?>
