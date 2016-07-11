<?php

// web/index.php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/HASH/Controller/HashController.php';
require_once __DIR__.'/HASH/Controller/HashEventController.php';
require_once __DIR__.'/HASH/Controller/HashPersonController.php';
require_once __DIR__.'/HASH/Controller/AdminController.php';
require_once __DIR__.'/config/ProdConfig.php';
require_once __DIR__.'/vendor/twig/twig/lib/Twig/AutoLoader.php';
require_once __DIR__.'/HASH/Controller/SuperAdminController.php';
require_once __DIR__.'/HASH/Controller/ObscureStatisticsController.php';
//test comment


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

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

$app = new Silex\Application();

#TWIG Constants
$twigClassPath = __DIR__.'vendor/twig/twig/lib';
$twigTemplateSourceDirectory = __DIR__.'/Twig_Templates/source';
$twigTemplateCompiledDirectory = __DIR__.'/Twig_Templates/compiled';
# End TWIG Configurations-------------------------------------------------------

#Registers a database connection -----------------------------------------------
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'dbs.options' => array(
    'mysql_read' => array(
      'driver'   	=> DB_DRIVER,
      'dbname'	=> DB_NAME,
      'host'		=> DB_HOST,
      'port'		=> DB_PORT,
      'user'		=> DB_READ_ONLY_USER,
      'password'	=> DB_READ_ONLY_PASSWORD
    ),
    'mysql_write' => array(
      'driver'   	=> DB_DRIVER,
    	'dbname'	=> DB_NAME,
    	'host'		=> DB_HOST,
    	'port'		=> DB_PORT,
    	'user'		=> DB_USER,
    	'password'	=> DB_PASSWORD
    ))));


#Create users table in database-------------------------------------------------
$schema = $app['dbs']['mysql_write']->getSchemaManager();
if (!$schema->tablesExist('users')) {
    $users = new Table('users');
    $users->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
    $users->setPrimaryKey(array('id'));
    $users->addColumn('username', 'string', array('length' => 32));
    $users->addUniqueIndex(array('username'));
    $users->addColumn('password', 'string', array('length' => 255));
    $users->addColumn('roles', 'string', array('length' => 255));

    $schema->createTable($users);

    $app['dbs']['mysql_write']->insert('users', array(
      'username' => 'fabien',
      'password' => DEFAULT_USER_PASSWORD,
      'roles' => 'ROLE_USER'
    ));

    $app['dbs']['mysql_write']->insert('users', array(
      'username' => 'admin',
      'password' => DEFAULT_USER_PASSWORD,
      'roles' => 'ROLE_ADMIN'
    ));

    $app['dbs']['mysql_write']->insert('users', array(
      'username' => 'superadmin',
      'password' => DEFAULT_USER_PASSWORD,
      'roles' => 'ROLE_SUPERADMIN'
    ));

}


#-------------------------------------------------------------------------------

#Set your global assertions and stuff ------------------------------------------
$app['controllers']
  ->assert("hash_id", "\d+")
  ->assert("hasher_id", "\d+")
  ->assert("hare_id", "\d+")
  ->assert("year_value", "\d+")
  ->assert("kennel_id","\d+")
  ->assert("analversary_number","\d+")
  ->assert("kennel_abbreviation","^(sch4|SCH4|SCH4BASH|sch4bash|LVH3|lvh3|SWOT|swot)$")
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
        #'supersecured' => array(
        #    'pattern' => '^/superadmin',
        #    'form' => array('login_path' => '/logonscreen', 'check_path' => '/admin/login_check'),
        #    'logout' => array('logout_path' => '/logoutaction'),
        #    'users' => $app->share(function () use ($app) {return new UserProvider($app['db']);}),
        #    'logout' => array('logout_path' => '/admin/logoutaction', 'invalidate_session' => true),
        #),
        #'secured' => array(
        #    'pattern' => '^/admin',
        #    'form' => array('login_path' => '/logonscreen', 'check_path' => '/admin/login_check'),
        #    'logout' => array('logout_path' => '/logoutaction'),
        #    'users' => $app->share(function () use ($app) {return new UserProvider($app['db']);}),
        #    'logout' => array('logout_path' => '/admin/logoutaction', 'invalidate_session' => true),
        #),
        'secured' => array(
            'pattern' => '^/superadmin|/admin',
            'form' => array('login_path' => '/logonscreen', 'check_path' => '/admin/login_check'),
            'logout' => array('logout_path' => '/logoutaction'),
            'users' => $app->share(function () use ($app) {return new UserProvider($app['db']);}),
            'logout' => array('logout_path' => '/admin/logoutaction', 'invalidate_session' => true),
        ),
        #'supersecured' => array(
        #    'pattern' => '^/superadmin',
        #),
        'unsecured' => array(
          'pattern' => '^.*$',
        )
    )
));

