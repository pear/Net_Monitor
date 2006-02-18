<?php
/**
 *
 * This file gives an example of configuring Net_Monitor using an INI file
 *
 * @package Net_Monitor
 * @see config.ini.php
 */
/**
 *
 */
require_once('Net/Monitor.php');
// what config file we use
$cfg = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'config.ini.php';

$config = parse_ini_file($cfg,true);
if (!($services = $config['services'])) {
    exit("You must at least have a section [services] !\n");
}
unset( $config['services']);
while(list($key,$value) = each($services)) {
    $value = explode(',',$value);
    $value = array_map('trim',$value);
    $services[$key] = $value;
}
$options = array();
if (isset($config['options'])) {
    $options = $config['options'];
    unset( $config['options']);
}
if (isset($config['SMTP'])) {
    $options['SMTP_default'] = $config['SMTP'];
    unset( $config['SMTP']);
}
if (isset($config['SMS'])) {
    $options['SMS_default'] = $config['SMS'];
    unset( $config['SMS']);
}
// proceed rest of sections for extra servers
$SMTP = $SMS = array();
foreach ($config as $sec => $param) {
    if (isset($param['SMS_provider'])) {
        $SMS[$sec] = $param;
        unset( $config[$sec]);
    }
    if (isset($param['host'])) {
        $SMTP[$sec] = $param;
        unset( $config[$sec]);
    }
}
// users
$alerts = array();
foreach ($config as $user => $param) {
    $alert = array();
    if (isset($param['SMTP'])) {
        $alert['SMTP'] = $param['SMTP'];
        unset( $param['SMTP']);
    }
    if (isset($param['SMS'])) {
        $alert['SMS'] = $param['SMS'];
        unset( $param['SMS']);
    }
    foreach ($param as $server=>$extra) {
        if (isset($SMTP[$server])) {
            $alert['SMTP'] = $SMTP[$server];
            $alert['SMTP']['email'] = $extra;
        } elseif (isset($SMS[$server])) {
            $alert['SMS'] = $SMS[$server];
            $alert['SMS']['phone_number'] = $extra;
        } else {
            exit("Unknown server {$server} for user {$user} !\n");
        }
    }
    if (!$alert) {
        exit("Empty for user {$user} !\n");
    }
    $alerts[$user] = $alert;
}
print_r($services);
print_r($alerts);
print_r($options);
$monitor = new Net_Monitor($services, $alerts, $options);
$monitor->checkAll();
