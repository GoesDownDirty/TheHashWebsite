<?php

namespace HASH\Controller;

require_once realpath(__DIR__ . '/../..').'/config/SQL_Queries.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;




class ObscureStatisticsController{


  private function obtainKennelKeyFromKennelAbbreviation(Request $request, Application $app, string $kennel_abbreviation){

    #Define the SQL to RuntimeException
    $sql = "SELECT * FROM KENNELS WHERE KENNEL_ABBREVIATION = ?";

    #Query the database
    $kennelValue = $app['db']->fetchAssoc($sql, array((string) $kennel_abbreviation));

    #Obtain the kennel ky from the returned object
    $returnValue = $kennelValue['KENNEL_KY'];

    #return the return value
    return $returnValue;

  }

    #Landing screen for year in review
    public function getYearInReviewAction(Request $request, Application $app, int $year_value, string $kennel_abbreviation){

      #Establish the page title
      $pageTitle = "$year_value: Year in review";

      #Establish the return value
      $returnValue = $app['twig']->render('year_in_review.twig', array (
        'pageTitle' => $pageTitle,
        'yearValue' => $year_value,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }

    #Obtain hashers for an event
    public function getHasherCountsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hasherCountSQL = HASHER_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hasherCountList = $app['db']->fetchAll($hasherCountSQL,array((int)$theYear, (int) $kennelKy));

      #Set the return value
      $returnValue =  $app->json($hasherCountList, 200);
      return $returnValue;
    }

    #Obtain total hare counts per year
    public function getTotalHareCountsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = TOTAL_HARE_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array((int)$theYear, (int) $kennelKy));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    #Obtain total hare counts per year
    public function getHyperHareCountsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = HYPER_HARE_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array((int)$theYear, (int) $kennelKy));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    #Obtain total hare counts per year
    public function getNonHyperHareCountsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = NONHYPER_HARE_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array((int)$theYear, (int) $kennelKy));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    #Obtain the first hash of a given hasher
    public function getHashersVirginHash(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = SELECT_HASHERS_VIRGIN_HASH;

      #Query the database
      $theirVirginHash = $app['db']->fetchAssoc($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theirVirginHash,200);
      return $returnValue;

    }

    #Obtain the latest hash of a given hasher
    public function getHashersLatestHash(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = SELECT_HASHERS_MOST_RECENT_HASH;

      #Query the database
      $theirLatestHash = $app['db']->fetchAssoc($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theirLatestHash,200);
      return $returnValue;

    }


    #Obtain the hasher hashes attended by year
    public function getHasherHashesByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_YEAR;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    #Obtain the hasher hashes attended by quarter
    public function getHasherHashesByQuarter(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_QUARTER;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    #Obtain the hasher hashes attended by quarter
    public function getHasherHashesByMonth(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_MONTH;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    #Obtain the hasher hashes attended by day name
    public function getHasherHashesByDayName(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_DAYNAME;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    #Obtain the hasher hashes attended by state
    public function getHasherHashesByState(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_STATE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    #Obtain the hasher hashes attended by city
    public function getHasherHashesByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    public function getHasherAllHaringsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_YEAR;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByQuarter(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_QUARTER;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByMonth(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_MONTH;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByDayName(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_DAYNAME;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByState(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_STATE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    # Mappings for hasher (non hyper) harings by (year/month/state/etc)
    public function getHasherNonHyperHaringsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_YEAR;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByQuarter(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_QUARTER;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByMonth(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_MONTH;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByDayName(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_DAYNAME;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByState(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_STATE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    public function getHasherNonHyperHaringsByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $theKennelKey));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }



    public function getCohareCountByHareNonHypers(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = COHARE_COUNT_BY_HARE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $theKennelKey,
        (int) $theHasherKey,
        (int) $theHasherKey,
        0,
        0,));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    public function getCohareCountByHareOnlyHypers(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = COHARE_COUNT_BY_HARE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $theKennelKey,
        (int) $theHasherKey,
        (int) $theHasherKey,
        1,
        1,));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    public function getCohareCountByHareAllHashes(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');
      $theKennelKey = $request->request->get('kennel_id');

      #Define the sql statement to execute
      $theSql = COHARE_COUNT_BY_HARE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $theKennelKey,
        (int) $theHasherKey,
        (int) $theHasherKey,
        0,
        1,));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

}
