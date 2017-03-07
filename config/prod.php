<?php

// configure your app for the production environment

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
