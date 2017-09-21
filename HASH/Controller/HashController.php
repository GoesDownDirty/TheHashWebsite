<?php

namespace HASH\Controller;

require_once realpath(__DIR__ . '/../..').'/config/SQL_Queries.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use \Datetime;

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
      EMAIL,
      HASHER_ABBREVIATION FROM HASHERS";

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

  #Define the action
  public function listStreakersByDateByHashAction(Request $request, Application $app, string $kennel_abbreviation, int $hash_id){

    #Execute the SQL statement; create an array of rows
    $theList = $app['db']->fetchAll(EVENTS_LAST_MISSED_FOR_EACH_HASHER,array((int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Add a days difference field
    /*
    foreach($theList as &$theListEntry){
      //$theHashEventLocation = $theHashValue['EVENT_LOCATION'];
      $lastMissedDate = new DateTime($theListEntry['LAST_MISSED_DATE']);
      $thisHashDate = new DateTime($theHashValue['EVENT_DATE']);
      $app['monolog']->addDebug("lastMissedDate: $lastMissedDate");
      $app['monolog']->addDebug("thisHashDate: $thisHashDate");
      $tempDaysDiff = $lastMissedDate->diff($thisHashDate);

      $tempDaysDiffFormated = $tempDaysDiff->days;
      $app['monolog']->addDebug("tempDaysDiffFormated: $tempDaysDiffFormated");
      $theListEntry['DAYS_DIFF'] = "hello";
    }
    */



    # Establish and set the return value
    $returnValue = $app['twig']->render('streaker_list_date_version.twig',array(
      'pageTitle' => 'The Streakers!',
      'pageSubTitle' => '...Last Hash Missed',
      'theList' => $theList,
      'kennel_abbreviation' => $kennel_abbreviation,
      'theHashValue' => $theHashValue,
      'pageCaption' => "",
      'tableCaption' => ""
    ));

    #Return the return value
    return $returnValue;

  }



  public function listStreakersByCountsByHashAction(Request $request, Application $app, string $kennel_abbreviation, int $hash_id){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Execute the SQL statement; create an array of rows
    $theList = $app['db']->fetchAll(EVENTS_MISSED_COUNT_FOR_EACH_HASHER,array((int) $hash_id,(int) $kennelKy,(int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $returnValue = $app['twig']->render('streaker_list_count_version.twig',array(
      'pageTitle' => 'The Streakers!',
      'pageSubTitle' => '...Last Hash Missed',
      'theList' => $theList,
      'kennel_abbreviation' => $kennel_abbreviation,
      'theHashValue' => $theHashValue,
      'pageCaption' => "",
      'tableCaption' => ""
    ));

    #Return the return value
    return $returnValue;

  }

  #Define the action
  public function listHashersPreActionJson(Request $request, Application $app, string $kennel_abbreviation){

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_list_json.twig',array(
      'pageTitle' => 'The List of Hashers',
      'pageSubTitle' => '',
      #'theList' => $hasherList,
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
      HASHERS.EMAIL,
      HASHERS.HASHER_ABBREVIATION
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
      HASHERS.EMAIL,
      HASHERS.HASHER_ABBREVIATION
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

  public function getHasherListJson(Request $request, Application $app, string $kennel_abbreviation){

    #$app['monolog']->addDebug("Entering the function------------------------");

    #Obtain the post parameters
    #$inputDraw = $_POST['draw'] ;
    $inputStart = $_POST['start'] ;
    $inputLength = $_POST['length'] ;
    $inputColumns = $_POST['columns'];
    $inputSearch = $_POST['search'];
    $inputSearchValue = $inputSearch['value'];

    #-------------- Begin: Validate the post parameters ------------------------
    #Validate input start
    if(!is_numeric($inputStart)){
      #$app['monolog']->addDebug("input start is not numeric: $inputStart");
      $inputStart = 0;
    }

    #Validate input length
    if(!is_numeric($inputLength)){
      #$app['monolog']->addDebug("input length is not numeric");
      $inputStart = "0";
      $inputLength = "50";
    } else if($inputLength == "-1"){
      #$app['monolog']->addDebug("input length is negative one (all rows selected)");
      $inputStart = "0";
      $inputLength = "1000000000";
    }

    #Validate input search
    #We are using database parameterized statements, so we are good already...

    #---------------- End: Validate the post parameters ------------------------

    #-------------- Begin: Modify the input parameters  ------------------------
    #Modify the search string
    $inputSearchValueModified = "%$inputSearchValue%";

    #Obtain the column/order information
    $inputOrderRaw = isset($_POST['order']) ? $_POST['order'] : null;
    $inputOrderColumnExtracted = "1";
    $inputOrderColumnIncremented = "1";
    $inputOrderDirectionExtracted = "asc";
    if(!is_null($inputOrderRaw)){
      #$app['monolog']->addDebug("inside inputOrderRaw not null");
      $inputOrderColumnExtracted = $inputOrderRaw[0]['column'];
      $inputOrderColumnIncremented = $inputOrderColumnExtracted + 1;
      $inputOrderDirectionExtracted = $inputOrderRaw[0]['dir'];
    }else{
      #$app['monolog']->addDebug("inside inputOrderRaw is null");
    }

    #-------------- End: Modify the input parameters  --------------------------


    #-------------- Begin: Define the SQL used here   --------------------------

    #Define the sql that performs the filtering
    $sql = "SELECT
      HASHER_NAME AS NAME,
      HASHER_ABBREVIATION,
      HASHER_KY AS THE_KEY
      FROM HASHERS
      WHERE
        (
          HASHER_NAME LIKE ? OR
          HASHER_ABBREVIATION LIKE ?)
      ORDER BY $inputOrderColumnIncremented $inputOrderDirectionExtracted
      LIMIT $inputStart,$inputLength";
      #$app['monolog']->addDebug("sql: $sql");

    #Define the SQL that gets the count for the filtered results
    $sqlFilteredCount = "SELECT COUNT(*) AS THE_COUNT
      FROM HASHERS
      WHERE
          HASHER_NAME LIKE ? OR
          HASHER_ABBREVIATION LIKE ?";

    #Define the sql that gets the overall counts
    $sqlUnfilteredCount = "SELECT COUNT(*) AS THE_COUNT FROM HASHERS";

    #-------------- End: Define the SQL used here   ----------------------------

    #-------------- Begin: Query the database   --------------------------------
    #Perform the filtered search
    $theResults = $app['db']->fetchAll($sql,array(
      (string) $inputSearchValueModified,
      (string) $inputSearchValueModified));

    #Perform the untiltered count
    $theUnfilteredCount = ($app['db']->fetchAssoc($sqlUnfilteredCount,array()))['THE_COUNT'];

    #Perform the filtered count
    $theFilteredCount = ($app['db']->fetchAssoc($sqlFilteredCount,array(
      (string) $inputSearchValueModified,
      (string) $inputSearchValueModified)))['THE_COUNT'];
    #-------------- End: Query the database   --------------------------------

    #Establish the output
    $output = array(
      "sEcho" => "foo",
      "iTotalRecords" => $theUnfilteredCount,
      "iTotalDisplayRecords" => $theFilteredCount,
      "aaData" => $theResults
    );

    #Set the return value
    $returnValue = $app->json($output,200);

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

  public function attendanceRecordForHasherAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll(HASHER_ATTENDANCE_RECORD_LIST,array((int)$kennelKy,(int) $hasher_id, (int)$kennelKy));

    # Declare the SQL used to retrieve this information
    $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

    # Establish and set the return value
    $hasherName = $hasher['HASHER_NAME'];
    $pageSubtitle = "The hashes attended by  $hasherName";
    $returnValue = $app['twig']->render('hasher_attendance_list.twig',array(
      'pageTitle' => 'Attendance Record',
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

  public function viewHasherChartsAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Make a database call to obtain the hasher information
    $hasher = $app['db']->fetchAssoc($sql, array((int) $hasher_id));

    # Obtain their hashes
    $sqlTheHashes = "SELECT HASHES.* FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
    WHERE HASHER_KY = ? AND KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $hasher_id, (int) $kennelKy));

    #Obtain the average lat
    $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
    WHERE HASHER_KY = ? AND KENNEL_KY = ? and LAT is not null and LNG is not null";
    $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $hasher_id, (int) $kennelKy));
    $avgLat = $theAverageLatLong['THE_LAT'];
    $avgLng = $theAverageLatLong['THE_LNG'];

    # Obtain the number of hashings
    $hashCountValue = $app['db']->fetchAssoc(PERSONS_HASHING_COUNT, array((int) $hasher_id, (int) $kennelKy));

    # Obtain the number of harings
    $hareCountValue = $app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id, (int) $kennelKy,  (int) 0, (int) 1));

    # Obtain the hashes by month (name)
    $theHashesByMonthNameList = $app['db']->fetchAll(HASHER_HASH_COUNTS_BY_MONTH_NAME, array((int) $hasher_id, (int) $kennelKy));

    # Obtain the hashes by quarter
    $theHashesByQuarterList = $app['db']->fetchAll(HASHER_HASH_COUNTS_BY_QUARTER, array((int) $hasher_id, (int) $kennelKy));

    # Obtain the hashes by quarter
    $theHashesByStateList = $app['db']->fetchAll(HASHER_HASH_COUNTS_BY_STATE, array((int) $hasher_id, (int) $kennelKy));

    # Obtain the hashes by county
    $theHashesByCountyList = $app['db']->fetchAll(HASHER_HASH_COUNTS_BY_COUNTY, array((int) $hasher_id, (int) $kennelKy));

    # Obtain the hashes by postal code
    $theHashesByPostalCodeList = $app['db']->fetchAll(HASHER_HASH_COUNTS_BY_POSTAL_CODE, array((int) $hasher_id, (int) $kennelKy));

    # Obtain the hashes by day name
    $theHashesByDayNameList = $app['db']->fetchAll(HASHER_HASH_COUNTS_BY_DAYNAME, array((int) $hasher_id, (int) $kennelKy));

    #Obtain the hashes by year
    $sqlHashesByYear = "SELECT YEAR(EVENT_DATE) AS THE_VALUE, COUNT(*) AS THE_COUNT
     FROM
    	HASHINGS
        JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
      WHERE
    	HASHINGS.HASHER_KY = ? AND
        HASHES.KENNEL_KY = ?
    GROUP BY YEAR(EVENT_DATE)
    ORDER BY YEAR(EVENT_DATE)";
    $hashesByYearList = $app['db']->fetchAll($sqlHashesByYear, array((int) $hasher_id,(int) $kennelKy));

    #Obtain the harings by year
    $sqlHaringsByYear = "SELECT
    	  YEAR(EVENT_DATE) AS THE_VALUE,
        SUM(CASE WHEN HASHES.IS_HYPER IN (0)  THEN 1 ELSE 0 END) NON_HYPER_COUNT,
    	  SUM(CASE WHEN HASHES.IS_HYPER IN (1)  THEN 1 ELSE 0 END) HYPER_COUNT,
        COUNT(*) AS TOTAL_HARING_COUNT
    FROM
        HARINGS
    	  JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
    WHERE
        HARINGS.HARINGS_HASHER_KY = ? AND
        HASHES.KENNEL_KY = ?
    GROUP BY YEAR(EVENT_DATE)
    ORDER BY YEAR(EVENT_DATE)";
    $haringsByYearList = $app['db']->fetchAll($sqlHaringsByYear, array((int) $hasher_id,(int) $kennelKy));

    #Query the database
    $cityHashingsCountList = $app['db']->fetchAll(HASHER_HASH_COUNTS_BY_CITY, array((int) $hasher_id, (int) $kennelKy));

    #Obtain largest entry from the list
    $cityHashingsCountMax = 1;
    if(isset($cityHashingsCountList[0]['THE_COUNT'])){
      $cityHashingsCountMax = $cityHashingsCountList[0]['THE_COUNT'];
    }

    # Establish and set the return value
    $returnValue = $app['twig']->render('hasher_chart_details.twig',array(
      'pageTitle' => 'Hasher Charts and Details',
      'firstHeader' => 'Basic Details',
      'secondHeader' => 'Statistics',
      'hasherValue' => $hasher,
      'hashCount' => $hashCountValue['THE_COUNT'],
      'hareCount' => $hareCountValue['THE_COUNT'],
      'kennel_abbreviation' => $kennel_abbreviation,
      'hashes_by_year_list' => $hashesByYearList,
      'harings_by_year_list' => $haringsByYearList,
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
      'avg_lng' => $avgLng
    ));

    # Return the return value
    return $returnValue;

  }


  public function viewHashAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Obtain the hound count
    $houndCountSQL = HOUND_COUNT_BY_HASH_KEY;
    $theHoundCountValue = $app['db']->fetchAssoc($houndCountSQL, array((int) $hash_id));
    $theHoundCount = $theHoundCountValue['THE_COUNT'];

    #Execute the SQL statement; create an array of rows
    $hasherList = $app['db']->fetchAll($sql,array((int) $hash_id));


    # Obtain the hare count
    # Obtain the hound count
    $hareCountSQL = HARE_COUNT_BY_HASH_KEY;
    $theHareCountValue = $app['db']->fetchAssoc($hareCountSQL, array((int) $hash_id));
    $theHareCount = $theHareCountValue['THE_COUNT'];


    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql, array((int) $hash_id));

    $state = $theHashValue['EVENT_STATE'];
    $county =$theHashValue['COUNTY'];
    $city = $theHashValue['EVENT_CITY'];
    $neighborhood = $theHashValue['NEIGHBORHOOD'];
    $postalCode = $theHashValue['POSTAL_CODE'];

    $showState = true;
    $showCounty = true;
    $showCity = true;
    $showNeighborhood = true;
    $showPostalCode = true;

    if(strlen($state)==0){
      $showState = false;
    }

    if(strlen($county)==0){
      $showCounty = false;
    }

    if(strlen($city)==0){
      $showCity = false;
    }

    if(strlen($neighborhood)==0){
      $showNeighborhood = false;
    }

    if(strlen($postalCode)==0){
      $showPostalCode = false;
    }

    # Establish and set the return value
    $returnValue = $app['twig']->render('hash_details.twig',array(
      'pageTitle' => 'Hash Details',
      'firstHeader' => 'Basic Details',
      'secondHeader' => 'Statistics',
      'hashValue' => $theHashValue,
      'kennel_abbreviation' => $kennel_abbreviation,
      'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
      'showStateCountList' => $showState,
      'showCountyCountList' => $showCounty,
      'showCityCountList' => $showCity,
      'showNeighborhoodCountList' => $showNeighborhood,
      'showPostalCodeCountList' => $showPostalCode,
      'theHoundCount' => $theHoundCount,
      'theHareCount' => $theHareCount
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





  public function omniAnalversariesForEventAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

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

    # Declare the SQL used to retrieve this information
    $sqlForHareAnalversaries = "SELECT
        hashers.HASHER_NAME AS HASHER_NAME,
        (COUNT(*)) AS THE_COUNT,
        MAX(HARINGS.HARINGS_HASH_KY) AS MAX_HASH_KY
    FROM
        ((hashers
        JOIN harings ON ((hashers.HASHER_KY = harings.HARINGS_HASHER_KY)))
        JOIN hashes ON ((harings.HARINGS_HASH_KY = hashes.HASH_KY)))
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
    $analversaryListHounds = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $kennelKy, (int) $hash_id));
    $analversaryListHares = $app['db']->fetchAll($sqlForHareAnalversaries, array((int) $hash_id,(int) $kennelKy, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT *, YEAR(EVENT_DATE) AS THE_YEAR, MONTHNAME(EVENT_DATE) AS THE_MONTH, DAYNAME(EVENT_DATE) AS THE_DAY FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventState = $theHashValue['EVENT_STATE'];
    if(strlen($theHashEventState)==0){
      $theHashEventState = "UNKNOWN";
    }

    $theHashYear = $theHashValue['THE_YEAR'];
    if(strlen($theHashYear)==0){
      $theHashYear = "UNKNOWN";
    }

    $theHashMonth = $theHashValue['THE_MONTH'];
    if(strlen($theHashMonth)==0){
      $theHashMonth = "UNKNOWN";
    }

    $theHashDay = $theHashValue['THE_DAY'];
    if(strlen($theHashDay)==0){
      $theHashDay = "UNKNOWN";
    }

    $theHashEventCity = $theHashValue['EVENT_CITY'];
    if(strlen($theHashEventCity)==0){
      $theHashEventCity = "UNKNOWN";
    }

    $theHashEventNeighborhood = $theHashValue['NEIGHBORHOOD'];
    if(strlen($theHashEventNeighborhood)==0){
      $theHashEventNeighborhood = "UNKNOWN";
    }

    $theHashEventCounty = $theHashValue['COUNTY'];
    if(strlen($theHashEventCounty)==0){
      $theHashEventCounty = "UNKNOWN";
    }

    $theHashEventZip = $theHashValue['POSTAL_CODE'];
    if(strlen($theHashEventZip)==0){
      $theHashEventZip = "UNKNOWN";
    }

    $theHashEventRoute = $theHashValue['ROUTE'];
    if(strlen($theHashEventRoute)==0){
      $theHashEventRoute = "UNKNOWN";
    }

    # Declare the SQL used to retrieve this information
    $sqlHoundAnalversaryTemplate = "SELECT
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
        HASHES.KENNEL_KY = ? AND
        HASHES.XXX = ?
    GROUP BY hashers.HASHER_NAME
    HAVING ((((THE_COUNT % 5) = 0)
        OR ((THE_COUNT % 69) = 0)
        OR ((THE_COUNT % 666) = 0)
        OR (((THE_COUNT - 69) % 100) = 0)))
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    $sqlHoundAnalversaryTemplateDateBased = "SELECT
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
        HASHES.KENNEL_KY = ? AND
        XXX(HASHES.EVENT_DATE) = ?
    GROUP BY hashers.HASHER_NAME
    HAVING ((((THE_COUNT % 5) = 0)
        OR ((THE_COUNT % 69) = 0)
        OR ((THE_COUNT % 666) = 0)
        OR (((THE_COUNT - 69) % 100) = 0)))
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Declare the SQL used to retrieve this information
    $sqlHareAnalversaryTemplate = "SELECT
        hashers.HASHER_NAME AS HASHER_NAME,
        (COUNT(*)) AS THE_COUNT,
        MAX(HARINGS.HARINGS_HASH_KY) AS MAX_HASH_KY
    FROM
        ((hashers
        JOIN harings ON ((hashers.HASHER_KY = harings.HARINGS_HASHER_KY)))
        JOIN hashes ON ((harings.HARINGS_HASH_KY = hashes.HASH_KY)))
    WHERE
        (hashers.DECEASED = 0) AND
        HASHES.HASH_KY <= ? AND
        HASHES.KENNEL_KY = ? AND
        HASHES.XXX = ?
    GROUP BY hashers.HASHER_NAME
    HAVING ((((THE_COUNT % 5) = 0)
        OR ((THE_COUNT % 69) = 0)
        OR ((THE_COUNT % 666) = 0)
        OR (((THE_COUNT - 69) % 100) = 0)))
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    $sqlHareAnalversaryTemplateDateBased = "SELECT
        hashers.HASHER_NAME AS HASHER_NAME,
        (COUNT(*)) AS THE_COUNT,
        MAX(HARINGS.HARINGS_HASH_KY) AS MAX_HASH_KY
    FROM
        ((hashers
        JOIN harings ON ((hashers.HASHER_KY = harings.HARINGS_HASHER_KY)))
        JOIN hashes ON ((harings.HARINGS_HASH_KY = hashes.HASH_KY)))
    WHERE
        (hashers.DECEASED = 0) AND
        HASHES.HASH_KY <= ? AND
        HASHES.KENNEL_KY = ? AND
        XXX(HASHES.EVENT_DATE) = ?
    GROUP BY hashers.HASHER_NAME
    HAVING ((((THE_COUNT % 5) = 0)
        OR ((THE_COUNT % 69) = 0)
        OR ((THE_COUNT % 666) = 0)
        OR (((THE_COUNT - 69) % 100) = 0)))
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Derive the various SQL statements
    $theSqlHoundState = str_replace("XXX","EVENT_STATE",$sqlHoundAnalversaryTemplate);
    $theSqlHoundCity = str_replace("XXX","EVENT_CITY",$sqlHoundAnalversaryTemplate);
    $theSqlHoundNeighborhood = str_replace("XXX","NEIGHBORHOOD",$sqlHoundAnalversaryTemplate);
    $theSqlHoundCounty = str_replace("XXX","COUNTY",$sqlHoundAnalversaryTemplate);
    $theSqlHoundZip = str_replace("XXX","POSTAL_CODE",$sqlHoundAnalversaryTemplate);
    $theSqlHoundRoad = str_replace("XXX","ROUTE",$sqlHoundAnalversaryTemplate);
    $theSqlHoundYear = str_replace("XXX","YEAR",$sqlHoundAnalversaryTemplateDateBased);
    $theSqlHoundMonth = str_replace("XXX","MONTHNAME",$sqlHoundAnalversaryTemplateDateBased);
    $theSqlHoundDayName = str_replace("XXX","DAYNAME",$sqlHoundAnalversaryTemplateDateBased);

    $theSqlHareState = str_replace("XXX","EVENT_STATE",$sqlHareAnalversaryTemplate);
    $theSqlHareCity = str_replace("XXX","EVENT_CITY",$sqlHareAnalversaryTemplate);
    $theSqlHareNeighborhood = str_replace("XXX","NEIGHBORHOOD",$sqlHareAnalversaryTemplate);
    $theSqlHareCounty = str_replace("XXX","COUNTY",$sqlHareAnalversaryTemplate);
    $theSqlHareZip = str_replace("XXX","POSTAL_CODE",$sqlHareAnalversaryTemplate);
    $theSqlHareRoad = str_replace("XXX","ROUTE",$sqlHareAnalversaryTemplate);
    $theSqlHareYear = str_replace("XXX","YEAR",$sqlHareAnalversaryTemplateDateBased);
    $theSqlHareMonth = str_replace("XXX","MONTHNAME",$sqlHareAnalversaryTemplateDateBased);
    $theSqlHareDayName = str_replace("XXX","DAYNAME",$sqlHareAnalversaryTemplateDateBased);

    # Query the datbase a bunch of times
    $theHoundStateList = $app['db']->fetchAll($theSqlHoundState, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventState ,(int) $hash_id));
    $theHoundCityList = $app['db']->fetchAll($theSqlHoundCity, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventCity ,(int) $hash_id));
    $theHoundNeighborhoodList = $app['db']->fetchAll($theSqlHoundNeighborhood, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventNeighborhood ,(int) $hash_id));
    $theHoundCountyList = $app['db']->fetchAll($theSqlHoundCounty, array((int) $hash_id,(int) $kennelKy,(string) $theHashEventCounty , (int) $hash_id));
    $theHoundZipList = $app['db']->fetchAll($theSqlHoundZip, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventZip ,(int) $hash_id));
    $theHoundRoadList = $app['db']->fetchAll($theSqlHoundRoad, array((int) $hash_id,(int) $kennelKy,(string) $theHashEventRoute , (int) $hash_id));
    $theHoundYearList = $app['db']->fetchAll($theSqlHoundYear, array((int) $hash_id,(int) $kennelKy,(string) $theHashYear , (int) $hash_id));
    $theHoundMonthList = $app['db']->fetchAll($theSqlHoundMonth, array((int) $hash_id,(int) $kennelKy,(string) $theHashMonth , (int) $hash_id));
    $theHoundDayNameList = $app['db']->fetchAll($theSqlHoundDayName, array((int) $hash_id,(int) $kennelKy,(string) $theHashDay , (int) $hash_id));

    $theHareStateList = $app['db']->fetchAll($theSqlHareState, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventState ,(int) $hash_id));
    $theHareCityList = $app['db']->fetchAll($theSqlHareCity, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventCity ,(int) $hash_id));
    $theHareNeighborhoodList = $app['db']->fetchAll($theSqlHareNeighborhood, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventNeighborhood ,(int) $hash_id));
    $theHareCountyList = $app['db']->fetchAll($theSqlHareCounty, array((int) $hash_id,(int) $kennelKy,(string) $theHashEventCounty , (int) $hash_id));
    $theHareZipList = $app['db']->fetchAll($theSqlHareZip, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventZip ,(int) $hash_id));
    $theHareRoadList = $app['db']->fetchAll($theSqlHareRoad, array((int) $hash_id,(int) $kennelKy,(string) $theHashEventRoute , (int) $hash_id));
    $theHareYearList = $app['db']->fetchAll($theSqlHareYear, array((int) $hash_id,(int) $kennelKy,(string) $theHashYear , (int) $hash_id));
    $theHareMonthList = $app['db']->fetchAll($theSqlHareMonth, array((int) $hash_id,(int) $kennelKy,(string) $theHashMonth , (int) $hash_id));
    $theHareDayNameList = $app['db']->fetchAll($theSqlHareDayName, array((int) $hash_id,(int) $kennelKy,(string) $theHashDay , (int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageSubtitle = "All Analversaries at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('omni_analversary_list.twig',array(
      'pageTitle' => 'All Analversaries for this Hash',
      'pageSubTitle' => $pageSubtitle,
      'theHoundListOverall' => $analversaryListHounds,
      'theHoundListState' => $theHoundStateList,
      'theHoundListCity' => $theHoundCityList,
      'theHoundListNeighborhood' => $theHoundNeighborhoodList,
      'theHoundListCounty' => $theHoundCountyList,
      'theHoundListZip' => $theHoundZipList,
      'theHoundListRoad' => $theHoundRoadList,
      'theHoundListYear' => $theHoundYearList,
      'theHoundListMonth' => $theHoundMonthList,
      'theHoundListDay' => $theHoundDayNameList,

      'theHareListOverall' => $analversaryListHares,
      'theHareListState' => $theHareStateList,
      'theHareListCity' => $theHareCityList,
      'theHareListNeighborhood' => $theHareNeighborhoodList,
      'theHareListCounty' => $theHareCountyList,
      'theHareListZip' => $theHareZipList,
      'theHareListRoad' => $theHareRoadList,
      'theHareListYear' => $theHareYearList,
      'theHareListMonth' => $theHareMonthList,
      'theHareListDay' => $theHareDayNameList,

      'kennel_abbreviation' => $kennel_abbreviation,
      'theState' => $theHashEventState,
      'theCity' => $theHashEventCity,
      'theNeighborhood' => $theHashEventNeighborhood,
      'theCounty' => $theHashEventCounty,
      'theZip' => $theHashEventZip,
      'theRoad' => $theHashEventRoute,
      'theYear' => $theHashYear,
      'theMonth' => $theHashMonth,
      'theDay' => $theHashDay
    ));

    # Return the return value
    return $returnValue;
  }






  public function hasherCountsForEventAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

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
    HAVING (
          (THE_COUNT % 1) = 0
      )
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
    $pageSubtitle = "Hasher Counts at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => 'Hasher Counts',
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;
  }


  public function hasherCountsForEventCountyAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventCounty = $theHashValue['COUNTY'];
    if(strlen($theHashEventCounty)==0){
      $theHashEventCounty = "UNKNOWN";
    }

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
        HASHES.KENNEL_KY = ? AND
        HASHES.COUNTY = ?
    GROUP BY hashers.HASHER_NAME
    HAVING (
          (THE_COUNT % 1) = 0
      )
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventCounty, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageTitle = "Hasher Counts for $theHashEventCounty";
    $pageSubtitle = "Hasher Counts in $theHashEventCounty at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => $pageTitle,
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;
  }

  public function hasherCountsForEventPostalCodeAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventPostalCode = $theHashValue['POSTAL_CODE'];
    if(strlen($theHashEventPostalCode)==0){
      $theHashEventPostalCode = "UNKNOWN";
    }

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
        HASHES.KENNEL_KY = ? AND
        HASHES.POSTAL_CODE = ?
    GROUP BY hashers.HASHER_NAME
    HAVING (
          (THE_COUNT % 1) = 0
      )
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventPostalCode, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageTitle = "Hasher Counts for $theHashEventPostalCode postal code";
    $pageSubtitle = "Hasher Counts in $theHashEventPostalCode postal code at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => $pageTitle,
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;
  }


  public function hasherCountsForEventStateAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventState = $theHashValue['EVENT_STATE'];
    if(strlen($theHashEventState)==0){
      $theHashEventState = "UNKNOWN";
    }

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
        HASHES.KENNEL_KY = ? AND
        HASHES.EVENT_STATE = ?
    GROUP BY hashers.HASHER_NAME
    HAVING (
          (THE_COUNT % 1) = 0
      )
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventState, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    #$sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    #$theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageTitle = "Hasher Counts for $theHashEventState state";
    $pageSubtitle = "Hasher Counts in $theHashEventState state at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => $pageTitle,
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;
  }


  public function hasherCountsForEventNeighborhoodAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventNeighborhood = $theHashValue['NEIGHBORHOOD'];
    if(strlen($theHashEventNeighborhood)==0){
      $theHashEventNeighborhood = "UNKNOWN";
    }

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
        HASHES.KENNEL_KY = ? AND
        HASHES.NEIGHBORHOOD = ?
    GROUP BY hashers.HASHER_NAME
    HAVING (
          (THE_COUNT % 1) = 0
      )
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventNeighborhood, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    #$sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    #$theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageTitle = "Hasher Counts for $theHashEventNeighborhood neighborhood";
    $pageSubtitle = "Hasher Counts in $theHashEventNeighborhood neighborhood at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => $pageTitle,
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;
  }

  public function hasherCountsForEventCityAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

    #Obtain the kennel key
    $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

    # Declare the SQL used to retrieve this information
    $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Obtain information for this particular hash
    $theHashEventCity = $theHashValue['EVENT_CITY'];

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
        HASHES.KENNEL_KY = ? AND
        HASHES.EVENT_CITY = ?
    GROUP BY hashers.HASHER_NAME
    HAVING (
          (THE_COUNT % 1) = 0
      )
        AND MAX_HASH_KY = ?
    ORDER BY THE_COUNT DESC";

    # Make a database call to obtain the hasher information
    $analversaryList = $app['db']->fetchAll($sql, array((int) $hash_id,(int) $kennelKy, (string) $theHashEventCity, (int) $hash_id));

    # Declare the SQL used to retrieve this information
    #$sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    #$theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

    # Establish and set the return value
    $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
    $hashLocation = $theHashValue['EVENT_LOCATION'];
    $pageTitle = "Hasher Counts for $theHashEventCity city";
    $pageSubtitle = "Hasher Counts in $theHashEventCity city at the $hashNumber ($hashLocation) Hash";

    # Establish the return value
    $returnValue = $app['twig']->render('analversary_list.twig',array(
      'pageTitle' => $pageTitle,
      'pageSubTitle' => $pageSubtitle,
      'theList' => $analversaryList,
      'kennel_abbreviation' => $kennel_abbreviation
    ));

    # Return the return value
    return $returnValue;
  }


    public function backSlidersForEventAction(Request $request, Application $app, int $hash_id, string $kennel_abbreviation){

      #Obtain the kennel key
      $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

      # Declare the SQL used to retrieve this information
      $sql = BACKSLIDERS_FOR_SPECIFIC_HASH_EVENT;

      # Make a database call to obtain the hasher information
      $backSliderList = $app['db']->fetchAll($sql, array((int) $kennelKy,(int) $hash_id,(int) $kennelKy, (int) $hash_id));

      # Declare the SQL used to retrieve this information
      $sql_for_hash_event = "SELECT * FROM HASHES WHERE HASH_KY = ?";

      # Make a database call to obtain the hasher information
      $theHashValue = $app['db']->fetchAssoc($sql_for_hash_event, array((int) $hash_id));

      # Establish and set the return value
      $hashNumber = $theHashValue['KENNEL_EVENT_NUMBER'];
      $hashLocation = $theHashValue['EVENT_LOCATION'];
      $pageSubtitle = "Back Sliders at the $hashNumber ($hashLocation) Hash";

      # Establish the return value
      $returnValue = $app['twig']->render('backslider_list.twig',array(
        'pageTitle' => 'Back Sliders',
        'pageSubTitle' => $pageSubtitle,
        'theList' => $backSliderList,
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

public function trueHaringCountsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = NON_HYPER_HARING_COUNTS;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'True Haring Counts',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'Hares, and the number of (non hyper-hash) hashes they have hared. More is better.',
    'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  #Return the return value
  return $returnValue;

}

public function hyperHaringCountsAction(Request $request, Application $app, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = HYPER_HARING_COUNTS;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Execute the SQL statement; create an array of rows
  $hasherList = $app['db']->fetchAll($sql, array((int) $kennelKy));

  # Establish and set the return value
  $returnValue = $app['twig']->render('name_number_list.twig',array(
    'pageTitle' => 'Hyper Haring Counts',
    'columnOneName' => 'Hare Name',
    'columnTwoName' => 'Hash Count',
    'tableCaption' => 'Hares, and the number of (hyper-hash) hashes they have hared. More is better.',
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

  # Establish and set the return value
  $returnValue = $app['twig']->render('basic_stats.twig',array(
    'pageTitle' => 'Basic Information and Statistics',
    'kennel_abbreviation' => $kennel_abbreviation,
    'first_hash' => $firstHashValue,
    'latest_hash' => $mostRecentHashValue,
    'theYearValues' => $yearValues
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
    "Hashes where someone coveted their neighbor's wife",
    "Hashes where hashers were mocked for their Kentucky heritage",
    "Hashes where hashers were mocked for their Michigan heritage",
    "Hashes where people did it on trail",
    "Hashes where a hasher was arrested",
    "Hashes where the police showed up",
    "Hashes where the streams were crossed",
    "Hashes where no harriettes showed up",
    "Hashes that could have used better beer",
    "Hashes that could have used a better trail",
    "Hashes that could have used better hares",
    "Hashes that caused somebody to move away",
    "Hashes where someone shat on trail",
    "Hashes where someone shat themselves",
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
  $returnValue = $app['twig']->render('switch_kennel_screen.twig',array(
    'firstEvent' => $firstHashValue,
    'mostRecentEvent' => $mostRecentHashValue,
    'theYearValues' => $yearValues,
    'pageTitle' => 'Switch Kennel',
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


public function getProjectedHasherAnalversariesAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  # Declare the SQL used to retrieve this information
  $sql_for_hasher_lookup = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

  # Make a database call to obtain the hasher information
  $hasher = $app['db']->fetchAssoc($sql_for_hasher_lookup, array((int) $hasher_id));

  #Define the sql that performs the filtering
  $sql = "SELECT
      HASHER_NAME,
      HASH_COUNT,
      LATEST_HASH.EVENT_DATE AS LATEST_EVENT_DATE,
      HASHER_ABBREVIATION,
      LAST_NAME,
      FIRST_NAME,
      EMAIL,
      HOME_KENNEL,
      HOME_KENNEL_KY,
      DECEASED,
      FIRST_HASH_KEY,
  	  FIRST_HASH.KENNEL_EVENT_NUMBER AS FIRST_KENNEL_EVENT_NUMBER,
      FIRST_HASH.EVENT_DATE AS FIRST_EVENT_DATE,
      LATEST_HASH_KEY,
      LATEST_HASH.KENNEL_EVENT_NUMBER AS LATEST_KENNEL_EVENT_NUMBER,
      HASHER_KY,
      (DATEDIFF(CURDATE(),FIRST_HASH.EVENT_DATE)) AS DAYS_SINCE_FIRST_HASH,
      ((DATEDIFF(CURDATE(),FIRST_HASH.EVENT_DATE)) / HASH_COUNT) AS DAYS_BETWEEN_HASHES
  FROM
  	(
  	SELECT
  		HASHERS.*,
  		HASHERS.HASHER_KY AS OUTER_HASHER_KY,
  		(
  			SELECT COUNT(*)
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
          AND HASHES.EVENT_DATE >= (CURDATE() - INTERVAL ? DAY)) AS HASH_COUNT,
  		(
  			SELECT HASHES.HASH_KY
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
          AND HASHES.EVENT_DATE >= (CURDATE() - INTERVAL ? DAY)
              ORDER BY HASHES.EVENT_DATE ASC LIMIT 1) AS FIRST_HASH_KEY,
  		(
  			SELECT HASHES.HASH_KY
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
          AND HASHES.EVENT_DATE >= (CURDATE() - INTERVAL ? DAY)
              ORDER BY HASHES.EVENT_DATE DESC LIMIT 1) AS LATEST_HASH_KEY
  	FROM
  		HASHERS
  )
  MAIN_TABLE
  JOIN HASHES LATEST_HASH ON LATEST_HASH.HASH_KY = LATEST_HASH_KEY
  JOIN HASHES FIRST_HASH ON FIRST_HASH.HASH_KY = FIRST_HASH_KEY
  WHERE HASHER_KY = ? ";

  # Make a database call to obtain the hasher information
  $numberOfDaysInDateRange = 360000;
  $hasherStatsObject = $app['db']->fetchAssoc($sql, array(
    (int) $kennelKy,
    (int) $numberOfDaysInDateRange,
    (int) $kennelKy,
    (int) $numberOfDaysInDateRange,
    (int) $kennelKy,
    (int) $numberOfDaysInDateRange,
    (int) $hasher_id));
  $hasherStatsHashCount = $hasherStatsObject['HASH_COUNT'];
  $hasherStatsDaysSinceFirstHash = $hasherStatsObject['DAYS_SINCE_FIRST_HASH'];
  $hasherStatsDaysPerHash = $hasherStatsObject['DAYS_BETWEEN_HASHES'];

  $numberOfDaysInRecentDateRange = 365;
  $hasherRecentStatsObject = $app['db']->fetchAssoc($sql, array(
    (int) $kennelKy,
    (int) $numberOfDaysInRecentDateRange,
    (int) $kennelKy,
    (int) $numberOfDaysInRecentDateRange,
    (int) $kennelKy,
    (int) $numberOfDaysInRecentDateRange,
    (int) $hasher_id));
  if(empty($hasherRecentStatsObject)){
    $recentEventCount = 0;
    $recentDaysPerHash =  "Infinity";
  }else{
    $recentEventCount = $hasherRecentStatsObject['HASH_COUNT'];
    $recentDaysPerHash =  $hasherRecentStatsObject['DAYS_BETWEEN_HASHES'];
  }





  #Project out the next bunch of hash analversaries

  # Add a count into their list of hashes
  $destinationArray = array();

  #Loop through 500 events
  for ($x = 1; $x <= 750; $x++) {
    $incrementedHashCount = $hasherStatsHashCount + $x;
    if(
      ($incrementedHashCount % 25 == 0) ||
      ($incrementedHashCount % 69 == 0) ||
      ($incrementedHashCount % 666 == 0) ||
      (($incrementedHashCount - 69) % 100 == 0)
      ){

        $daysToAdd = round($hasherStatsDaysPerHash * $x);
        $nowDate = date("Y/m/d");
        #$app['monolog']->addDebug("XX:nowDate $nowDate");
        #$incrementedDate = strtotime($nowDate."+ 2 days");

        $incrementedDateOverall = date('Y-m-d',strtotime($nowDate) + (24*3600*$daysToAdd));

        if(empty($hasherRecentStatsObject)){
          $daysToAddRecent = "infinity";
          $incrementedDateRecent = null;
        }else{
          $daysToAddRecent = round($recentDaysPerHash * $x);
          $incrementedDateRecent = date('Y-m-d',strtotime($nowDate) + (24*3600*$daysToAddRecent));
        }

        #$app['monolog']->addDebug("XD:incrementedHashCount $incrementedHashCount");
        #$app['monolog']->addDebug("XE:daysToAdd $daysToAdd");
        #$app['monolog']->addDebug("XF:date $date");

        $obj = [
          'incrementedHashCount' => $incrementedHashCount,
          'incrementedDateOverall' => $incrementedDateOverall,
          'daysAddedOverall' => $daysToAdd,
          'incrementedDateRecent' => $incrementedDateRecent,
          'daysAddedRecent' => $daysToAddRecent
        ];


      array_push($destinationArray,$obj);
    }
  }

  # Establish and set the return value
  $hasherName = $hasher['HASHER_NAME'];
  $pageTitle = "Projected Hashing Analversaries";
  $returnValue = $app['twig']->render('projected_hasher_analversary_list.twig',array(
    'theList' => $destinationArray,
    'pageTitle' => $pageTitle,
    'pageSubTitle' => $hasherName,
    'tableCaption' => 'The projected analversaries are based on how many hashes this hasher has done, and how frequently this hasher has hashed them. It applies their days between hashes average and projects out when they might hit certain analversaries.',
    'kennel_abbreviation' => $kennel_abbreviation,
    'participant_column_header' => 'Hasher',
    'number_column_header' => 'Number of non-hyper harings',
    'percentage_column_header' => 'Percentage of non-hypers hared',
    'overall_boolean' => 'FALSE',
    'firstHashKey' => $hasherStatsObject['FIRST_HASH_KEY'],
    'firstKennelEventNumber' => $hasherStatsObject['FIRST_KENNEL_EVENT_NUMBER'],
    'firstEventDate' => $hasherStatsObject['FIRST_EVENT_DATE'],
    'overallRunRate' => $hasherStatsDaysPerHash,
    'recentDateRangeInDays' => $numberOfDaysInRecentDateRange,
    'recentRunRate' => $recentDaysPerHash,
    'overallHashCount' => $hasherStatsHashCount,
    'recentHashCount' => $recentEventCount
  ));

  #Return the return value
  return $returnValue;



}



#Define the action
public function jumboCountsTablePreActionJson(Request $request, Application $app, string $kennel_abbreviation){

  #Establish the subTitle
  $minimumHashCount = JUMBO_COUNTS_MINIMUM_HASH_COUNT;
  $subTitle = "Minimum of $minimumHashCount hashes";

  # Establish and set the return value
  $returnValue = $app['twig']->render('jumbo_counts_list_json.twig',array(
    'pageTitle' => 'The Jumbo List of Counts (Experimental Page)',
    'pageSubTitle' => $subTitle,
    #'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation,
    'pageCaption' => "",
    'tableCaption' => ""
  ));

  #Return the return value
  return $returnValue;

}


public function jumboCountsTablePostActionJson(Request $request, Application $app, string $kennel_abbreviation){

  #$app['monolog']->addDebug("Entering the function jumboStatsTablePostActionJson------------------------");

  #Establish he minimum hash count
  $minimumHashCount = JUMBO_COUNTS_MINIMUM_HASH_COUNT;

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Obtain the post parameters
  #$inputDraw = $_POST['draw'] ;
  $inputStart = $_POST['start'] ;
  $inputLength = $_POST['length'] ;
  $inputColumns = $_POST['columns'];
  $inputSearch = $_POST['search'];
  $inputSearchValue = $inputSearch['value'];

  #-------------- Begin: Validate the post parameters ------------------------
  #Validate input start
  if(!is_numeric($inputStart)){
    #$app['monolog']->addDebug("input start is not numeric: $inputStart");
    $inputStart = 0;
  }

  #Validate input length
  if(!is_numeric($inputLength)){
    #$app['monolog']->addDebug("input length is not numeric");
    $inputStart = "0";
    $inputLength = "50";
  } else if($inputLength == "-1"){
    #$app['monolog']->addDebug("input length is negative one (all rows selected)");
    $inputStart = "0";
    $inputLength = "1000000000";
  }

  #Validate input search
  #We are using database parameterized statements, so we are good already...

  #---------------- End: Validate the post parameters ------------------------

  #-------------- Begin: Modify the input parameters  ------------------------
  #Modify the search string
  $inputSearchValueModified = "%$inputSearchValue%";

  #Obtain the column/order information
  $inputOrderRaw = isset($_POST['order']) ? $_POST['order'] : null;
  $inputOrderColumnExtracted = "3";
  $inputOrderColumnIncremented = "3";
  $inputOrderDirectionExtracted = "desc";
  if(!is_null($inputOrderRaw)){
    #$app['monolog']->addDebug("inside inputOrderRaw not null");
    $inputOrderColumnExtracted = $inputOrderRaw[0]['column'];
    $inputOrderColumnIncremented = $inputOrderColumnExtracted + 1;
    $inputOrderDirectionExtracted = $inputOrderRaw[0]['dir'];
  }else{
    #$app['monolog']->addDebug("inside inputOrderRaw is null");
  }

  #-------------- End: Modify the input parameters  --------------------------


  #-------------- Begin: Define the SQL used here   --------------------------

  #Define the sql that performs the filtering
  $sql = "SELECT
      HASHER_NAME,
      HASH_COUNT,
      HARE_COUNT,
      NON_HYPER_HARE_COUNT,
      HYPER_HARE_COUNT,
      LATEST_HASH.EVENT_DATE AS LATEST_EVENT_DATE,
      HASHER_ABBREVIATION,
      LAST_NAME,
      FIRST_NAME,
      EMAIL,
      HOME_KENNEL,
      HOME_KENNEL_KY,
      DECEASED,
      (HARE_COUNT/HASH_COUNT) AS HARING_TO_HASHING_PERCENTAGE,
      (NON_HYPER_HARE_COUNT/HASH_COUNT) AS NON_HYPER_HARING_TO_HASHING_PERCENTAGE,
      (HYPER_HARE_COUNT/HARE_COUNT) AS HYPER_TO_OVERALL_HARING_PERCENTAGE,
      (NON_HYPER_HARE_COUNT/HARE_COUNT) AS NON_HYPER_TO_OVERALL_HARING_PERCENTAGE,
      FIRST_HASH_KEY,
  	  FIRST_HASH.KENNEL_EVENT_NUMBER AS FIRST_KENNEL_EVENT_NUMBER,
      FIRST_HASH.EVENT_DATE AS FIRST_EVENT_DATE,
      LATEST_HASH_KEY,
      LATEST_HASH.KENNEL_EVENT_NUMBER AS LATEST_KENNEL_EVENT_NUMBER,
      HASHER_KY
  FROM
  	(
  	SELECT
  		HASHERS.*,
  		HASHERS.HASHER_KY AS OUTER_HASHER_KY,
  		(
  			SELECT COUNT(*)
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HASH_COUNT,
  		(
  			SELECT COUNT(*)
  			FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  			WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HARE_COUNT,
  		(
  			SELECT COUNT(*)
  			FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  			WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
  			AND HASHES.KENNEL_KY = ?
  			AND HASHES.IS_HYPER = 0) AS NON_HYPER_HARE_COUNT,
  		(
  			SELECT COUNT(*)
  			FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  			WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
  			AND HASHES.KENNEL_KY = ?
  			AND HASHES.IS_HYPER = 1) AS HYPER_HARE_COUNT,
  		(
  			SELECT HASHES.HASH_KY
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
              ORDER BY HASHES.EVENT_DATE ASC LIMIT 1) AS FIRST_HASH_KEY,
  		(
  			SELECT HASHES.HASH_KY
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
              ORDER BY HASHES.EVENT_DATE DESC LIMIT 1) AS LATEST_HASH_KEY
  	FROM
  		HASHERS
  )
  MAIN_TABLE
  JOIN HASHES LATEST_HASH ON LATEST_HASH.HASH_KY = LATEST_HASH_KEY
  JOIN HASHES FIRST_HASH ON FIRST_HASH.HASH_KY = FIRST_HASH_KEY
  WHERE HASH_COUNT > $minimumHashCount AND (HASHER_NAME LIKE ? )
  ORDER BY $inputOrderColumnIncremented $inputOrderDirectionExtracted
  LIMIT $inputStart,$inputLength";
  #$app['monolog']->addDebug("sql: $sql");

  #Define the SQL that gets the count for the filtered results
  $sqlFilteredCount = "SELECT COUNT(*) AS THE_COUNT
  FROM
    (
    SELECT
      HASHERS.*,
      HASHERS.HASHER_KY AS OUTER_HASHER_KY,
      (
        SELECT COUNT(*)
        FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
        WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HASH_COUNT
    FROM
      HASHERS
  )
  MAIN_TABLE
  WHERE HASH_COUNT > $minimumHashCount AND (
        HASHER_NAME LIKE ? )";

  #Define the sql that gets the overall counts
  $sqlUnfilteredCount = "SELECT COUNT(*) AS THE_COUNT
  FROM
      (
      SELECT
        HASHERS.*,
        HASHERS.HASHER_KY AS OUTER_HASHER_KY,
        (
          SELECT COUNT(*)
          FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
          WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HASH_COUNT,
        (
          SELECT COUNT(*)
          FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HARE_COUNT,
        (
          SELECT COUNT(*)
          FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
          AND HASHES.KENNEL_KY = ?
          AND HASHES.IS_HYPER = 0) AS NON_HYPER_HARE_COUNT,
        (
          SELECT COUNT(*)
          FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
          AND HASHES.KENNEL_KY = ?
          AND HASHES.IS_HYPER = 1) AS HYPER_HARE_COUNT,
        (
          SELECT HASHES.HASH_KY
          FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
          WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
                ORDER BY HASHES.EVENT_DATE ASC LIMIT 1) AS FIRST_HASH_KEY,
        (
          SELECT HASHES.HASH_KY
          FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
          WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
                ORDER BY HASHES.EVENT_DATE DESC LIMIT 1) AS LATEST_HASH_KEY
      FROM
        HASHERS
    )
    MAIN_TABLE
    JOIN HASHES LATEST_HASH ON LATEST_HASH.HASH_KY = LATEST_HASH_KEY
    JOIN HASHES FIRST_HASH ON FIRST_HASH.HASH_KY = FIRST_HASH_KEY
    WHERE HASH_COUNT > $minimumHashCount";

  #-------------- End: Define the SQL used here   ----------------------------

  #-------------- Begin: Query the database   --------------------------------
  #$app['monolog']->addDebug("Point A");

  #Perform the filtered search
  $theResults = $app['db']->fetchAll($sql,array(
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (string) $inputSearchValueModified));
  #$app['monolog']->addDebug("Point B");

  #Perform the untiltered count
  $theUnfilteredCount = ($app['db']->fetchAssoc($sqlUnfilteredCount,array(
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
  )))['THE_COUNT'];
  #$app['monolog']->addDebug("Point C");

  #Perform the filtered count
  $theFilteredCount = ($app['db']->fetchAssoc($sqlFilteredCount,array(
    (int) $kennelKy,
    (string) $inputSearchValueModified)))['THE_COUNT'];
  #$app['monolog']->addDebug("Point D");
  #-------------- End: Query the database   --------------------------------

  #$app['monolog']->addDebug("Point theUnfilteredCount $theUnfilteredCount");
  #$app['monolog']->addDebug("Point theFilteredCount $theFilteredCount");

  #Establish the output
  $output = array(
    "sEcho" => "foo",
    "iTotalRecords" => $theUnfilteredCount,
    "iTotalDisplayRecords" => $theFilteredCount,
    "aaData" => $theResults
  );

  #Set the return value
  $returnValue = $app->json($output,200);

  #Return the return value
  return $returnValue;
}










#Define the action
public function jumboPercentagesTablePreActionJson(Request $request, Application $app, string $kennel_abbreviation){

  #Establish the sub title
  $minimumHashCount = JUMBO_PERCENTAGES_MINIMUM_HASH_COUNT;
  $subTitle = "Minimum of $minimumHashCount hashes";

  # Establish and set the return value
  $returnValue = $app['twig']->render('jumbo_percentages_list_json.twig',array(
    'pageTitle' => 'The Jumbo List of Percentages (Experimental Page)',
    'pageSubTitle' => $subTitle,
    #'theList' => $hasherList,
    'kennel_abbreviation' => $kennel_abbreviation,
    'pageCaption' => "",
    'tableCaption' => ""
  ));

  #Return the return value
  return $returnValue;

}


public function jumboPercentagesTablePostActionJson(Request $request, Application $app, string $kennel_abbreviation){

  #$app['monolog']->addDebug("Entering the function jumboPercentagesTablePostActionJson------------------------");

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Define the minimum hash count
  $minimumHashCount = JUMBO_PERCENTAGES_MINIMUM_HASH_COUNT;

  #Obtain the post parameters
  #$inputDraw = $_POST['draw'] ;
  $inputStart = $_POST['start'] ;
  $inputLength = $_POST['length'] ;
  $inputColumns = $_POST['columns'];
  $inputSearch = $_POST['search'];
  $inputSearchValue = $inputSearch['value'];

  #-------------- Begin: Validate the post parameters ------------------------
  #Validate input start
  if(!is_numeric($inputStart)){
    #$app['monolog']->addDebug("input start is not numeric: $inputStart");
    $inputStart = 0;
  }

  #Validate input length
  if(!is_numeric($inputLength)){
    #$app['monolog']->addDebug("input length is not numeric");
    $inputStart = "0";
    $inputLength = "50";
  } else if($inputLength == "-1"){
    #$app['monolog']->addDebug("input length is negative one (all rows selected)");
    $inputStart = "0";
    $inputLength = "1000000000";
  }

  #Validate input search
  #We are using database parameterized statements, so we are good already...

  #---------------- End: Validate the post parameters ------------------------

  #-------------- Begin: Modify the input parameters  ------------------------
  #Modify the search string
  $inputSearchValueModified = "%$inputSearchValue%";

  #Obtain the column/order information
  $inputOrderRaw = isset($_POST['order']) ? $_POST['order'] : null;
  $inputOrderColumnExtracted = "3";
  $inputOrderColumnIncremented = "3";
  $inputOrderDirectionExtracted = "desc";
  if(!is_null($inputOrderRaw)){
    #$app['monolog']->addDebug("inside inputOrderRaw not null");
    $inputOrderColumnExtracted = $inputOrderRaw[0]['column'];
    $inputOrderColumnIncremented = $inputOrderColumnExtracted + 1;
    $inputOrderDirectionExtracted = $inputOrderRaw[0]['dir'];
  }else{
    #$app['monolog']->addDebug("inside inputOrderRaw is null");
  }

  #-------------- End: Modify the input parameters  --------------------------


  #-------------- Begin: Define the SQL used here   --------------------------

  #Define the sql that performs the filtering
  $sql = "SELECT
      HASHER_NAME,
      (HARE_COUNT/HASH_COUNT) AS HARING_TO_HASHING_PERCENTAGE,
      (NON_HYPER_HARE_COUNT/HASH_COUNT) AS NON_HYPER_HARING_TO_HASHING_PERCENTAGE,
      (HYPER_HARE_COUNT/HARE_COUNT) AS HYPER_TO_OVERALL_HARING_PERCENTAGE,
      (NON_HYPER_HARE_COUNT/HARE_COUNT) AS NON_HYPER_TO_OVERALL_HARING_PERCENTAGE,
      HASH_COUNT,
      HARE_COUNT,
      HYPER_HARE_COUNT,
      NON_HYPER_HARE_COUNT,

      LATEST_HASH.EVENT_DATE AS LATEST_EVENT_DATE,
      HASHER_ABBREVIATION,
      LAST_NAME,
      FIRST_NAME,
      EMAIL,
      HOME_KENNEL,
      HOME_KENNEL_KY,
      DECEASED,
      FIRST_HASH_KEY,
  	  FIRST_HASH.KENNEL_EVENT_NUMBER AS FIRST_KENNEL_EVENT_NUMBER,
      FIRST_HASH.EVENT_DATE AS FIRST_EVENT_DATE,
      LATEST_HASH_KEY,
      LATEST_HASH.KENNEL_EVENT_NUMBER AS LATEST_KENNEL_EVENT_NUMBER,
      HASHER_KY
  FROM
  	(
  	SELECT
  		HASHERS.*,
  		HASHERS.HASHER_KY AS OUTER_HASHER_KY,
  		(
  			SELECT COUNT(*)
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HASH_COUNT,
  		(
  			SELECT COUNT(*)
  			FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  			WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HARE_COUNT,
  		(
  			SELECT COUNT(*)
  			FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  			WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
  			AND HASHES.KENNEL_KY = ?
  			AND HASHES.IS_HYPER = 0) AS NON_HYPER_HARE_COUNT,
  		(
  			SELECT COUNT(*)
  			FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  			WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
  			AND HASHES.KENNEL_KY = ?
  			AND HASHES.IS_HYPER = 1) AS HYPER_HARE_COUNT,
  		(
  			SELECT HASHES.HASH_KY
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
              ORDER BY HASHES.EVENT_DATE ASC LIMIT 1) AS FIRST_HASH_KEY,
  		(
  			SELECT HASHES.HASH_KY
  			FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
  			WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
              ORDER BY HASHES.EVENT_DATE DESC LIMIT 1) AS LATEST_HASH_KEY
  	FROM
  		HASHERS
  )
  MAIN_TABLE
  JOIN HASHES LATEST_HASH ON LATEST_HASH.HASH_KY = LATEST_HASH_KEY
  JOIN HASHES FIRST_HASH ON FIRST_HASH.HASH_KY = FIRST_HASH_KEY
  WHERE HASH_COUNT > $minimumHashCount AND (HASHER_NAME LIKE ? )
  ORDER BY $inputOrderColumnIncremented $inputOrderDirectionExtracted
  LIMIT $inputStart,$inputLength";
  #$app['monolog']->addDebug("sql: $sql");

  #Define the SQL that gets the count for the filtered results
  $sqlFilteredCount = "SELECT COUNT(*) AS THE_COUNT
  FROM
    (
    SELECT
      HASHERS.*,
      HASHERS.HASHER_KY AS OUTER_HASHER_KY,
      (
        SELECT COUNT(*)
        FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
        WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HASH_COUNT
    FROM
      HASHERS
  )
  MAIN_TABLE
  WHERE HASH_COUNT > $minimumHashCount AND (
        HASHER_NAME LIKE ? )";

  #Define the sql that gets the overall counts
  $sqlUnfilteredCount = "SELECT COUNT(*) AS THE_COUNT
  FROM
      (
      SELECT
        HASHERS.*,
        HASHERS.HASHER_KY AS OUTER_HASHER_KY,
        (
          SELECT COUNT(*)
          FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
          WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HASH_COUNT,
        (
          SELECT COUNT(*)
          FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?) AS HARE_COUNT,
        (
          SELECT COUNT(*)
          FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
          AND HASHES.KENNEL_KY = ?
          AND HASHES.IS_HYPER = 0) AS NON_HYPER_HARE_COUNT,
        (
          SELECT COUNT(*)
          FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
          WHERE HARINGS_HASHER_KY = OUTER_HASHER_KY
          AND HASHES.KENNEL_KY = ?
          AND HASHES.IS_HYPER = 1) AS HYPER_HARE_COUNT,
        (
          SELECT HASHES.HASH_KY
          FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
          WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
                ORDER BY HASHES.EVENT_DATE ASC LIMIT 1) AS FIRST_HASH_KEY,
        (
          SELECT HASHES.HASH_KY
          FROM HASHINGS JOIN HASHES ON HASHINGS.HASH_KY = HASHES.HASH_KY
          WHERE HASHINGS.HASHER_KY = OUTER_HASHER_KY AND HASHES.KENNEL_KY = ?
                ORDER BY HASHES.EVENT_DATE DESC LIMIT 1) AS LATEST_HASH_KEY
      FROM
        HASHERS
    )
    MAIN_TABLE
    JOIN HASHES LATEST_HASH ON LATEST_HASH.HASH_KY = LATEST_HASH_KEY
    JOIN HASHES FIRST_HASH ON FIRST_HASH.HASH_KY = FIRST_HASH_KEY
    WHERE HASH_COUNT > $minimumHashCount";

  #-------------- End: Define the SQL used here   ----------------------------

  #-------------- Begin: Query the database   --------------------------------
  #$app['monolog']->addDebug("Point A");

  #Perform the filtered search
  $theResults = $app['db']->fetchAll($sql,array(
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (string) $inputSearchValueModified));
  #$app['monolog']->addDebug("Point B");

  #Perform the untiltered count
  $theUnfilteredCount = ($app['db']->fetchAssoc($sqlUnfilteredCount,array(
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
    (int) $kennelKy,
  )))['THE_COUNT'];
  #$app['monolog']->addDebug("Point C");

  #Perform the filtered count
  $theFilteredCount = ($app['db']->fetchAssoc($sqlFilteredCount,array(
    (int) $kennelKy,
    (string) $inputSearchValueModified)))['THE_COUNT'];
  #$app['monolog']->addDebug("Point D");
  #-------------- End: Query the database   --------------------------------

  #$app['monolog']->addDebug("Point theUnfilteredCount $theUnfilteredCount");
  #$app['monolog']->addDebug("Point theFilteredCount $theFilteredCount");

  #Establish the output
  $output = array(
    "sEcho" => "foo",
    "iTotalRecords" => $theUnfilteredCount,
    "iTotalDisplayRecords" => $theFilteredCount,
    "aaData" => $theResults
  );

  #Set the return value
  $returnValue = $app->json($output,200);

  #Return the return value
  return $returnValue;
}

private function getStandardHareChartsAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

  # Declare the SQL used to retrieve this information
  $sql = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  # Make a database call to obtain the hasher information
  $hasher = $app['db']->fetchAssoc($sql, array((int) $hasher_id));

  # Obtain the number of harings
  $overallHareCountValue = $app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id, (int) $kennelKy,  (int) 0, (int) 1));

  # Obtain the number of hyper harings
  $hyperHareCountValue = $app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id, (int) $kennelKy,  (int) 1, (int) 1));

  # Obtain the number of true harings
  $trueHareCountValue = $app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id, (int) $kennelKy,  (int) 0, (int) 0));


  #Obtain the harings by year
  $sqlHaringsByYear = "SELECT
      YEAR(EVENT_DATE) AS THE_VALUE,
      SUM(CASE WHEN HASHES.IS_HYPER IN (0)  THEN 1 ELSE 0 END) NON_HYPER_COUNT,
      SUM(CASE WHEN HASHES.IS_HYPER IN (1)  THEN 1 ELSE 0 END) HYPER_COUNT,
      COUNT(*) AS TOTAL_HARING_COUNT
  FROM
      HARINGS
      JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  WHERE
      HARINGS.HARINGS_HASHER_KY = ? AND
      HASHES.KENNEL_KY = ?
  GROUP BY YEAR(EVENT_DATE)
  ORDER BY YEAR(EVENT_DATE)";
  $haringsByYearList = $app['db']->fetchAll($sqlHaringsByYear, array((int) $hasher_id,(int) $kennelKy));

  # Obtain the hashes by month (name)
  $sqlHaringsByMonth = "SELECT
      THE_VALUE,
        NON_HYPER_COUNT ,
        HYPER_COUNT,
        TOTAL_HARING_COUNT,
      CASE THE_VALUE
        WHEN '1' THEN 'January'
        WHEN '2' THEN 'February'
        WHEN '3' THEN 'March'
        WHEN '4' THEN 'April'
        WHEN '5' THEN 'May'
        WHEN '6' THEN 'June'
        WHEN '7' THEN 'July'
        WHEN '8' THEN 'August'
        WHEN '9' THEN 'September'
        WHEN '10' THEN 'October'
        WHEN '11' THEN 'November'
        WHEN '12' THEN 'December'
        END AS MONTH_NAME
    FROM (
      SELECT
          MONTH(EVENT_DATE) AS THE_VALUE,
          SUM(CASE WHEN HASHES.IS_HYPER IN (0)  THEN 1 ELSE 0 END) NON_HYPER_COUNT,
          SUM(CASE WHEN HASHES.IS_HYPER IN (1)  THEN 1 ELSE 0 END) HYPER_COUNT,
          COUNT(*) AS TOTAL_HARING_COUNT
        FROM
          HARINGS
          JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
        WHERE
          HARINGS.HARINGS_HASHER_KY = ? AND
          HASHES.KENNEL_KY = ?
        GROUP BY MONTH(EVENT_DATE)
        ORDER BY MONTH(EVENT_DATE)
    ) TEMPTABLE";
  $theHaringsByMonthNameList = $app['db']->fetchAll($sqlHaringsByMonth, array((int) $hasher_id, (int) $kennelKy));

  # Obtain the hashes by quarter
      $sqlHaringsByQuarter = "SELECT
        QUARTER(EVENT_DATE) AS THE_VALUE,
        SUM(CASE WHEN HASHES.IS_HYPER IN (0)  THEN 1 ELSE 0 END) NON_HYPER_COUNT,
        SUM(CASE WHEN HASHES.IS_HYPER IN (1)  THEN 1 ELSE 0 END) HYPER_COUNT,
        COUNT(*) AS TOTAL_HARING_COUNT
      FROM
        HARINGS
        JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
      WHERE
        HARINGS.HARINGS_HASHER_KY = ? AND
        HASHES.KENNEL_KY = ?
      GROUP BY QUARTER(EVENT_DATE)
      ORDER BY QUARTER(EVENT_DATE)
  ";
  $theHaringsByQuarterList = $app['db']->fetchAll($sqlHaringsByQuarter, array((int) $hasher_id, (int) $kennelKy));

  # Obtain the hashes by state
  $sqlHaringsByState = "SELECT
      HASHES.EVENT_STATE,
      SUM(CASE WHEN HASHES.IS_HYPER IN (0)  THEN 1 ELSE 0 END) NON_HYPER_COUNT,
      SUM(CASE WHEN HASHES.IS_HYPER IN (1)  THEN 1 ELSE 0 END) HYPER_COUNT,
      COUNT(*) AS TOTAL_HARING_COUNT
    FROM
      HARINGS
      JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
    WHERE
      HARINGS.HARINGS_HASHER_KY = ? AND
      HASHES.KENNEL_KY = ?
    GROUP BY HASHES.EVENT_STATE
    ORDER BY HASHES.EVENT_STATE
  ";
  $theHaringsByStateList = $app['db']->fetchAll($sqlHaringsByState, array((int) $hasher_id, (int) $kennelKy));


  # Obtain the hashes by day name
  $sqlHaringsByDayName = "SELECT
      THE_VALUE,
        NON_HYPER_COUNT,
        HYPER_COUNT,
        TOTAL_HARING_COUNT,
      CASE THE_VALUE
        WHEN 'Sunday' THEN '0'
        WHEN 'Monday' THEN '1'
        WHEN 'Tuesday' THEN '2'
        WHEN 'Wednesday' THEN '3'
        WHEN 'Thursday' THEN '4'
        WHEN 'Friday' THEN '5'
        WHEN 'Saturday' THEN '6'
      END AS DAYNUMBER
    FROM
    (
      SELECT
        DAYNAME(EVENT_DATE) AS THE_VALUE,
        SUM(CASE WHEN HASHES.IS_HYPER IN (0)  THEN 1 ELSE 0 END) NON_HYPER_COUNT,
        SUM(CASE WHEN HASHES.IS_HYPER IN (1)  THEN 1 ELSE 0 END) HYPER_COUNT,
        COUNT(*) AS TOTAL_HARING_COUNT
      FROM
        HARINGS
        JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
      WHERE
        HARINGS.HARINGS_HASHER_KY = ? AND
        HASHES.KENNEL_KY = ?
      GROUP BY DAYNAME(EVENT_DATE)
      ORDER BY DAYNAME(EVENT_DATE)
    )TEMP
    ORDER BY DAYNUMBER ASC";
  $theHaringsByDayNameList = $app['db']->fetchAll($sqlHaringsByDayName, array((int) $hasher_id, (int) $kennelKy));

  # Establish and set the return value
  $returnValue = array(
    'hasherValue' => $hasher,
    'overallHareCount' => $overallHareCountValue['THE_COUNT'],
    'trueHareCount' => $trueHareCountValue['THE_COUNT'],
    'hyperHareCount' =>$hyperHareCountValue['THE_COUNT'],
    'kennel_abbreviation' => $kennel_abbreviation,
    'harings_by_year_list' => $haringsByYearList,
    'harings_by_month_list' => $theHaringsByMonthNameList,
    'harings_by_quarter_list' => $theHaringsByQuarterList,
    'harings_by_state_list' => $theHaringsByStateList,
    'harings_by_dayname_list' => $theHaringsByDayNameList
  );

  # Return the return value
  return $returnValue;

}


public function viewOverallHareChartsAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

  $commonValues = $this->getStandardHareChartsAction($request, $app, $hasher_id, $kennel_abbreviation);

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Obtain the list of favorite cities to hare in
  $cityHaringCountList = $app['db']->fetchAll(HASHER_ALL_HARING_COUNTS_BY_CITY, array((int) $hasher_id, (int) $kennelKy));

  #Obtain largest entry from the list
  $cityHaringsCountMax = 1;
  if(isset($cityHaringCountList[0]['THE_COUNT'])){
    $cityHaringsCountMax = $cityHaringCountList[0]['THE_COUNT'];
  }

  #Obtain the favorite cohare list
  $cohareCountList = $app['db']->fetchAll(COHARE_COUNT_BY_HARE, array(
    (int) $kennelKy,
    (int) $hasher_id,
    (int) $hasher_id,
    0,
    1,));

  #Obtain the largest entry from the list
  $cohareCountMax = 1;
  if(isset($cohareCountList[0]['THE_COUNT'])){
    $cohareCountMax = $cohareCountList[0]['THE_COUNT'];
  }

  # Obtain their hashes
  $sqlTheHashes = "SELECT HASHES.* FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  WHERE HARINGS.HARINGS_HASHER_KY = ? AND KENNEL_KY = ? and LAT is not null and LNG is not null";
  $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $hasher_id, (int) $kennelKy));

  #Obtain the average lat
  $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  WHERE HARINGS.HARINGS_HASHER_KY = ? AND KENNEL_KY = ? and LAT is not null and LNG is not null";
  $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $hasher_id, (int) $kennelKy));
  $avgLat = $theAverageLatLong['THE_LAT'];
  $avgLng = $theAverageLatLong['THE_LNG'];

  $customValues = array(
    'pageTitle' => 'Overall Hare Charts and Details',
    'firstHeader' => 'Basic Details',
    'secondHeader' => 'Statistics',
    'city_haring_count_list' => $cityHaringCountList,
    'city_harings_max_value' => $cityHaringsCountMax,
    'cohare_count_list' =>$cohareCountList,
    'cohare_count_max' => $cohareCountMax,
    'the_hashes' => $theHashes,
    'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
    'avg_lat' => $avgLat,
    'avg_lng' => $avgLng
  );
  $finalArray = array_merge($commonValues,$customValues);
  $returnValue = $app['twig']->render('hare_chart_overall_details.twig',$finalArray);



  # Return the return value
  return $returnValue;

}



public function viewTrueHareChartsAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){

  $commonValues = $this->getStandardHareChartsAction($request, $app, $hasher_id, $kennel_abbreviation);

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Obtain the list of favorite cities to hare in
  $cityHaringCountList = $app['db']->fetchAll(HASHER_NONHYPER_HARING_COUNTS_BY_CITY, array((int) $hasher_id, (int) $kennelKy));

  #Obtain largest entry from the list
  $cityHaringsCountMax = 1;
  if(isset($cityHaringCountList[0]['THE_COUNT'])){
    $cityHaringsCountMax = $cityHaringCountList[0]['THE_COUNT'];
  }

  #Obtain the favorite cohare list
  $cohareCountList = $app['db']->fetchAll(COHARE_COUNT_BY_HARE, array(
    (int) $kennelKy,
    (int) $hasher_id,
    (int) $hasher_id,
    0,
    0,));

  #Obtain the largest entry from the list
  $cohareCountMax = 1;
  if(isset($cohareCountList[0]['THE_COUNT'])){
    $cohareCountMax = $cohareCountList[0]['THE_COUNT'];
  }

  # Obtain their hashes
  $sqlTheHashes = "SELECT HASHES.* FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  WHERE HARINGS.HARINGS_HASHER_KY = ? AND KENNEL_KY = ? and HASHES.IS_HYPER = 0 and LAT is not null and LNG is not null";
  $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $hasher_id, (int) $kennelKy));

  #Obtain the average lat
  $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  WHERE HARINGS.HARINGS_HASHER_KY = ? AND KENNEL_KY = ? and HASHES.IS_HYPER = 0 and LAT is not null and LNG is not null";
  $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $hasher_id, (int) $kennelKy));
  $avgLat = $theAverageLatLong['THE_LAT'];
  $avgLng = $theAverageLatLong['THE_LNG'];

  $customValues = array(
    'pageTitle' => 'True Hare Charts and Details',
    'firstHeader' => 'Basic Details',
    'secondHeader' => 'Statistics',
    'city_haring_count_list' => $cityHaringCountList,
    'city_harings_max_value' => $cityHaringsCountMax,
    'cohare_count_list' =>$cohareCountList,
    'cohare_count_max' => $cohareCountMax,
    'the_hashes' => $theHashes,
    'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
    'avg_lat' => $avgLat,
    'avg_lng' => $avgLng
  );
  $finalArray = array_merge($commonValues,$customValues);
  $returnValue = $app['twig']->render('hare_chart_true_details.twig',$finalArray);

  # Return the return value
  return $returnValue;

}