$app['security.access_rules'] = array(
    array('^/superadmin',   'ROLE_SUPERADMIN',),
    array('^/admin',        'ROLE_ADMIN',),
);


/*
$app['security.access_rules'] = array(
    #array('^/superadmin',   'ROLE_SUPERADMIN'),
    #array('^/superadmin',   'ROLE_ADMIN'),
    array('^/admin',        'ROLE_ADMIN')
);
*/

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array('translator.messages' => array(),));
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
$app->get('/{kennel_abbreviation}',                               'HASH\Controller\HashController::slashKennelAction');


$app->get('/logonscreen',                                         'HASH\Controller\HashController::logonScreenAction');
$app->get('/admin/logoutaction',                                  'HASH\Controller\AdminController::logoutAction');
$app->get('/superadmin/hello',                                    'HASH\Controller\SuperAdminController::helloAction');
$app->get('/admin/hello',                                         'HASH\Controller\AdminController::adminHelloAction');
$app->get('/user/hello',                                          'HASH\Controller\AdminController::userHelloAction');

$app->get('/{kennel_abbreviation}/listhashers',                                         'HASH\Controller\HashController::listHashersAction');
$app->get('/{kennel_abbreviation}/listhashers/byhash/{hash_id}',                        'HASH\Controller\HashController::listHashersByHashAction');
$app->get('/{kennel_abbreviation}/listhares/byhash/{hash_id}',                          'HASH\Controller\HashController::listHaresByHashAction');
$app->get('/{kennel_abbreviation}/listhashes',                                          'HASH\Controller\HashController::listHashesAction');
$app->get('/{kennel_abbreviation}/listhashes/byhasher/{hasher_id}',                     'HASH\Controller\HashController::listHashesByHasherAction');
$app->get('/{kennel_abbreviation}/listhashes/byhare/{hasher_id}',                       'HASH\Controller\HashController::listHashesByHareAction');
$app->get('/{kennel_abbreviation}/hashers/{hasher_id}',                                 'HASH\Controller\HashController::viewHasherAction');
$app->get('/{kennel_abbreviation}/hashes/{hash_id}',                                    'HASH\Controller\HashController::viewHashAction');
$app->get('/{kennel_abbreviation}/hasherAnalversariesForEvent/{hash_id}',               'HASH\Controller\HashController::hasherAnalversariesForEventAction');
$app->get('/{kennel_abbreviation}/hareAnalversariesForEvent/{hash_id}',                 'HASH\Controller\HashController::hareAnalversariesForEventAction');
$app->get('/{kennel_abbreviation}/pendingHasherAnalversaries',                          'HASH\Controller\HashController::pendingHasherAnalversariesAction');
$app->get('/{kennel_abbreviation}/pendingHareAnalversaries',                            'HASH\Controller\HashController::pendingHareAnalversariesAction');
$app->get('/{kennel_abbreviation}/haringPercentageAllHashes',                           'HASH\Controller\HashController::haringPercentageAllHashesAction');
$app->get('/{kennel_abbreviation}/haringPercentageNonHypers',                           'HASH\Controller\HashController::haringPercentageNonHypersAction');
$app->get('/{kennel_abbreviation}/hashingCounts',                                       'HASH\Controller\HashController::hashingCountsAction');
$app->get('/{kennel_abbreviation}/haringCounts',                                        'HASH\Controller\HashController::haringCountsAction');
$app->get('/{kennel_abbreviation}/nonHyperharingCounts',                                'HASH\Controller\HashController::nonHyperHaringCountsAction');
$app->get('/{kennel_abbreviation}/coharelist/byhare/allhashes/{hasher_id}',             'HASH\Controller\HashController::coharelistByHareAllHashesAction');
$app->get('/{kennel_abbreviation}/coharelist/byhare/nonhypers/{hasher_id}',             'HASH\Controller\HashController::coharelistByHareNonHypersAction');
$app->get('/{kennel_abbreviation}/coharecount/byhare/allhashes/{hasher_id}',            'HASH\Controller\HashController::cohareCountByHareAllHashesAction');
$app->get('/{kennel_abbreviation}/coharecount/byhare/nonhypers/{hasher_id}',            'HASH\Controller\HashController::cohareCountByHareNonHypersAction');
$app->get('/{kennel_abbreviation}/hashattendance/byhare/lowest',                        'HASH\Controller\HashController::hashAttendanceByHareLowestAction');
$app->get('/{kennel_abbreviation}/hashattendance/byhare/highest',                       'HASH\Controller\HashController::hashAttendanceByHareHighestAction');
$app->get('/{kennel_abbreviation}/hashattendance/byhare/average',                       'HASH\Controller\HashController::hashAttendanceByHareAverageAction');
$app->get('/{kennel_abbreviation}/hashattendance/byhare/grandtotal/nondistincthashers', 'HASH\Controller\HashController::hashAttendanceByHareGrandTotalNonDistinctHashersAction');
$app->get('/{kennel_abbreviation}/hashattendance/byhare/grandtotal/distincthashers',    'HASH\Controller\HashController::hashAttendanceByHareGrandTotalDistinctHashersAction');
$app->get('/{kennel_abbreviation}/getHasherCountsByHare/{hare_id}',                     'HASH\Controller\HashController::hasherCountsByHareAction');
$app->get('/{kennel_abbreviation}/percentages/percentageofharingsthatwerehypers',       'HASH\Controller\HashController::percentageHaringsHypersVsNonHypers');
#$app->get('/getHasherCountsByHound/{hasher_id}',                     'HASH\Controller\HashController::hasherCountsByHoundAction');

