<?php

namespace HASH\Controller;

require_once realpath(__DIR__ . '/../..').'/config/SQL_Queries.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class HashController
{


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

  #Define the action
  public function logonScreenAction(Request $request, Application $app){

    #$app['monolog']->addDebug('Entering the logonScreenAction');

    # Establisht the last error
    $lastError = $app['security.last_error']($request);
    #$app['monolog']->addDebug($lastError);

    # Establish the last username
    $lastUserName = $app['session']->get('_security.last_username');
    #$lastUserName = $app['session']->get('_security.last_username');
    #$app['monolog']->addDebug($lastUserName);


    # Establish the return value
    $returnValue =  $app['twig']->render('logon_screen.twig', array (
      'pageTitle' => 'SCH4 Stats Logon',
      'pageHeader' => 'Please log on!',
      'error' => $lastError,
      'last_username' => $lastUserName,
    ));

    #$app['monolog']->addDebug('Leaving the logonScreenAction');

    # Return the return value;
    return $returnValue;
  }






  public function logoutAction(Request $request, Application $app){

    # Invalidate the session
    $app['session']->invalidate();

    # Redirect the user to the root url
    return $app->redirect('/');

  }

  #Define the action
  public function helloAction(Request $request, Application $app){

      #return $app->redirect('/');
      return $app['twig']->render('admin_landing.twig', array (
        'pageTitle' => 'This is the admin landing screen',
        'subTitle1' => 'This is the admin landing screen'));
  }

  #Define the action
  public function adminHelloAction(Request $request, Application $app){

      #return $app->redirect('/');
      return $app['twig']->render('admin_landing.twig', array (
        'pageTitle' => 'This is the admin hello landing screen (page title)',
        'subTitle1' => 'This is the admin hello landing screen (sub title 1)'));
  }

  #Define the action
  public function slashAction(Request $request, Application $app){

    #$app['monolog']->addDebug('Entering the slash action');

    #$token = $app['security.token_storage']->getToken();
    #$app['monolog']->addDebug($token);

    # Obtain the logged in user
    #$user = $app['session']->get('user');
    #$userName = $user['username'];

    #Establish the page caption
    #$pageCaption = "You are logged in as: $userName and your password is password";

    #Establish the kennel abbreviation. By default, it is sch4
    $kennelAbbreviation = "SCH4";

    #Establish the page title
    $pageTitle = "$kennelAbbreviation Stats";

    #Set the return value
    $returnValue = $app['twig']->render('slash.twig',array(
      'pageTitle' => $pageTitle,
      #'pageCaption' => $pageCaption,
      'subTitle1' => 'Standard Statistics',
      'subTitle2' => 'Analversary Statistics',
      'subTitle3' => 'Hare Statistics',
      'subTitle4' => 'Other Statistics',
      'kennel_abbreviation' => $kennelAbbreviation
    ));

    #Return the return value
    return $returnValue;

  }

  #Define the action
  public function slashKennelAction(Request $request, Application $app, string $kennel_abbreviation){

    #Establish the page title
    $pageTitle = "$kennel_abbreviation Stats";

    #Set the return value
    $returnValue = $app['twig']->render('slash.twig',array(
      'pageTitle' => $pageTitle,
      'pageCaption' => "Provide page caption",
      'subTitle1' => 'Standard Statistics',
      'subTitle2' => 'Analversary Statistics',
      'subTitle3' => 'Hare Statistics',
      'subTitle4' => 'Other Statistics',
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;

  }


  #Define the action
  public function listHashersAction(Request $request, Application $app, string $kennel_abbreviation){

    #Define the SQL to execute
    $sql = "SELECT
      HASHER_KY AS THE_KEY,
      HASHER_NAME AS NAME,
      FIRST_NAME,
      LAST_NAME,
      EMAIL FROM HASHERS";

    #Execute the SQL statement; create an array of rows
    $hasherList = $app['db']->fetchAll($sql);

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_list.twig',array(
      'pageTitle' => 'The List of Hashers',
      'pageSubTitle' => 'The List of *ALL* Hashers',
      'theList' => $hasherList,
      'kennel_abbreviation' => $kennel_abbreviation,
      'pageCaption' => "",
      'tableCaption' => ""
    ));

    #Return the return value
    return $returnValue;

  }

  public function listHashersByHashAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Define the SQL to execute
    $sql = "SELECT
      HASHERS.HASHER_KY AS THE_KEY,
      HASHERS.HASHER_NAME AS NAME,
      HASHERS.FIRST_NAME,
      HASHERS.LAST_NAME,
      HASHERS.EMAIL
      FROM HASHERS JOIN HASHINGS ON HASHERS.HASHER_KY = HASHINGS.HASHER_KY WHERE HASHINGS.HASH_KY = ?";

    #Execute the SQL statement; create an array of rows
    $hasherList = $app['db']->fetchAll($sql,array((int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $theHashEventLocation = $theHashValue['EVENT_LOCATION'];
    $theSubTitle = "Hashers at Hash Number $theHashEventNumber ($theHashEventLocation) ";

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_list.twig',array(
      'pageTitle' => 'The List of Hashers',
      'pageSubTitle' => $theSubTitle,
      'theList' => $hasherList,
      'tableCaption' => $theSubTitle,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;

  }

  public function listHaresByHashAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){


    #Define the SQL to execute
    $sql = "SELECT
      HASHERS.HASHER_KY AS THE_KEY,
      HASHERS.HASHER_NAME AS NAME ,
      HASHERS.FIRST_NAME,
      HASHERS.LAST_NAME,
      HASHERS.EMAIL
      FROM HASHERS JOIN HARINGS ON HASHERS.HASHER_KY = HARINGS.HARINGS_HASHER_KY WHERE HARINGS.HARINGS_HASH_KY = ?";

    #Execute the SQL statement; create an array of rows
    $hasherList = $app['db']->fetchAll($sql,array((int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $theHashEventLocation = $theHashValue['EVENT_LOCATION'];
    $theSubTitle = "Hares at Hash Number $theHashEventNumber ($theHashEventLocation) ";

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_list.twig',array(
      'pageTitle' => 'The List of Hares',
      'pageSubTitle' => $theSubTitle,
      'theList' => $hasherList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;
  }



  public function listhashesAction(Request $request, Application $app, string $kennel_abbreviation){

    #Define the SQL to execute
    $sql = "SELECT
      HASH_KY,
      KENNEL_EVENT_NUMBER,
      EVENT_DATE,
      DAYNAME(EVENT_DATE) AS EVENT_DAY_NAME,
      EVENT_LOCATION,
      EVENT_CITY,
      EVENT_STATE,
      SPECIAL_EVENT_DESCRIPTION,
      IS_HYPER,
      VIRGIN_COUNT
    FROM HASHES
    WHERE KENNEL_KY = ?
    ORDER BY HASH_KY DESC";

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $kennelKy));

    # Establish and set the return value
    $returnValue = $app['twig']->render('hash_list.twig',array(
      'pageTitle' => 'The List of Hashes',
      'pageSubTitle' => 'The List of *All* Hashes',
      'theList' => $hashList,
      'tableCaption' => 'A list of all hashes ever, since forever.',
      'kennel_abbreviation' => $kennel_abbreviation
    ));


    #Return the return value
    return $returnValue;
  }

  public function listHashesByHasherAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Define the SQL to execute
    $sql = "SELECT
          HASHES.HASH_KY,
          KENNEL_EVENT_NUMBER,
          EVENT_DATE,
          DAYNAME(EVENT_DATE) AS EVENT_DAY_NAME,
          EVENT_LOCATION,
          EVENT_CITY,
          EVENT_STATE,
          SPECIAL_EVENT_DESCRIPTION,
          IS_HYPER,
          VIRGIN_COUNT
    FROM HASHES JOIN HASHINGS ON HASHES.HASH_KY = HASHINGS.HASH_KY
    WHERE HASHINGS.HASHER_KY = ? AND HASHES.KENNEL_KY = ?
    ORDER BY HASHES.HASH_KY DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int)$kennelKy));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

    # Establish and set the return value
    $hasherName = $hasher['HASHER_NAME'];
    $pageSubtitle = "The hashes $hasherName has done";
    $returnValue = $app['twig']->render('hash_list.twig',array(
      'pageTitle' => 'The List of Hashes',
      'pageSubTitle' => $pageSubtitle,
      'theList' => $hashList,
      'tableCaption' => '',
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;

  }



  public function listHashesByHareAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Define the SQL to execute
    $sql = "SELECT
        HASHES.HASH_KY,
        KENNEL_EVENT_NUMBER,
        EVENT_DATE,
        DAYNAME(EVENT_DATE) AS EVENT_DAY_NAME,
        EVENT_LOCATION,
        EVENT_CITY,
        EVENT_STATE,
        SPECIAL_EVENT_DESCRIPTION,
        IS_HYPER,
        VIRGIN_COUNT
      FROM HASHES JOIN HARINGS ON HASHES.HASH_KY = HARINGS.HARINGS_HASH_KY
      WHERE HARINGS.HARINGS_HASHER_KY = ? AND HASHES.KENNEL_KY = ?
      ORDER BY EVENT_DATE DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $kennelKy));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ? ";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

    # Establish and set the return value
    $hasherName = $hasher['HASHER_NAME'];
    $pageSubtitle = "The hashes $hasherName has hared";
    $returnValue = $app['twig']->render('hash_list.twig',array(
      'pageTitle' => 'The List of Hashes',
      'pageSubTitle' => $pageSubtitle,
      'theList' => $hashList,
      'tableCaption' => '',
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;
  }


  public function viewHasherAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql, array((int) $hasher_id));

    # Obtain the number of hashings
    $sqlHashCount = "SELECT COUNT(*) AS THE_COUNT FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
    WHERE HASHER_KY = ? AND KENNEL_KY = ?";
    $hashCountValue = $app['db']->fetchAssoc($sqlHashCount, array((int) $hasher_id, (int) $kennelKy));

    # Obtain the number of harings
    $sqlHareCount = "SELECT COUNT(*) AS THE_COUNT FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
    WHERE HARINGS_HASHER_KY = ? AND HASHES.KENNEL_KY = ?";
    $hareCountValue = $app['db']->fetchAssoc($sqlHareCount, array((int) $hasher_id, (int) $kennelKy));

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_details.twig',array(
      'pageTitle' => 'Hasher Details',
      'firstHeader' => 'Basic Details',
      'secondHeader' => 'Statistics',
      'hasherValue' => $hasher,
      'hashCount' => $hashCountValue['THE_COUNT'],
      'hareCount' => $hareCountValue['THE_COUNT'],
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;

  }


  public function viewHashAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql, array((int) $hash_id));

    # Establish and set the return value
    $returnValue = $app['twig']->render('hash_details.twig',array(
      'pageTitle' => 'Hash Details',
      'firstHeader' => 'Basic Details',
      'secondHeader' => 'Statistics',
      'hashValue' => $theHashValue,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;

  }

  public function hasherAnalversariesForEventAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Declare the SQL used to retrieve this information
    $sql = "SELECT
        hashers.HASHER_NAME AS HASHER_NAME,
        (COUNT(*)) AS THE_COUNT,
        MAX(HASHINGS.HASH_KY) AS MAX_HASH_KY
    FROM
        ((hashers
        JOIN hashings ON ((hashers.HASHER_KY = hashings.HASHER_KY)))
        JOIN hashes ON ((hashings.HASH_KY = hashes.HASH_KY)))
    WHERE
        (hashers.DECEASED = 0) AND
        HASHES.HASH_KY <= ? AND
        HASHES.KENNEL_KY = ?
    GROUP BY hashers.HASHER_NAME
    HAVING ((((THE_COUNT % 5) = 0)
        OR ((THE_COUNT % 69) = 0)
        OR ((THE_COUNT % 666) = 0)
        OR (((THE_COUNT - 69) % 100) = 0)))
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $kennelKy, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageSubtitle = "Analversaries at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => 'Hasher Analversaries',
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;
  }

  public function hareAnalversariesForEventAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Declare the SQL used to retrieve this information
    $sql = "	SELECT
        		HASHERS.HASHER_NAME AS HASHER_NAME,
                COUNT(*) AS THE_COUNT,
                MAX(HARINGS.HARINGS_HASH_KY) AS MAX_HASH_KY
        	FROM
        		HASHERS
                JOIN HARINGS ON HASHERS.HASHER_KY = HARINGS.HARINGS_HASHER_KY
                JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
        	WHERE
        		HASHERS.DECEASED = 0 AND
                HARINGS.HARINGS_HASH_KY <= ? AND
                HASHES.KENNEL_KY = ?
        	GROUP BY
        		HASHERS.HASHER_NAME
        	HAVING
        		(((THE_COUNT % 5) = 0)
                OR ((THE_COUNT % 69) = 0)
                OR ((THE_COUNT % 666) = 0)
                OR (((THE_COUNT - 69) % 100) = 0))
                AND MAX_HASH_KY = ?
        	ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id, (int) $kennelKy, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageSubtitle = "Analversaries at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => 'Hare Analversaries',
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));


    # Return the return value
    return $returnValue;
  }

public function pendingHasherAnalversariesAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = PENDING_HASHER_ANALVERSARIES;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #The number of harings into the future in which the analversaries will take place
  $fastForwardValue = 1;

  #The number of years absence before removing from the list...
  $yearsAbsenceLimit = 7;

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $fastForwardValue, (int) $kennelKy, (int) $yearsAbsenceLimit));

  # Declare the SQL to get the most recent hash
  $sqlMostRecentHash = "SELECT KENNEL_EVENT_NUMBER, EVENT_DATE, EVENT_LOCATION, SPECIAL_EVENT_DESCRIPTION
    FROM HASHES
    JOIN (
        select max(HASHINGS.hash_ky) as HASH_KY
        from hashings
        JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
        WHERE HASHES.KENNEL_KY = ?
      ) AS TEMPTABLE
    ON HASHES.HASH_KY = TEMPTABLE.HASH_KY";

  # Execute the SQL to get the most recent hash
  $theMostRecentHashValue = $app['db']->fetchAssoc($sqlMostRecentHash, array((int) $kennelKy));

  $tableCaption = "The most recent hash was: $theMostRecentHashValue[KENNEL_EVENT_NUMBER]
  at $theMostRecentHashValue[EVENT_LOCATION]";

  # Establish the return value
  $returnValue = $app['twig']->render('pending_analversary_list.twig',array(
    'pageTitle' => 'Pending Hasher Analversaries',
    'pageSubTitle' => 'The analversaries at their *next* hashes',
    'theList' => $hasherList,
    'tableCaption' => $tableCaption,
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Pending Count',
    'columnThreeName' => 'Years Absent',
    'kennel_abbreviation' => $kennel_abbreviation
  ));


  #Return the return value
  return $returnValue;

}

