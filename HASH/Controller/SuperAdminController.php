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



class SuperAdminController{

  #Define the action
  public function helloAction(Request $request, Application $app){

      #Establish the list of admin users
      $userList = $app['db']->fetchAll("SELECT username, roles FROM USERS ORDER BY username ASC");

      #Establish the list of kennels
      $kennelList = $app['db']->fetchAll("SELECT KENNEL_NAME, KENNEL_DESCRIPTION,
          KENNEL_ABBREVIATION, IN_RECORD_KEEPING, SITE_ADDRESS
          FROM KENNELS ORDER BY IN_RECORD_KEEPING DESC, SITE_ADDRESS DESC");

      #return $app->redirect('/');
      return $app['twig']->render('superadmin_landing.twig', array (
        'pageTitle' => 'This is the super admin landing screen',
        'subTitle1' => 'This is the super admin landing screen',
        'user_list' => $userList,
        'kennel_list' => $kennelList
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

}