# Hash event modification
$app->get('/admin/modifyhash/form/{hash_id}',                     'HASH\Controller\HashEventController::adminModifyHashAction');
$app->post('/admin/modifyhash/form/{hash_id}',                    'HASH\Controller\HashEventController::adminModifyHashAction');

# Hash event creation
$app->get('/admin/newhash/form',                                  'HASH\Controller\HashEventController::adminCreateHashAction');
$app->post('/admin/newhash/form',                                 'HASH\Controller\HashEventController::adminCreateHashAction');

# Hash person modification
$app->get('/admin/modifyhasher/form/{hasher_id}',                 'HASH\Controller\HashPersonController::modifyHashPersonAction');
$app->post('/admin/modifyhasher/form/{hasher_id}',                'HASH\Controller\HashPersonController::modifyHashPersonAction');

# Hash person creation
$app->get('/admin/newhasher/form',                                'HASH\Controller\HashPersonController::createHashPersonAction');
$app->post('/admin/newhasher/form',                               'HASH\Controller\HashPersonController::createHashPersonAction');

# Change admin password
#XXXXXXX
$app->get('/admin/newPassword/form',                                'HASH\Controller\AdminController::newPasswordAction');
$app->post('/admin/newPassword/form',                               'HASH\Controller\AdminController::newPasswordAction');


