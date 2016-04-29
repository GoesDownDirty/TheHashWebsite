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



class HashPersonController
{


  public function modifyHashPersonAction(Request $request, Application $app, int $hasher_id){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHERS WHERE HASHER_KY = ?";

    # Make a database call to obtain the hasher information
    $hasherValue = $app['db']->fetchAssoc($sql, array((int) $hasher_id));

    $data = array(
        'HASHER_KY' => $hasherValue['HASHER_KY'],
        'HASHER_NAME' => $hasherValue['HASHER_NAME'],
        'HASHER_ABBREVIATION' => $hasherValue['HASHER_ABBREVIATION'],
        'LAST_NAME' => $hasherValue['LAST_NAME'],
        'FIRST_NAME' => $hasherValue['FIRST_NAME'],
        'EMAIL' => $hasherValue['EMAIL'],
        'HOME_KENNEL' => $hasherValue['HOME_KENNEL'],
        'HOME_KENNEL_KY' => $hasherValue['HOME_KENNEL_KY'],
        'DECEASED' => $hasherValue['DECEASED'],
    );

    $formFactoryThing = $app['form.factory']->createBuilder(FormType::class, $data)
      ->add('HASHER_NAME')
      ->add('HASHER_ABBREVIATION')
      ->add('LAST_NAME')
      ->add('FIRST_NAME')
      ->add('EMAIL')
      ->add('HOME_KENNEL')
      ->add('HOME_KENNEL_KY')
      ->add('DECEASED', ChoiceType::class, array('choices'  => array(
        'No' => '0000000000',
        'Yes, let us cherish their memory' => '0000000001',
      )));

    $formFactoryThing->add('save', SubmitType::class, array('label' => 'Submit the form'));
    $formFactoryThing->setAction('#');
    $formFactoryThing->setMethod('POST');
    $form=$formFactoryThing->getForm();


    $form->handleRequest($request);

    if($request->getMethod() == 'POST'){

      if ($form->isValid()) {
          #Obtain the name/value pairs from the form
          $data = $form->getData();


          #Establish the values from the form
          $tempHasherName = $data['HASHER_NAME'];
          $tempHasherAbbreviation = $data['HASHER_ABBREVIATION'];
          $tempLastName = $data['LAST_NAME'];
          $tempFirstName = $data['FIRST_NAME'];
          $tempEmail = $data['EMAIL'];
          $tempHomeKennel = $data['HOME_KENNEL'];
          $tempKennelKy = $data['HOME_KENNEL_KY'];
          $tempDeceased = $data['DECEASED'];

          $sql = "
            UPDATE HASHERS
            SET
              HASHER_NAME= ?, HASHER_ABBREVIATION= ?, LAST_NAME= ?, FIRST_NAME=?,
              EMAIL=?, HOME_KENNEL=?, HOME_KENNEL_KY=?, DECEASED=?
            WHERE HASHER_KY=?";
          $app['dbs']['mysql_write']->executeUpdate($sql,array(
            $tempHasherName,
            $tempHasherAbbreviation,
            $tempLastName,
            $tempFirstName,
            $tempEmail,
            $tempHomeKennel,
            $tempKennelKy,
            $tempDeceased,
            $hasher_id
          ));

          #Add a confirmation that everything worked
          $app['session']->getFlashBag()->add('success', 'Success! You modified the person. They were not good enough as they were, so you made them better.');

      } else{
        $app['session']->getFlashBag()->add('danger', 'Wrong! You broke it.');
      }

    }

    $returnValue = $app['twig']->render('edit_hasher_form.twig', array (
      'pageTitle' => 'Hasher Person Modification',
      'pageHeader' => 'Why is this so complicated ?',
      'form' => $form->createView(),
      'hasherValue' => $hasherValue,
    ));

    #Return the return value
    return $returnValue;

  }

  #Define the action
  public function createHashPersonAction(Request $request, Application $app){

    $formFactoryThing = $app['form.factory']->createBuilder(FormType::class)
      ->add('HASHER_NAME')
      ->add('HASHER_ABBREVIATION')
      ->add('LAST_NAME')
      ->add('FIRST_NAME')
      ->add('EMAIL')
      ->add('HOME_KENNEL')
      ->add('HOME_KENNEL_KY')
      ->add('DECEASED', ChoiceType::class, array('choices'  => array(
        'No' => '0000000000',
        'Yes, let us cherish their memory' => '0000000001',
      )));


    $formFactoryThing->add('save', SubmitType::class, array('label' => 'Submit the form'));
    $formFactoryThing->setAction('#');
    $formFactoryThing->setMethod('POST');
    $form=$formFactoryThing->getForm();


    $form->handleRequest($request);

    if($request->getMethod() == 'POST'){

      if ($form->isValid()) {
          #Obtain the name/value pairs from the form
          $data = $form->getData();

          #Establish the values from the form
          $tempHasherName = $data['HASHER_NAME'];
          $tempHasherAbbreviation = $data['HASHER_ABBREVIATION'];
          $tempLastName = $data['LAST_NAME'];
          $tempFirstName = $data['FIRST_NAME'];
          $tempEmail = $data['EMAIL'];
          $tempHomeKennel = $data['HOME_KENNEL'];
          $tempKennelKy = $data['HOME_KENNEL_KY'];
          $tempDeceased = $data['DECEASED'];


          $sql = "
            INSERT INTO HASHERS (
              HASHER_NAME,
              HASHER_ABBREVIATION,
              LAST_NAME,
              FIRST_NAME,
              EMAIL,
              HOME_KENNEL,
              HOME_KENNEL_KY,
              DECEASED
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


          $app['dbs']['mysql_write']->executeUpdate($sql,array(
            $tempHasherName,
            $tempHasherAbbreviation,
            $tempLastName,
            $tempFirstName,
            $tempEmail,
            $tempHomeKennel,
            $tempKennelKy,
            $tempDeceased
          ));


          #Add a confirmation that everything worked
          $app['session']->getFlashBag()->add('success', 'Success! You created a person.');

      } else{
        $app['session']->getFlashBag()->add('danger', 'Wrong! You broke it.');
      }

    }

    $returnValue = $app['twig']->render('new_hasher_form.twig', array (
      'pageTitle' => 'Hasher Person Creation',
      'pageHeader' => 'Why is this so complicated ?',
      'form' => $form->createView(),
    ));

    #Return the return value
    return $returnValue;

  }

}