public function pendingHareAnalversariesAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = PENDING_HARE_ANALVERSARIES;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #The number of harings into the future in which the analversaries will take place
  $fastForwardValue = 1;

  #The number of years absence before removing from the list...
  $yearsAbsenceLimit = 7;

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $fastForwardValue, (int) $kennelKy, (int) $yearsAbsenceLimit));

  # Declare the SQL to get the most recent hash
  $sqlMostRecentHash = "SELECT KENNEL_EVENT_NUMBER, EVENT_DATE, EVENT_LOCATION, SPECIAL_EVENT_DESCRIPTION
    FROM HASHES
    JOIN
		(
			SELECT MAX(harings_hash_ky) as HASH_KY
            FROM HARINGS
            JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
            WHERE HASHES.KENNEL_KY = ?
		) AS TEMPTABLE
    ON HASHES.HASH_KY = TEMPTABLE.HASH_KY";

  # Execute the SQL to get the most recent hash
  $theMostRecentHashValue = $app['db']->fetchAssoc($sqlMostRecentHash, array((int) $kennelKy));

  $tableCaption = "The most recent hash was: $theMostRecentHashValue[KENNEL_EVENT_NUMBER]
  at $theMostRecentHashValue[EVENT_LOCATION]";

  # Establish the return value
  $returnValue = $app['twig']->render('pending_analversary_list.twig',array(
    'pageTitle' => 'Pending Hare Analversaries',
    'pageSubTitle' => 'The analversaries at their *next* harings',
    'theList' => $hasherList,
    'tableCaption' => $tableCaption,
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Pending Count',
    'columnThreeName' => 'Years Absent',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}

public function haringPercentageAllHashesAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = HARING_PERCENTAGE_ALL_HASHES;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #define the minimum number of hashes
  $minHashCount = 0;

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy,(int) $kennelKy,(int) $minHashCount));

  # Establish the return value
  $returnValue = $app['twig']->render('percentage_list.twig',array(
    'pageTitle' => 'Haring Percentage List',
    'tableCaption' => 'Percentage of harings per hashings for each hasher',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Hashing Count',
    'columnThreeName' => 'Haring Count',
    'columnFourName' => 'Haring Percentage',
    'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}


public function haringPercentageNonHypersAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = HARING_PERCENTAGE_NON_HYPER_HASHES;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #define the minimum number of hashes
  $minHashCount = 0;

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy,(int) $kennelKy,(int) $minHashCount));

  # Establish the return value
  $returnValue = $app['twig']->render('percentage_list.twig',array(
    'pageTitle' => '(Non Hyper Hash) Haring Percentage List',
    'tableCaption' => 'Percentage of non hyper harings per hashings for each hasher',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Hashing Count',
    'columnThreeName' => 'Haring Count',
    'columnFourName' => 'Haring Percentage',
    'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;
}



public function percentageHaringsHypersVsNonHypers(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = HARING_PERCENTAGES_HYPERS_VS_ALL;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy,(int) $kennelKy));


  # Establish the return value
  $returnValue = $app['twig']->render('percentage_list_multiple_values.twig',array(
    'pageTitle' => 'Hyper Haring Percentages',
    'tableCaption' => 'This shows the percentage of hyper hashes that make up each hare\'s haring list. The hyper haring percentage shows the percentage of harings people have done that were hyper hashes.',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Haring Count (All)',
    'columnThreeName' => 'Haring Count (Hyper Hashes)',
    'columnFourName' => 'Hyper Haring Percentage',
    'columnFiveName' => 'Non-Hyper Haring Percentage',
    'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;
}






public function hashingCountsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = HASHING_COUNTS;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Hasher Counts',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'Hashers, and the number of hashes they have done. More is better.',
    'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}


public function haringCountsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = HARING_COUNTS;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Haring Counts',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Haring Count',
    'tableCaption' => 'Hares, and the number of times they have hared. More is better.',
    'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}

public function nonHyperHaringCountsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = NON_HYPER_HARING_COUNTS;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Non Hyper-Hash Haring Counts',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'Hares, and the number of (non hyper-hash) hashes they have hared. More is better. These numbers will never truely be accurate until Hot Tub Slut gets me the list of all hyper hashes.',
    'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}


  public function coharelistByHareAllHashesAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Define the SQL to execute
    $sql = "SELECT
      	TEMPTABLE.HASHER_NAME,TEMPTABLE.HARINGS_HASHER_KY AS HASHER_KY,
          HASHES.KENNEL_EVENT_NUMBER,
          HASHES.SPECIAL_EVENT_DESCRIPTION,
          HASHES.EVENT_LOCATION,
          HASHES.HASH_KY
      FROM
      	HARINGS JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
          JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          JOIN (
      		SELECT
      			HARINGS_HASH_KY,
                  HASHER_NAME,
                  HARINGS_HASHER_KY
      		FROM
      			HARINGS
                  JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
      		) TEMPTABLE ON HARINGS.HARINGS_HASH_KY = TEMPTABLE.HARINGS_HASH_KY
      WHERE
      	HARINGS.HARINGS_HASHER_KY = ?
          AND TEMPTABLE.HARINGS_HASHER_KY <> ?
          AND HASHES.IS_HYPER IN (?,?) AND HASHES.KENNEL_KY = ?
      ORDER BY HARINGS.HARINGS_HASH_KY ASC";

    #Execute the SQL statement; create an array of rows
    $cohareList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,1, (int) $kennelKy));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

    # Establish and set the return value
    $hasherName = $hasher['HASHER_NAME'];
    $captionValue = "The hares who've had the shame of haring with $hasherName";
    $returnValue = $app['twig']->render('cohare_list.twig',array(
      'pageTitle' => 'Cohare List (All Hashes)',
      'pageSubTitle' => 'All Hashes',
      'tableCaption' => $captionValue,
      'theList' => $cohareList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));



    #Return the return value
    return $returnValue;

  }


  public function coharelistByHareNonHypersAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){


    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Define the SQL to execute
    $sql = "SELECT
      	TEMPTABLE.HASHER_NAME, TEMPTABLE.HARINGS_HASHER_KY AS HASHER_KY,
          HASHES.KENNEL_EVENT_NUMBER,
          HASHES.SPECIAL_EVENT_DESCRIPTION,
          HASHES.EVENT_LOCATION,
          HASHES.HASH_KY
      FROM
      	HARINGS JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
          JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          JOIN (
      		SELECT
      			HARINGS_HASH_KY,
                  HASHER_NAME,
                  HARINGS_HASHER_KY
      		FROM
      			HARINGS
                  JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
      		) TEMPTABLE ON HARINGS.HARINGS_HASH_KY = TEMPTABLE.HARINGS_HASH_KY
      WHERE
      	HARINGS.HARINGS_HASHER_KY = ?
          AND TEMPTABLE.HARINGS_HASHER_KY <> ?
          AND HASHES.IS_HYPER IN (?,?) AND HASHES.KENNEL_KY = ?
      ORDER BY HARINGS.HARINGS_HASH_KY ASC";

    #Execute the SQL statement; create an array of rows
    $cohareList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0, (int) $kennelKy));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

    # Establish and set the return value
    $hasherName = $hasher['HASHER_NAME'];
    $captionValue = "The hares who've had the shame of haring with $hasherName";
    $returnValue = $app['twig']->render('cohare_list.twig',array(
      'pageTitle' => 'Cohare List (Non Hyper Hashes)',
      'pageSubTitle' => 'Non Hyper Hashes Only',
      'tableCaption' => $captionValue,
      'theList' => $cohareList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;

  }


  public function cohareCountByHareAllHashesAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Define the SQL to execute
    $sql = "SELECT
      	   TEMPTABLE.HASHER_NAME AS NAME,
           COUNT(*) AS VALUE
      FROM
      	HARINGS
          JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
          JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          JOIN (
      		SELECT
      			HARINGS_HASH_KY,
                  HASHER_NAME,
                  HARINGS_HASHER_KY
      		FROM
      			HARINGS
                  JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
      		) TEMPTABLE ON HARINGS.HARINGS_HASH_KY = TEMPTABLE.HARINGS_HASH_KY
      WHERE
      	HARINGS.HARINGS_HASHER_KY = ?
          AND TEMPTABLE.HARINGS_HASHER_KY <> ?
          AND HASHES.IS_HYPER IN (?,?) AND HASHES.KENNEL_KY = ?
      GROUP BY TEMPTABLE.HASHER_NAME
      ORDER BY VALUE DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,1, (int) $kennelKy));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

    # Establish and set the return value
    $hasherName = $hasher['HASHER_NAME'];
    $captionValue = "The hares who've hared with  $hasherName";
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Hare Counts (All Hashes)',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hare Count',
      'tableCaption' => $captionValue,
      'theList' => $hashList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;

  }

  public function cohareCountByHareNonHypersAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Define the SQL to execute
    $sql = "SELECT
      	TEMPTABLE.HASHER_NAME AS NAME,
          COUNT(*) AS VALUE
      FROM
      	HARINGS
          JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
          JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          JOIN (
      		SELECT
      			HARINGS_HASH_KY,
                  HASHER_NAME,
                  HARINGS_HASHER_KY
      		FROM
      			HARINGS
                  JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
      		) TEMPTABLE ON HARINGS.HARINGS_HASH_KY = TEMPTABLE.HARINGS_HASH_KY
      WHERE
      	HARINGS.HARINGS_HASHER_KY = ?
          AND TEMPTABLE.HARINGS_HASHER_KY <> ?
          AND HASHES.IS_HYPER IN (?,?) AND HASHES.KENNEL_KY = ?
      GROUP BY TEMPTABLE.HASHER_NAME
      ORDER BY VALUE DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0, (int) $kennelKy));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

    # Establish and set the return value
    $hasherName = $hasher['HASHER_NAME'];
    $captionValue = "The hares who've hared with  $hasherName";
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Hare Counts (Hyper Hashes Excluded)',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hare Count',
      'tableCaption' => $captionValue,
      'theList' => $hashList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;
  }

  public function hashAttendanceByHareLowestAction(Request $request, Application $app, string $kennel_abbreviation){

    #Define the SQL to execute
    $sql = LOWEST_HASH_ATTENDANCE_BY_HARE;

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $kennelKy));

    # Establish and set the return value
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Lowest hash attendance by hare',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hasher Count',
      'tableCaption' => 'The lowest hash attendance for each hare.',
      'theList' => $hashList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;

  }