public function viewHyperHareChartsAction(Request $request, Application $app, int $hasher_id, string $kennel_abbreviation){


  $commonValues = $this->getStandardHareChartsAction($request, $app, $hasher_id, $kennel_abbreviation);

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  #Obtain the list of favorite cities to hare in
  $cityHaringCountList = $app['db']->fetchAll(HASHER_HYPER_HARING_COUNTS_BY_CITY, array((int) $hasher_id, (int) $kennelKy));

  #Obtain largest entry from the list
  $cityHaringsCountMax = 1;
  if(isset($cityHaringCountList[0]['THE_COUNT'])){
    $cityHaringsCountMax = $cityHaringCountList[0]['THE_COUNT'];
  }

  #Obtain the favorite cohare list
  $cohareCountList = $app['db']->fetchAll(COHARE_COUNT_BY_HARE, array(
    (int) $kennelKy,
    (int) $hasher_id,
    (int) $hasher_id,
    1,
    1,));

  #Obtain the largest entry from the list
  $cohareCountMax = 1;
  if(isset($cohareCountList[0]['THE_COUNT'])){
    $cohareCountMax = $cohareCountList[0]['THE_COUNT'];
  }

  # Obtain their hashes
  $sqlTheHashes = "SELECT HASHES.* FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  WHERE HARINGS.HARINGS_HASHER_KY = ? AND KENNEL_KY = ? and HASHES.IS_HYPER = 1 and LAT is not null and LNG is not null";
  $theHashes = $app['db']->fetchAll($sqlTheHashes, array((int) $hasher_id, (int) $kennelKy));

  #Obtain the average lat
  $sqlTheAverageLatLong = "SELECT AVG(LAT) AS THE_LAT, AVG(LNG) AS THE_LNG FROM HARINGS JOIN HASHES ON HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY
  WHERE HARINGS.HARINGS_HASHER_KY = ? AND KENNEL_KY = ? and HASHES.IS_HYPER = 1 and LAT is not null and LNG is not null";
  $theAverageLatLong = $app['db']->fetchAssoc($sqlTheAverageLatLong, array((int) $hasher_id, (int) $kennelKy));
  $avgLat = $theAverageLatLong['THE_LAT'];
  $avgLng = $theAverageLatLong['THE_LNG'];


  $customValues = array(
    'pageTitle' => 'Hyper Hare Charts and Details',
    'firstHeader' => 'Basic Details',
    'secondHeader' => 'Statistics',
    'city_haring_count_list' => $cityHaringCountList,
    'city_harings_max_value' => $cityHaringsCountMax,
    'cohare_count_list' =>$cohareCountList,
    'cohare_count_max' => $cohareCountMax,
    'the_hashes' => $theHashes,
    'geocode_api_value' => GOOGLE_MAPS_JAVASCRIPT_API_KEY,
    'avg_lat' => $avgLat,
    'avg_lng' => $avgLng
  );
  $finalArray = array_merge($commonValues,$customValues);
  $returnValue = $app['twig']->render('hare_chart_hyper_details.twig',$finalArray);

  # Return the return value
  return $returnValue;

}

