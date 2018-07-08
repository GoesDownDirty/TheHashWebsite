<?php

namespace HASH\Controller;

require_once realpath(__DIR__ . '/../..').'/config/SQL_Queries.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Wamania\Snowball\English;




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


  public function kennelEventsHeatMap(Request $request, Application $app, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Obtain the hashes
    $sqlTheHashes = "SELECT HASHES.* FROM HASHES
    WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $kennelKy));

    #Obtain the average lat
    $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HASHES
    WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $kennelKy));
    $avgLat = $theAverageLatLong['THE_LAT'];
    $avgLng = $theAverageLatLong['THE_LNG'];

    # Establish and set the return value
    $returnValue = $app['twig']->render('generic_heat_map_page.twig',array(
      'pageTitle' => 'The Kennel Heat Map',
      'pageSubTitle' => 'Location of all the hashes',
      'kennel_abbreviation' => $kennel_abbreviation,
      'the_hashes' => $theHashes,
      'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
      'avg_lat' => $avgLat,
      'avg_lng' => $avgLng
    ));

    # Return the return value
    return $returnValue;


  }

  public function kennelEventsClusterMap(Request $request, Application $app, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Obtain the hashes
    $sqlTheHashes = "SELECT HASHES.* FROM HASHES
    WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $kennelKy));

    #Obtain the average lat
    $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HASHES
    WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $kennelKy));
    $avgLat = $theAverageLatLong['THE_LAT'];
    $avgLng = $theAverageLatLong['THE_LNG'];

    # Establish and set the return value
    $returnValue = $app['twig']->render('generic_cluster_map_page.twig',array(
      'pageTitle' => 'The Kennel Cluster Map',
      'pageSubTitle' => 'Location of all the hashes',
      'kennel_abbreviation' => $kennel_abbreviation,
      'the_hashes' => $theHashes,
      'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
      'avg_lat' => $avgLat,
      'avg_lng' => $avgLng
    ));

    # Return the return value
    return $returnValue;


  }

  public function kennelEventsMarkerMap(Request $request, Application $app, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Obtain the hashes
    $sqlTheHashes = "SELECT HASHES.* FROM HASHES
    WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $kennelKy));

    #Obtain the average lat
    $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HASHES
    WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $kennelKy));
    $avgLat = $theAverageLatLong['THE_LAT'];
    $avgLng = $theAverageLatLong['THE_LNG'];

    # Establish and set the return value
    $returnValue = $app['twig']->render('generic_marker_map_page.twig',array(
      'pageTitle' => 'The Kennel Marker Map',
      'pageSubTitle' => 'Location of all the hashes',
      'kennel_abbreviation' => $kennel_abbreviation,
      'the_hashes' => $theHashes,
      'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
      'avg_lat' => $avgLat,
      'avg_lng' => $avgLng
    ));

    # Return the return value
    return $returnValue;


  }

    #Landing screen for year in review
    public function getYearInReviewAction(Request $request, Application $app, int $year_value, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Establish the page title
      $pageTitle = "$year_value: Year in review";

      #Obtain number of hashes
      $hashCount = ($app['db']->fetchAssoc(PER_KENNEL_HASH_COUNTS_BY_YEAR,array((int)$year_value, (int) $kennelKy)))['THE_COUNT'];

      #Obtain number of hyper hashes
      $hyperHashCount = ($app['db']->fetchAssoc(PER_KENNEL_HYPER_HASH_COUNTS_BY_YEAR,array((int)$year_value, (int) $kennelKy)))['THE_COUNT'];

      #Obtain number of true hashes
      $trueHashCount = ($app['db']->fetchAssoc(PER_KENNEL_TRUE_HASH_COUNTS_BY_YEAR,array((int)$year_value, (int) $kennelKy)))['THE_COUNT'];

      #Obtain number of hashers
      $hasherCount = ($app['db']->fetchAssoc(PER_KENNEL_HASHERS_COUNT_BY_YEAR,array((int)$year_value, (int) $kennelKy)))['THE_COUNT'];

      #Obtain number of overall hares
      $overallHareCount = ($app['db']->fetchAssoc(PER_KENNEL_HARES_COUNT_BY_YEAR,array((int)$year_value, (int) $kennelKy)))['THE_COUNT'];

      #Obtain number of hyper hares
      $hyperHareCount = ($app['db']->fetchAssoc(PER_KENNEL_HYPER_HARES_COUNT_BY_YEAR,array((int)$year_value, (int) $kennelKy)))['THE_COUNT'];

      #Obtain number of true hares
      $trueHareCount = ($app['db']->fetchAssoc(PER_KENNEL_TRUE_HARES_COUNT_BY_YEAR,array((int)$year_value, (int) $kennelKy)))['THE_COUNT'];

      # Obtain the number of newbie hashers
      $newHashers = $app['db']->fetchAll(NEW_HASHERS_FOR_THIS_YEAR, array((int) $kennelKy,(int) $kennelKy, (int)$year_value));
      $newHashersCount = count($newHashers);

      # Obtain the number of newbie true hares
      $newTrueHares = $app['db']->fetchAll(NEW_HARES_FOR_THIS_YEAR, array(0,0,(int) $kennelKy,0,0,(int) $kennelKy, 0,0,(int)$year_value));
      $newTrueHaresCount = count($newTrueHares);

      # Obtain the number of newbie hyper hares
      $newHyperHares = $app['db']->fetchAll(NEW_HARES_FOR_THIS_YEAR, array(1,1,(int) $kennelKy,1,1,(int) $kennelKy, 1,1,(int)$year_value));
      $newHyperHaresCount = count($newHyperHares);

      # Obtain the number of newbie hyper hares
      $newOverallHares = $app['db']->fetchAll(NEW_HARES_FOR_THIS_YEAR, array(0,1,(int) $kennelKy,0,1,(int) $kennelKy, 0,1,(int)$year_value));
      $newOverallHaresCount = count($newOverallHares);

      #Establish the return value
      $returnValue = $app['twig']->render('year_in_review.twig', array (
        'pageTitle' => $pageTitle,
        'yearValue' => $year_value,
        'kennel_abbreviation' => $kennel_abbreviation,
        'hash_count' => $hashCount,
        'hyper_hash_count' => $hyperHashCount,
        'true_hash_count' => $trueHashCount,
        'hasher_count' => $hasherCount,
        'overall_hare_count' => $overallHareCount,
        'true_hare_count' => $trueHareCount,
        'hyper_hare_counts' => $hyperHareCount,
        'newbie_hashers_count' => $newHashersCount,
        'newbie_true_hares_count' => $newTrueHaresCount,
        'newbie_hyper_hares_count' => $newHyperHaresCount,
        'newbie_overall_hares_count' => $newOverallHaresCount
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

    #Obtain total hare counts per year
    public function getNewbieHasherListByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = NEW_HASHERS_FOR_THIS_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array((int) $kennelKy,(int) $kennelKy,(int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    public function getNewbieTrueHareListByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = NEW_HARES_FOR_THIS_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array(
        0,0,
        (int) $kennelKy,
        0,0,
        (int) $kennelKy,
        0,0,
        (int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    public function getNewbieHyperHareListByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = NEW_HARES_FOR_THIS_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array(
        1,1,
        (int) $kennelKy,
        1,1,
        (int) $kennelKy,
        1,1,
        (int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }


    public function getNewbieOverallHareListByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = NEW_HARES_FOR_THIS_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array(
        0,1,
        (int) $kennelKy,
        0,1,
        (int) $kennelKy,
        0,1,
        (int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    #Obtain the first hash of a given hasher
    public function getHashersVirginHash(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Define the sql statement to execute
      $theSql = SELECT_HASHERS_VIRGIN_HASH;

      #Query the database
      $theirVirginHash = $app['db']->fetchAssoc($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theirVirginHash,200);
      return $returnValue;

    }

    public function getKennelsVirginHash(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = SELECT_KENNELS_VIRGIN_HASH;

      #Query the database
      $theirVirginHash = $app['db']->fetchAssoc($theSql, array((int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theirVirginHash,200);
      return $returnValue;

    }

    #Obtain the latest hash of a given hasher
    public function getHashersLatestHash(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = SELECT_HASHERS_MOST_RECENT_HASH;

      #Query the database
      $theirLatestHash = $app['db']->fetchAssoc($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theirLatestHash,200);
      return $returnValue;

    }

    public function getKennelsLatestHash(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = SELECT_KENNELS_MOST_RECENT_HASH;

      #Query the database
      $theirLatestHash = $app['db']->fetchAssoc($theSql, array((int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theirLatestHash,200);
      return $returnValue;

    }


    #Obtain the hasher hashes attended by year
    public function getHasherHashesByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_YEAR;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    #Obtain the hasher hashes attended by quarter
    public function getHasherHashesByQuarter(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_QUARTER;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    #Obtain the hasher hashes attended by quarter
    public function getHasherHashesByMonth(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_MONTH;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    #Obtain the hasher hashes attended by day name
    public function getHasherHashesByDayName(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_DAYNAME;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    #Obtain the hasher hashes attended by state
    public function getHasherHashesByState(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_STATE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    #Obtain the hasher hashes attended by city
    public function getHasherHashesByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_HASH_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getKennelHashesByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = KENNEL_HASH_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getKennelHashesByCounty(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = KENNEL_HASH_COUNTS_BY_COUNTY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getKennelHashesByPostalcode(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = KENNEL_HASH_COUNTS_BY_POSTAL_CODE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    public function getHasherAllHaringsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_YEAR;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByQuarter(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_QUARTER;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByMonth(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_MONTH;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByDayName(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_DAYNAME;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByState(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_STATE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherAllHaringsByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_ALL_HARING_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    # Mappings for hasher (non hyper) harings by (year/month/state/etc)
    public function getHasherNonHyperHaringsByYear(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_YEAR;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByQuarter(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_QUARTER;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByMonth(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_MONTH;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByDayName(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_DAYNAME;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherNonHyperHaringsByState(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_STATE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }


    public function getHasherNonHyperHaringsByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_NONHYPER_HARING_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }

    public function getHasherHyperHaringsByCity(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = HASHER_HYPER_HARING_COUNTS_BY_CITY;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $theHasherKey, (int) $kennelKy));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }



    public function getCohareCountByHareNonHypers(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the post values
      $theHasherKey = $request->request->get('hasher_id');

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = COHARE_COUNT_BY_HARE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
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

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = COHARE_COUNT_BY_HARE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
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

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = COHARE_COUNT_BY_HARE;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) $theHasherKey,
        (int) $theHasherKey,
        0,
        1,));

      #Set the return value
      $returnValue = $app->json($theResults,200);
      return $returnValue;

    }




    public function quickestToReachAnalversaryByDaysAction(Request $request, Application $app, string $kennel_abbreviation, int $analversary_number){




            #Obtain the kennel key
            $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

            #Obtain the analversary number, then subtract one (for the query requires it)
            $modifiedAnalversaryNumber = $analversary_number -1;

            #Define the sql statement to execute
            #$theSql = FASTEST_HASHERS_TO_ANALVERSARIES;
            $theSql = str_replace("XLIMITX",$modifiedAnalversaryNumber,FASTEST_HASHERS_TO_ANALVERSARIES2);
            $theSql = str_replace("XORDERX","ASC",$theSql);
            $theSql = str_replace("XORDERCOLUMNX","DAYS_TO_REACH_ANALVERSARY",$theSql);

            #Query the database
            $theResults = $app['db']->fetchAll($theSql, array((int) $kennelKy,(int) $kennelKy));

            #Define the page title
            $pageTitle = "Quickest to reach $analversary_number hashes";

            #Set the return value
            $returnValue = $app['twig']->render('analversaries_achievements_non_json.twig',array(
              'pageTitle' => $pageTitle,
              'tableCaption' => 'Faster is better',
              'pageSubTitle' => 'Measured in days',
              #'subTitle1' => 'Standard Statistics',
              #'subTitle2' => 'Analversary Statistics',
              #'subTitle3' => 'Hare Statistics',
              #'subTitle4' => 'Other Statistics',
              #'url_value' => $urlValue,
              'theList' => $theResults,
              'analversary_number' => $analversary_number,
              'kennel_abbreviation' => $kennel_abbreviation
            ));

            return $returnValue;
          }

          public function quickestToReachAnalversaryByDate(Request $request, Application $app, string $kennel_abbreviation, int $analversary_number){

                  #Obtain the kennel key
                  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

                  #Obtain the analversary number, then subtract one (for the query requires it)
                  $modifiedAnalversaryNumber = $analversary_number -1;

                  #Define the sql statement to execute
                  #$theSql = FASTEST_HASHERS_TO_ANALVERSARIES;
                  $theSql = str_replace("XLIMITX",$modifiedAnalversaryNumber,FASTEST_HASHERS_TO_ANALVERSARIES2);
                  $theSql = str_replace("XORDERX","ASC",$theSql);
                  $theSql = str_replace("XORDERCOLUMNX","ANALVERSARY_DATE",$theSql);

                  #Query the database
                  $theResults = $app['db']->fetchAll($theSql, array((int) $kennelKy,(int) $kennelKy));

                  #Define the page title
                  $pageTitle = "Chronological order of analversaries";
                  $pageSubTitle = "($analversary_number hashes)";

                  #Set the return value
                  $returnValue = $app['twig']->render('analversaries_achievements_chronological.twig',array(
                    'pageTitle' => $pageTitle,
                    'tableCaption' => '',
                    'pageSubTitle' => $pageSubTitle,
                    #'subTitle1' => 'Standard Statistics',
                    #'subTitle2' => 'Analversary Statistics',
                    #'subTitle3' => 'Hare Statistics',
                    #'subTitle4' => 'Other Statistics',
                    #'url_value' => $urlValue,
                    'theList' => $theResults,
                    'analversary_number' => $analversary_number,
                    'kennel_abbreviation' => $kennel_abbreviation
                  ));

                  return $returnValue;
                }


    public function slowestToReachAnalversaryByDaysAction(Request $request, Application $app, string $kennel_abbreviation, int $analversary_number){


      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Obtain the analversary number, then subtract one (for the query requires it)
      $modifiedAnalversaryNumber = $analversary_number -1;

      #Define the sql statement to execute
      #$theSql = FASTEST_HASHERS_TO_ANALVERSARIES;
      $theSql = str_replace("XLIMITX",$modifiedAnalversaryNumber,FASTEST_HASHERS_TO_ANALVERSARIES2);
      $theSql = str_replace("XORDERX","DESC",$theSql);
      $theSql = str_replace("XORDERCOLUMNX","DAYS_TO_REACH_ANALVERSARY",$theSql);

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $kennelKy,(int) $kennelKy));

      #Define the page title
      $pageTitle = "Slowest to reach $analversary_number hashes";

      #Set the return value
      $returnValue = $app['twig']->render('analversaries_achievements_non_json.twig',array(
        'pageTitle' => $pageTitle,
        'tableCaption' => 'Faster is better',
        'pageSubTitle' => 'Measured in days',
        #'subTitle1' => 'Standard Statistics',
        #'subTitle2' => 'Analversary Statistics',
        #'subTitle3' => 'Hare Statistics',
        #'subTitle4' => 'Other Statistics',
        #'url_value' => $urlValue,
        'theList' => $theResults,
        'analversary_number' => $analversary_number,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      return $returnValue;
    }


    public function getLongestStreaksAction(Request $request, Application $app, string $kennel_abbreviation){


      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql statement to execute
      $theSql = THE_LONGEST_STREAKS;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array((int) $kennelKy));

      #Define the page title
      $pageTitle = "The longest streaks";

      #Set the return value
      $returnValue = $app['twig']->render('name_number_list.twig',array(
        'pageTitle' => $pageTitle,
        'tableCaption' => 'Longest streak per hasher',

        'columnOneName' => 'Hasher Name',
        'columnTwoName' => 'Streak Length',
        'theList' => $theResults,
        'kennel_abbreviation' => $kennel_abbreviation,
        'pageTracking' => 'LongestStreaks'
      ));

      return $returnValue;
    }

    public function longestCareerAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LONGEST_HASHING_CAREER_IN_DAYS;
      $theSql = str_replace("XORDERCOLUMNX","DIFFERENCE",LONGEST_HASHING_CAREER_IN_DAYS);
      $theSql = str_replace("XUPORDOWNX","DESC",$theSql);

      #Define the minimum hashing count
      $minHashingCount = 4;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) $kennelKy,
        (int) $kennelKy,
        (int)$minHashingCount
      ));

      #Define the page sub title
      $pageSubTitle = "Days between first hashes and most recent hashes";

      #Define the table caption
      $tableCaption = "Minimum hashing count: $minHashingCount";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('career_length_by_day.twig',array(
        'pageTitle' => "Longest Hashing Career (By Days)",
        'pageSubTitle' => $pageSubTitle,
        'tableCaption' => $tableCaption,
        #'pageCaption' => $pageCaption,
        #'subTitle1' => 'Standard Statistics',
        #'subTitle2' => 'Analversary Statistics',
        #'subTitle3' => 'Hare Statistics',
        #'subTitle4' => 'Other Statistics',
        #'url_value' => $urlValue,
        'theList' => $theResults,
        #'analversary_number' => $analversary_number,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }



    public function everyonesLatestHashesAction(Request $request, Application $app, string $kennel_abbreviation, int $min_hash_count){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LONGEST_HASHING_CAREER_IN_DAYS;
      $theSql = str_replace("XORDERCOLUMNX","LATEST_HASH_DATE",LONGEST_HASHING_CAREER_IN_DAYS);
      $theSql = str_replace("XUPORDOWNX","DESC",$theSql);

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) $kennelKy,
        (int) $kennelKy,
        (int) $min_hash_count
      ));

      #Define the page sub title
      $pageSubTitle = "Everyone's latest hash, sorted by date";

      #Define the table caption
      $tableCaption = "Minimum hashing count: $min_hash_count";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('career_length_by_day.twig',array(
        'pageTitle' => $pageSubTitle,
        'pageSubTitle' => "",
        'tableCaption' => $tableCaption,
        #'pageCaption' => $pageCaption,
        #'subTitle1' => 'Standard Statistics',
        #'subTitle2' => 'Analversary Statistics',
        #'subTitle3' => 'Hare Statistics',
        #'subTitle4' => 'Other Statistics',
        #'url_value' => $urlValue,
        'theList' => $theResults,
        #'analversary_number' => $analversary_number,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }

    public function everyonesFirstHashesAction(Request $request, Application $app, string $kennel_abbreviation, int $min_hash_count){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LONGEST_HASHING_CAREER_IN_DAYS;
      $theSql = str_replace("XORDERCOLUMNX","FIRST_HASH_DATE",LONGEST_HASHING_CAREER_IN_DAYS);
      $theSql = str_replace("XUPORDOWNX","DESC",$theSql);

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) $kennelKy,
        (int) $kennelKy,
        (int)$min_hash_count
      ));

      #Define the page sub title
      $pageSubTitle = "Everyone's first hash, sorted by date";

      #Define the table caption
      $tableCaption = "Minimum hashing count: $min_hash_count";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('career_length_by_day.twig',array(
        'pageTitle' => $pageSubTitle,
        'pageSubTitle' => "",
        'tableCaption' => $tableCaption,
        #'pageCaption' => $pageCaption,
        #'subTitle1' => 'Standard Statistics',
        #'subTitle2' => 'Analversary Statistics',
        #'subTitle3' => 'Hare Statistics',
        #'subTitle4' => 'Other Statistics',
        #'url_value' => $urlValue,
        'theList' => $theResults,
        #'analversary_number' => $analversary_number,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }

    public function highestAverageDaysBetweenHashesAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LONGEST_HASHING_CAREER_IN_DAYS;
      $theSql = str_replace("XORDERCOLUMNX","DAYS_BETWEEN_HASHES",LONGEST_HASHING_CAREER_IN_DAYS);
      $theSql = str_replace("XUPORDOWNX","DESC",$theSql);

      #Define the minimum hashing count
      $minHashingCount = 2;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) $kennelKy,
        (int) $kennelKy,
        (int)$minHashingCount
      ));

      #Define the page sub title
      $pageSubTitle = "Days between first and last hashes, divided by pi";

      #Define the table caption
      $tableCaption = "Minimum hashing count: $minHashingCount";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('career_length_by_day.twig',array(
        'pageTitle' => "Average days between hashing",
        'pageSubTitle' => $pageSubTitle,
        'tableCaption' => $tableCaption,
        #'pageCaption' => $pageCaption,
        #'subTitle1' => 'Standard Statistics',
        #'subTitle2' => 'Analversary Statistics',
        #'subTitle3' => 'Hare Statistics',
        #'subTitle4' => 'Other Statistics',
        #'url_value' => $urlValue,
        'theList' => $theResults,
        #'analversary_number' => $analversary_number,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }


    public function lowestAverageDaysBetweenHashesAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LONGEST_HASHING_CAREER_IN_DAYS;
      $theSql = str_replace("XORDERCOLUMNX","DAYS_BETWEEN_HASHES",LONGEST_HASHING_CAREER_IN_DAYS);
      $theSql = str_replace("XUPORDOWNX","ASC",$theSql);

      #Define the minimum hashing count
      $minHashingCount = 6;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) $kennelKy,
        (int) $kennelKy,
        (int)$minHashingCount
      ));

      #Define the page sub title
      $pageSubTitle = "Days between first and last hashes, divided by pi";

      #Define the table caption
      $tableCaption = "Minimum hashing count: $minHashingCount";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('career_length_by_day.twig',array(
        'pageTitle' => "Average days between hashing",
        'pageSubTitle' => $pageSubTitle,
        'tableCaption' => $tableCaption,
        #'pageCaption' => $pageCaption,
        #'subTitle1' => 'Standard Statistics',
        #'subTitle2' => 'Analversary Statistics',
        #'subTitle3' => 'Hare Statistics',
        #'subTitle4' => 'Other Statistics',
        #'url_value' => $urlValue,
        'theList' => $theResults,
        #'analversary_number' => $analversary_number,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }



    public function lowestAverageDaysBetweenAllHaringsAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LOWEST_NUMBER_OF_DAYS_BETWEEN_HARINGS;
      $theSql = str_replace("XORDERCOLUMNX","DAYS_BETWEEN_HARINGS",$theSql);
      $theSql = str_replace("XUPORDOWNX","ASC",$theSql);

      #Define the minimum haring count
      $minHaringCount = 2;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) 0,
        (int) 1,
        (int) $kennelKy,
        (int) 0,
        (int) 1,
        (int) $kennelKy,
        (int) 0,
        (int) 1,
        (int)$minHaringCount
      ));

      #Define the page sub title
      $pageSubTitle = "Days between first and last harings, divided by pi";

      #Define the table caption
      $tableCaption = "Minimum haring count: $minHaringCount";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('haring_career_length_by_day.twig',array(
        'pageTitle' => "Average days between harings",
        'pageSubTitle' => $pageSubTitle,
        'tableCaption' => $tableCaption,
        'theList' => $theResults,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }

    public function highestAverageDaysBetweenAllHaringsAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LOWEST_NUMBER_OF_DAYS_BETWEEN_HARINGS;
      $theSql = str_replace("XORDERCOLUMNX","DAYS_BETWEEN_HARINGS",$theSql);
      $theSql = str_replace("XUPORDOWNX","DESC",$theSql);

      #Define the minimum haring count
      $minHaringCount = 2;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) 0,
        (int) 1,
        (int) $kennelKy,
        (int) 0,
        (int) 1,
        (int) $kennelKy,
        (int) 0,
        (int) 1,
        (int)$minHaringCount
      ));

      #Define the page sub title
      $pageSubTitle = "Days between first and last harings, divided by pi";

      #Define the table caption
      $tableCaption = "Minimum haring count: $minHaringCount";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('haring_career_length_by_day.twig',array(
        'pageTitle' => "Average days between harings",
        'pageSubTitle' => $pageSubTitle,
        'tableCaption' => $tableCaption,
        'theList' => $theResults,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }



    public function lowestAverageDaysBetweenNonHyperHaringsAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LOWEST_NUMBER_OF_DAYS_BETWEEN_HARINGS;
      $theSql = str_replace("XORDERCOLUMNX","DAYS_BETWEEN_HARINGS",$theSql);
      $theSql = str_replace("XUPORDOWNX","ASC",$theSql);

      #Define the minimum haring count
      $minHaringCount = 5;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) 0,
        (int) 0,
        (int) $kennelKy,
        (int) 0,
        (int) 0,
        (int) $kennelKy,
        (int) 0,
        (int) 0,
        (int)$minHaringCount
      ));

      #Define the page sub title
      $pageSubTitle = "Days between first and last harings (non-hyper hashes only)";

      #Define the table caption
      $tableCaption = "Minimum haring count: $minHaringCount";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('haring_career_length_by_day.twig',array(
        'pageTitle' => "Average days between harings",
        'pageSubTitle' => $pageSubTitle,
        'tableCaption' => $tableCaption,
        'theList' => $theResults,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }


    public function highestAverageDaysBetweenNonHyperHaringsAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the sql
      $theSql = LOWEST_NUMBER_OF_DAYS_BETWEEN_HARINGS;
      $theSql = str_replace("XORDERCOLUMNX","DAYS_BETWEEN_HARINGS",$theSql);
      $theSql = str_replace("XUPORDOWNX","DESC",$theSql);

      #Define the minimum haring count
      $minHaringCount = 2;

      #Query the database
      $theResults = $app['db']->fetchAll($theSql, array(
        (int) $kennelKy,
        (int) 0,
        (int) 0,
        (int) $kennelKy,
        (int) 0,
        (int) 0,
        (int) $kennelKy,
        (int) 0,
        (int) 0,
        (int)$minHaringCount
      ));

      #Define the page sub title
      $pageSubTitle = "Days between first and last harings (non-hyper hashes only)";

      #Define the table caption
      $tableCaption = "Minimum haring count: $minHaringCount";

      #Add the results into the twig template
      $returnValue = $app['twig']->render('haring_career_length_by_day.twig',array(
        'pageTitle' => "Average days between harings",
        'pageSubTitle' => $pageSubTitle,
        'tableCaption' => $tableCaption,
        'theList' => $theResults,
        'kennel_abbreviation' => $kennel_abbreviation
      ));

      #Return the return value
      return $returnValue;

    }




    public function viewAttendanceChartsAction(Request $request, Application $app, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      # Obtain the average event attendance per year
      $sqlAvgEvtParticipationByYear = "SELECT
            YEAR(THE_DATE) AS THE_VALUE,
            AVG(THE_COUNT) AS THE_COUNT
        FROM (
        		SELECT
        			HASHES.HASH_KY AS THE_KEY,
        			HASHES.EVENT_DATE AS THE_DATE,
        			COUNT(*) AS THE_COUNT
        		FROM HASHES JOIN HASHINGS ON HASHES.HASH_KY = HASHINGS.HASH_KY
        		WHERE KENNEL_KY = ?
        		GROUP BY HASHES.HASH_KY
            ) TEMPORARY_TABLE
        GROUP BY YEAR(THE_DATE)";
      $avgEvtParticipationByYear = $app['db']->fetchAll($sqlAvgEvtParticipationByYear, array((int) $kennelKy));

      # Obtain the average event attendance per (year/month)
      $sqlAvgEvtParticipationByYearMonth = "SELECT
            DATE_FORMAT(THE_DATE,'%Y/%m') AS THE_VALUE,
            AVG(THE_COUNT) AS THE_COUNT
        FROM (
            SELECT
              HASHES.HASH_KY AS THE_KEY,
              HASHES.EVENT_DATE AS THE_DATE,
              COUNT(*) AS THE_COUNT
            FROM HASHES JOIN HASHINGS ON HASHES.HASH_KY = HASHINGS.HASH_KY
            WHERE KENNEL_KY = ?
            GROUP BY HASHES.HASH_KY
            ) TEMPORARY_TABLE
        GROUP BY DATE_FORMAT(THE_DATE,'%Y/%m')";
      $avgEvtParticipationByYearMonth = $app['db']->fetchAll($sqlAvgEvtParticipationByYearMonth, array((int) $kennelKy));

      # Obtain the average event attendance per (year/quarter)
      $sqlAvgEvtParticipationByYearQuarter = "SELECT
            CONCAT_WS('/',YEAR(THE_DATE),QUARTER(THE_DATE)) AS THE_VALUE,
            AVG(THE_COUNT) AS THE_COUNT
        FROM (
            SELECT
              HASHES.HASH_KY AS THE_KEY,
              HASHES.EVENT_DATE AS THE_DATE,
              COUNT(*) AS THE_COUNT
            FROM HASHES JOIN HASHINGS ON HASHES.HASH_KY = HASHINGS.HASH_KY
            WHERE KENNEL_KY = ?
            GROUP BY HASHES.HASH_KY
            ) TEMPORARY_TABLE
        GROUP BY CONCAT_WS('/',YEAR(THE_DATE),QUARTER(THE_DATE))";
      $avgEvtParticipationByYearQuarter = $app['db']->fetchAll($sqlAvgEvtParticipationByYearQuarter, array((int) $kennelKy));


      # Obtain the average event attendance per (year/month)
      $sqlAvgEvtParticipationByMonth = "SELECT
            DATE_FORMAT(THE_DATE,'%m') AS THE_VALUE,
            AVG(THE_COUNT) AS THE_COUNT
        FROM (
            SELECT
              HASHES.HASH_KY AS THE_KEY,
              HASHES.EVENT_DATE AS THE_DATE,
              COUNT(*) AS THE_COUNT
            FROM HASHES JOIN HASHINGS ON HASHES.HASH_KY = HASHINGS.HASH_KY
            WHERE KENNEL_KY = ?
            GROUP BY HASHES.HASH_KY
            ) TEMPORARY_TABLE
        GROUP BY DATE_FORMAT(THE_DATE,'%m')";
      $avgEvtParticipationByMonth = $app['db']->fetchAll($sqlAvgEvtParticipationByMonth, array((int) $kennelKy));

      # Establish and set the return value
      $returnValue = $app['twig']->render('event_participation_charts.twig',array(
        'pageTitle' => 'Event Participation Statistics',
        'firstHeader' => 'FIRST HEADER',
        'secondHeader' => 'SECOND HEADER',
        'kennel_abbreviation' => $kennel_abbreviation,
        'Avg_Evt_Participation_By_Year_List' => $avgEvtParticipationByYear,
        'Avg_Evt_Participation_By_YearMonth_List' => $avgEvtParticipationByYearMonth,
        'Avg_Evt_Participation_By_YearQuarter_List' => $avgEvtParticipationByYearQuarter,
        'Avg_Evt_Participation_By_Month_List' => $avgEvtParticipationByMonth
      ));

      # Return the return value
      return $returnValue;

    }


    public function viewFirstTimersChartsAction(Request $request, Application $app, string $kennel_abbreviation, int $min_hash_count){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      # Obtain the average event attendance per year
      $sqlNewComersByYear = NEWCOMERS_BY_YEAR;
      $newComersByYear = $app['db']->fetchAll($sqlNewComersByYear, array((int) $kennelKy,(int) $kennelKy, $min_hash_count));

      # Obtain the average event attendance per (year/month)
      $sqlNewComersByYearQuarter = NEWCOMERS_BY_YEAR_QUARTER;
      $newComersByYearQuarter = $app['db']->fetchAll($sqlNewComersByYearQuarter, array((int) $kennelKy, (int) $kennelKy, $min_hash_count));

      # Obtain the average event attendance per (year/quarter)
      $sqlNewComersByYearMonth = NEWCOMERS_BY_YEAR_MONTH;
      $newComersByYearMonth = $app['db']->fetchAll($sqlNewComersByYearMonth, array((int) $kennelKy, (int) $kennelKy, $min_hash_count));


      # Obtain the average event attendance per (year/month)
      $sqlNewComersByMonth = NEWCOMERS_BY_MONTH;
      $newComersByMonth = $app['db']->fetchAll($sqlNewComersByMonth, array((int) $kennelKy,(int) $kennelKy, $min_hash_count));

      # Establish and set the return value
      $returnValue = $app['twig']->render('newcomers_charts.twig',array(
        'pageTitle' => 'First Timers / New Comers Statistics',
        'firstHeader' => 'FIRST HEADER',
        'secondHeader' => 'SECOND HEADER',
        'kennel_abbreviation' => $kennel_abbreviation,
        'New_Comers_By_Year_List' => $newComersByYear,
        'New_Comers_By_YearMonth_List' => $newComersByYearMonth,
        'New_Comers_By_YearQuarter_List' => $newComersByYearQuarter,
        'New_Comers_By_Month_List' => $newComersByMonth,
        'Min_Hash_Count' => $min_hash_count
      ));

      # Return the return value
      return $returnValue;

    }

    public function viewLastTimersChartsAction(Request $request, Application $app, string $kennel_abbreviation, int $min_hash_count, int $month_count){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      # Obtain the average event attendance per year
      $sqlLastComersByYear = DEPARTERS_BY_YEAR;
      $lastComersByYear = $app['db']->fetchAll($sqlLastComersByYear, array((int) $kennelKy,(int) $kennelKy, $min_hash_count, $month_count));

      # Obtain the average event attendance per (year/month)
      $sqlLastComersByYearQuarter = DEPARTERS_BY_YEAR_QUARTER;
      $lastComersByYearQuarter = $app['db']->fetchAll($sqlLastComersByYearQuarter, array((int) $kennelKy, (int) $kennelKy, $min_hash_count, $month_count));

      # Obtain the average event attendance per (year/quarter)
      $sqlLastComersByYearMonth = DEPARTERS_BY_YEAR_MONTH;
      $lastComersByYearMonth = $app['db']->fetchAll($sqlLastComersByYearMonth, array((int) $kennelKy, (int) $kennelKy, $min_hash_count, $month_count));


      # Obtain the average event attendance per (year/month)
      $sqlLastComersByMonth = DEPARTERS_BY_MONTH;
      $lastComersByMonth = $app['db']->fetchAll($sqlLastComersByMonth, array((int) $kennelKy,(int) $kennelKy, $min_hash_count, $month_count));

      # Establish and set the return value
      $returnValue = $app['twig']->render('lastcomers_charts.twig',array(
        'pageTitle' => 'Last Comers Statistics',
        'firstHeader' => 'FIRST HEADER',
        'secondHeader' => 'SECOND HEADER',
        'kennel_abbreviation' => $kennel_abbreviation,
        'Last_Comers_By_Year_List' => $lastComersByYear,
        'Last_Comers_By_YearMonth_List' => $lastComersByYearMonth,
        'Last_Comers_By_YearQuarter_List' => $lastComersByYearQuarter,
        'Last_Comers_By_Month_List' => $lastComersByMonth,
        'Min_Hash_Count' => $min_hash_count,
        'Month_Count' => $month_count
      ));

      # Return the return value
      return $returnValue;

    }




    public function trendingHashersAction(Request $request, Application $app, string $kennel_abbreviation, int $day_count){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Establish the row limit
      $rowLimit = 15;

      # Obtain the average event attendance per year
      $sqlTrendingHashers = "SELECT
        	HASHERS.HASHER_NAME AS THE_VALUE,
        	COUNT(*) AS THE_COUNT
        FROM
        	HASHERS
        	JOIN HASHINGS ON HASHERS.HASHER_KY = HASHINGS.HASHER_KY
        	JOIN HASHES on HASHINGS.HASH_KY = HASHES.HASH_KY
        WHERE HASHES.KENNEL_KY = ?
        AND EVENT_DATE >= (CURRENT_DATE - INTERVAL ? DAY)
        GROUP BY HASHERS.HASHER_NAME
        ORDER BY THE_COUNT DESC
        LIMIT $rowLimit";
      $trendingHashersList = $app['db']->fetchAll($sqlTrendingHashers, array((int) $kennelKy, (int) $day_count));

      # Establish and set the return value
      $returnValue = $app['twig']->render('trending_hashers_charts.twig',array(
        'pageTitle' => 'Trending Hashers',
        'firstHeader' => 'FIRST HEADER',
        'secondHeader' => 'SECOND HEADER',
        'kennel_abbreviation' => $kennel_abbreviation,
        'trending_hashers_list' => $trendingHashersList,
        'day_count' => $day_count,
        'row_limit' => $rowLimit
      ));

      # Return the return value
      return $returnValue;

    }

    public function trendingTrueHaresAction(Request $request, Application $app, string $kennel_abbreviation, int $day_count){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Establish the row limit
      $rowLimit = 15;

      # Obtain the average event attendance per year
      $sqlTrendingTrueHares = "SELECT
      	HASHERS.HASHER_NAME AS THE_VALUE,
      	COUNT(*) AS THE_COUNT
      FROM
      	HASHERS
      	JOIN HARINGS ON HASHERS.HASHER_KY = HARINGS.HARINGS_HASHER_KY
      	JOIN HASHES on HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
      WHERE HASHES.KENNEL_KY = ?
        AND HASHES.IS_HYPER = 0
        AND EVENT_DATE >= (CURRENT_DATE - INTERVAL ? DAY)
      GROUP BY HASHERS.HASHER_NAME
      ORDER BY THE_COUNT DESC
      LIMIT $rowLimit";
      $trendingTrueHaresList = $app['db']->fetchAll($sqlTrendingTrueHares, array((int) $kennelKy, (int) $day_count));

      # Establish and set the return value
      $returnValue = $app['twig']->render('trending_true_hares_charts.twig',array(
        'pageTitle' => 'Trending True Hares',
        'firstHeader' => 'FIRST HEADER',
        'secondHeader' => 'SECOND HEADER',
        'kennel_abbreviation' => $kennel_abbreviation,
        'trending_true_hares_list' => $trendingTrueHaresList,
        'day_count' => $day_count,
        'row_limit' => $rowLimit
      ));

      # Return the return value
      return $returnValue;

    }


    #Define the action
    public function unTrendingTrueHaresJsonPreAction(Request $request,
          Application $app,
          string $kennel_abbreviation,
          int $day_count,
          int $min_hash_count,
          int $max_percentage,
          int $row_limit){

      # Establish and set the return value
      $returnValue = $app['twig']->render('un_trending_true_hares_charts_json.twig',array(
        'pageTitle' => 'Un-Trending True Hares',
        'pageSubTitle' => 'The List of *ALL* Hashers',
        'kennel_abbreviation' => $kennel_abbreviation,
        'day_count' => $day_count,
        'row_limit' => $row_limit,
        'min_hash_count' => $min_hash_count,
        'max_percentage' => $max_percentage
      ));

      #Return the return value
      return $returnValue;

    }



    public function unTrendingTrueHaresJsonPostAction(
      Request $request,
      Application $app,
      string $kennel_abbreviation,
      int $day_count,
      int $min_hash_count,
      int $max_percentage,
      int $row_limit){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      # Obtain the average event attendance per year
      $sqlUnTrendingTrueHares = "SELECT
            HASHER_NAME,
            ((NON_HYPER_HARE_COUNT/HASH_COUNT)*100) AS NON_HYPER_HARING_TO_HASHING_PERCENTAGE,
            HASH_COUNT,
            NON_HYPER_HARE_COUNT,
            HASHER_KY
        FROM
          (
          SELECT
            HASHERS.*,
            HASHERS.HASHER_KY AS OUTER_HASHER_KY,
            (
              SELECT COUNT(*)
              FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
              WHERE
              HASHINGS.HASHER_KY = OUTER_HASHER_KY
                      AND HASHES.KENNEL_KY = ?
                      AND EVENT_DATE >= (CURRENT_DATE - INTERVAL ? DAY)) AS HASH_COUNT,
            (
              SELECT COUNT(*)
              FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
              WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
              AND HASHES.KENNEL_KY = ?
              AND HASHES.IS_HYPER = 0
                  AND EVENT_DATE >= (CURRENT_DATE - INTERVAL ? DAY)) AS NON_HYPER_HARE_COUNT
          FROM
            HASHERS
        )
        MAIN_TABLE
        WHERE HASH_COUNT > ?
        AND ((NON_HYPER_HARE_COUNT/HASH_COUNT)*100) < $max_percentage
        ORDER BY NON_HYPER_HARING_TO_HASHING_PERCENTAGE ,HASH_COUNT DESC
      LIMIT $row_limit";
      $unTrendingTrueHaresList = $app['db']->fetchAll(
        $sqlUnTrendingTrueHares,
        array(
          (int) $kennelKy,
          (int) $day_count,
          (int) $kennelKy,
          (int) $day_count,
          (int) $min_hash_count
        ));



        #Establish the output
        $output = array(
          "sEcho" => "foo",
          "day_count" => $day_count,
          "row_limit" => $row_limit,
          "min_hash_count" => $min_hash_count,
          "max_percentage" => $max_percentage,
          "resultList" => $unTrendingTrueHaresList
        );

        #Set the return value
        $returnValue = $app->json($output,200);

        #Return the return value
        return $returnValue;

    }




    #Landing screen for year in review
    public function wordcloudTestAction(Request $request, Application $app, string $kennel_abbreviation){

      #Establish the page title
      $pageTitle = "Top Hashers of 2016";

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the SQL to execute
      $hasherCountSQL = HASHER_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hasherCountList = $app['db']->fetchAll($hasherCountSQL,array((int) 2016, (int) $kennelKy));

      #Establish the return value
      $returnValue = $app['twig']->render('wordcloudtest.twig', array (
        'pageTitle' => $pageTitle,
        'yearValue' => $year_value,
        'kennel_abbreviation' => $kennel_abbreviation,
        'theList' => $hasherCountList
      ));

      #Return the return value
      return $returnValue;

    }

    #Landing screen for year in review
    public function aboutContactAction(Request $request, Application $app, string $kennel_abbreviation){

      #Establish the page title
      $pageTitle = "About this application";

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Establish the return value
      $returnValue = $app['twig']->render('about.twig', array (
        'pageTitle' => $pageTitle,
        'yearValue' => $year_value,
        'kennel_abbreviation' => $kennel_abbreviation,
        'theList' => $hasherCountList,
        'adminEmail' => ADMINISTRATOR_EMAIL
      ));

      #Return the return value
      return $returnValue;

    }

    #Landing screen for year in review
    public function googleGeoCodeTestAction(Request $request, Application $app, string $kennel_abbreviation){

      #Establish the page title
      $pageTitle = "Google Geocode Test";
      $zip = "";
      $streetNumber = "";
      $route = "";
      $neighborhood = "";
      $city = "";
      $county = "";
      $state = "";
      $country = "";
      $lat = "";
      $lng = "";

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      $address = "Paul Brown Stadium Cincinnati OH 45226";
      $address2 = str_replace(" ", "+", $address);


      $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address2&sensor=false");
      $json_decoded = json_decode($json);
      $json_pretty =  json_encode($json_decoded, JSON_PRETTY_PRINT);
      $lat = $json_decoded->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
      $lng = $json_decoded->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};

      $addressComponents = $json_decoded->{'results'}[0]->{'address_components'};
      foreach($addressComponents as $index=>$component){
        $type = $component->types[0];

        if($city=="" && ($type=="sublocality_level_1" || $type=="locality")){
          $city = trim($component->short_name);
        }

        if($state == "" && $type=="administrative_area_level_1"){
          $state = trim($component->short_name);
        }

        if($country == "" && $type=="country"){
          $country = trim($component->short_name);
        }

        if($county == "" && ($type=="administrative_area_level_2")){
          $county = trim($component->short_name);
        }

        if($neighborhood =="" && $type=="neighborhood"){
          $neighborhood = trim($component->short_name);
        }

        if($route =="" && $type=="route"){
          $route = trim($component->short_name);
        }

        if($streetNumber =="" && $type=="street_number"){
          $streetNumber = trim($component->short_name);
        }

        if($zip =="" && $type=="postal_code"){
          $zip = trim($component->short_name);
        }

      }

      #Establish the return value
      $returnValue = $app['twig']->render('googlegeocodetest.twig', array (
        'pageTitle' => $pageTitle,
        'kennel_abbreviation' => $kennel_abbreviation,
        'Address' => "$address2",
        'Zip' => $zip,
        'StreetNumber' => $streetNumber,
        'Route' => $route,
        'Neighborhood' => $neighborhood,
        'City' => $city,
        'County' => $county,
        'State' => $state,
        'Country' => $country,
        'Lat' => $lat,
        'Long' => $lng,
        'json_original' => $json,
        'address_components' => $addressComponents
      ));

      #Return the return value
      return $returnValue;

    }


    #Landing screen for year in review
    public function hasherNameAnalysisAction(Request $request, Application $app, string $kennel_abbreviation){

      #Establish the page title
      $pageTitle = "Hasher Nickname Substring Frequency Analsis";
      $pageSubTitle = "sub title";
      $pageTableCaption = "page table caption";

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the SQL to execute
      $SQL = "SELECT HASHER_NAME, HASHER_KY
        FROM HASHERS
        WHERE
          HASHER_NAME NOT LIKE '%NHN%' AND HASHER_NAME NOT LIKE 'JUST %'";

      #Obtain the hare list
      $hasherNameList = $app['db']->fetchAll($SQL,array((int) $kennelKy));
      $tokenizerString = " -\'&,!?().";

      #Create an array that will be used to store the sub strings
      $theArrayOfSubstrings = array();

      #Iterate through the hasher name list
      foreach($hasherNameList as $hasherName){
        $tempName = $hasherName['HASHER_NAME'];
        $tempKey = $hasherName['HASHER_KY'];
        #$app['monolog']->addDebug("Item = $temp");
        $token = strtok($tempName, $tokenizerString);
        while($token !== false){

          #Log the substring
          $lowerToken = strtolower($token);

          #Create a hasher name and hasher key pair
          $tempNameKey = array('NAME'=> $tempName, 'KEY' => $tempKey);

          #Check if substring exists in the substring array
          if(array_key_exists($lowerToken,$theArrayOfSubstrings)){

            #Grab the entry corresponding to this key (substring)
            $tempEntry = $theArrayOfSubstrings[$lowerToken];

            #Push the entry onto the array
            array_push($tempEntry, $tempNameKey);

            #Replace the old value with the new value
            $theArrayOfSubstrings[$lowerToken] = $tempEntry;

          }else{
            $theArrayOfSubstrings[$lowerToken] = array($tempNameKey);
          }


          #Grab the next substring
          $token = strtok($tokenizerString);
        }
      }

      #ksort($theArrayOfSubstrings);
      uasort($theArrayOfSubstrings, function ($a, $b){
        $a = count($a);
        $b = count($b);
        return ($a == $b) ? 0 : (($a < $b) ? 1 : -1);
      });


      #foreach($theArrayOfSubstrings as $key => $value){
      #  $app['monolog']->addDebug("key:$key");
      #  foreach($value as $individualEntry){
      #    $app['monolog']->addDebug("   entry:$individualEntry");
      #  }
      #}

      #Establish the return value
      $returnValue = $app['twig']->render('hasher_name_substring_analysis.twig', array (
        'pageTitle' => $pageTitle,
        'kennel_abbreviation' => $kennel_abbreviation,
        #'theList' => $hasherNameList,
        'subStringArray' => $theArrayOfSubstrings,
        'pageSubTitle' => "The individual words in the hashernames, from most common to least common",
        'tableCaption1' => "Hashername sub-word",
        'tableCaption2' => "All names containing the sub-word"
      ));

      #Return the return value
      return $returnValue;

    }

    private function extractRootWordFromToken($tokenValue){

      #establish the return value
      $returnValue = null;

      #Define the list of root words and their exceptions
      #$rootArray = array (
      #  "shit" => null,
      #  "dick" => null,
      #  "cum" => array("scum"),
      #  "pussy" => null
      #);

      #Iterate through the list of exceptions; see if there is a match; see if there is an exception match

      $stemmer = new English();
      $stem = $stemmer->stem($tokenValue);


      #Set the return value
      $returnValue = $stem;

      #return the return value
      return $returnValue;
    }

    public function viewKennelChartsAction(Request $request, Application $app, string $kennel_abbreviation){

        #Obtain the kennel key
        $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

        #Obtain the kennel value
        $kennelValueSql = "SELECT KENNELS.* FROM KENNELS WHERE KENNEL_KY = ?";
        $kennelValue = $app['db']->fetchAssoc($kennelValueSql, array((int) $kennelKy));

        # Obtain their hashes
        $sqlTheHashes = "SELECT HASHES.* FROM HASHES
        WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
        $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $kennelKy));

        #Obtain the average lat
        $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
        WHERE KENNEL_KY = ? and LAT is not null and LNG is not null";
        $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $kennelKy));
        $avgLat = $theAverageLatLong['THE_LAT'];
        $avgLng = $theAverageLatLong['THE_LNG'];

        #Obtain the number of hashes for this kennel
        $sqlHashCountForKennel = "SELECT COUNT(*) AS THE_COUNT FROM HASHES WHERE KENNEL_KY = ?";
        $hashCountValueForKennel = $app['db']->fetchAssoc($sqlHashCountForKennel, array((int) $kennelKy));
        $hashCountForKennel = $hashCountValueForKennel['THE_COUNT'];

        #Obtain the number of distinct hashers
        $distinctHasherCountValueForKennel = $app['db']->fetchAssoc(KENNEL_NUM_OF_DISTINCT_HASHERS, array((int) $kennelKy));
        $distinctHasherCountForKennel = $distinctHasherCountValueForKennel['THE_COUNT'];

        #Obtain the number of distinct hashers
        $distinctOverallHareCountValueForKennel = $app['db']->fetchAssoc(KENNEL_NUM_OF_DISTINCT_OVERALL_HARES, array((int) $kennelKy));
        $distinctOverallHareCountForKennel = $distinctOverallHareCountValueForKennel['THE_COUNT'];

        #Obtain the number of distinct hashers
        $distinctTrueHareCountValueForKennel = $app['db']->fetchAssoc(KENNEL_NUM_OF_DISTINCT_TRUE_HARES, array((int) $kennelKy));
        $distinctTrueHareCountForKennel = $distinctTrueHareCountValueForKennel['THE_COUNT'];

        #Obtain the number of distinct hashers
        $distinctHyperHareCountValueForKennel = $app['db']->fetchAssoc(KENNEL_NUM_OF_DISTINCT_HYPER_HARES, array((int) $kennelKy));
        $distinctHyperHareCountForKennel = $distinctHyperHareCountValueForKennel['THE_COUNT'];


        # Obtain the number of hashings
        #$hashCountValue = $app['db']->fetchAssoc(PERSONS_HASHING_COUNT, array((int) $hasher_id, (int) $kennelKy));

        # Obtain the hashes by month (name)
        $theHashesByMonthNameList = $app['db']->fetchAll(KENNEL_HASH_COUNTS_BY_MONTH_NAME, array((int) $kennelKy));

        # Obtain the hashes by quarter
        $theHashesByQuarterList = $app['db']->fetchAll(KENNEL_HASH_COUNTS_BY_QUARTER, array((int) $kennelKy));

        # Obtain the hashes by quarter
        $theHashesByStateList = $app['db']->fetchAll(KENNEL_HASH_COUNTS_BY_STATE, array((int) $kennelKy));

        # Obtain the hashes by county
        $theHashesByCountyList = $app['db']->fetchAll(KENNEL_HASH_COUNTS_BY_COUNTY, array((int) $kennelKy));

        # Obtain the hashes by postal code
        $theHashesByPostalCodeList = $app['db']->fetchAll(KENNEL_HASH_COUNTS_BY_POSTAL_CODE, array((int) $kennelKy));

        # Obtain the hashes by day name
        $theHashesByDayNameList = $app['db']->fetchAll(KENNEL_HASH_COUNTS_BY_DAYNAME, array((int) $kennelKy));

        #Obtain the hashes by year
        $sqlHashesByYear = "SELECT YEAR(EVENT_DATE) AS THE_VALUE, COUNT(*) AS THE_COUNT
         FROM
        	HASHES
          WHERE
            HASHES.KENNEL_KY = ?
        GROUP BY YEAR(EVENT_DATE)
        ORDER BY YEAR(EVENT_DATE)";
        $hashesByYearList = $app['db']->fetchAll($sqlHashesByYear, array((int) $kennelKy));

        #Query the database
        $cityHashingsCountList = $app['db']->fetchAll(KENNEL_HASH_COUNTS_BY_CITY, array((int) $kennelKy));

        #Obtain largest entry from the list
        $cityHashingsCountMax = 1;
        if(isset($cityHashingsCountList[0]['THE_COUNT'])){
          $cityHashingsCountMax = $cityHashingsCountList[0]['THE_COUNT'];
        }


        # Establish and set the return value
        $returnValue = $app['twig']->render('kennel_chart_details.twig',array(
          'pageTitle' => 'Kennel Charts and Details',
          'firstHeader' => 'Basic Details',
          'secondHeader' => 'Statistics',
          'kennelName' => $kennelValue['KENNEL_NAME'],
          #'hasherValue' => $hasher,
          #'hashCount' => $hashCountValue['THE_COUNT'],
          #'hareCount' => $hareCountValue['THE_COUNT'],
          'kennel_abbreviation' => $kennel_abbreviation,
          'hashes_by_year_list' => $hashesByYearList,
          #'harings_by_year_list' => $haringsByYearList,
          'hashes_by_month_name_list' => $theHashesByMonthNameList,
          'hashes_by_quarter_list' => $theHashesByQuarterList,
          'hashes_by_state_list' => $theHashesByStateList,
          'hashes_by_county_list' => $theHashesByCountyList,
          'hashes_by_postal_code_list' => $theHashesByPostalCodeList,
          'hashes_by_day_name_list' => $theHashesByDayNameList,
          'city_hashings_count_list' => $cityHashingsCountList,
          'city_hashings_max_value' => $cityHashingsCountMax,
          'the_hashes' => $theHashes,
          'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
          'avg_lat' => $avgLat,
          'avg_lng' => $avgLng,
          'hash_count' => $hashCountForKennel,
          'distinct_hasher_count' => $distinctHasherCountForKennel,
          'distinct_true_hare_count' => $distinctTrueHareCountForKennel,
          'distinct_hyper_hare_count' => $distinctHyperHareCountForKennel,
          'distinct_overall_hare_count' =>$distinctOverallHareCountForKennel
        ));


        # Return the return value
        return $returnValue;
    }

    #Landing screen for year in review
    public function hasherNameAnalysisAction2(Request $request, Application $app, string $kennel_abbreviation){

      #Establish the page title
      $pageTitle = "Hasher Nickname Stemmed Substring Frequency Analysis";
      $pageSubTitle = "sub title";
      $pageTableCaption = "page table caption";

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the SQL to execute
      $SQL = "SELECT HASHER_NAME, HASHER_KY FROM HASHERS WHERE HASHER_NAME NOT LIKE '%NHN%' AND HASHER_NAME NOT LIKE 'JUST %'";

      #Obtain the hare list
      $hasherNameList = $app['db']->fetchAll($SQL,array((int) $kennelKy));
      $tokenizerString = " -\'&,!?().";

      #Create an array that will be used to store the sub strings
      $theArrayOfSubstrings = array();

      #Iterate through the hasher name list
      foreach($hasherNameList as $hasherName){
        $tempName = $hasherName['HASHER_NAME'];
        $tempKey = $hasherName['HASHER_KY'];
        #$app['monolog']->addDebug("Item = $temp");
        $token = strtok($tempName, $tokenizerString);
        while($token !== false){

          #Log the substring
          $lowerToken = strtolower($token);

          #test function call to stemmer function
          $stemmedLowerToken = $this->extractRootWordFromToken($lowerToken);
          #$app['monolog']->addDebug("tokenValue:$token|stem:$stemmedLowerToken");
          $lowerToken = $stemmedLowerToken;

          #Create a hasher name and hasher key pair
          $tempNameKey = array('NAME'=> $tempName, 'KEY' => $tempKey);

          #Check if substring exists in the substring array
          if(array_key_exists($lowerToken,$theArrayOfSubstrings)){

            #Grab the entry corresponding to this key (substring)
            $tempEntry = $theArrayOfSubstrings[$lowerToken];

            #Push the entry onto the array
            array_push($tempEntry, $tempNameKey);

            #Replace the old value with the new value
            $theArrayOfSubstrings[$lowerToken] = $tempEntry;

          }else{
            $theArrayOfSubstrings[$lowerToken] = array($tempNameKey);
          }


          #Grab the next substring
          $token = strtok($tokenizerString);
        }
      }

      #ksort($theArrayOfSubstrings);
      uasort($theArrayOfSubstrings, function ($a, $b){
        $a = count($a);
        $b = count($b);
        return ($a == $b) ? 0 : (($a < $b) ? 1 : -1);
      });


      #foreach($theArrayOfSubstrings as $key => $value){
      #  $app['monolog']->addDebug("key:$key");
      #  foreach($value as $individualEntry){
      #    $app['monolog']->addDebug("   entry:$individualEntry");
      #  }
      #}




      #Establish the return value
      $returnValue = $app['twig']->render('hasher_name_substring_analysis2.twig', array (
        'pageTitle' => $pageTitle,
        'kennel_abbreviation' => $kennel_abbreviation,
        #'theList' => $hasherNameList,
        'subStringArray' => $theArrayOfSubstrings,
        'pageSubTitle' => "The individual words in the hashernames, from most common to least common",
        'tableCaption1' => "Hashername sub-word",
        'tableCaption2' => "All names containing the sub-word"
      ));

      #Return the return value
      return $returnValue;

    }


    #Landing screen for year in review
    public function hasherNameAnalysisWordCloudAction(Request $request, Application $app, string $kennel_abbreviation){

      #Establish the page title
      $pageTitle = "Hasher Nickname Stemmed Substring Frequency Analysis";
      $pageSubTitle = "sub title";
      $pageTableCaption = "page table caption";

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      #Define the SQL to execute
      $SQL = "SELECT HASHER_NAME, HASHER_KY FROM HASHERS WHERE HASHER_NAME NOT LIKE '%NHN%' AND HASHER_NAME NOT LIKE 'JUST %'";

      #Obtain the hare list
      $hasherNameList = $app['db']->fetchAll($SQL,array((int) $kennelKy));
      $tokenizerString = " -\'&,!?().";

      #Create an array that will be used to store the sub strings
      $theArrayOfSubstrings = array();

      #Iterate through the hasher name list
      foreach($hasherNameList as $hasherName){
        $tempName = $hasherName['HASHER_NAME'];
        $tempKey = $hasherName['HASHER_KY'];
        #$app['monolog']->addDebug("Item = $temp");
        $token = strtok($tempName, $tokenizerString);
        while($token !== false){

          #Log the substring
          $lowerToken = strtolower($token);

          #test function call to stemmer function
          $stemmedLowerToken = $this->extractRootWordFromToken($lowerToken);
          $lowerToken = $stemmedLowerToken;

          #Create a hasher name and hasher key pair
          $tempNameKey = array('NAME'=> $tempName, 'KEY' => $tempKey);

          #Check if substring exists in the substring array
          if(array_key_exists($lowerToken,$theArrayOfSubstrings)){

            #Grab the entry corresponding to this key (substring)
            $tempEntry = $theArrayOfSubstrings[$lowerToken];

            #Push the entry onto the array
            array_push($tempEntry, $tempNameKey);

            #Replace the old value with the new value
            $theArrayOfSubstrings[$lowerToken] = $tempEntry;

          }else{
            $theArrayOfSubstrings[$lowerToken] = array($tempNameKey);
          }


          #Grab the next substring
          $token = strtok($tokenizerString);
        }
      }

      #ksort($theArrayOfSubstrings);
      uasort($theArrayOfSubstrings, function ($a, $b){
        $a = count($a);
        $b = count($b);
        return ($a == $b) ? 0 : (($a < $b) ? 1 : -1);
      });

      #Count up the names tied to each substring
      $subStringCounts = array();
      foreach($theArrayOfSubstrings as $keyValue => $valueValue){
        $tempCount = count($valueValue);
        $temp = array("THE_VALUE" => $keyValue, "THE_COUNT" => $tempCount);
        array_push($subStringCounts,$temp);
      }


      #Establish the return value
      $returnValue = $app['twig']->render('wordcloud_hashername_analysis.twig', array (
        'pageTitle' => $pageTitle,
        'kennel_abbreviation' => $kennel_abbreviation,
        'subStringArray' => $subStringCounts,
        'pageSubTitle' => "The individual words in the hashernames, from most common to least common",
        'tableCaption1' => "Hashername sub-word",
        'tableCaption2' => "All names containing the sub-word"
      ));

      #Return the return value
      return $returnValue;

    }



}
