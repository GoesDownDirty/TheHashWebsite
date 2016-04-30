<?php

namespace HASH\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class HashController
{




  #Define the action
  public function logonScreenAction(Request $request, Application $app){

    $app['monolog']->addDebug('Entering the logonScreenAction');

    # Establisht the last error
    $lastError = $app['security.last_error']($request);
    $app['monolog']->addDebug($lastError);

    # Establish the last username
    $lastUserName = $app['session']->get('_security.last_username');
    #$lastUserName = $app['session']->get('_security.last_username');
    $app['monolog']->addDebug($lastUserName);


    # Establish the return value
    $returnValue =  $app['twig']->render('logon_screen.twig', array (
      'pageTitle' => 'SCH4 Stats Logon',
      'pageHeader' => 'Please log on!',
      'error' => $lastError,
      'last_username' => $lastUserName,
    ));

    $app['monolog']->addDebug('Leaving the logonScreenAction');

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

    $app['monolog']->addDebug('Entering the slash action');

    $token = $app['security.token_storage']->getToken();
    $app['monolog']->addDebug($token);

    // Symfony 2.6+
    /*
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
        // ...
    }
    */

    #$token = $app['security.token_storage']->getToken();
    #$token = $app['security']->getToken();

    #$encoder = $app['security.encoder_factory']->getEncoder($user);

    # Obtain the logged in user
    $user = $app['session']->get('user');
    $userName = $user['username'];

     // find the encoder for a UserInterface instance
    #$encoder = $app['security.encoder_factory']->getEncoder($user);
    // compute the encoded password for foo
    #$password = $encoder->encodePassword('foo', $user->getSalt());

    #Establish the page caption
    $pageCaption = "You are logged in as: $userName and your password is password";


    #Set the return value
    $returnValue = $app['twig']->render('slash.twig',array(
      'pageTitle' => 'SCH4 Stats Home Page',
      'pageCaption' => $pageCaption,
      'subTitle1' => 'Standard Statistics',
      'subTitle2' => 'Analversary Statistics',
      'subTitle3' => 'Hare Statistics',
      'subTitle4' => 'Other Statistics'
    ));

    #Return the return value
    return $returnValue;

  }


