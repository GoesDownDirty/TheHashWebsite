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

    public function unTrendingTrueHaresAction(
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

      # Establish and set the return value
      $returnValue = $app['twig']->render('un_trending_true_hares_charts.twig',array(
        'pageTitle' => 'Un-trending True Hares',
        'firstHeader' => 'FIRST HEADER',
        'secondHeader' => 'SECOND HEADER',
        'kennel_abbreviation' => $kennel_abbreviation,
        'un_trending_true_hares_list' => $unTrendingTrueHaresList,
        'day_count' => $day_count,
        'row_limit' => $row_limit,
        'min_hash_count' => $min_hash_count,
        'max_percentage' => $max_percentage
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



}
