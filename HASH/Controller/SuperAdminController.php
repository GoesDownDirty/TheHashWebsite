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

      #return $app->redirect('/');
      return $app['twig']->render('superadmin_landing.twig', array (
        'pageTitle' => 'This is the super admin landing screen',
        'subTitle1' => 'This is the super admin landing screen',
      ));
  }

}