  #Define the action
  public function listHashersAction(Request $request, Application $app){

    #Define the SQL to execute
    $sql = "SELECT HASHER_KY AS THE_KEY, HASHER_NAME AS NAME FROM HASHERS";

    #Execute the SQL statement; create an array of rows
    $hasherList = $app['db']->fetchAll($sql);

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_list.twig',array(
      'pageTitle' => 'The List of Hashers',
      'pageSubTitle' => 'The List of *ALL* Hashers',
      'theList' => $hasherList
    ));

    #Return the return value
    return $returnValue;

  }

  public function listHashersByHashAction(Request $request, Application $app, int $hash_id){

    #Define the SQL to execute
    $sql = "SELECT HASHERS.HASHER_KY AS THE_KEY, HASHERS.HASHER_NAME AS NAME FROM HASHERS JOIN HASHINGS ON HASHERS.HASHER_KY = HASHINGS.HASHER_KY WHERE HASHINGS.HASH_KY = ?";

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
      'theList' => $hasherList
    ));

    #Return the return value
    return $returnValue;

  }

  public function listHaresByHashAction(Request $request, Application $app, int $hash_id){


    #Define the SQL to execute
    $sql = "SELECT HASHERS.HASHER_KY AS THE_KEY, HASHERS.HASHER_NAME AS NAME FROM HASHERS JOIN HARINGS ON HASHERS.HASHER_KY = HARINGS.HARINGS_HASHER_KY WHERE HARINGS.HARINGS_HASH_KY = ?";

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
      'theList' => $hasherList
    ));

    #Return the return value
    return $returnValue;
  }

  public function listhashesAction(Request $request, Application $app){

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
    FROM HASHES ORDER BY HASH_KY DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql);

    # Establish and set the return value
    $returnValue = $app['twig']->render('hash_list.twig',array(
      'pageTitle' => 'The List of Hashes',
      'pageSubTitle' => 'The List of *All* Hashes',
      'theList' => $hashList,
      'tableCaption' => 'A list of all hashes ever, since forever.',
    ));


    #Return the return value
    return $returnValue;
  }

  public function listHashesByHasherAction(Request $request, Application $app, int $hasher_id){

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
    WHERE HASHINGS.HASHER_KY = ?
    ORDER BY HASHES.HASH_KY DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id));

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
      'tableCaption' => ''
    ));

    #Return the return value
    return $returnValue;

  }



  public function listHashesByHareAction(Request $request, Application $app, int $hasher_id){

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
      WHERE HARINGS.HARINGS_HASHER_KY = ?";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

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
    ));

    #Return the return value
    return $returnValue;
  }


  public function viewHasherAction(Request $request, Application $app, int $hasher_id){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql, array((int) $hasher_id));

    # Obtain the number of hashings
    $sqlHashCount = "SELECT COUNT(*) AS THE_COUNT FROM HASHINGS WHERE HASHER_KY = ?";
    $hashCountValue = $app['db']->fetchAssoc($sqlHashCount, array((int) $hasher_id));

    # Obtain the number of harings
    $sqlHareCount = "SELECT COUNT(*) AS THE_COUNT FROM HARINGS WHERE HARINGS_HASHER_KY = ?";
    $hareCountValue = $app['db']->fetchAssoc($sqlHareCount, array((int) $hasher_id));

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_details.twig',array(
      'pageTitle' => 'Hasher Details',
      'firstHeader' => 'Basic Details',
      'secondHeader' => 'Statistics',
      'hasherValue' => $hasher,
      'hashCount' => $hashCountValue[THE_COUNT],
      'hareCount' => $hareCountValue[THE_COUNT],
    ));

    # Return the return value
    return $returnValue;

  }


  public function viewHashAction(Request $request, Application $app, int $hash_id){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql, array((int) $hash_id));

    # Establish and set the return value
    $returnValue = $app['twig']->render('hash_details.twig',array(
      'pageTitle' => 'Hash Details',
      'firstHeader' => 'Basic Details',
      'secondHeader' => 'Statistics',
      'hashValue' => $theHashValue
    ));

    # Return the return value
    return $returnValue;

  }

  public function hasherAnalversariesForEventAction(Request $request, Application $app, int $hash_id){

    # Declare the SQL used to retrieve this information
    $sql = "    SELECT
        hashers.HASHER_NAME AS HASHER_NAME,
        (COUNT(*)) AS THE_COUNT,
        MAX(HASHINGS.HASH_KY) AS MAX_HASH_KY
    FROM
        ((hashers
        JOIN hashings ON ((hashers.HASHER_KY = hashings.HASHER_KY)))
        JOIN hashes ON ((hashings.HASH_KY = hashes.HASH_KY)))
    WHERE
        (hashers.DECEASED = 0) AND
        HASHES.HASH_KY <= ?
    GROUP BY hashers.HASHER_NAME
    HAVING ((((THE_COUNT % 5) = 0)
        OR ((THE_COUNT % 69) = 0)
        OR ((THE_COUNT % 666) = 0)
        OR (((THE_COUNT - 69) % 100) = 0)))
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $hash_id));

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
      'theList' => $analversaryList
    ));

    # Return the return value
    return $returnValue;
  }

  public function hareAnalversariesForEventAction(Request $request, Application $app, int $hash_id){

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
                HARINGS.HARINGS_HASH_KY <= ?
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
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $hash_id));

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
      'theList' => $analversaryList
    ));


    # Return the return value
    return $returnValue;
  }

public function pendingHasherAnalversariesAction(Request $request, Application $app){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT * FROM PENDING_HASHER_ANALVERSARIES";

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql);

  # Declare the SQL to get the most recent hash
  $sqlMostRecentHash = "SELECT KENNEL_EVENT_NUMBER, EVENT_DATE, EVENT_LOCATION, SPECIAL_EVENT_DESCRIPTION
    FROM HASHES
    JOIN (select max(hash_ky) as HASH_KY from hashings) AS TEMPTABLE
    ON HASHES.HASH_KY = TEMPTABLE.HASH_KY";

  # Execute the SQL to get the most recent hash
  $theMostRecentHashValue = $app['db']->fetchAssoc($sqlMostRecentHash);

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
  ));


  #Return the return value
  return $returnValue;

}

