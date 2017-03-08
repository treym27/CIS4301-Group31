<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;

$app = new Application();
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new MonologServiceProvider());
$app->register(new SecurityServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});

return $app;
