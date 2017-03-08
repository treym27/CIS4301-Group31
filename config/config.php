<?php

// disable these for production
$app['debug'] = true;
$app['monolog.logfile'] = __DIR__.'/../var/logs/monolog.log';

// general configuration
$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');
$app['db.options'] = array (
    "driver"    =>  "oci8",
    "user"      =>  getenv("DBUSER"),
    "password"  =>  getenv("DBPASS"),
    "host"      =>  getenv("DBHOST"),
    "port"      =>  getenv("DBPORT"),
    "dbname"    =>  getenv("DBSID")
);

// configure the symfony security firewall
require __DIR__.'/firewall.php';

