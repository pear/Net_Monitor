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
 * requires and extends the Net_Monitor_Alert class
 */
require_once 'Net/Monitor/Alert.php';
/**
 * requires and uses the Net_SMS class for SMS alerts
 */
require_once 'Net/SMS.php';
/** 
 * class Net_Monitor_Alert_SMS
 *
 * @package Net_Monitor
 * @access public
 * @see Net_Monitor_Alert
 */
class Net_Monitor_Alert_SMS extends Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'SMS';
    /**
     * The alert object to be used
     *
     * @var object $_alert
     * @access private
     */
    var $_alert = null;
    /** 
     * function Net_Monitor_Alert_SMS
     *
     * @access public
     */
    function Net_Monitor_Alert_SMS()

    {
        $this->_alert = new Net_SMS();
    }
}
?>