public function twoPersonComparisonPreAction(Request $request, Application $app, string $kennel_abbreviation){

  $pageTitle = "Two Person Comparison";

  #Establish the return value
  $returnValue = $app['twig']->render('hasher_comparison_selection_screen.twig', array (
    'pageTitle' => $pageTitle,
    'playerOneDefault' => 'Selection Required',
    'playerTwoDefault' => 'Selection Required',
    'pageSubTitle' => 'Select Your Contestants',
    'pageHeader' => 'Why is this so complicated ?',
    'instructions' => 'You need to select two hashers to compare. Start typing in the search box to find your favorite hasher. When their name shows up, click the "+ player one" link next to their name. Repeat the process of typing in the search box and then click the "+ player two" link. Then, when both hashers have been selected, click on the the giant "submit" button. Enjoy!',
    'kennel_abbreviation' => $kennel_abbreviation
  ));

  # Return the return value
  return $returnValue;

}

private function createComparisonObjectCoreAttributes(string $hasher1, string $hasher2, string $statTitle, string $dataType){

  #Establish the return value object
  $returnValue = array();

  $returnValue = array(
    'statName' => $statTitle,
    'hasher1' => $hasher1,
    'hasher2' => $hasher2,
    'dataType' => $dataType
  );

  #Return the return object
  return $returnValue;
}