public function hashAttendanceByHareHighestAction(Request $request, Application $app, string $kennel_abbreviation){

  #Define the SQL to execute
  $sql = HIGHEST_HASH_ATTENDANCE_BY_HARE;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hashList = $app['db']->fetchAll($sql,array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Highest attended hashes by hare',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hasher Count',
    'tableCaption' => 'The highest attended hashes for each hare.',
    'theList' => $hashList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;
}



  public function hashAttendanceByHareAverageAction(Request $request, Application $app, string $kennel_abbreviation){

    #Define the SQL to execute
    $sql = AVERAGE_HASH_ATTENDANCE_BY_HARE;

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $kennelKy));

    # Establish and set the return value
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Average hash attendance by hare',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hasher Count',
      'tableCaption' => 'The average hash attendance for each hare.',
      'theList' => $hashList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;
  }


  public function hashAttendanceByHareGrandTotalNonDistinctHashersAction(Request $request, Application $app, string $kennel_abbreviation){

    #Define the SQL to execute
    $sql = GRANDTOTAL_NONDISTINCT_HASH_ATTENDANCE_BY_HARE;

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $kennelKy));

    # Establish and set the return value
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Total (non distinct) hashers at their hashes',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hash Count',
      'tableCaption' => 'If hasher X has done 100 of hare Y\'s events, they contribute 100 to the hash count.',
      'theList' => $hashList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    #Return the return value
    return $returnValue;
  }

