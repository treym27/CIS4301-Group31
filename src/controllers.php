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
    $pending = $app['db']->fetchAll("select * from account where account.status = 1");
    $avg_gender = $app['db']->fetchAll("select gender,avg(value) as avg,stddev(value) as stddev from account,makes,transaction where account.type = 0 and account.id = makes.toacc and makes.tid = transaction.id and transaction.memo = 'Salary Payment' group by gender order by gender asc");
    $liked = $app['db']->fetchAll("select * from (select likes.smid, fromacc, toacc, text, count(distinct who) as count from likes,social_media_post,makes where likes.smid = social_media_post.id and likes.smid = makes.smid group by likes.smid, fromacc, toacc, text order by count(distinct who) desc) where ROWNUM <= 10");
    $salary_by_city = $app['db']->fetchAll("select address_city, address_state, avg(value) as avg, stddev(value) as stddev from account,makes,transaction where account.type = 0 and account.id = makes.toacc and makes.tid = transaction.id and transaction.memo = 'Salary Payment' group by account.address_city, account.address_state order by avg(value) desc");
    $friends = $app['db']->fetchAll("select * from (select friend1, count(distinct friend2) as count from is_friends_with group by friend1 order by count(distinct friend2) desc) where ROWNUM <= 10");
    $money = $app['db']->fetchAll("select * from (select fromacc, pos-neg as total from (select fromacc, sum(value) as neg from makes,transaction where makes.tid = transaction.id group by fromacc), (select toacc, sum(value) as pos from makes,transaction where makes.tid = transaction.id group by toacc) where fromacc = toacc and fromacc != 1337 order by pos-neg desc) where rownum <= 10");
    $year = $app['db']->fetchAll("select * from (select extract(year from dob) as year, avg(value) as avg from account,makes,transaction where account.id = makes.toacc and makes.tid = transaction.id and makes.toacc != 1337 and transaction.memo = 'Salary Payment' group by extract(year from dob) order by avg(value) desc) where rownum <= 10");
    return $app['twig']->render('admin_home.html.twig', array(
        $avg_gender[0]["GENDER"].'Count' => $avg_gender[0]["AVG"],
        $avg_gender[1]["GENDER"].'Count' => $avg_gender[1]["AVG"],
        'salarybycity' => $salary_by_city,
        'friends' => $friends,
        'likes' => $liked,
        'pending' => $pending,
        'money' => $money,
        'years' => $year,
    ));
})->bind('admin_home');

// admin search page
$app->get('/admin/search', function () use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('admin_search');

// user home page
$app->get('/user', function () use ($app) {
$token = $app['security.token_storage']->getToken();
if (null !== $token) {
    $user = $token->getUser();
}
    $topTen= $app['db']->fetchAll("select * from (select timestamp, type, value, ID from Transaction, Makes where (fromacc = 1337 or toacc= 1337) and transaction.id=makes.tid order by timestamp desc) where rownum <=10");
    $userID= $app['db'] -> fetchAssoc("select name from account where email_address = '$user'");
	   
return $app['twig']->render('user.html.twig', array(
	
	"topTen" => $topTen,
	"userID" => $userID
	));
})->bind('user_home');

// user timeline
$app->get('/user/timeline', function () use ($app) {
    return $app['twig']->render('user_timeline.html.twig');
})->bind('user_timeline');

// user profile page

$app->get('/user/{id}', function ($id) use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('user_profile');

// PAGE 2 EDIT USER INFO !!!!!
$app->get('/user/acc_info', function () use ($app) {
    $token = $app['security.token_storage']->getToken();
    if (null !== $token) {
        $user = $token -> getUser();
        
    } 
    $name = $app['db'] -> fetchAssoc("select name, DOB, address_street, phone_number, email_address, password from account where email_address = '$user'");
    return $app['twig'] -> render('user_acc_info.html.twig', array(
    "name" => $name
    )); 
})->bind('user_acc_info'); 

/*$app->get('/user/acc_info', function () use ($app) {
    return $app['twig'] -> render('user_acc_info.html.twig');
})->bind('user_acc_info'); */

// user edit page (can only view your own, or admin can view all)
$app->get('/user/edit/{id}', function ($id) use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('user_edit');

// login page
$app->get('/login', function (Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('login');

// admin login page
$app->get('/secret/login', function (Request $request) use ($app) {
    return $app['twig']->render('secret_login.html.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('secret_login');

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