private function createComparisonObjectWithStatsAsInts(int $stat1, int $stat2, string $hasher1, string $hasher2, string $statTitle){

  #Establish the return value object
  $returnValue = $this->createComparisonObjectCoreAttributes($hasher1, $hasher2, $statTitle, "int");

  #Establish the winner
  $verdict = '';
  if($stat1 > $stat2){
    $verdict = 'hasher1';
  }else if ($stat2 > $stat1){
    $verdict = 'hasher2';
  }else{
    $verdict = 'tie';
  }

  #Fill in the return value with more attributes
  $additionalAttributes =   array(
    'val1' => $stat1,
    'val2' => $stat2,
    'verdict' => $verdict);

  #Combine the arrays
  $returnValue = $returnValue + $additionalAttributes;

  #Return the return value
  return $returnValue;
}


private function createComparisonObjectWithStatsAsDoubles(float $stat1, float $stat2, string $hasher1, string $hasher2, string $statTitle){

  #Establish the return value object
  $returnValue = $this->createComparisonObjectCoreAttributes($hasher1, $hasher2, $statTitle,"float");

  $verdict = '';
  if($stat1 > $stat2){
    $verdict = 'hasher1';
  }else if ($stat2 > $stat1){
    $verdict = 'hasher2';
  }else{
    $verdict = 'tie';
  }

  #Fill in the return value with more attributes
  $additionalAttributes = array(
    'val1' => $stat1,
    'val2' => $stat2,
    'verdict' => $verdict);

  #Combine the arrays
  $returnValue = $returnValue + $additionalAttributes;

  #Return the return value
  return $returnValue;
}