public function pendingHareAnalversariesAction(Request $request, Application $app){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT * FROM PENDING_HARE_ANALVERSARIES";

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql);

  # Declare the SQL to get the most recent hash
  $sqlMostRecentHash = "SELECT KENNEL_EVENT_NUMBER, EVENT_DATE, EVENT_LOCATION, SPECIAL_EVENT_DESCRIPTION
    FROM HASHES
    JOIN (select max(harings_hash_ky) as HASH_KY from harings) AS TEMPTABLE
    ON HASHES.HASH_KY = TEMPTABLE.HASH_KY";

  # Execute the SQL to get the most recent hash
  $theMostRecentHashValue = $app['db']->fetchAssoc($sqlMostRecentHash);

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
  ));

  #Return the return value
  return $returnValue;

}

public function haringPercentageAllHashesAction(Request $request, Application $app){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT * FROM HARING_PERCENTAGE_ALL_HASHES";

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql);

  # Establish the return value
  $returnValue = $app['twig']->render('percentage_list.twig',array(
    'pageTitle' => 'Haring Percentage List',
    'tableCaption' => 'Percentage of harings per hashings for each hasher',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Hashing Count',
    'columnThreeName' => 'Haring Count',
    'columnFourName' => 'Haring Percentage',
    'theList' => $hasherList
  ));

  #Return the return value
  return $returnValue;

}


public function haringPercentageNonHypersAction(Request $request, Application $app){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT * FROM HARING_PERCENTAGE_NON_HYPERS";

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql);

  # Establish the return value
  $returnValue = $app['twig']->render('percentage_list.twig',array(
    'pageTitle' => '(Non Hyper Hash) Haring Percentage List',
    'tableCaption' => 'Percentage of non hyper harings per hashings for each hasher',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Hashing Count',
    'columnThreeName' => 'Haring Count',
    'columnFourName' => 'Haring Percentage',
    'theList' => $hasherList
  ));

  #Return the return value
  return $returnValue;
}


public function hashingCountsAction(Request $request, Application $app){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT HASHER_NAME AS NAME, THE_COUNT as VALUE FROM HASHER_COUNTS";

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql);

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Hasher Counts',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'Hashers, and the number of hashes they have done. More is better.',
    'theList' => $hasherList
  ));

  #Return the return value
  return $returnValue;

}


public function haringCountsAction(Request $request, Application $app){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT HASHER_NAME AS NAME, THE_COUNT AS VALUE FROM HARING_COUNTS";

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql);

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Haring Counts',
    'columnOneName' => 'Hasher Name',
    'columnTwoName' => 'Haring Count',
    'tableCaption' => 'Hares, and the number of times they have hared. More is better.',
    'theList' => $hasherList
  ));

  #Return the return value
  return $returnValue;

}

public function nonHyperHaringCountsAction(Request $request, Application $app){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT HASHER_NAME AS NAME, THE_COUNT AS VALUE FROM NON_HYPER_HARING_COUNTS";

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql);

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Non Hyper-Hash Haring Counts',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'Hares, and the number of (non hyper-hash) hashes they have hared. More is better. These numbers will never truely be accurate until Hot Tub Slut gets me the list of all hyper hashes.',
    'theList' => $hasherList
  ));

  #Return the return value
  return $returnValue;

}


  public function coharelistByHareAllHashesAction(Request $request, Application $app, int $hasher_id){

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
          AND HASHES.IS_HYPER IN (?,?)
      ORDER BY HARINGS.HARINGS_HASH_KY ASC";

    #Execute the SQL statement; create an array of rows
    $cohareList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,1));

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
      'theList' => $cohareList
    ));



    #Return the return value
    return $returnValue;

  }


  public function coharelistByHareNonHypersAction(Request $request, Application $app, int $hasher_id){

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
          AND HASHES.IS_HYPER IN (?,?)
      ORDER BY HARINGS.HARINGS_HASH_KY ASC";

    #Execute the SQL statement; create an array of rows
    $cohareList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0));

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
      'theList' => $cohareList
    ));

    #Return the return value
    return $returnValue;

  }


  public function cohareCountByHareAllHashesAction(Request $request, Application $app, int $hasher_id){

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
          AND HASHES.IS_HYPER IN (?,?)
      GROUP BY TEMPTABLE.HASHER_NAME
      ORDER BY VALUE DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,1));

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
      'theList' => $hashList
    ));

    #Return the return value
    return $returnValue;

  }

  public function cohareCountByHareNonHypersAction(Request $request, Application $app, int $hasher_id){

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
          AND HASHES.IS_HYPER IN (?,?)
      GROUP BY TEMPTABLE.HASHER_NAME
      ORDER BY VALUE DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0));

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
      'theList' => $hashList
    ));

    #Return the return value
    return $returnValue;
  }

  public function hashAttendanceByHareLowestAction(Request $request, Application $app){

    #Define the SQL to execute
    $sql = "SELECT HASHER_NAME AS NAME, MIN_NUMBER_OF_PEOPLE_AT_THEIR_EVENTS AS VALUE FROM LOWEST_HASH_ATTENDANCE_BY_HARE";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0));

    # Establish and set the return value
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Lowest hash attendance by hare',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hasher Count',
      'tableCaption' => 'The lowest hash attendance for each hare.',
      'theList' => $hashList
    ));

    #Return the return value
    return $returnValue;

  }