# Modify the participation for an event
$app->get('/admin/event/manageparticipation/{hash_id}',            'HASH\Controller\HashEventController::hashParticipationAction');

# Functions to add and delete hounds and hares to the hashes
$app->post('/admin/event/addHasherToHash',                         'HASH\Controller\HashEventController::addHashParticipant');
$app->post('/admin/event/addHareToHash',                           'HASH\Controller\HashEventController::addHashOrganizer');
$app->post('/admin/event/deleteHasherFromHash',                    'HASH\Controller\HashEventController::deleteHashParticipant');
$app->post('/admin/event/deleteHareFromHash',                      'HASH\Controller\HashEventController::deleteHashOrganizer');

$app->post('/admin/event/getHaresForEvent',                        'HASH\Controller\HashEventController::getHaresForEvent');
$app->post('/admin/event/getHashersForEvent',                      'HASH\Controller\HashEventController::getHashersForEvent');

$app->get('/admin/listhashes',                                     'HASH\Controller\AdminController::listHashesAction');
$app->get('/admin/listhashers',                                    'HASH\Controller\AdminController::listHashersAction');

$app->get('/admin/d3test','HASH\Controller\AdminController::d3testAction');

# Functions for the "by year" statistics
$app->get('/{kennel_abbreviation}/statistics/getYearInReview/{year_value}',               'HASH\Controller\ObscureStatisticsController::getYearInReviewAction');
$app->post('/{kennel_abbreviation}/statistics/getHasherCountsByYear',                     'HASH\Controller\ObscureStatisticsController::getHasherCountsByYear');
$app->post('/{kennel_abbreviation}/statistics/getTotalHareCountsByYear',                  'HASH\Controller\ObscureStatisticsController::getTotalHareCountsByYear');
$app->post('/{kennel_abbreviation}/statistics/getHyperHareCountsByYear',                  'HASH\Controller\ObscureStatisticsController::getHyperHareCountsByYear');
$app->post('/{kennel_abbreviation}/statistics/getNonHyperHareCountsByYear',               'HASH\Controller\ObscureStatisticsController::getNonHyperHareCountsByYear');

# Mappings for hasher specific statistics
$app->post('/{kennel_abbreviation}/statistics/hasher/firstHash',                           'HASH\Controller\ObscureStatisticsController::getHashersVirginHash');
$app->post('/{kennel_abbreviation}/statistics/hasher/mostRecentHash',                      'HASH\Controller\ObscureStatisticsController::getHashersLatestHash');

# Mappings for hasher hashes by (year/month/state/etc)
$app->post('/{kennel_abbreviation}/statistics/hasher/hashes/by/year',                      'HASH\Controller\ObscureStatisticsController::getHasherHashesByYear');
$app->post('/{kennel_abbreviation}/statistics/hasher/hashes/by/quarter',                   'HASH\Controller\ObscureStatisticsController::getHasherHashesByQuarter');
$app->post('/{kennel_abbreviation}/statistics/hasher/hashes/by/month',                     'HASH\Controller\ObscureStatisticsController::getHasherHashesByMonth');
$app->post('/{kennel_abbreviation}/statistics/hasher/hashes/by/dayname',                   'HASH\Controller\ObscureStatisticsController::getHasherHashesByDayName');
$app->post('/{kennel_abbreviation}/statistics/hasher/hashes/by/state',                     'HASH\Controller\ObscureStatisticsController::getHasherHashesByState');
$app->post('/{kennel_abbreviation}/statistics/hasher/hashes/by/city',                      'HASH\Controller\ObscureStatisticsController::getHasherHashesByCity');