private function createComparisonObjectWithStatsAsDates(string $stat1, string $stat2, string $hasher1, string $hasher2, string $statTitle, bool $greaterIsBetter, int $key1, int $key2){

  #Establish the return value object
  $returnValue = $this->createComparisonObjectCoreAttributes($hasher1, $hasher2, $statTitle,"date");

  #Establish the verdict variable
  $verdict = '';

  #Establish the date time values
  $date1 = DateTime::createFromFormat('m/d/Y',$stat1);
  $date2 = DateTime::createFromFormat('m/d/Y',$stat2);

  #Populate the verdict value
  if($date1 > $date2){
    $verdict = ($greaterIsBetter ? 'hasher1':'hasher2');
  }else if ($date2 > $date1){
    $verdict = ($greaterIsBetter ? 'hasher2':'hasher1');
  }else {
    $verdict = 'tie';
  }




  #Fill in the return value with more attributes
  $additionalAttributes = array(
    'val1' => $stat1,
    'val2' => $stat2,
    'verdict' => $verdict,
    'hashKey1' => $key1,
    'hashKey2' => $key2);

  #Combine the arrays
  $returnValue = $returnValue + $additionalAttributes;

  #Return the return value
  return $returnValue;

}

private function twoPersonComparisonDataFetch(Request $request, Application $app, int $kennelKy, int $hasher_id1, int $hasher_id2){

  #Establish the reurn value array
  $returnValue = array();

  # Declare the SQL used to retrieve this information
  $sql = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

  # Make a database call to obtain the hasher information
  $hasher1 = $app['db']->fetchAssoc($sql, array((int) $hasher_id1));
  $hasher2 = $app['db']->fetchAssoc($sql, array((int) $hasher_id2));


  #Obtain the overall hashing count
  $hashingCountH1 = ($app['db']->fetchAssoc(PERSONS_HASHING_COUNT, array((int) $hasher_id1, (int) $kennelKy)))['THE_COUNT'];
  $hashingCountH2 = ($app['db']->fetchAssoc(PERSONS_HASHING_COUNT, array((int) $hasher_id2, (int) $kennelKy)))['THE_COUNT'];
  $statObject = $this-> createComparisonObjectWithStatsAsInts($hashingCountH1, $hashingCountH2,$hasher1['HASHER_NAME'], $hasher2['HASHER_NAME'], "Hashing Count");
  $returnValue[] = $statObject;

  #Obtain the overall haring count
  $hareCountOverallH1 = ($app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id1, (int) $kennelKy,  (int) 0, (int) 1)))['THE_COUNT'];
  $hareCountOverallH2 = ($app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id2, (int) $kennelKy,  (int) 0, (int) 1)))['THE_COUNT'];
  $statObject = $this-> createComparisonObjectWithStatsAsInts($hareCountOverallH1, $hareCountOverallH2,$hasher1['HASHER_NAME'], $hasher2['HASHER_NAME'], "Overall Haring Count");
  $returnValue[] = $statObject;

  #Obtain the true haring count
  $hareCountTrueH1 = ($app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id1, (int) $kennelKy,  (int) 0, (int) 0)))['THE_COUNT'];
  $hareCountTrueH2 = ($app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id2, (int) $kennelKy,  (int) 0, (int) 0)))['THE_COUNT'];
  $statObject = $this->createComparisonObjectWithStatsAsInts($hareCountTrueH1, $hareCountTrueH2, $hasher1['HASHER_NAME'], $hasher2['HASHER_NAME'], "True Haring Count");
  $returnValue[] = $statObject;

  #Obtain the hyper haring count
  $hareCountHyperH1 = ($app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id1, (int) $kennelKy,  (int) 1, (int) 1)))['THE_COUNT'];
  $hareCountHyperH2 = ($app['db']->fetchAssoc(PERSONS_HARING_COUNT_FLEXIBLE, array((int) $hasher_id2, (int) $kennelKy,  (int) 1, (int) 1)))['THE_COUNT'];
  $statObject = $this->createComparisonObjectWithStatsAsInts($hareCountHyperH1, $hareCountHyperH2, $hasher1['HASHER_NAME'], $hasher2['HASHER_NAME'], "Hyper Haring Count");
  $returnValue[] = $statObject;

  #Obtain the overall haring percentage
  $statObject = $this->createComparisonObjectWithStatsAsDoubles( ($hashingCountH1 == 0 ? 0 : $hareCountOverallH1/$hashingCountH1),
    ($hashingCountH2 == 0 ? 0 : $hareCountOverallH2/$hashingCountH2), $hasher1['HASHER_NAME'], $hasher2['HASHER_NAME'], "Overall Haring/Hashing %");
  $returnValue[] = $statObject;

  #Obtain the true haring percentage
  $statObject = $this->createComparisonObjectWithStatsAsDoubles( ($hashingCountH1 == 0 ? 0 : $hareCountTrueH1/$hashingCountH1),
    ($hashingCountH2 == 0 ? 0 : $hareCountTrueH2/$hashingCountH2), $hasher1['HASHER_NAME'], $hasher2['HASHER_NAME'], "True Haring/Hashing %");
  $returnValue[] = $statObject;

  #Obtain the true haring / all haring percentage
  $statObject = $this->createComparisonObjectWithStatsAsDoubles( ($hareCountOverallH1 == 0 ? 0 : $hareCountTrueH1/$hareCountOverallH1),
    ($hareCountOverallH2 == 0 ? 0 : $hareCountTrueH2/$hareCountOverallH2), $hasher1['HASHER_NAME'], $hasher2['HASHER_NAME'], "True Haring / All Haring %");
  $returnValue[] = $statObject;

  #Obtain the virgin hash dates
  $virginHashH1 = $app['db']->fetchAssoc(SELECT_HASHERS_VIRGIN_HASH, array((int) $hasher_id1, (int) $kennelKy));
  $virginHashH2 = $app['db']->fetchAssoc(SELECT_HASHERS_VIRGIN_HASH, array((int) $hasher_id2, (int) $kennelKy));
  $statObject = $this->createComparisonObjectWithStatsAsDates(
    is_null($virginHashH1['EVENT_DATE_FORMATTED']) ? "": $virginHashH1['EVENT_DATE_FORMATTED'] ,
    is_null($virginHashH2['EVENT_DATE_FORMATTED']) ? "": $virginHashH2['EVENT_DATE_FORMATTED'] ,
    $hasher1['HASHER_NAME'],
    $hasher2['HASHER_NAME'],
    "First Hash",
    FALSE,
    is_null($virginHashH1['HASH_KY']) ? 0 : $virginHashH1['HASH_KY'] ,
    is_null($virginHashH2['HASH_KY']) ? 0 : $virginHashH2['HASH_KY']);
  $returnValue[] = $statObject;

  #Obtain the latest hash dates
  $latestHashH1 = $app['db']->fetchAssoc(SELECT_HASHERS_MOST_RECENT_HASH, array((int) $hasher_id1, (int) $kennelKy));
  $latestHashH2 = $app['db']->fetchAssoc(SELECT_HASHERS_MOST_RECENT_HASH, array((int) $hasher_id2, (int) $kennelKy));
  $statObject = $this->createComparisonObjectWithStatsAsDates(
    is_null($latestHashH1['EVENT_DATE_FORMATTED']) ? "": $latestHashH1['EVENT_DATE_FORMATTED'] ,
    is_null($latestHashH2['EVENT_DATE_FORMATTED']) ? "": $latestHashH2['EVENT_DATE_FORMATTED'] ,
    $hasher1['HASHER_NAME'],
    $hasher2['HASHER_NAME'],
    "Latest Hash",
    TRUE,
    is_null($latestHashH1['HASH_KY']) ? 0 : $latestHashH1['HASH_KY'] ,
    is_null($latestHashH2['HASH_KY']) ? 0 : $latestHashH2['HASH_KY']);
  $returnValue[] = $statObject;

  #Return the return value
  return $returnValue;

}

