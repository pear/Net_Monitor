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
 * @version 0.2.0
 */
/**
 * require and extend the Net_Monitor_Service_HTTP class by re-implementing the prepare() method
 */
require_once 'Net/Monitor/Service/HTTP.php';
/** 
 * class Net_Monitor_Service_HTTPS
 *
 * A class for checking HTTPS (web over SSL) services
 *
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Service_HTTP
 */
class Net_Monitor_Service_HTTPS extends Net_Monitor_Service_HTTP
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'HTTPS';
    /**
     * The prefix used to form a fully-qualified URL
     *
     * @var string $_prefix
     * @access public
     */
    var $_prefix = 'https://';
    /** 
     * function Net_Monitor_Service_HTTPS
     *
     * @access public
     */
    function Net_Monitor_Service_HTTPS()

    {
        if (!extension_loaded('openssl')) {
    	    PEAR::raiseError('Net_Monitor_Service_HTTPS requires OpenSSL');
    	}
        $this->Net_Monitor_Service_HTTP();
    }
}
?>