public function hashAttendanceByHareHighestAction(Request $request, Application $app){

  #Define the SQL to execute
  $sql = "SELECT HASHER_NAME AS NAME, MAX_NUMBER_OF_PEOPLE_AT_THEIR_EVENTS AS VALUE FROM HIGHEST_HASH_ATTENDANCE_BY_HARE";

  #Execute the SQL statement; create an array of rows
  $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Highest attended hashes by hare',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hasher Count',
    'tableCaption' => 'The highest attended hashes for each hare.',
    'theList' => $hashList
  ));

  #Return the return value
  return $returnValue;
}



  public function hashAttendanceByHareAverageAction(Request $request, Application $app){

    #Define the SQL to execute
    $sql = "SELECT HASHER_NAME AS NAME, AVERAGE_NUMBER_OF_PEOPLE_AT_THEIR_EVENTS AS VALUE FROM AVERAGE_HASH_ATTENDANCE_BY_HARE";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0));

    # Establish and set the return value
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Average hash attendance by hare',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hasher Count',
      'tableCaption' => 'The average hash attendance for each hare.',
      'theList' => $hashList
    ));

    #Return the return value
    return $returnValue;
  }


  public function hashAttendanceByHareGrandTotalNonDistinctHashersAction(Request $request, Application $app){

    #Define the SQL to execute
    $sql = "SELECT HASHER_NAME AS NAME, GRAND_NUMBER_OF_PEOPLE_AT_THEIR_EVENTS AS VALUE FROM GRANDTOTAL_NONDISTINCT_HASH_ATTENDANCE_BY_HARE";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql,array((int) $hasher_id, (int) $hasher_id,0,0));

    # Establish and set the return value
    $returnValue = $app['twig']->render('name_number_list.twig',array(
      'pageTitle' => 'Total (non distinct) hashers at their hashes',
      'columnOneName' => 'Hare Name',
      'columnTwoName' => 'Hash Count',
      'tableCaption' => 'If hasher X has done 100 of hare Y\'s events, they contribute 100 to the hash count.',
      'theList' => $hashList
    ));

    #Return the return value
    return $returnValue;
  }

public function hashAttendanceByHareGrandTotalDistinctHashersAction(Request $request, Application $app){

  #Define the SQL to execute
  $sql = "SELECT HASHER_NAME AS NAME, THE_COUNT AS VALUE FROM GRANDTOTAL_DISTINCT_HASH_ATTENDANCE_BY_HARE";

  #Execute the SQL statement; create an array of rows
  $hashList = $app['db']->fetchAll($sql);

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Total distinct hashers at their hashes',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'If hasher X has done 100 of hare Y\'s events, they contribute 1 to the hash count.',
    'theList' => $hashList
  ));

  #Return the return value
  return $returnValue;

}

public function hasherCountsByHareAction(Request $request, Application $app, int $hare_id){

  #Define the SQL to execute
  $sql = "SELECT
    	HASHERS.HASHER_NAME AS NAME,
        COUNT(*) AS VALUE
    FROM
    	HARINGS
        JOIN HASHINGS ON HARINGS.HARINGS_HASH_KY = HASHINGS.HASH_KY
        JOIN HASHERS ON HASHINGS.HASHER_KY = HASHERS.HASHER_KY
    WHERE
    	HARINGS.HARINGS_HASHER_KY = ?
        AND HASHINGS.HASHER_KY != ?
    GROUP BY HASHERS.HASHER_NAME
    ORDER BY VALUE DESC";

  #Execute the SQL statement; create an array of rows
  $hashList = $app['db']->fetchAll($sql,array( (int) $hare_id, (int)$hare_id));

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
    'theList' => $hashList
  ));

  #Return the return value
  return $returnValue;

}


}
