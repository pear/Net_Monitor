<?php
require('Net/Monitor.php');
$monitor = new Net_Monitor();
$alerts = array('User1' => array('SMTP' => 'user1@example.com'),
                'User2' => array('SMTP' => array('email'=>'user2@example.com',
                                 'host' => 'example.com', 
                                 'port' => 25, 
                                 'auth' => true, 
                                 'username' => 'user2', 
                                 'password' => 'supersecret')));
$services = array('foo.example.com' => array('SMTP','DNS'),
                  'bar.example.com' => array('HTTP','FTP','DNS'));
$monitor->setAlerts($alerts);
$monitor->setServices($services);
$monitor->checkAll();
