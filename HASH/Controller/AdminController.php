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



class AdminController
{

  public static $stateDropdownArray = array(
      'Ohio' => 'OH',
      'Alabama' => 'AL',
      'Alaska' => 'AK',
      'Arizona' => 'AZ',
      'Arkansas' => 'AR',
      'Colorado' => 'CO',
      'California' => 'CA',
      'Connecticut' => 'CT',
      'Delaware' => 'DE',
      'District Of Columbia' => 'DC',
      'Florida' => 'FL',
      'Georgia' => 'GA',
      'Hawaii' => 'HI',
      'Idaho' => 'ID',
      'Illinois' => 'IL',
      'Indiana' => 'IN',
      'Iowa' => 'IA',
      'Kansas' => 'KS',
      'Kentucky' => 'KY',
      'Louisiana' => 'LA',
      'Maine' => 'ME',
      'Maryland' => 'MD',
      'Massachusetts' => 'MA',
      'Michigan' => 'MI',
      'Minnesota' => 'MN',
      'Mississippi' => 'MS',
      'Missouri' => 'MO',
      'Montana' => 'MT',
      'Nebraska' => 'NE',
      'Nevada' => 'NV',
      'New Hampshire' => 'NH',
      'New Jersey' => 'NJ',
      'New Mexico' => 'NM',
      'New York' => 'NY',
      'North Carolina' => 'NC',
      'North Dakota' => 'ND',
      'Oklahoma' => 'OK',
      'Oregon' => 'OR',
      'Pennsylvania' => 'PA',
      'Rhode Island' => 'RI',
      'South Carolina' => 'SC',
      'South Dakota' => 'SD',
      'Tennessee' => 'TN',
      'Texas' => 'TX',
      'Utah' => 'UT',
      'Vermont' => 'VT',
      'Virginia' => 'VA',
      'Washington' => 'WA',
      'West Virginia' => 'WV',
      'Wisconsin' => 'WI',
      'Wyoming' => 'WY'
  );

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

