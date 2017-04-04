<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// general home page
$app->get('/', function () use ($app) {
    // This was just testing to make sure the database works
    // $sql = "select * from account";
    // $rows = $app['db']->fetchAll($sql);
    // $app['monolog']->info(sprintf("found %d accounts.", count($rows)));

    $app['monolog']->info('[/] pre-token check');
    $token = $app['security.token_storage']->getToken();
    if ($token !== null) {
        $app['monolog']->info('[/] found a login');
        if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
            $app['monolog']->info('[/] redirecting to admin');
            $app->redirect(url('admin'));
        }
    }

    return $app['twig']->render('index.html.twig');
})->bind('homepage');

// the admin home page
$app->get('/admin', function () use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('admin_home');

// admin search page
$app->get('/admin/search', function () use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('admin_search');

// user home page
$app->get('/user', function () use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('user_home');

// user timeline
$app->get('/user/timeline', function () use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('user_timeline');

// user profile page
$app->get('/user/{email}', function ($email) use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('user_profile');

// user edit page (can only view your own, or admin can view all)
$app->get('/user/edit/{email}', function ($email) use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('user_edit');

// login page
$app->get('/login', function (Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('login');

// error handler
$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
