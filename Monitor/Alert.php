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
 * @category   Net
 * @package    Net_Monitor
 * @author     Robert Peake <cyberscribe@php.net>
 * @author     Bertrand Gugger <bertrand@toggg.com>
 * @copyright  2004-2006 Robert Peake
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.0.6
 */
/**
 * class Net_Monitor_Alert
 *
 * This is the generic alert class
 *
 * @category Net
 * @package	Net_Monitor
 * @access public
 *
 */
class Net_Monitor_Alert
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'Generic';
    /**
     * The alert object used for sending alerts
     *
     * @var object $_alert
     * @access private
     */
    var $_alert = null;
    /** 
     * function alert
     *
     * Sends the specified results to the specified server
     * <ul>
     * <li> $server is the server to alert
     * <li> $result is the array of results
     * <li> $options is the array of additional options
     * </ul>
     * Does not return a value.
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
