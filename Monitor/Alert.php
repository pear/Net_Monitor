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
 * class Net_Monitor_Alert
 *
 * This is the generic alert class
 *
 * @package Net_Monitor
 * @access public
 *
 */
class Net_Monitor_Alert
{
    var $_service = 'Generic';
    var $_alert = NULL;
    /** 
     * function alert
     *
     * Sends the specified results to the specified server
     *
     * @access private
     * @param mixed server
     * @param array results
     * @return mixed
     */
    function alert($server,$results,$options=array()) 
    {
      print "ALERT: $server ".$this->_service." not yet implemented\n";
      print_r($results);
    }
}
?>
