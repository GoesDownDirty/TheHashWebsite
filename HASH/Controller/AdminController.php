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
        'subTitle1' => 'This is the admin landing screen',
    ));
  }

  #Define the action
  public function adminHelloAction(Request $request, Application $app){

      #return $app->redirect('/');
      return $app['twig']->render('admin_landing.twig', array (
        'pageTitle' => 'Site Administration',
        'subTitle1' => 'This is the admin hello landing screen (sub title 1)',
      ));
  }


  #Define the action
  public function userHelloAction(Request $request, Application $app){

      #return $app->redirect('/');
      return $app['twig']->render('admin_landing.twig', array (
        'pageTitle' => 'This is the user hello landing screen (page title)',
        'subTitle1' => 'This is the user hello landing screen (sub title 1)',
      ));
  }

  public function listhashesAction(Request $request, Application $app){

    #Define the SQL to execute
    $sql = "SELECT
      KENNEL_ABBREVIATION,
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
    FROM HASHES JOIN KENNELS on HASHES.KENNEL_KY = KENNELS.KENNEL_KY
    ORDER BY EVENT_DATE DESC";

    #Execute the SQL statement; create an array of rows
    $hashList = $app['db']->fetchAll($sql);

    # Establish and set the return value
    $returnValue = $app['twig']->render('admin_hash_list.twig',array(
      'pageTitle' => 'The List of Hashes',
      'pageSubTitle' => 'The List of *All* Hashes',
      'theList' => $hashList,
      'tableCaption' => 'A list of all hashes ever, since forever.',
      'kennel_abbreviation' => 'XXX'
    ));


    #Return the return value
    return $returnValue;
  }



  #Define the action
  public function listHashersAction(Request $request, Application $app){

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
    $returnValue = $app['twig']->render('admin_hasher_list.twig',array(
      'pageTitle' => 'The List of Hashers',
      'pageSubTitle' => 'The List of *ALL* Hashers',
      'theList' => $hasherList,
      'pageCaption' => "",
      'tableCaption' => ""
    ));

    #Return the return value
    return $returnValue;

  }

  #Define the action
  public function d3testAction(Request $request, Application $app){

    # Establish and set the return value
    $returnValue = $app['twig']->render('d3_test.twig',array(
      'pageTitle' => 'D3 Test Title',
      'pageSubTitle' => 'D3 Test Subtitle',
      'pageCaption' => 'D3 Test Page Caption',
      'tableCaption' => 'D3 Test Table Caption'
    ));

    # Return the return value
    return $returnValue;

  }


  public function newPasswordAction(Request $request, Application $app){


    $formFactoryThing = $app['form.factory']->createBuilder(FormType::class, $data)

      ->add('Current_Password', TextType::class, array('constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 3)))))
      ->add('New_Password_Initial', TextType::class, array('constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 8)))))
      ->add('New_Password_Confirmation', TextType::class, array('constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 8)))));


    $formFactoryThing->add('save', SubmitType::class, array('label' => 'Change your password!'));
    $formFactoryThing->setAction('#');
    $formFactoryThing->setMethod('POST');
    $form=$formFactoryThing->getForm();


    $form->handleRequest($request);

    if($request->getMethod() == 'POST'){

      if ($form->isValid()) {
          #Obtain the name/value pairs from the form
          $data = $form->getData();

          #Establish the values from the form
          $tempCurrentPassword = $data['Current_Password'];
          $tempNewPasswordInitial = $data['New_Password_Initial'];
          $tempNewPasswordConfirmation = $data['New_Password_Confirmation'];

          #Establish the userid value
          $token = $app['security.token_storage']->getToken();
          if (null !== $token) {
            $userid = $token->getUser();
          }

          // find the encoder for a UserInterface instance
          $encoder = $app['security.encoder_factory']->getEncoder($userid);

          // compute the encoded password for the new password
          $encodedNewPassword = $encoder->encodePassword($tempNewPasswordInitial, $userid->getSalt());

          // compute the encoded password for the current password
          $encodedCurrentPassword = $encoder->encodePassword($tempCurrentPassword, $userid->getSalt());



          #Check if the current password is valid
          # Declare the SQL used to retrieve this information
          $sql = "SELECT * FROM USERS WHERE USERNAME = ? AND PASSWORD = ?";

          # Make a database call to obtain the hasher information
          $retrievedUserValue = $app['db']->fetchAssoc($sql, array((string) $userid, (string) $encodedCurrentPassword));
          $sizeOfRetrievedUserValueArray = sizeof($retrievedUserValue);


          # If there are more than one columns, then it is valid
          $foundValidationError=FALSE;
          $validCurrentPassword = FALSE;
          if($sizeOfRetrievedUserValueArray > 1){
            $validCurrentPassword = TRUE;
          }else{
            $app['session']->getFlashBag()->add('danger', 'Wrong! You screwed up your current password.');
            $foundValidationError=TRUE;
          }

          #Check if the initial new password and the confirmation new password match
          $validNewPasswordsMatch = FALSE;
          if($tempNewPasswordInitial == $tempNewPasswordConfirmation){
            $validNewPasswordsMatch = TRUE;
          }else{
            $app['session']->getFlashBag()->add('danger', 'Wrong! The new passwords do not match.');
            $foundValidationError=TRUE;
          }


          #Check if the new password matches password complexity requirements
          $validPasswordComplexity = FALSE;
          if (preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $tempNewPasswordInitial)){
            $validPasswordComplexity = TRUE;
          }else{
            $app['session']->getFlashBag()->add('danger', 'Wrong! Your proposed password is too simple. It must be 8 characters long, contain a lower case letter, an upper case letter, and a special character!');
            $foundValidationError=TRUE;
          }

          if(!$foundValidationError){
            #Define the SQL for the password update
            $updateSql = "UPDATE USERS SET PASSWORD = ? WHERE USERNAME = ?";

            #Run the update SQL
            $app['dbs']['mysql_write']->executeUpdate($updateSql,array($encodedNewPassword,$userid));

            #Show the confirmation message
            $app['session']->getFlashBag()->add('success', 'Success! You updated your password. Probably.');
          }

      } else{
        $app['session']->getFlashBag()->add('danger', 'Wrong! You screwed up.');
      }

    }

    #Establish the userid value
    $token = $app['security.token_storage']->getToken();
    if (null !== $token) {
      $userid = $token->getUser();
    }

    $returnValue = $app['twig']->render('admin_change_password_form.twig', array (
      'pageTitle' => 'Password change',
      'pageHeader' => 'Your new password must contain letters, numbers, an odd number of prime numbers.',
      'form' => $form->createView(),
      'userid' => $userid,
    ));

    #Return the return value
    return $returnValue;

  }




}