  public function adminModifyHashAction(Request $request, Application $app, int $hash_id){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHES WHERE HASH_KY = ?";

    # Make a database call to obtain the hasher information
    $hashValue = $app['db']->fetchAssoc($sql, array((int) $hash_id));

    $data = array(
        'Hash_KY' => $hashValue['HASH_KY'],
        'Kennel_KY' => $hashValue['KENNEL_KY'],
        'Kennel_Event_Number' => $hashValue['KENNEL_EVENT_NUMBER'],
        'Event_Date' => $hashValue['EVENT_DATE'],
        'Event_Location' => $hashValue['EVENT_LOCATION'],
        'Event_City' => $hashValue['EVENT_CITY'],
        'Event_State' => $hashValue['EVENT_STATE'],
        'Special_Event_Description' => $hashValue['SPECIAL_EVENT_DESCRIPTION'],
        'Virgin_Count' => $hashValue['VIRGIN_COUNT'],
        'Is_Hyper' => $hashValue['IS_HYPER'],
    );

    $formFactoryThing = $app['form.factory']->createBuilder(FormType::class, $data)
      ->add('Kennel_KY', TextType::class, array(
              'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
          ))
      ->add('Hash_KY')
      ->add('Kennel_KY')
      ->add('Kennel_Event_Number')
      ->add('Event_Date')
      ->add('Event_Location')
      ->add('Event_City', TextType::class, array(
              'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
          ))
      #->add('Event_State')


      ->add('Event_State', ChoiceType::class, array(
        'choices' => self::$stateDropdownArray,
        'preferred_choices' => array('OH','KY','IN'),
    ))

      ->add('Special_Event_Description')
      ->add('Virgin_Count', ChoiceType::class, array('choices'  => array(
        '0'=>'0000000000',
        '1'=>'0000000001',
        '2'=>'0000000002',
        '3'=>'0000000003',
        '4'=>'0000000004',
        '5'=>'0000000005',
        '6'=>'0000000006',
        '7'=>'0000000007',
        '8'=>'0000000008',
        '9'=>'0000000009')))
      ->add('Is_Hyper', ChoiceType::class, array('choices'  => array(
        'Yes' => '0000000001',
        'No' => '0000000000',
      ),
      ));

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
          $tempEventDate = $data['Event_Date'];
          $tempEventLocation = $data['Event_Location'];
          $tempEventCity = $data['Event_City'];
          $tempEventState = $data['Event_State'];
          $tempSpecialEventDescription = $data['Special_Event_Description'];
          $tempVirginCount = $data['Virgin_Count'];
          $tempIsHyper = $data['Is_Hyper'];

          $sql = "
            UPDATE HASHES
            SET
              EVENT_DATE= ?, EVENT_LOCATION= ?, EVENT_CITY= ?, EVENT_STATE=?,
              SPECIAL_EVENT_DESCRIPTION=?, VIRGIN_COUNT=?, IS_HYPER=?
            WHERE HASH_KY=?";
          $app['db']->executeUpdate($sql,array(
            $tempEventDate,
            $tempEventLocation,
            $tempEventCity,
            $tempEventState,
            $tempSpecialEventDescription,
            $tempVirginCount,
            $tempIsHyper,
            $hash_id
          ));

          #Add a confirmation that everything worked
          $app['session']->getFlashBag()->add('success', 'Success! You modified the event.');

      } else{
        $app['session']->getFlashBag()->add('danger', 'Wrong! You broke it.');
      }

    }

    $returnValue = $app['twig']->render('edit_hash_form.twig', array (
      'pageTitle' => 'Hash Event Modification',
      'pageHeader' => 'Why is this so complicated ?',
      'form' => $form->createView(),
      'hashValue' => $hashValue,
    ));

    #Return the return value
    return $returnValue;

  }

  #Define the action
  public function adminCreateHashAction(Request $request, Application $app){

    $tempDateTime = new \DateTime();
    $tempDateTime->setTime(16,0,0);

    $formFactoryThing = $app['form.factory']->createBuilder(FormType::class)
      ->add('Kennel_KY', TextType::class, array(
              'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
          ))
      ->add('Hash_KY')
      ->add('Kennel_Event_Number')
      ->add('Event_Date', DatetimeType::class,array(
        'data' => $tempDateTime
        ))
      ->add('Event_Location')
      ->add('Event_City', TextType::class, array(
              'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
          ))


      ->add('Event_State', ChoiceType::class, array(
        'choices' => self::$stateDropdownArray,
        'preferred_choices' => array('OH','KY','IN'),
    ))

      ->add('Special_Event_Description')
      ->add('Virgin_Count', ChoiceType::class, array('choices'  => array(
        '0'=>'0000000000',
        '1'=>'0000000001',
        '2'=>'0000000002',
        '3'=>'0000000003',
        '4'=>'0000000004',
        '5'=>'0000000005',
        '6'=>'0000000006',
        '7'=>'0000000007',
        '8'=>'0000000008',
        '9'=>'0000000009')))
      ->add('Is_Hyper', ChoiceType::class, array('choices'  => array(
        'No' => '0000000000',
        'Yes' => '0000000001',
      ),
      ));


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
          $tempKennelKy = $data['Kennel_KY'];
          $tempEventDate = $data['Event_Date'];
          $tempEventDateFormatted = date_format($tempEventDate, 'Y-m-d H:i:s');
          $tempEventLocation = $data['Event_Location'];
          $tempKennelEventNumber = $data['Kennel_Event_Number'];
          $tempEventCity = $data['Event_City'];
          $tempEventState = $data['Event_State'];
          $tempSpecialEventDescription = $data['Special_Event_Description'];
          $tempVirginCount = $data['Virgin_Count'];
          $tempIsHyper = $data['Is_Hyper'];

          print "tempKennelKy $tempKennelKy <br>";
          print "tempKennelEventNumber = $tempKennelEventNumber <br>";
          print "tempEventDateFormatted = $tempEventDateFormatted <br>";
          print "tempEventLocation $tempEventLocation <br>";
          print "tempEventCity $tempEventCity <br>";
          print "tempEventState $tempEventState <br>";
          print "tempSpecialEventDescription $tempSpecialEventDescription <br>";
          print "tempVirginCount $tempVirginCount <br>";
          print "tempIsHyper $tempIsHyper <br>";

          $sql = "
            INSERT INTO HASHES (
              KENNEL_KY,
              KENNEL_EVENT_NUMBER,
              EVENT_DATE,
              EVENT_LOCATION,
              EVENT_CITY,
              EVENT_STATE,
              SPECIAL_EVENT_DESCRIPTION,
              VIRGIN_COUNT,
              IS_HYPER
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";


          $app['db']->executeUpdate($sql,array(
            $tempKennelKy,
            $tempKennelEventNumber,
            $tempEventDateFormatted,
            $tempEventLocation,
            $tempEventCity,
            $tempEventState,
            $tempSpecialEventDescription,
            $tempVirginCount,
            $tempIsHyper
          ));


          #Add a confirmation that everything worked
          $app['session']->getFlashBag()->add('success', 'Success! You created the event.');

      } else{
        $app['session']->getFlashBag()->add('danger', 'Wrong! You broke it.');
      }

    }

    $returnValue = $app['twig']->render('new_hash_form.twig', array (
      'pageTitle' => 'Hash Event Creation',
      'pageHeader' => 'Why is this so complicated ?',
      'form' => $form->createView(),
    ));

    #Return the return value
    return $returnValue;

  }



  #Define the action
  public function userHelloAction(Request $request, Application $app){

      #return $app->redirect('/');
      return $app['twig']->render('admin_landing.twig', array (
        'pageTitle' => 'This is the user hello landing screen (page title)',
        'subTitle1' => 'This is the user hello landing screen (sub title 1)'));
  }


}
