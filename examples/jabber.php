<?php
require('Net/Monitor.php');
$monitor = new Net_Monitor();
$alerts = array('User1' => array('SMTP' => 'user1@example.com'),
                'User2' => array('Jabber' => array('server' => 'jabber.org',
				'recipient' => 'user2@jabber.org',
				'login' => 'user2',
				'password' => 'foo')),
                'User3' => array('Jabber' => array('server' => 'jabber.org',
				'recipient' => 'user3@jabber.org',
				'login' => 'user3',
				'password' => 'bar')) );
$services = array('example.com' => array('DNS'));
$monitor->setAlerts($alerts);
$monitor->setServices($services);
$monitor->checkAll();
?>