public function hashAttendanceByHareGrandTotalDistinctHashersAction(Request $request, Application $app, string $kennel_abbreviation){

  #Define the SQL to execute
  $sql = GRANDTOTAL_DISTINCT_HASH_ATTENDANCE_BY_HARE;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hashList = $app['db']->fetchAll($sql,array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Total distinct hashers at their hashes',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'If hasher X has done 100 of hare Y\'s events, they contribute 1 to the hash count.',
    'theList' => $hashList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}

public function hasherCountsByHareAction(Request $request, Application $app, int $hare_id, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Define the SQL to execute
  $sql = "SELECT
    	HASHERS.HASHER_NAME AS NAME,
        COUNT(*) AS VALUE
    FROM
    	HARINGS
        JOIN HASHINGS ON HARINGS.HARINGS_HASH_KY = HASHINGS.HASH_KY
        JOIN HASHERS ON HASHINGS.HASHER_KY = HASHERS.HASHER_KY
        JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
    WHERE
    	HARINGS.HARINGS_HASHER_KY = ?
        AND HASHINGS.HASHER_KY != ?
        AND HASHES.KENNEL_KY = ?
    GROUP BY HASHERS.HASHER_NAME
    ORDER BY VALUE DESC";

  #Execute the SQL statement; create an array of rows
  $hashList = $app['db']->fetchAll($sql,array( (int) $hare_id, (int)$hare_id, (int) $kennelKy));

  # Declare the SQL used to retrieve this information
  $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

  # Make a database call to obtain the hasher information
  $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hare_id));

  # Establish and set the return value
  $hasherName = $hasher['HASHER_NAME'];
  $captionValue = "The hashers who've hashed under the hare, $hasherName";
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Hasher Counts',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => $captionValue,
    'theList' => $hashList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}




