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
 * @category Net
 * @package  Net_Monitor
 * @author   Robert Peake <cyberscribe@php.net>
 * @author   Bertrand Gugger <bertrand@toggg.com>
 * @license  http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link     http://pear.php.net/package/Net_Monitor
 * @access   public
 * @see      Net_Monitor_Service
 */
class Net_Monitor_Service_HTTP extends Net_Monitor_Service
{
    /**
     * Defines the name of the service
     *
     * @var string $_service
     * @access private
     */
    var $_service = 'HTTP';

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
     * Checks the specified HTTP server ($host) for availability.
     * Returns false on success, or a notification array on failure.
     *
     * @param mixed $host HTTP server
     *
     * @return mixed
     */
    function check($host) 
    {
        $response = 0;

        $full_host     = $this->_prefix.$host;
        $this->_client = new HTTP_Request($full_host);  
        $http_client   = $this->_client;

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
            $return = array($response, $msg);
        }
        $this->_last_code = $response;
        return $return;
    }
}