# Mappings for hasher harings by (year/month/state/etc)
$app->post('/{kennel_abbreviation}/statistics/hasher/all/harings/by/year',                      'HASH\Controller\ObscureStatisticsController::getHasherAllHaringsByYear');
$app->post('/{kennel_abbreviation}/statistics/hasher/all/harings/by/quarter',                   'HASH\Controller\ObscureStatisticsController::getHasherAllHaringsByQuarter');
$app->post('/{kennel_abbreviation}/statistics/hasher/all/harings/by/month',                     'HASH\Controller\ObscureStatisticsController::getHasherAllHaringsByMonth');
$app->post('/{kennel_abbreviation}/statistics/hasher/all/harings/by/dayname',                   'HASH\Controller\ObscureStatisticsController::getHasherAllHaringsByDayName');
$app->post('/{kennel_abbreviation}/statistics/hasher/all/harings/by/state',                     'HASH\Controller\ObscureStatisticsController::getHasherAllHaringsByState');
$app->post('/{kennel_abbreviation}/statistics/hasher/all/harings/by/city',                      'HASH\Controller\ObscureStatisticsController::getHasherAllHaringsByCity');

# Mappings for hasher (non hyper) harings by (year/month/state/etc)
$app->post('/{kennel_abbreviation}/statistics/hasher/nonhyper/harings/by/year',                      'HASH\Controller\ObscureStatisticsController::getHasherNonHyperHaringsByYear');
$app->post('/{kennel_abbreviation}/statistics/hasher/nonhyper/harings/by/quarter',                   'HASH\Controller\ObscureStatisticsController::getHasherNonHyperHaringsByQuarter');
$app->post('/{kennel_abbreviation}/statistics/hasher/nonhyper/harings/by/month',                     'HASH\Controller\ObscureStatisticsController::getHasherNonHyperHaringsByMonth');
$app->post('/{kennel_abbreviation}/statistics/hasher/nonhyper/harings/by/dayname',                   'HASH\Controller\ObscureStatisticsController::getHasherNonHyperHaringsByDayName');
$app->post('/{kennel_abbreviation}/statistics/hasher/nonhyper/harings/by/state',                     'HASH\Controller\ObscureStatisticsController::getHasherNonHyperHaringsByState');
$app->post('/{kennel_abbreviation}/statistics/hasher/nonhyper/harings/by/city',                      'HASH\Controller\ObscureStatisticsController::getHasherNonHyperHaringsByCity');

# Per person stats (more of them)
$app->post('/{kennel_abbreviation}/coharecount/byhare/nonhypers','HASH\Controller\ObscureStatisticsController::getCohareCountByHareNonHypers');
$app->post('/{kennel_abbreviation}/coharecount/byhare/onlyhypers','HASH\Controller\ObscureStatisticsController::getCohareCountByHareOnlyHypers');
$app->post('/{kennel_abbreviation}/coharecount/byhare/allhashes','HASH\Controller\ObscureStatisticsController::getCohareCountByHareAllHashes');


$app->get('/{kennel_abbreviation}/basic/stats',         'HASH\Controller\HashController::basicStatsAction');
$app->get('/{kennel_abbreviation}/hashing/stats',       'HASH\Controller\HashController::hashingStatsAction');
$app->get('/{kennel_abbreviation}/haring/stats',        'HASH\Controller\HashController::haringStatsAction');
$app->get('/{kennel_abbreviation}/analversary/stats',   'HASH\Controller\HashController::analversaryStatsAction');
$app->get('/{kennel_abbreviation}/cautionary/stats',    'HASH\Controller\HashController::cautionaryStatsAction');
$app->get('/{kennel_abbreviation}/miscellaneous/stats', 'HASH\Controller\HashController::miscellaneousStatsAction');

