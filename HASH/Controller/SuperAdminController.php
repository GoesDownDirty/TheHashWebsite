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
use Symfony\Component\Security\Core\User\User;

class SuperAdminController extends BaseController {

  private function convertInputToMask($input) {
    if(is_array($input)) {
      $mask = 0;
      foreach($input as $intValue) {
        $mask += (int) $intValue;
      }
    } else {
      $mask = (int) $input;
    }
    return $mask;
  }

  #Define the action
  public function helloAction(Request $request, Application $app){

      #Establish the list of admin users
      $userList = $app['db']->fetchAll("SELECT id, username, roles FROM USERS ORDER BY username ASC");

      #Establish the list of kennels
      $kennelList = $app['db']->fetchAll("SELECT KENNEL_NAME, KENNEL_DESCRIPTION,
         KENNEL_ABBREVIATION, IN_RECORD_KEEPING, SITE_ADDRESS, KENNEL_KY,
         EXISTS(SELECT 1 FROM HASHES WHERE HASHES.KENNEL_KY = KENNELS.KENNEL_KY) AS IN_USE
         FROM KENNELS ORDER BY IN_RECORD_KEEPING DESC, SITE_ADDRESS DESC");

      $hareTypes = $app['db']->fetchAll("SELECT *,
        EXISTS(SELECT 1 FROM HARINGS WHERE HARINGS.HARE_TYPE & HARE_TYPES.HARE_TYPE = HARE_TYPES.HARE_TYPE) AS IN_USE
        FROM HARE_TYPES ORDER BY SEQ");

      $hashTypes = $app['db']->fetchAll("SELECT *,
        EXISTS(SELECT 1 FROM HASHES_TABLE WHERE HASHES_TABLE.HASH_TYPE & HASH_TYPES.HASH_TYPE = HASH_TYPES.HASH_TYPE) AS IN_USE
        FROM HASH_TYPES ORDER BY SEQ");

      $siteConfig = $app['db']->fetchAll("SELECT NAME, VALUE FROM SITE_CONFIG WHERE DESCRIPTION IS NOT NULL ORDER BY NAME");

      #return $app->redirect('/');
      return $this->render($app, 'superadmin_landing.twig', array (
        'pageTitle' => 'This is the super admin landing screen',
        'subTitle1' => 'This is the super admin landing screen',
        'user_list' => $userList,
        'kennel_list' => $kennelList,
        'hare_types' => $hareTypes,
        'hash_types' => $hashTypes,
        'site_config' => $siteConfig));
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
    $returnValue =  $this->render($app, 'superadmin_logon_screen.twig', array (
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

    $hareTypes = $app['db']->fetchAll("
      SELECT *, (
        COALESCE((SELECT true
          FROM KENNELS
         WHERE KENNEL_ABBREVIATION = ?
           AND KENNELS.HARE_TYPE_MASK & HARE_TYPES.HARE_TYPE = HARE_TYPES.HARE_TYPE), false)) AS SELECTED
        FROM HARE_TYPES
       ORDER BY SEQ", array($kennel_abbreviation));

    $hashTypes = $app['db']->fetchAll("
      SELECT *, (
        COALESCE((SELECT true
          FROM KENNELS
         WHERE KENNEL_ABBREVIATION = ?
           AND KENNELS.HASH_TYPE_MASK & HASH_TYPES.HASH_TYPE = HASH_TYPES.HASH_TYPE), false)) AS SELECTED
        FROM HASH_TYPES
       ORDER BY SEQ", array($kennel_abbreviation));

    $returnValue = $this->render($app, 'edit_kennel_form_ajax.twig', array(
      'pageTitle' => 'Modify a Kennel!',
      'kennel_abbreviation' => $kennel_abbreviation,
      'kennelValue' => $kennelValue,
      'awardLevels' => $awardLevels,
      'hare_types' => $hareTypes,
      'hash_types' => $hashTypes
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
    $theHashTypes = $request->request->get('hashTypes');
    $theHareTypes = $request->request->get('hareTypes');

    if($theSiteAddress == "") {
      $theSiteAddress = null;
    }

    $theHashTypeMask = $this->convertInputToMask($theHashTypes);
    $theHareTypeMask = $this->convertInputToMask($theHareTypes);

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
            IN_RECORD_KEEPING = ?,
            HASH_TYPE_MASK = ?,
            HARE_TYPE_MASK = ?
         WHERE KENNEL_ABBREVIATION = ?";

        $app['dbs']['mysql_write']->executeUpdate($sql,array(
          $theKennelName,
          $theKennelAbbreviation,
          $theKennelDescription,
          $theSiteAddress,
          $theInRecordKeeping,
          $theHashTypeMask,
          $theHareTypeMask,
          $kennel_abbreviation,
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
  public function newKennelAjaxPreAction(Request $request, Application $app) {

    $kennelValue['KENNEL_NAME'] = "";
    $kennelValue['KENNEL_ABBREVIATION'] = "";
    $kennelValue['KENNEL_DESCRIPTION'] = "";
    $kennelValue['SITE_ADDRESS'] = "";
    $kennelValue['IN_RECORD_KEEPING'] = 1;

    $awardLevels = "10,25,50,69,100,200,300,400,500,600,700,800,900,1000";

    $hareTypes = $app['db']->fetchAll("
      SELECT *, false AS SELECTED
        FROM HARE_TYPES
       ORDER BY SEQ", array());

    $hashTypes = $app['db']->fetchAll("
      SELECT *, false AS SELECTED
        FROM HASH_TYPES
       ORDER BY SEQ", array());

    $returnValue = $this->render($app, 'edit_kennel_form_ajax.twig', array(
      'pageTitle' => 'Add a Kennel!',
      'kennel_abbreviation' => '_none',
      'kennelValue' => $kennelValue,
      'awardLevels' => $awardLevels,
      'hare_types' => $hareTypes,
      'hash_types' => $hashTypes
    ));

    #Return the return value
    return $returnValue;
  }

  public function newKennelAjaxPostAction(Request $request, Application $app) {

    $theKennelName = trim(strip_tags($request->request->get('kennelName')));
    $theKennelAbbreviation = trim(strip_tags($request->request->get('kennelAbbreviation')));
    $theKennelDescription = trim(strip_tags($request->request->get('kennelDescription')));
    $theSiteAddress = trim(strip_tags($request->request->get('siteAddress')));
    $theInRecordKeeping = (int) trim(strip_tags($request->request->get('inRecordKeeping')));
    $theAwardLevels = str_replace(' ', '', trim(strip_tags($request->request->get('awardLevels'))));
    $theHashTypes = $request->request->get('hashTypes');
    $theHareTypes = $request->request->get('hareTypes');

    if($theSiteAddress == "") {
      $theSiteAddress = null;
    }

    $theHashTypeMask = $this->convertInputToMask($theHashTypes);
    $theHareTypeMask = $this->convertInputToMask($theHareTypes);

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
        INSERT INTO KENNELS(KENNEL_NAME, KENNEL_ABBREVIATION, KENNEL_DESCRIPTION,
            SITE_ADDRESS, IN_RECORD_KEEPING, HASH_TYPE_MASK, HARE_TYPE_MASK)
        VALUES(?, ?, ?, ?, ?, ?, ?)";

      $app['dbs']['mysql_write']->executeUpdate($sql,array(
        $theKennelName,
        $theKennelAbbreviation,
        $theKennelDescription,
        $theSiteAddress,
        $theInRecordKeeping,
        $theHashTypeMask,
        $theHareTypeMask));

      $sql = "
        INSERT INTO AWARD_LEVELS(KENNEL_KY, AWARD_LEVEL)
          VALUES((SELECT KENNEL_KY FROM KENNELS WHERE KENNEL_ABBREVIATION = ?), ?)";

      $kennelAwards = preg_split("/,/", $theAwardLevels);

      foreach($kennelAwards as $kennelAward) {
        $app['dbs']['mysql_write']->executeUpdate($sql,array($theKennelAbbreviation, (int) $kennelAward));
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

    $returnValue = $this->render($app, 'edit_hare_type_form_ajax.twig', array(
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
  public function newHareTypeAjaxPreAction(Request $request, Application $app) {

    # Declare the SQL used to retrieve this information
    $sql = "
      SELECT MAX(SEQ) + 10 AS SEQ, null AS HARE_TYPE_NAME, '255,0,0' AS CHART_COLOR
        FROM HARE_TYPES";

    # Make a database call to obtain the hasher information
    $hareTypeValue = $app['db']->fetchAssoc($sql, array($hare_type));

    $returnValue = $this->render($app, 'edit_hare_type_form_ajax.twig', array(
      'pageTitle' => 'Create a Hare Type!',
      'hareTypeValue' => $hareTypeValue,
      'hare_type' => -1
    ));

    #Return the return value
    return $returnValue;
  }

  public function newHareTypeAjaxPostAction(Request $request, Application $app) {

    $theHareTypeName = trim(strip_tags($request->request->get('hareTypeName')));
    $theSequence = trim(strip_tags($request->request->get('sequence')));
    $theChartColor = trim(strip_tags($request->request->get('chartColor')));

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if($passedValidation) {

      $hare_type = 1;
      $sql = "SELECT HARE_TYPE FROM HARE_TYPES WHERE HARE_TYPE = ?";
      while(true) {
        if(!$app['db']->fetchOne($sql, array($hare_type))) break;
        $hare_type *= 2;
      }

      $sql = "
        INSERT INTO HARE_TYPES(HARE_TYPE_NAME, SEQ, CHART_COLOR, HARE_TYPE)
         VALUES(?, ?, ?, ?)";

        $app['dbs']['mysql_write']->executeUpdate($sql,array(
          $theHareTypeName,
          (int) $theSequence,
          $theChartColor,
          $hare_type));

      #Audit this activity
      $actionType = "Hare Type Creation (Ajax)";
      $actionDescription = "Created hare type $theHareTypeName";
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

    $returnValue = $this->render($app, 'edit_hash_type_form_ajax.twig', array(
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

    $theHareTypeMask = $this->convertInputToMask($theHareTypes);

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

  #Define action
  public function newHashTypeAjaxPreAction(Request $request, Application $app) {

    $sql = "
      SELECT MAX(SEQ)+10 AS SEQ, NULL AS HASH_TYPE_NAME
        FROM HASH_TYPES";

    # Make a database call to obtain the hasher information
    $hashTypeValue = $app['db']->fetchAssoc($sql, array($hash_type));

    $hareTypes = $app['db']->fetchAll("
      SELECT *, false AS SELECTED
        FROM HARE_TYPES
       ORDER BY SEQ", array());

    $returnValue = $this->render($app, 'edit_hash_type_form_ajax.twig', array(
      'pageTitle' => 'Create a Hash Type!',
      'hashTypeValue' => $hashTypeValue,
      'hash_type' => -1,
      'hare_types' => $hareTypes
    ));

    #Return the return value
    return $returnValue;
  }

  public function newHashTypeAjaxPostAction(Request $request, Application $app) {

    $theHashTypeName = trim(strip_tags($request->request->get('hashTypeName')));
    $theSequence = trim(strip_tags($request->request->get('sequence')));
    $theHareTypes = $request->request->get('hareTypes');

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    $theHareTypeMask = $this->convertInputToMask($theHareTypes);

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if($theHareTypeMask <= 0) {
      $passedValidation = FALSE;
      $returnMessage .= " |Failed validation on hare types";
    }

    if($passedValidation) {

      $hash_type = 1;
      $sql = "SELECT HASH_TYPE FROM HASH_TYPES WHERE HASH_TYPE = ?";
      while(true) {
        if(!$app['db']->fetchOne($sql, array($hash_type))) break;
        $hash_type *= 2;
      }

      $sql = "
        INSERT INTO HASH_TYPES(HASH_TYPE, HASH_TYPE_NAME, SEQ, HARE_TYPE_MASK)
        VALUES(?, ?, ?, ?)";

        $app['dbs']['mysql_write']->executeUpdate($sql,array(
          $hash_type,
          $theHashTypeName,
          (int) $theSequence,
          $theHareTypeMask));

      #Audit this activity
      $actionType = "Hash Type Creation (Ajax)";
      $actionDescription = "Created hash type $theHashTypeName";
      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      // Establish the return value message
      $returnMessage = "Success! Great, it worked";
    }

    #Set the return value
    $returnValue =  $app->json($returnMessage, 200);
    return $returnValue;
  }

  #Define action
  public function modifyUserAjaxPreAction(Request $request, Application $app, int $user_id) {

    # Declare the SQL used to retrieve this information
    $sql = "
      SELECT username, (INSTR(roles, 'ROLE_SUPERADMIN') > 1) AS SUPERADMIN
        FROM USERS
       WHERE ID = ?";

    # Make a database call to obtain the hasher information
    $userValue = $app['db']->fetchAssoc($sql, array($user_id));

    $returnValue = $this->render($app, 'edit_user_form_ajax.twig', array(
      'pageTitle' => 'Modify a User!',
      'userValue' => $userValue,
      'user_id' => $user_id
    ));

    #Return the return value
    return $returnValue;
  }

  public function modifyUserAjaxPostAction(Request $request, Application $app, int $user_id) {

    $theUsername = trim(strip_tags($request->request->get('username')));
    $thePassword = trim(strip_tags($request->request->get('password')));
    $theSuperadmin = $request->request->get('superadmin');

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    if($theSuperadmin == "1") {
      $roles="ROLE_ADMIN,ROLE_SUPERADMIN";
    } else {
      $roles="ROLE_ADMIN";
    }

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if(strlen($thePassword) >= 8) {

      // compute the encoded password for the new password
      $user = new User($theUsername, null, array("ROLE_USER"), true, true, true, true);

      // find the encoder for a UserInterface instance
      $encoder = $app['security.encoder_factory']->getEncoder($user);

      // compute the encoded password for the new password
      $encodedNewPassword = $encoder->encodePassword($thePassword, $user->getSalt());

    } else if(strlen($thePassword) != 0) {
      $passedValidation = FALSE;
      $returnMessage .= " |Failed validation on password";
    } else {
      $encodedNewPassword = null;
    }

    if($passedValidation) {

      $sql = "
        UPDATE USERS
          SET
            username = ?,
            roles = ?
         WHERE id = ?";

        $app['dbs']['mysql_write']->executeUpdate($sql,array(
          $theUsername,
          $roles,
          $user_id
        ));

      if($encodedNewPassword != null) {
        $sql = "
          UPDATE USERS
            SET
              password = ?
           WHERE id = ?";

          $app['dbs']['mysql_write']->executeUpdate($sql,array(
            $encodedNewPassword,
            $user_id
          ));
      }

      #Audit this activity
      $actionType = "User Modification (Ajax)";
      $actionDescription = "Modified user $theUsername";
      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      // Establish the return value message
      $returnMessage = "Success! Great, it worked";
    }

    #Set the return value
    $returnValue =  $app->json($returnMessage, 200);
    return $returnValue;
  }

  #Define action
  public function modifySiteConfigAjaxPreAction(Request $request, Application $app, string $name) {

    # Declare the SQL used to retrieve this information
    $sql = "
      SELECT * FROM SITE_CONFIG WHERE NAME = ?";

    # Make a database call to obtain the hasher information
    $item = $app['db']->fetchAssoc($sql, array($name));

    $returnValue = $this->render($app, 'edit_site_config_form_ajax.twig', array(
      'pageTitle' => 'Modify a Configuration Variable: '.$name,
      'item' => $item
    ));

    #Return the return value
    return $returnValue;
  }

  public function modifySiteConfigAjaxPostAction(Request $request, Application $app, string $name) {

    $theValue = trim($request->request->get('value'));

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if($passedValidation) {

      $sql = "
        UPDATE SITE_CONFIG
           SET VALUE = ?
         WHERE NAME = ?
           AND DESCRIPTION IS NOT NULL";

      $app['dbs']['mysql_write']->executeUpdate($sql,array(
        $theValue,
        $name));

      #Audit this activity
      $actionType = "SITE CONFIG Modification (Ajax)";
      $actionDescription = "Modified site config $name";
      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      // Establish the return value message
      $returnMessage = "Success! Great, it worked";
    }

    #Set the return value
    $returnValue =  $app->json($returnMessage, 200);
    return $returnValue;
  }

  #Define action
  public function newUserAjaxPreAction(Request $request, Application $app) {

    $userValue['username']='';
    $userValue['SUPERADMIN']=false;

    $returnValue = $this->render($app, 'edit_user_form_ajax.twig', array(
      'pageTitle' => 'Add a User!',
      'userValue' => $userValue,
      'user_id' => -1
    ));

    #Return the return value
    return $returnValue;
  }

  public function newUserAjaxPostAction(Request $request, Application $app) {

    $theUsername = trim(strip_tags($request->request->get('username')));
    $thePassword = trim(strip_tags($request->request->get('password')));
    $theSuperadmin = $request->request->get('superadmin');

    // Establish a "passed validation" variable
    $passedValidation = TRUE;

    if($theSuperadmin == "1") {
      $roles="ROLE_ADMIN,ROLE_SUPERADMIN";
    } else {
      $roles="ROLE_ADMIN";
    }

    // Establish the return message value as empty (at first)
    $returnMessage = "";

    if(strlen($thePassword) >= 8) {

      // compute the encoded password for the new password
      $user = new User($theUsername, null, array("ROLE_USER"), true, true, true, true);

      // find the encoder for a UserInterface instance
      $encoder = $app['security.encoder_factory']->getEncoder($user);

      // compute the encoded password for the new password
      $encodedPassword = $encoder->encodePassword($thePassword, $user->getSalt());

    } else {
      $passedValidation = FALSE;
      $returnMessage .= " |Failed validation on password";
    }

    if($passedValidation) {

      $sql = "INSERT INTO USERS(username, roles, password)
        VALUES(?, ?, ?)";

      $app['dbs']['mysql_write']->executeUpdate($sql,array(
        $theUsername,
        $roles,
        $encodedPassword));

      #Audit this activity
      $actionType = "User Creation (Ajax)";
      $actionDescription = "Created user $theUsername";
      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      // Establish the return value message
      $returnMessage = "Success! Great, it worked";
    }

    #Set the return value
    $returnValue =  $app->json($returnMessage, 200);
    return $returnValue;
  }

  public function deleteUser(Request $request, Application $app, int $user_id) {
    if($user_id != $app['user'].username) {

      $sql = "SELECT username FROM USERS WHERE ID = ?";
      $username = $app['db']->fetchOne($sql, array($user_id));

      $sql = "DELETE FROM USERS WHERE id = ?";
      $app['dbs']['mysql_write']->executeUpdate($sql,array($user_id));

      $actionType = "User Deletion (Ajax)";
      $actionDescription = "Deleted user $username";

      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

      header("Location: /superadmin/hello");
      return $app->json("", 302);
    }
  }

  public function deleteKennel(Request $request, Application $app, int $kennel_ky) {

    $sql = "SELECT KENNEL_ABBREVIATION FROM KENNELS WHERE KENNEL_KY = ?";
    $kennel = $app['db']->fetchOne($sql, array($kennel_ky));

    $sql = "DELETE FROM KENNELS WHERE KENNEL_KY = ?";
    $app['dbs']['mysql_write']->executeUpdate($sql,array($kennel_ky));

    $actionType = "Kennel Deletion (Ajax)";
    $actionDescription = "Deleted kennel $kennel";

    AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

    header("Location: /superadmin/hello");
    return $app->json("", 302);
  }

  public function deleteHashType(Request $request, Application $app, int $hash_type) {

    $sql = "SELECT EXISTS(SELECT 1 FROM HASHES_TABLE WHERE HASHES_TABLE.HASH_TYPE & ? = HASHES_TABLE.HASH_TYPE) AS IN_USE";
    $in_use = $app['db']->fetchOne($sql, array($kennel_ky));

    if(!$in_use) {
      $sql = "SELECT HASH_TYPE_NAME FROM HASH_TYPES WHERE HASH_TYPE = ?";
      $hash_type_name = $app['db']->fetchOne($sql, array($hash_type));

      $sql = "DELETE FROM HASH_TYPES WHERE HASH_TYPE = ?";
      $app['dbs']['mysql_write']->executeUpdate($sql,array($hash_type));

      $actionType = "Hash Type Deletion (Ajax)";
      $actionDescription = "Deleted hash type $hash_type_name";

      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);
    }

    header("Location: /superadmin/hello");
    return $app->json("", 302);
  }

  public function deleteHareType(Request $request, Application $app, int $hare_type) {

    $sql = "SELECT EXISTS(SELECT 1 FROM HARINGS WHERE HARINGS.HARE_TYPE & ? = HARINGS.HARE_TYPE) AS IN_USE";
    $in_use = $app['db']->fetchOne($sql, array($kennel_ky));

    if(!$in_use) {
      $sql = "SELECT HARE_TYPE_NAME FROM HARE_TYPES WHERE HARE_TYPE = ?";
      $hare_type_name = $app['db']->fetchOne($sql, array($hare_type));

      $sql = "DELETE FROM HARE_TYPES WHERE HARE_TYPE = ?";
      $app['dbs']['mysql_write']->executeUpdate($sql,array($hare_type));

      $actionType = "Hare Type Deletion (Ajax)";
      $actionDescription = "Deleted hare type $hare_type_name";

      AdminController::auditTheThings($request, $app, $actionType, $actionDescription);
    }

    header("Location: /superadmin/hello");
    return $app->json("", 302);
  }
}
