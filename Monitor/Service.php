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
 * class Net_Monitor_Service
 *
 * A generic service monitoring class
 *
 * @package Net_Monitor
 * @access public
 */
class Net_Monitor_Service
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'Generic';
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
     * function check
     * 
     * Checks the specified server ($server) for availability.
     * Returns false on success, or a notification array ( code, message) on failure.
     *
     * @param mixed server
     * @return mixed
     */
    function check($server) 

    {
        return array( -1, 'not yet implemented');
    }
}
?>