#URLs for fastest/slowest to reach analversaries
#$app->get('/{kennel_abbreviation}/{analversary_number}/quickest/to/reach/bydays', 'HASH\Controller\ObscureStatisticsController::quickestToReachAnalversaryByDaysPreAction');
#$app->get('/{kennel_abbreviation}/{analversary_number}/slowest/to/reach/bydays',  'HASH\Controller\ObscureStatisticsController::slowestToReachAnalversaryByDaysPreAction');
#$app->get('/{kennel_abbreviation}/{analversary_number}/earliest/to/reach',        'HASH\Controller\ObscureStatisticsController::earliestToReachAnalversaryByDatePreAction');
#$app->get('/{kennel_abbreviation}/{analversary_number}/most/recent/to/reach',     'HASH\Controller\ObscureStatisticsController::mostRecentToReachAnalversaryByDatePreAction');
$app->get('/{kennel_abbreviation}/{analversary_number}/quickest/to/reach/bydays', 'HASH\Controller\ObscureStatisticsController::quickestToReachAnalversaryByDaysAction');
$app->get('/{kennel_abbreviation}/{analversary_number}/slowest/to/reach/bydays',  'HASH\Controller\ObscureStatisticsController::slowestToReachAnalversaryByDaysAction');
$app->get('/{kennel_abbreviation}/{analversary_number}/quickest/to/reach/date', 'HASH\Controller\ObscureStatisticsController::quickestToReachAnalversaryByDate');


#$app->post('/{kennel_abbreviation}/{analversary_number}/quickest/to/reach/bydays', 'HASH\Controller\ObscureStatisticsController::quickestToReachAnalversaryByDaysAction');
#$app->post('/{kennel_abbreviation}/{analversary_number}/slowest/to/reach/bydays', 'HASH\Controller\ObscureStatisticsController::slowestToReachAnalversaryByDaysAction');

$app->get('/{kennel_abbreviation}/longest/career','HASH\Controller\ObscureStatisticsController::longestCareerAction');
$app->get('/{kennel_abbreviation}/highest/averageDaysBetweenHashes','HASH\Controller\ObscureStatisticsController::highestAverageDaysBetweenHashesAction');
$app->get('/{kennel_abbreviation}/lowest/averageDaysBetweenHashes','HASH\Controller\ObscureStatisticsController::lowestAverageDaysBetweenHashesAction');


$app->get('/{kennel_abbreviation}/highest/allharings/averageDaysBetweenHarings','HASH\Controller\ObscureStatisticsController::highestAverageDaysBetweenAllHaringsAction');
$app->get('/{kennel_abbreviation}/lowest/allharings/averageDaysBetweenHarings','HASH\Controller\ObscureStatisticsController::lowestAverageDaysBetweenAllHaringsAction');
$app->get('/{kennel_abbreviation}/highest/nonhyperharings/averageDaysBetweenHarings','HASH\Controller\ObscureStatisticsController::highestAverageDaysBetweenNonHyperHaringsAction');
$app->get('/{kennel_abbreviation}/lowest/nonhyperharings/averageDaysBetweenHarings','HASH\Controller\ObscureStatisticsController::lowestAverageDaysBetweenNonHyperHaringsAction');

$app->get('/{kennel_abbreviation}/highest/attendedHashes','HASH\Controller\HashController::highestAttendedHashesAction');
$app->get('/{kennel_abbreviation}/lowest/attendedHashes','HASH\Controller\HashController::lowestAttendedHashesAction');

$app->get('/{kennel_abbreviation}/hashers/of/the/years','HASH\Controller\HashController::hashersOfTheYearsAction');
$app->get('/{kennel_abbreviation}/hares/overall/of/the/years','HASH\Controller\HashController::overallHaresOfTheYearsAction');
$app->get('/{kennel_abbreviation}/hares/nonhyper/of/the/years','HASH\Controller\HashController::nonHyperHaresOfTheYearsAction');

# Set the before/after actions
$app->before(function (Request $request, Application $app) {
});

#Do magic on the json traffic
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->after(function (Request $request, Response $response) {
   $response->headers->set('X-Frame-Options', 'DENY');
   $response->headers->set('X-Content-Type-Options', 'nosniff');
   $response->headers->set('X-XSS-Protection','1; mode=block');
   $response->headers->set('x-frame-options','SAMEORIGIN');
});


/*$app['security.access_rules'] = array(
    array('^/superadmin',   'ROLE_SUPERADMIN',),
    array('^/admin',        'ROLE_ADMIN',),
);
*/

$app->run();
