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
 * require and extend the Net_Monitor_Service_HTTP class by re-implementing the prepare() method
 */
require_once 'Net/Monitor/Service/HTTP.php';
/** 
 * class Net_Monitor_Service_HTTPS
 *
 * A class for checking HTTPS (web over SSL) services
 *
 * @category Net
 * @package  Net_Monitor
 * @author   Robert Peake <cyberscribe@php.net>
 * @author   Bertrand Gugger <bertrand@toggg.com>
 * @license  http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link     http://pear.php.net/package/Net_Monitor
 * @access   public
 * @see      Net_Monitor_Service_HTTP
 */
class Net_Monitor_Service_HTTPS extends Net_Monitor_Service_HTTP
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access protected
     */
    protected $_service = 'HTTPS';

    /**
     * The prefix used to form a fully-qualified URL
     *
     * @var string $_prefix
     * @access protected
     */
    protected $_prefix = 'https://';

    /** 
     * function Net_Monitor_Service_HTTPS
     *
     * @access public
     */
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            PEAR::raiseError('Net_Monitor_Service_HTTPS requires OpenSSL');
        }

        parent::__construct();
    }
}
