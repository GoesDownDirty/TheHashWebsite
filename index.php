<?php

// web/index.php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/HASH/Controller/HashController.php';
require_once __DIR__.'/HASH/Controller/AdminController.php';
require_once __DIR__.'/config/ProdConfig.php';
require_once __DIR__.'/vendor/twig/twig/lib/Twig/AutoLoader.php';



require_once './HASH/UserProvider.php';

use Doctrine\DBAL\Schema\Table;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\FormServiceProvider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;

$app = new Silex\Application();

#TWIG Constants
$twigClassPath = __DIR__.'vendor/twig/twig/lib';
$twigTemplateSourceDirectory = __DIR__.'/Twig_Templates/source';
$twigTemplateCompiledDirectory = __DIR__.'/Twig_Templates/compiled';
# End TWIG Configurations-------------------------------------------------------

#Registers a database connection -----------------------------------------------
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => array(
	'driver'   	=> DB_DRIVER,
	'dbname'	=> DB_NAME,
	'host'		=> DB_HOST,
	'port'		=> DB_PORT,
	'user'		=> DB_USER,
	'password'	=> DB_PASSWORD
    ),
));


#Create users table in database-------------------------------------------------
$schema = $app['db']->getSchemaManager();
if (!$schema->tablesExist('users')) {
    $users = new Table('users');
    $users->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $users->setPrimaryKey(array('id'));
    $users->addColumn('username', 'string', array('length' => 32));
    $users->addUniqueIndex(array('username'));
    $users->addColumn('password', 'string', array('length' => 255));
    $users->addColumn('roles', 'string', array('length' => 255));

    $schema->createTable($users);

    $app['db']->insert('users', array(
      'username' => 'fabien',
      'password' => DEFAULT_USER_PASSWORD,
      'roles' => 'ROLE_USER'
    ));

    $app['db']->insert('users', array(
      'username' => 'admin',
      'password' => DEFAULT_USER_PASSWORD,
      'roles' => 'ROLE_ADMIN'
    ));
}


#-------------------------------------------------------------------------------

#Set your global assertions and stuff ------------------------------------------
$app['controllers']
  ->assert("hash_id", "\d+")
  ->assert("hasher_id", "\d+")
  ->assert("hare_id", "\d+")
  ;
#-------------------------------------------------------------------------------





/*
#Is something goes wrong, show them these messages....
$app->error(function (Exception $exception, $code){
  switch($code){
    case 404:
      $message = "$code: The page is not there";
      break;
    default:
      $message = "$code: Something weird happened";
  }

  return $message;
});
*/


# Begin: Register the session management stuff ---------------------------------
$app->register(new Silex\Provider\SessionServiceProvider());
# End: -------------------------------------------------------------------------


# Begin: Set the security firewalls --------------------------------------------

$app['debug'] = true;

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'login' => array(
            'pattern' => '^/logonscreen$',
        ),
        'secured' => array(
            'pattern' => '^/admin',
            'form' => array('login_path' => '/logonscreen', 'check_path' => '/admin/login_check'),
            'logout' => array('logout_path' => '/logoutaction'),
            'users' => $app->share(function () use ($app) {return new UserProvider($app['db']);}),
            'logout' => array('logout_path' => '/admin/logoutaction', 'invalidate_session' => true),
        ),
        #'sortofsecured' => array(
        #    'pattern' => '^/user',
        #    'form' => array('login_path' => '/logonscreen', 'check_path' => '/admin/login_check'),
        #    'logout' => array('logout_path' => '/logoutaction'),
        #    'users' => $app->share(function () use ($app) {return new UserProvider($app['db']);}),
        #    'logout' => array('logout_path' => '/admin/logoutaction', 'invalidate_session' => true),
        #),
        'unsecured' => array(
          'pattern' => '^.*$',
        )
    )
));



$app['security.access_rules'] = array(
    #array('^/user',   'ROLE_USER'),
    array('^/admin',  'ROLE_ADMIN'),
);


$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
#$app->register(new FormServiceProvider());
#$app->register(new Silex\Provider\ValidatorServiceProvider());
#$app->register(new Silex\Provider\TranslationServiceProvider(), array('translator.messages' => array(),));
#-------------------------------------------------------------------------------


# Not sure if this should be used here
$app->boot();


$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => $twigTemplateSourceDirectory,
  'twig.class_path' =>$twigClassPath,
  'twig.options' => array(
    'cache' => $twigTemplateCompiledDirectory,
    'auto_reload' => true)));

# Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/development.log',
    'monolog.level' => 'debug',
    'monolog.bubble' => true
));


