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
 * require and use the Net_FTP class to check FTP services
 */
require_once 'Net/FTP.php';
/** 
 * class Net_Monitor_Service_FTP
 *
 * A class to check FTP services
 *
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Service
 */
class Net_Monitor_Service_FTP extends Net_Monitor_Service
{
    /**
     * @var string _service
     * @access private
     */
    var $_service = 'FTP';
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
     * function Net_Monitor_Service_FTP
     *
     * @access public
     */
    function Net_Monitor_Service_FTP()
    {
        $this->_client = new Net_FTP();
    }
    /** 
     * function check
     * 
     * Checks the specified FTP server for availability.
     * Returns false on success, or a notification array on failure.
     *
     * @param mixed host
     * @return mixed
     */
    function check($host) 
    {
        $c = $this->_client;
    	$e = $c->connect($host);
    	if (!Pear::isError($e)) {
    	    $this->_last_code = '200';
    	    return false;
    	} else {
    	    $this->_last_code = 0;
    	    return array('host' => $host, 'service' => $this->_service, 'message' => $e->getMessage(), 'code' => 0); 
    	}
    }
}
?>
