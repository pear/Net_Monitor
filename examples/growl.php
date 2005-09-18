<?php
require('Net/Monitor.php');
$monitor = new Net_Monitor();
$alerts = array('User1' => array('SMTP' => 'user1@example.com'),
                'User2' => array('Growl' => array('server' => '127.0.0.1')),
                'User3' => array('Growl' => array('server' => '127.0.0.1',
				                                  'password' => 'foo')) 
                );
$services = array('example.com' => array('DNS'));
$monitor->setAlerts($alerts);
$monitor->setServices($services);
$monitor->checkAll();
?>