public function twoPersonComparisonAction(Request $request, Application $app, string $kennel_abbreviation, int $hasher_id, int $hasher_id2){

  $pageTitle = "Hasher Showdown";

  # Declare the SQL used to retrieve this information
  $sql = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

  #Obtain the kennel key
  $kennelKy = $this->obtainKennelKeyFromKennelAbbreviation($request, $app, $kennel_abbreviation);

  # Make a database call to obtain the hasher information
  $hasher1 = $app['db']->fetchAssoc($sql, array((int) $hasher_id));
  $hasher2 = $app['db']->fetchAssoc($sql, array((int) $hasher_id2));
  $pageSubtitle = $hasher1['HASHER_NAME'] . " VS " . $hasher2['HASHER_NAME'];

  $listOfStats = null;
  $listOfStats= $this->twoPersonComparisonDataFetch($request, $app, $kennelKy, $hasher_id, $hasher_id2);


  #Establish the return value
  $returnValue = $app['twig']->render('hasher_comparison_fluid_results.twig', array (
    'pageTitle' => $pageTitle,
    'pageSubTitle' => $pageSubtitle,
    'pageHeader' => 'Why is this so complicated ?',
    'kennel_abbreviation' => $kennel_abbreviation,
    'hasherName1' => $hasher1['HASHER_NAME'],
    'hasherName2' => $hasher2['HASHER_NAME'],
    'tempList' => $listOfStats
  ));

  # Return the return value
  return $returnValue;

}

}