# End: -------------------------------------------------------------------------





# Register the URls
$app->get('/',                                                    'HASH\Controller\HashController::slashAction')->bind('homepage');

$app->get('/logonscreen',                                         'HASH\Controller\HashController::logonScreenAction');
$app->get('/admin/logoutaction',                                  'HASH\Controller\AdminController::logoutAction');


$app->get('/admin/hello',                                         'HASH\Controller\AdminController::adminHelloAction');
$app->get('/user/hello',                                          'HASH\Controller\AdminController::userHelloAction');

$app->get('/listhashers',                                         'HASH\Controller\HashController::listHashersAction');
$app->get('/listhashers/byhash/{hash_id}',                        'HASH\Controller\HashController::listHashersByHashAction');
$app->get('/listhares/byhash/{hash_id}',                          'HASH\Controller\HashController::listHaresByHashAction');
$app->get('/listhashes',                                          'HASH\Controller\HashController::listHashesAction');
$app->get('/listhashes/byhasher/{hasher_id}',                     'HASH\Controller\HashController::listHashesByHasherAction');
$app->get('/listhashes/byhare/{hasher_id}',                       'HASH\Controller\HashController::listHashesByHareAction');
$app->get('/hashers/{hasher_id}',                                 'HASH\Controller\HashController::viewHasherAction');
$app->get('/hashes/{hash_id}',                                    'HASH\Controller\HashController::viewHashAction');
$app->get('/hasherAnalversariesForEvent/{hash_id}',               'HASH\Controller\HashController::hasherAnalversariesForEventAction');
$app->get('/hareAnalversariesForEvent/{hash_id}',                 'HASH\Controller\HashController::hareAnalversariesForEventAction');
$app->get('/pendingHasherAnalversaries',                          'HASH\Controller\HashController::pendingHasherAnalversariesAction');
$app->get('/pendingHareAnalversaries',                            'HASH\Controller\HashController::pendingHareAnalversariesAction');
$app->get('/haringPercentageAllHashes',                           'HASH\Controller\HashController::haringPercentageAllHashesAction');
$app->get('/haringPercentageNonHypers',                           'HASH\Controller\HashController::haringPercentageNonHypersAction');
$app->get('/hashingCounts',                                       'HASH\Controller\HashController::hashingCountsAction');
$app->get('/haringCounts',                                        'HASH\Controller\HashController::haringCountsAction');
$app->get('/nonHyperharingCounts',                                'HASH\Controller\HashController::nonHyperHaringCountsAction');
$app->get('/coharelist/byhare/allhashes/{hasher_id}',             'HASH\Controller\HashController::coharelistByHareAllHashesAction');
$app->get('/coharelist/byhare/nonhypers/{hasher_id}',             'HASH\Controller\HashController::coharelistByHareNonHypersAction');
$app->get('/coharecount/byhare/allhashes/{hasher_id}',            'HASH\Controller\HashController::cohareCountByHareAllHashesAction');
$app->get('/coharecount/byhare/nonhypers/{hasher_id}',            'HASH\Controller\HashController::cohareCountByHareNonHypersAction');
$app->get('/hashattendance/byhare/lowest',                        'HASH\Controller\HashController::hashAttendanceByHareLowestAction');
$app->get('/hashattendance/byhare/highest',                       'HASH\Controller\HashController::hashAttendanceByHareHighestAction');
$app->get('/hashattendance/byhare/average',                       'HASH\Controller\HashController::hashAttendanceByHareAverageAction');
$app->get('/hashattendance/byhare/grandtotal/nondistincthashers', 'HASH\Controller\HashController::hashAttendanceByHareGrandTotalNonDistinctHashersAction');
$app->get('/hashattendance/byhare/grandtotal/distincthashers',    'HASH\Controller\HashController::hashAttendanceByHareGrandTotalDistinctHashersAction');
$app->get('/getHasherCountsByHare/{hare_id}',                     'HASH\Controller\HashController::hasherCountsByHareAction');

#$app->get('/getHasherCountsByHound/{hasher_id}',                     'HASH\Controller\HashController::hasherCountsByHoundAction');



# Set the before/after actions
$app->before(function (Request $request, Application $app) {
});

$app->after(function (Request $request, Response $response) {
   $response->headers->set('X-Frame-Options', 'DENY');
   $response->headers->set('X-Content-Type-Options', 'nosniff');
   $response->headers->set('X-XSS-Protection','1; mode=block');
   $response->headers->set('x-frame-options','SAMEORIGIN');
});


$app->run();
