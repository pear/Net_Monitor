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
 * @copyright  2004-2005 Robert Peake
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.0.6
 */
/** 
 * class Net_Monitor_Service
 *
 * A generic service monitoring class
 *
 * @category Net
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
