<?php

namespace HASH\Controller;

require_once realpath(__DIR__ . '/../..').'/config/SQL_Queries.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;




class ObscureStatisticsController{

    #Landing screen for year in review
    public function getYearInReviewAction(Request $request, Application $app, int $year_value){

      #Establish the page title
      $pageTitle = "$year_value: Year in review";

      #Establish the return value
      $returnValue = $app['twig']->render('year_in_review.twig', array (
        'pageTitle' => $pageTitle,
        'yearValue' => $year_value,
      ));

      #Return the return value
      return $returnValue;

    }

    #Obtain hashers for an event
    public function getHasherCountsByYear(Request $request, Application $app){

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hasherCountSQL = HASHER_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hasherCountList = $app['db']->fetchAll($hasherCountSQL,array((int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hasherCountList, 200);
      return $returnValue;
    }

    #Obtain total hare counts per year
    public function getTotalHareCountsByYear(Request $request, Application $app){

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = TOTAL_HARE_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array((int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    #Obtain total hare counts per year
    public function getHyperHareCountsByYear(Request $request, Application $app){

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = HYPER_HARE_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array((int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    #Obtain total hare counts per year
    public function getNonHyperHareCountsByYear(Request $request, Application $app){

      #Obtain the post values
      $theYear = $request->request->get('year_value');

      #Define the SQL to execute
      $hareCountSQL = NONHYPER_HARE_COUNTS_BY_YEAR;

      #Obtain the hare list
      $hareCountList = $app['db']->fetchAll($hareCountSQL,array((int)$theYear));

      #Set the return value
      $returnValue =  $app->json($hareCountList, 200);
      return $returnValue;

    }

    #Obtain the first hash of a given hasher
    public function getHashersVirginHash(Request $request, Application $app){

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
    public function getHashersLatestHash(Request $request, Application $app){

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





}