public function basicStatsAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Obtain the first hash
  $firstHashSQL = "SELECT * FROM HASHES WHERE KENNEL_KY = ? ORDER BY EVENT_DATE ASC LIMIT 1";
  $firstHashValue = $app['db']->fetchAssoc($firstHashSQL, array((int) $kennelKy));

  #Obtain the most recent hash
  $mostRecentHashSQL = "SELECT * FROM HASHES WHERE KENNEL_KY = ? ORDER BY EVENT_DATE DESC LIMIT 1";
  $mostRecentHashValue = $app['db']->fetchAssoc($mostRecentHashSQL, array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('basic_stats.twig',array(
    'pageTitle' => 'Basic Information and Statistics',
    'kennel_abbreviation' => $kennel_abbreviation,
    'first_hash' => $firstHashValue,
    'latest_hash' => $mostRecentHashValue
  ));

  #Return the return value
  return $returnValue;

}


public function hashingStatsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Establish and set the return value
  $returnValue = $app['twig']->render('hashing_stats.twig',array(
    'pageTitle' => 'Hashing Statistics',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}


public function haringStatsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Establish and set the return value
  $returnValue = $app['twig']->render('haring_stats.twig',array(
    'pageTitle' => 'Haring Statistics',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}


public function analversaryStatsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Establish and set the return value
  $returnValue = $app['twig']->render('analversary_stats.twig',array(
    'pageTitle' => 'Analversary Statistics',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}


public function cautionaryStatsAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Establish the hasher keys for all hares for this kennel
  $hareKeysSQL = "SELECT HARINGS_HASHER_KY AS HARE_KEY
    FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
    WHERE HASHES.KENNEL_KY = ? ORDER BY RAND() LIMIT 5";

  #Execute the SQL statement; create an array of rows
  $hareKeys = $app['db']->fetchAll($hareKeysSQL,array( (int) $kennelKy));

  #Establish an array of ridiculous statistics
  $arrayOfRidiculousness = array(
    "Hashes where VD was contrated",
    "Hashes where someone got pregnant",
    "Hashes where someone was sexually harassed",
    "Hashes where Hot Tub talked about his childhood friend, Abe Lincoln",
    "Hashes where someone coveted their neighbor's wife",
    "Hashes where hashers were mocked for their Kentucky heritage",
    "Hashes where hashers were mocked for their Michigan heritage",
    "Hashes where people did it on trail",
    "Hashes where a hasher was arrested",
    "Hashes where the police showed up",
    "Hashes where religious leaders drank with us",
    "Hashes where the Pope ran trail",
    "Hashes where the streams were crossed",
    "Hashes where no harriettes showed up",
    "Hashes that could have used better beer",
    "Hashes that could have used a better trail",
    "Hashes that could have used better hares",
    "Hashes that caused somebody to move away",
    "Hashes where someone shat on trail",
    "Hashes where someone called the police on us",
    "Hashes that brought great shame to everyone involved",
    "Hashes where dogs did it on trail"

  );

  #Establish the keys of the random values to display
  $randomKeysForRidiculousStats = array_rand($arrayOfRidiculousness, 5);

  # Establish and set the return value
  $returnValue = $app['twig']->render('cautionary_stats.twig',array(
    'listOfRidiculousness' => $arrayOfRidiculousness,
    'randomKeysForRidiculousStats' => $randomKeysForRidiculousStats,
    'pageTitle' => 'Cautionary Statistics',
    'kennel_abbreviation' => $kennel_abbreviation,
    'hareKeys' => $hareKeys
  ));

  #Return the return value
  return $returnValue;

}


public function miscellaneousStatsAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #SQL to determine the distinct year values
  $sql = "SELECT YEAR(EVENT_DATE) AS YEAR, COUNT(*) AS THE_COUNT
  FROM HASHES
  WHERE
  	KENNEL_KY = ?
  GROUP BY YEAR(EVENT_DATE)
  ORDER BY YEAR(EVENT_DATE) DESC";

  #Execute the SQL statement; create an array of rows
  $yearValues = $app['db']->fetchAll($sql,array( (int) $kennelKy));

  #Obtain the first hash
  $firstHashSQL = "SELECT * FROM HASHES WHERE KENNEL_KY = ? ORDER BY EVENT_DATE ASC LIMIT 1";
  $firstHashValue = $app['db']->fetchAssoc($firstHashSQL, array((int) $kennelKy));

  #Obtain the most recent hash
  $mostRecentHashSQL = "SELECT * FROM HASHES WHERE KENNEL_KY = ? ORDER BY EVENT_DATE DESC LIMIT 1";
  $mostRecentHashValue = $app['db']->fetchAssoc($mostRecentHashSQL, array((int) $kennelKy));

  #Obtain the kennels that are being tracked in this website instance
  $listOfKennelsSQL = "SELECT * FROM KENNELS WHERE IN_RECORD_KEEPING = 1";
  $kennelValues = $app['db']->fetchAll($listOfKennelsSQL);

  # Establish and set the return value
  $returnValue = $app['twig']->render('miscellaneous_stats.twig',array(
    'firstEvent' => $firstHashValue,
    'mostRecentEvent' => $mostRecentHashValue,
    'theYearValues' => $yearValues,
    'pageTitle' => 'Miscellaneous Statistics',
    'kennel_abbreviation' => $kennel_abbreviation,
    'kennelValues' => $kennelValues
  ));

  #Return the return value
  return $returnValue;

}


public function highestAttendedHashesAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Define the sql
  $theSql = HASH_EVENTS_WITH_COUNTS;
  $theSql = str_replace("XLIMITX","25",$theSql);
  $theSql = str_replace("XUPORDOWNX","DESC",$theSql);

  #Execute the SQL statement; create an array of rows
  $theList = $app['db']->fetchAll($theSql,array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('hash_events_with_participation_counts.twig',array(
    'theList' => $theList,
    'pageTitle' => 'The Hashes',
    'pageSubTitle' => '...with the best attendances',
    'tableCaption' => '',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}


public function lowestAttendedHashesAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Define the sql
  $theSql = HASH_EVENTS_WITH_COUNTS;
  $theSql = str_replace("XLIMITX","25",$theSql);
  $theSql = str_replace("XUPORDOWNX","ASC",$theSql);

  #Execute the SQL statement; create an array of rows
  $theList = $app['db']->fetchAll($theSql,array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('hash_events_with_participation_counts.twig',array(
    'theList' => $theList,
    'pageTitle' => 'The Hashes',
    'pageSubTitle' => '...with the worst attendances',
    'tableCaption' => '',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}

public function hashersOfTheYearsAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #SQL to determine the distinct year values
  $distinctYearsSql = "SELECT YEAR(EVENT_DATE) AS YEAR, COUNT(*) AS THE_COUNT
  FROM HASHES
  WHERE
  	KENNEL_KY = ?
  GROUP BY YEAR(EVENT_DATE)
  ORDER BY YEAR(EVENT_DATE) DESC";

  #Execute the SQL statement; create an array of rows
  $yearValues = $app['db']->fetchAll($distinctYearsSql,array( (int) $kennelKy));

  #Define the sql
  $topHashersSql = "SELECT 		* , ? AS THE_YEAR,
	  (SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ?) AS THE_YEARS_HASH_COUNT,
    (THE_TEMPORARY_TABLE.THE_COUNT / (SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ?))*100 AS HASHING_PERCENTAGE
  FROM HASHERS JOIN (
    	SELECT HASHERS.HASHER_KY AS THE_HASHER_KY, COUNT(*) AS THE_COUNT
    	FROM HASHINGS
    		JOIN HASHERS ON HASHINGS.HASHER_KY = HASHERS.HASHER_KY
    		JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
    	WHERE
    		HASHES.KENNEL_KY = ?
    		AND YEAR(HASHES.EVENT_DATE) = ?
    	GROUP BY HASHERS.HASHER_KY
    	ORDER BY THE_COUNT DESC
    	LIMIT XLIMITX
    ) AS THE_TEMPORARY_TABLE on HASHERS.HASHER_KY = THE_TEMPORARY_TABLE.THE_HASHER_KY";
  $topHashersSql = str_replace("XLIMITX","12",$topHashersSql);


  #Initialize the array of arrays
  $array = array();

  #Loop through the year values
  for ($tempCounter = 1; $tempCounter <= sizeof($yearValues); $tempCounter++){

    #Establish the year for this loop iteration
    $tempYear = $yearValues[$tempCounter-1]["YEAR"];

    #Make a database call passing in this iteration's year value
    $tempResult = $app['db']->fetchAll($topHashersSql,array(
      (int) $tempYear,
      (int) $kennelKy,
      (int) $tempYear,
      (int) $kennelKy,
      (int) $tempYear,
      (int) $kennelKy,
      (int) $tempYear));

    #Add the database result set to the array of arrays
    $array[] = $tempResult;

  }



  # Establish and set the return value
  $returnValue = $app['twig']->render('top_hashers_by_years.twig',array(
    'theListOfLists' => $array,
    #'tempList' => $tempResult,
    'pageTitle' => 'Top Hashers Per Year',
    'pageSubTitle' => '',
    'tableCaption' => '',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}



public function overallHaresOfTheYearsAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #SQL to determine the distinct year values
  $distinctYearsSql = "SELECT YEAR(EVENT_DATE) AS YEAR, COUNT(*) AS THE_COUNT
  FROM HASHES
  WHERE
  	KENNEL_KY = ?
  GROUP BY YEAR(EVENT_DATE)
  ORDER BY YEAR(EVENT_DATE) DESC";

  #Execute the SQL statement; create an array of rows
  $yearValues = $app['db']->fetchAll($distinctYearsSql,array( (int) $kennelKy));

  #Define the sql
  $topHaresSql = "SELECT
    	* , ? AS THE_YEAR,
    	(SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ? AND HASHES.IS_HYPER = 1) AS THE_YEARS_HYPER_HASH_COUNT,
        (SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ? AND HASHES.IS_HYPER = 0) AS THE_YEARS_NON_HYPER_HASH_COUNT,
        (SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ? ) AS THE_YEARS_OVERALL_HASH_COUNT
    FROM
    HASHERS JOIN (
    	SELECT HASHERS.HASHER_KY AS THE_HASHER_KY, COUNT(*) AS THE_COUNT
    	FROM HARINGS
    		JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
    		JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
    	WHERE
    		HASHES.KENNEL_KY = ?
    		AND YEAR(HASHES.EVENT_DATE) = ?
            AND HASHES.IS_HYPER in (?,?)
    	GROUP BY HASHERS.HASHER_KY
    	ORDER BY THE_COUNT DESC
    	LIMIT XLIMITX
    ) AS THE_TEMPORARY_TABLE on HASHERS.HASHER_KY = THE_TEMPORARY_TABLE.THE_HASHER_KY";
  $topHaresSql = str_replace("XLIMITX","12",$topHaresSql);


  #Initialize the array of arrays
  $array = array();

  #Loop through the year values
  for ($tempCounter = 1; $tempCounter <= sizeof($yearValues); $tempCounter++){

    #Establish the year for this loop iteration
    $tempYear = $yearValues[$tempCounter-1]["YEAR"];

    #Make a database call passing in this iteration's year value
    $tempResult = $app['db']->fetchAll($topHaresSql,array(
      (int) $tempYear,

      (int) $kennelKy,
      (int) $tempYear,
      (int) $kennelKy,
      (int) $tempYear,
      (int) $kennelKy,
      (int) $tempYear,

      (int) $kennelKy,
      (int) $tempYear,
      (int) 0,
      (int) 1));

    #Add the database result set to the array of arrays
    $array[] = $tempResult;

  }



  # Establish and set the return value
  $returnValue = $app['twig']->render('top_hares_by_years.twig',array(
    'theListOfLists' => $array,
    #'tempList' => $tempResult,
    'pageTitle' => 'Top Hares Per Year (All harings)',
    'pageSubTitle' => '(All hashes included)',
    'tableCaption' => '',
    'kennel_abbreviation' => $kennel_abbreviation,
    'participant_column_header' => 'Hasher',
    'number_column_header' => 'Number of overall harings',
    'percentage_column_header' => 'Percentage of overall hashes hared',
    'overall_boolean' => 'TRUE'
  ));

  #Return the return value
  return $returnValue;

}


public function nonHyperHaresOfTheYearsAction(Request $request, Application $app, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #SQL to determine the distinct year values
  $distinctYearsSql = "SELECT YEAR(EVENT_DATE) AS YEAR, COUNT(*) AS THE_COUNT
  FROM HASHES
  WHERE
  	KENNEL_KY = ?
  GROUP BY YEAR(EVENT_DATE)
  ORDER BY YEAR(EVENT_DATE) DESC";

  #Execute the SQL statement; create an array of rows
  $yearValues = $app['db']->fetchAll($distinctYearsSql,array( (int) $kennelKy));

  #Define the sql
  $topHaresSql = "SELECT
    	* , ? AS THE_YEAR,
    	(SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ? AND HASHES.IS_HYPER = 1) AS THE_YEARS_HYPER_HASH_COUNT,
        (SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ? AND HASHES.IS_HYPER = 0) AS THE_YEARS_NON_HYPER_HASH_COUNT,
        (SELECT COUNT(*) AS THE_HASH_COUNT FROM HASHES WHERE KENNEL_KY = ? AND YEAR(HASHES.EVENT_DATE) = ? ) AS THE_YEARS_OVERALL_HASH_COUNT
    FROM
    HASHERS JOIN (
    	SELECT HASHERS.HASHER_KY AS THE_HASHER_KY, COUNT(*) AS THE_COUNT
    	FROM HARINGS
    		JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
    		JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
    	WHERE
    		HASHES.KENNEL_KY = ?
    		AND YEAR(HASHES.EVENT_DATE) = ?
            AND HASHES.IS_HYPER in (?,?)
    	GROUP BY HASHERS.HASHER_KY
    	ORDER BY THE_COUNT DESC
    	LIMIT XLIMITX
    ) AS THE_TEMPORARY_TABLE on HASHERS.HASHER_KY = THE_TEMPORARY_TABLE.THE_HASHER_KY";
  $topHaresSql = str_replace("XLIMITX","12",$topHaresSql);


  #Initialize the array of arrays
  $array = array();

  #Loop through the year values
  for ($tempCounter = 1; $tempCounter <= sizeof($yearValues); $tempCounter++){

    #Establish the year for this loop iteration
    $tempYear = $yearValues[$tempCounter-1]["YEAR"];

    #Make a database call passing in this iteration's year value
    $tempResult = $app['db']->fetchAll($topHaresSql,array(
      (int) $tempYear,

      (int) $kennelKy,
      (int) $tempYear,
      (int) $kennelKy,
      (int) $tempYear,
      (int) $kennelKy,
      (int) $tempYear,

      (int) $kennelKy,
      (int) $tempYear,
      (int) 0,
      (int) 0));

    #Add the database result set to the array of arrays
    $array[] = $tempResult;

  }



  # Establish and set the return value
  $returnValue = $app['twig']->render('top_hares_by_years.twig',array(
    'theListOfLists' => $array,
    #'tempList' => $tempResult,
    'pageTitle' => 'Top Hares Per Year (non-hyper harings)',
    'pageSubTitle' => '(hyper-hashes excluded)',
    'tableCaption' => '',
    'kennel_abbreviation' => $kennel_abbreviation,
    'participant_column_header' => 'Hasher',
    'number_column_header' => 'Number of non-hyper harings',
    'percentage_column_header' => 'Percentage of non-hypers hared',
    'overall_boolean' => 'FALSE'
  ));

  #Return the return value
  return $returnValue;

}

public function getHasherAnalversariesAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  # Declare the SQL used to retrieve this information
  $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

  # Make a database call to obtain the hasher information
  $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

  # Define the SQL to retrieve all of their hashes
  $sql_all_hashes_for_this_hasher = "	SELECT
	HASHERS.HASHER_KY, HASHERS.HASHER_NAME , HASHES.*
	FROM HASHINGS
		JOIN HASHERS ON HASHINGS.HASHER_KY = HASHERS.HASHER_KY
		JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
	WHERE
		HASHERS.HASHER_KY = ?
		AND HASHES.KENNEL_KY = ?
	ORDER By HASHES.EVENT_DATE ASC";

  #Retrieve all of this hasher's hashes
  $theInitialListOfHashes = $app['db']->fetchAll($sql_all_hashes_for_this_hasher,array(
        (int) $hasher_id,
        (int) $kennelKy));

  # Add a count into their list of hashes
  $destinationArray = array();
  $tempCounter = 1;
  foreach ($theInitialListOfHashes as &$individualHash) {
    $individualHash['ANALVERSARY_NUMBER'] = $tempCounter;
    if(
      ($tempCounter % 5 == 0) ||
      ($tempCounter % 69 == 0) ||
      ($tempCounter % 666 == 0) ||
      (($tempCounter - 69) % 100 == 0)
      ){
      array_push($destinationArray,$individualHash);
    }
    $tempCounter ++;
  }

  # Establish and set the return value
  $hasherName = $hasher['HASHER_NAME'];
  $pageTitle = "Hashing Analversaries: $hasherName";
  $returnValue = $app['twig']->render('hasher_analversary_list.twig',array(
    'theList' => $destinationArray,
    'pageTitle' => $pageTitle,
    'pageSubTitle' => '',
    'tableCaption' => '',
    'kennel_abbreviation' => $kennel_abbreviation,
    'participant_column_header' => 'Hasher',
    'number_column_header' => 'Number of non-hyper harings',
    'percentage_column_header' => 'Percentage of non-hypers hared',
    'overall_boolean' => 'FALSE'
  ));

  #Return the return value
  return $returnValue;



}


}
