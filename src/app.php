<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\DoctrineServiceProvider;

$app = new Application();
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new DoctrineServiceProvider(), array(
    "db.options" => array (
        "driver"    =>  "oci8",
        "user"      =>  "",
        "password"  =>  "",
        "host"      =>  "",
        "port"      =>  0,
        "dbname"    =>  ""
    )
));
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});

return $app;
