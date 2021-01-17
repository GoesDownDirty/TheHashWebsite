<?php

namespace HASH\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class SuperAdminController extends BaseController {

  #Define the action
  public function helloAction(Request $request, Application $app){

      #Establish the list of admin users
      $userList = $app['db']->fetchAll("SELECT username, roles FROM USERS ORDER BY username ASC");

      #Establish the list of kennels
      $kennelList = $app['db']->fetchAll("SELECT KENNEL_NAME, KENNEL_DESCRIPTION,
          KENNEL_ABBREVIATION, IN_RECORD_KEEPING, SITE_ADDRESS
          FROM KENNELS ORDER BY IN_RECORD_KEEPING DESC, SITE_ADDRESS DESC");

      $hareTypes = $app['db']->fetchAll("SELECT * FROM HARE_TYPES ORDER BY SEQ");

      $hashTypes = $app['db']->fetchAll("SELECT * FROM HASH_TYPES ORDER BY SEQ");

      #return $app->redirect('/');
      return $app['twig']->render('superadmin_landing.twig', array (
        'pageTitle' => 'This is the super admin landing screen',
        'subTitle1' => 'This is the super admin landing screen',
        'user_list' => $userList,
        'kennel_list' => $kennelList,
        'hare_types' => $hareTypes,
        'hash_types' => $hashTypes
      ));
  }

  #Define the action
  public function logonScreenAction(Request $request, Application $app){

    # Establisht the last error
    $lastError = $app['security.last_error']($request);
    #$app['monolog']->addDebug($lastError);

    # Establish the last username
    $lastUserName = $app['session']->get('_security.last_username');
    #$lastUserName = $app['session']->get('_security.last_username');
    #$app['monolog']->addDebug($lastUserName);

    # Establish the return value
    $returnValue =  $app['twig']->render('superadmin_logon_screen.twig', array (
      'pageTitle' => 'Super Admin Logon',
      'pageHeader' => 'Please log on!',
      'error' => $lastError,
      'last_username' => $lastUserName,
    ));

    # Return the return value;
    return $returnValue;
  }

  public function logoutAction(Request $request, Application $app){

    # Invalidate the session
    $app['session']->invalidate();

    # Redirect the user to the root url
    return $app->redirect('/');

  }

  #Define action
  public function modifyKennelAjaxPreAction(Request $request, Application $app, string $kennel_abbreviation) {

    # Declare the SQL used to retrieve this information
    $sql = "
      SELECT *
        FROM KENNELS
       WHERE KENNEL_ABBREVIATION = ?";

    # Make a database call to obtain the hasher information
    $kennelValue = $app['db']->fetchAssoc($sql, array($kennel_abbreviation));

    $sql = "
      SELECT GROUP_CONCAT(AWARD_LEVEL ORDER BY AWARD_LEVEL)
        FROM AWARD_LEVELS
       GROUP BY KENNEL_KY
      HAVING KENNEL_KY = (SELECT KENNEL_KY
                            FROM KENNELS
                           WHERE KENNEL_ABBREVIATION = ?)";

    $awardLevels = $app['db']->fetchOne($sql, array($kennel_abbreviation));

    $returnValue = $app['twig']->render('edit_kennel_form_ajax.twig', array(
      'pageTitle' => 'Modify a Kennel!',
      'kennel_abbreviation' => $kennel_abbreviation,
      'kennelValue' => $kennelValue,
      'awardLevels' => $awardLevels
    ));

    #Return the return value
    return $returnValue;
  }

  public function modifyKennelAjaxPostAction(Request $request, Application $app, string $kennel_abbreviation) {

    $theKennelName = trim(strip_tags($request->request->get('kennelName')));
    $theKennelAbbreviation = trim(strip_tags($request->request->get('kennelAbbreviation')));
    $theKennelDescription = trim(strip_tags($request->request->get('kennelDescription')));
    $theSiteAddress = trim(strip_tags($request->request->get('siteAddress')));
    $theInRecordKeeping = (int) trim(strip_tags($request->request->get('inRecordKeeping')));
    $theAwardLevels = str_replace(' ', '', trim(strip_tags($request->request->get('awardLevels'))));
    $theOrigAwardLevels = trim(strip_tags($request->request->get('origAwardLevels')));

    if($theSiteAddress == "") {
      $theSiteAddress = null;
    }

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if($theInRecordKeeping !=0 && $theInRecordKeeping != 1) {
      $passedValidation = FALSE;
      $returnMessage .= " |Failed validation on inRecordKeeping";
    }

    if($passedValidation) {

      $sql = "
        UPDATE KENNELS
          SET
            KENNEL_NAME = ?,
            KENNEL_ABBREVIATION = ?,
            KENNEL_DESCRIPTION = ?,
            SITE_ADDRESS = ?,
            IN_RECORD_KEEPING = ?
         WHERE KENNEL_ABBREVIATION = ?";

        $app['dbs']['mysql_write']->executeUpdate($sql,array(
          $theKennelName,
          $theKennelAbbreviation,
          $theKennelDescription,
          $theSiteAddress,
          $theInRecordKeeping,
          $kennel_abbreviation
        ));

      if($theAwardLevels != $theOrigAwardLevels) {
        $sql = "
          DELETE FROM AWARD_LEVELS
           WHERE KENNEL_KY = (
          SELECT KENNEL_KY
            FROM KENNELS
           WHERE KENNEL_ABBREVIATION = ?)";

        $app['dbs']['mysql_write']->executeUpdate($sql,array($kennel_abbreviation));

        $sql = "
          INSERT INTO AWARD_LEVELS(KENNEL_KY, AWARD_LEVEL)
          VALUES((SELECT KENNEL_KY FROM KENNELS WHERE KENNEL_ABBREVIATION = ?), ?)";

        $kennelAwards = preg_split("/,/", $theAwardLevels);

        foreach($kennelAwards as $kennelAward) {
          $app['dbs']['mysql_write']->executeUpdate($sql,array($kennel_abbreviation, (int) $kennelAward));
        }
      }

      #Audit this activity
      $actionType = "Kennel Modification (Ajax)";
      $actionDescription = "Modified kennel $kennel_abbreviation";
      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      // Establish the return value message
      $returnMessage = "Success! Great, it worked";
    }

    #Set the return value
    $returnValue =  $app->json($returnMessage, 200);
    return $returnValue;
  }

  #Define action
  public function modifyHareTypeAjaxPreAction(Request $request, Application $app, int $hare_type) {

    # Declare the SQL used to retrieve this information
    $sql = "
      SELECT *
        FROM HARE_TYPES
       WHERE HARE_TYPE = ?";

    # Make a database call to obtain the hasher information
    $hareTypeValue = $app['db']->fetchAssoc($sql, array($hare_type));

    $returnValue = $app['twig']->render('edit_hare_type_form_ajax.twig', array(
      'pageTitle' => 'Modify a Hare Type!',
      'hareTypeValue' => $hareTypeValue,
      'hare_type' => $hare_type
    ));

    #Return the return value
    return $returnValue;
  }

  public function modifyHareTypeAjaxPostAction(Request $request, Application $app, int $hare_type) {

    $theHareTypeName = trim(strip_tags($request->request->get('hareTypeName')));
    $theSequence = trim(strip_tags($request->request->get('sequence')));
    $theChartColor = trim(strip_tags($request->request->get('chartColor')));

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if($passedValidation) {

      $sql = "
        UPDATE HARE_TYPES
          SET
            HARE_TYPE_NAME = ?,
            SEQ = ?,
            CHART_COLOR = ?
         WHERE HARE_TYPE = ?";

        $app['dbs']['mysql_write']->executeUpdate($sql,array(
          $theHareTypeName,
          (int) $theSequence,
          $theChartColor,
          $hare_type
        ));

      #Audit this activity
      $actionType = "Hare Type Modification (Ajax)";
      $actionDescription = "Modified hare type $theHareTypeName";
      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      // Establish the return value message
      $returnMessage = "Success! Great, it worked";
    }

    #Set the return value
    $returnValue =  $app->json($returnMessage, 200);
    return $returnValue;
  }

  #Define action
  public function modifyHashTypeAjaxPreAction(Request $request, Application $app, int $hash_type) {

    # Declare the SQL used to retrieve this information
    $sql = "
      SELECT *
        FROM HASH_TYPES
       WHERE HASH_TYPE = ?";

    # Make a database call to obtain the hasher information
    $hashTypeValue = $app['db']->fetchAssoc($sql, array($hash_type));

    $hareTypes = $app['db']->fetchAll("
      SELECT *, (
        COALESCE((SELECT true
          FROM HASH_TYPES
         WHERE HASH_TYPE = ?
           AND HASH_TYPES.HARE_TYPE_MASK & HARE_TYPES.HARE_TYPE = HARE_TYPES.HARE_TYPE), false)) AS SELECTED
        FROM HARE_TYPES
       ORDER BY SEQ", array($hash_type));

    $returnValue = $app['twig']->render('edit_hash_type_form_ajax.twig', array(
      'pageTitle' => 'Modify a Hash Type!',
      'hashTypeValue' => $hashTypeValue,
      'hash_type' => $hash_type,
      'hare_types' => $hareTypes
    ));

    #Return the return value
    return $returnValue;
  }

  public function modifyHashTypeAjaxPostAction(Request $request, Application $app, int $hash_type) {

    $theHashTypeName = trim(strip_tags($request->request->get('hashTypeName')));
    $theSequence = trim(strip_tags($request->request->get('sequence')));
    $theHareTypes = $request->request->get('hareTypes');

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    if(is_array($theHareTypes)) {
      $theHareTypeMask = 0;
      foreach($theHareTypes as $theHareType) {
        $theHareTypeMask += (int) $theHareType;
      }
    } else {
      $theHareTypeMask = (int) $theHareTypes;
    }

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if($theHareTypeMask <= 0) {
      $passedValidation = FALSE;
      $returnMessage .= " |Failed validation on hare types";
    }

    if($passedValidation) {

      $sql = "
        UPDATE HASH_TYPES
          SET
            HASH_TYPE_NAME = ?,
            SEQ = ?,
            HARE_TYPE_MASK = ?
         WHERE HASH_TYPE = ?";

        $app['dbs']['mysql_write']->executeUpdate($sql,array(
          $theHashTypeName,
          (int) $theSequence,
          $theHareTypeMask,
          $hash_type
        ));

      #Audit this activity
      $actionType = "Hash Type Modification (Ajax)";
      $actionDescription = "Modified hash type $theHashTypeName";
      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      // Establish the return value message
      $returnMessage = "Success! Great, it worked";
    }

    #Set the return value
    $returnValue =  $app->json($returnMessage, 200);
    return $returnValue;
  }
}
