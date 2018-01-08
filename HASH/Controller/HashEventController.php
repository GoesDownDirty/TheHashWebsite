<?php

namespace HASH\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;



class HashEventController
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

  public function adminModifyHashAction(Request $request, Application $app, int $hash_id){

    # Declare the SQL used to retrieve this information
    $sql = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

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

    #Obtain list of kennels
    $kennelsSQL = "SELECT KENNEL_KY, KENNEL_ABBREVIATION  FROM KENNELS WHERE IN_RECORD_KEEPING = 1";

    #Execute the SQL statement; create an array of rows
    $kennelList = $app['db']->fetchAll($kennelsSQL);

    #Convert kennel list to the appropriate format for a dropdown menu
    $kennelDropdown = array();
    foreach ($kennelList as $kennelValue){
      $tempKennelAbbreviation = $kennelValue['KENNEL_ABBREVIATION'];
      $tempKennelKey = $kennelValue['KENNEL_KY'];
      $kennelDropdown[$tempKennelAbbreviation] = $tempKennelKey;
    }

    $formFactoryThing = $app['form.factory']->createBuilder(FormType::class, $data)
      #->add('Kennel_KY', TextType::class, array(
      #        'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
      #    ))

      ->add('Kennel_KY', ChoiceType::class, array(
        'choices' => array($kennelDropdown)
      ))

      ->add('Hash_KY')
      #->add('Kennel_KY')
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
          $tempKennelKy = $data['Kennel_KY'];

          $sql = "
            UPDATE HASHES
            SET
              EVENT_DATE= ?, EVENT_LOCATION= ?, EVENT_CITY= ?, EVENT_STATE=?,
              SPECIAL_EVENT_DESCRIPTION=?, VIRGIN_COUNT=?, IS_HYPER=?, KENNEL_KY=?
            WHERE HASH_KY=?";
          $app['dbs']['mysql_write']->executeUpdate($sql,array(
            $tempEventDate,
            $tempEventLocation,
            $tempEventCity,
            $tempEventState,
            $tempSpecialEventDescription,
            $tempVirginCount,
            $tempIsHyper,
            $tempKennelKy,
            $hash_id
          ));

          #Audit this activity
          $actionType = "Event Modification";
          $tempKennelEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
          $tempKennelAbbrevation = $hashValue['KENNEL_ABBREVIATION'];
          $actionDescription = "Modified event ($tempKennelAbbrevation # $tempKennelEventNumber)";
          AdminController::auditTheThings($request, $app, $actionType, $actionDescription);

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

  #Define action
  public function adminCreateHashAjaxPreAction(Request $request, Application $app){

    #Obtain list of kennels
    $kennelsSQL = "SELECT KENNEL_KY, KENNEL_ABBREVIATION  FROM KENNELS WHERE IN_RECORD_KEEPING = 1";

    #Execute the SQL statement; create an array of rows
    $kennelList = $app['db']->fetchAll($kennelsSQL);

    #Convert kennel list to the appropriate format for a dropdown menu
    $kennelDropdown = array();
    foreach ($kennelList as $kennelValue){
      $tempKennelAbbreviation = $kennelValue['KENNEL_ABBREVIATION'];
      $tempKennelKey = $kennelValue['KENNEL_KY'];
      $kennelDropdown[$tempKennelAbbreviation] = $tempKennelKey;
    }

    $returnValue = $app['twig']->render('new_hash_form_ajax.twig', array(
      'pageTitle' => 'Create an Event!',
      'pageHeader' => 'Page Header',
      'kennelList' => $kennelDropdown,
      'geocode_api_value' => GOOGLE_PLACES_API_WEB_SERVICE_KEY
    ));

    #Return the return value
    return $returnValue;

  }

    public function adminCreateHashAjaxPostAction(Request $request, Application $app){

      #Establish the return message
      $returnMessage = "This has not been set yet...";

      #Obtain list of kennels
      $kennelsSQL = "SELECT KENNEL_KY, KENNEL_ABBREVIATION  FROM KENNELS WHERE IN_RECORD_KEEPING = 1";

      #Execute the SQL statement; create an array of rows
      $kennelList = $app['db']->fetchAll($kennelsSQL);

      $theKennel = trim(strip_tags($request->request->get('kennelName')));
      $theHashEventNumber = trim(strip_tags($request->request->get('hashEventNumber')));
      $theHashEventDescription = trim(strip_tags($request->request->get('hashEventDescription')));
      $theHyperIndicator= trim(strip_tags($request->request->get('hyperIndicator')));
      $theEventDate= trim(strip_tags($request->request->get('eventDate')));
      $theEventTime= trim(strip_tags($request->request->get('eventTime')));
      $theEventDateAndTime = $theEventDate." ".$theEventTime;
      $theLocationDescription= trim(strip_tags($request->request->get('locationDescription')));
      $theStreet_number= trim(strip_tags($request->request->get('street_number')));
      $theRoute= trim(strip_tags($request->request->get('route')));
      $theLocality= trim(strip_tags($request->request->get('locality')));
      $theAdministrative_area_level_1= trim(strip_tags($request->request->get('administrative_area_level_1')));
      $theAdministrative_area_level_2= trim(strip_tags($request->request->get('administrative_area_level_2')));
      $thePostal_code= trim(strip_tags($request->request->get('postal_code')));
      $theNeighborhood= trim(strip_tags($request->request->get('neighborhood')));
      $theCountry= trim(strip_tags($request->request->get('country')));
      $theLat= trim(strip_tags($request->request->get('lat')));
      $theLng= trim(strip_tags($request->request->get('lng')));
      $theFormatted_address= trim(strip_tags($request->request->get('formatted_address')));
      $thePlace_id= trim(strip_tags($request->request->get('place_id')));

      // Establish a "passed validation" variable
      $passedValidation = TRUE;

      // Establish the return message value as empty (at first)
      $returnMessage = "";

      if(!is_numeric($theKennel)){
        $passedValidation = FALSE;
        //$app['monolog']->addDebug("--- theKennel failed validation: $theKennel");
        $returnMessage .= " |Failed validation on the kennel";
      }

      if(!(is_numeric($theLat)||empty($theLat))){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the lat";
        //$app['monolog']->addDebug("--- theLat failed validation: $theLat");
      }

      if(!(is_numeric($theLng)||empty($theLng))){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the lng";
        //$app['monolog']->addDebug("--- theLng failed validation: $theLng");
      }

      if(!(is_numeric($thePostal_code)||empty($thePostal_code))){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the postal code";
        //$app['monolog']->addDebug("--- thePostal_code failed validation: $thePostal_code");
      }

      if(!is_numeric($theLat)){
        $theLat = NULL;
      }

      if(!is_numeric($theLng)){
        $theLng = NULL;
      }

      // Ensure the following is a date
      // $theEventDate
      if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$theEventDate)){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the event date";
        //$app['monolog']->addDebug("--- the date failed validation $theEventDate");
      }


      // Ensure the following is a time
      // $theEventTime
      if (!preg_match("/^([01]\d|2[0-3]):([0-5][0-9]):([0-5][0-9])$/",$theEventTime)){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the event time";
        //$app['monolog']->addDebug("--- the time failed validation $theEventTime");

      }


      if($passedValidation){

        $sql = "
          INSERT INTO HASHES (
            KENNEL_KY,
            KENNEL_EVENT_NUMBER,
            EVENT_DATE,
            EVENT_LOCATION,
            EVENT_CITY,
            EVENT_STATE,
            SPECIAL_EVENT_DESCRIPTION,
            IS_HYPER,
            STREET_NUMBER,
            ROUTE,
            COUNTY,
            POSTAL_CODE,
            NEIGHBORHOOD,
            COUNTRY,
            FORMATTED_ADDRESS,
            PLACE_ID,
            LAT,
            LNG
          ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";





          $app['dbs']['mysql_write']->executeUpdate($sql,array(
            $theKennel,
            $theHashEventNumber,
            $theEventDateAndTime,
            $theLocationDescription,
            $theLocality,
            $theAdministrative_area_level_1,
            $theHashEventDescription,
            $theHyperIndicator,
            $theStreet_number,
            $theRoute,
            $theAdministrative_area_level_2,
            $thePostal_code,
            $theNeighborhood,
            $theCountry,
            $theFormatted_address,
            $thePlace_id,
            $theLat,
            $theLng
          ));



        #Audit this activity
        $actionType = "Event Creation (Ajax)";
        $tempKennelAbbreviation2 = "Unknown";
        foreach ($kennelList as $kennelValue){
          if($kennelValue['KENNEL_KY'] == $theKennel){
            $tempKennelAbbreviation2 = $kennelValue['KENNEL_ABBREVIATION'];
          }
        }
        $actionDescription = "Created event ($tempKennelAbbreviation2 # $theHashEventNumber)";
        AdminController::auditTheThings($request, $app, $actionType, $actionDescription);


        // Establish the return value message
        $returnMessage = "Success! Great, it worked";

      }

      #Set the return value
      $returnValue =  $app->json($returnMessage, 200);
      return $returnValue;
    }













    #Define action
    public function adminModifyHashAjaxPreAction(Request $request, Application $app, int $hash_id){

      #Obtain list of kennels
      $kennelsSQL = "SELECT KENNEL_KY, KENNEL_ABBREVIATION  FROM KENNELS WHERE IN_RECORD_KEEPING = 1";

      #Execute the SQL statement; create an array of rows
      $kennelList = $app['db']->fetchAll($kennelsSQL);

      # Declare the SQL used to retrieve this information
      $sql = "SELECT * ,date_format(event_date, '%Y-%m-%d' ) AS EVENT_DATE_DATE, date_format(event_date, '%k:%i:%S') AS EVENT_DATE_TIME FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

      # Make a database call to obtain the hasher information
      $hashValue = $app['db']->fetchAssoc($sql, array((int) $hash_id));

      #Convert kennel list to the appropriate format for a dropdown menu
      $kennelDropdown = array();
      foreach ($kennelList as $kennelValue){
        $tempKennelAbbreviation = $kennelValue['KENNEL_ABBREVIATION'];
        $tempKennelKey = $kennelValue['KENNEL_KY'];
        $kennelDropdown[$tempKennelAbbreviation] = $tempKennelKey;
      }

      $returnValue = $app['twig']->render('edit_hash_form_ajax.twig', array(
        'pageTitle' => 'Modify an Event!',
        'pageHeader' => 'Page Header',
        'kennelList' => $kennelDropdown,
        'geocode_api_value' => GOOGLE_PLACES_API_WEB_SERVICE_KEY,
        'hashValue' => $hashValue,
        'hashKey' => $hash_id
      ));

      #Return the return value
      return $returnValue;

    }


    public function adminModifyHashAjaxPostAction(Request $request, Application $app, int $hash_id){

      #Establish the return message
      $returnMessage = "This has not been set yet...";

      #Obtain list of kennels
      $kennelsSQL = "SELECT KENNEL_KY, KENNEL_ABBREVIATION  FROM KENNELS WHERE IN_RECORD_KEEPING = 1";

      #Execute the SQL statement; create an array of rows
      $kennelList = $app['db']->fetchAll($kennelsSQL);

      $theKennel = trim(strip_tags($request->request->get('kennelName')));
      //$theHashEventNumber = trim(strip_tags($request->request->get('hashEventNumber')));
      $theHashEventDescription = trim(strip_tags($request->request->get('hashEventDescription')));
      $theHyperIndicator= trim(strip_tags($request->request->get('hyperIndicator')));
      $theEventDate= trim(strip_tags($request->request->get('eventDate')));
      $theEventTime= trim(strip_tags($request->request->get('eventTime')));
      $theEventDateAndTime = $theEventDate." ".$theEventTime;
      $theLocationDescription= trim(strip_tags($request->request->get('locationDescription')));
      $theStreet_number= trim(strip_tags($request->request->get('street_number')));
      $theRoute= trim(strip_tags($request->request->get('route')));
      $theLocality= trim(strip_tags($request->request->get('locality')));
      $theAdministrative_area_level_1= trim(strip_tags($request->request->get('administrative_area_level_1')));
      $theAdministrative_area_level_2= trim(strip_tags($request->request->get('administrative_area_level_2')));
      $thePostal_code= trim(strip_tags($request->request->get('postal_code')));
      $theNeighborhood= trim(strip_tags($request->request->get('neighborhood')));
      $theCountry= trim(strip_tags($request->request->get('country')));
      $theLat= trim(strip_tags($request->request->get('lat')));
      $theLng= trim(strip_tags($request->request->get('lng')));
      $theFormatted_address= trim(strip_tags($request->request->get('formatted_address')));
      $thePlace_id= trim(strip_tags($request->request->get('place_id')));
      //$app['monolog']->addDebug("--- thePlace_id: $thePlace_id");

      // Establish a "passed validation" variable
      $passedValidation = TRUE;

      // Establish the return message value as empty (at first)
      $returnMessage = "";

      if(!is_numeric($theKennel)){
        $passedValidation = FALSE;
        //$app['monolog']->addDebug("--- theKennel failed validation: $theKennel");
        $returnMessage .= " |Failed validation on the kennel";
      }

      if(!(is_numeric($theLat)||empty($theLat))){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the lat";
        //$app['monolog']->addDebug("--- theLat failed validation: $theLat");
      }

      if(!(is_numeric($theLng)||empty($theLng))){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the lng";
        //$app['monolog']->addDebug("--- theLng failed validation: $theLng");
      }

      if(!(is_numeric($thePostal_code)||empty($thePostal_code))){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the postal code";
        //$app['monolog']->addDebug("--- thePostal_code failed validation: $thePostal_code");
      }

      if(!is_numeric($theLat)){
        $theLat = NULL;
      }

      if(!is_numeric($theLng)){
        $theLng = NULL;
      }

      // Ensure the following is a date
      // $theEventDate
      if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$theEventDate)){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the event date";
        //$app['monolog']->addDebug("--- the date failed validation $theEventDate");
      }


      // Ensure the following is a time
      // $theEventTime
      if (!preg_match("/^([01]\d|2[0-3]):([0-5][0-9]):([0-5][0-9])$/",$theEventTime)){
        $passedValidation = FALSE;
        $returnMessage .= " |Failed validation on the event time";
        //$app['monolog']->addDebug("--- the time failed validation $theEventTime");

      }


      if($passedValidation){

        $sql = "
          UPDATE HASHES
            SET
              KENNEL_KY = ?,
              EVENT_DATE = ?,
              EVENT_LOCATION = ?,
              EVENT_CITY = ?,
              EVENT_STATE = ?,
              SPECIAL_EVENT_DESCRIPTION = ?,
              IS_HYPER = ?,
              STREET_NUMBER = ?,
              ROUTE = ?,
              COUNTY = ?,
              POSTAL_CODE = ?,
              NEIGHBORHOOD = ?,
              COUNTRY = ?,
              FORMATTED_ADDRESS = ?,
              PLACE_ID = ?,
              LAT = ?,
              LNG = ?
           WHERE HASH_KY = ?";

          $app['dbs']['mysql_write']->executeUpdate($sql,array(
            $theKennel,
            $theEventDateAndTime,
            $theLocationDescription,
            $theLocality,
            $theAdministrative_area_level_1,
            $theHashEventDescription,
            $theHyperIndicator,
            $theStreet_number,
            $theRoute,
            $theAdministrative_area_level_2,
            $thePostal_code,
            $theNeighborhood,
            $theCountry,
            $theFormatted_address,
            $thePlace_id,
            $theLat,
            $theLng,
            $hash_id
          ));

          # Declare the SQL used to retrieve this information
          $sqlOriginal = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

          # Make a database call to obtain the hasher information
          $hashValue = $app['db']->fetchAssoc($sqlOriginal, array((int) $hash_id));

        #Audit this activity
        $tempEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
        $actionType = "Event Modification (Ajax)";
        $tempKennelAbbreviation2 = "Unknown";
        foreach ($kennelList as $kennelValue){
          if($kennelValue['KENNEL_KY'] == $theKennel){
            $tempKennelAbbreviation2 = $kennelValue['KENNEL_ABBREVIATION'];
          }
        }
        $actionDescription = "Modified event ($tempKennelAbbreviation2 # $tempEventNumber)";
        AdminController::auditTheThings($request, $app, $actionType, $actionDescription);


        // Establish the return value message
        $returnMessage = "Success! Great, it worked";

      }

      #Set the return value
      $returnValue =  $app->json($returnMessage, 200);
      return $returnValue;
    }

  #Define the action
  public function adminCreateHashAction(Request $request, Application $app){


    #Obtain list of kennels
    $kennelsSQL = "SELECT KENNEL_KY, KENNEL_ABBREVIATION  FROM KENNELS WHERE IN_RECORD_KEEPING = 1";

    #Execute the SQL statement; create an array of rows
    $kennelList = $app['db']->fetchAll($kennelsSQL);

    #Convert kennel list to the appropriate format for a dropdown menu
    $kennelDropdown = array();
    foreach ($kennelList as $kennelValue){
      $tempKennelAbbreviation = $kennelValue['KENNEL_ABBREVIATION'];
      $tempKennelKey = $kennelValue['KENNEL_KY'];
      $kennelDropdown[$tempKennelAbbreviation] = $tempKennelKey;
    }

    $tempDateTime = new \DateTime();
    $tempDateTime->setTime(16,0,0);

    $formFactoryThing = $app['form.factory']->createBuilder(FormType::class)
      #->add('Kennel_KY', TextType::class, array(
      #        'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 1)))
      #    ))


      ->add('Kennel_KY', ChoiceType::class, array(
        'choices' => array($kennelDropdown)
      ))
      ->add('Hash_KY')
      ->add('Kennel_Event_Number')
      ->add('Event_Date', DatetimeType::class,array(
        'data' => $tempDateTime,
        'years' => range(Date('Y'), 1980),
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
          $tempVirginCount = 0;
          $tempIsHyper = $data['Is_Hyper'];



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


          $app['dbs']['mysql_write']->executeUpdate($sql,array(
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

          #Audit this activity
          $actionType = "Event Creation";
          $tempKennelAbbreviation2 = "Unknown";
          foreach ($kennelList as $kennelValue){
            if($kennelValue['KENNEL_KY'] == $tempKennelKy){
              $tempKennelAbbreviation2 = $kennelValue['KENNEL_ABBREVIATION'];
            }
          }
          $actionDescription = "Created event ($tempKennelAbbreviation2 # $tempKennelEventNumber)";
          AdminController::auditTheThings($request, $app, $actionType, $actionDescription);


          #Add a confirmation that everything worked
          $theSuccessMessage = "Success! You created the event. (Number $tempKennelEventNumber)";
          $app['session']->getFlashBag()->add('success', $theSuccessMessage);

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




    public function hashParticipationJsonPreAction(Request $request, Application $app, int $hash_id){


      #Define the SQL to execute
      $hasherListSQL = "SELECT *
        FROM HASHINGS
        JOIN HASHERS ON HASHINGS.HASHER_KY = HASHERS.HASHER_KY
        WHERE HASHINGS.HASH_KY = ? ";

      $hareListSQL = "SELECT *
        FROM HARINGS
        JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
        WHERE HARINGS.HARINGS_HASH_KY = ?";


      #Obtain hash event information
      $hashEventInfoSQL = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

      #Execute the SQL statement; create an array of rows
      $hasherList = $app['db']->fetchAll($hasherListSQL,array((int)$hash_id));
      $hareList = $app['db']->fetchAll($hareListSQL,array((int)$hash_id));
      $hashEvent = $app['db']->fetchAssoc($hashEventInfoSQL,array((int)$hash_id));

      $kennelAbbreviation = $hashEvent['KENNEL_ABBREVIATION'];
      $kennelEventNumber = $hashEvent['KENNEL_EVENT_NUMBER'];
      $eventDate = $hashEvent['EVENT_DATE'];
      $pageTitle = "Participation: $kennelAbbreviation # $kennelEventNumber ($eventDate)";

      #Establish the return value
      $returnValue = $app['twig']->render('event_participation_json.twig', array (
        'pageTitle' => $pageTitle,
        'pageSubTitle' => 'Not Sure',
        'pageHeader' => 'Why is this so complicated ?',
        'hasherList' => $hasherList,
        'hareList' => $hareList,
        'hash_key'=> $hash_id,
        'kennel_abbreviation' => $kennelAbbreviation,
        'kennel_event_number' => $kennelEventNumber
      ));

      #Return the return value
      return $returnValue;

    }

    #Test function
    public function addHashParticipant (Request $request, Application $app){

      #Establish the return message
      $returnMessage = "This has not been set yet...";

      #Obtain the post values
      $hasherKey = $request->request->get('hasher_key');
      $hashKey = $request->request->get('hash_key');

      #Validate the post values; ensure that they are both numbers
      if(ctype_digit($hasherKey)  && ctype_digit($hashKey)){

        #Determine the hasher identity
        $hasherIdentitySql = "SELECT * FROM HASHERS WHERE HASHERS.HASHER_KY = ? ;";

        # Make a database call to obtain the hasher information
        $hasherValue = $app['db']->fetchAssoc($hasherIdentitySql, array((int) $hasherKey));

        #Obtain the object from the database results
        $data = array(
            'HASHER_KY' => $hasherValue['HASHER_KY'],
            'HASHER_NAME' => $hasherValue['HASHER_NAME'],
            'HASHER_ABBREVIATION' => $hasherValue['HASHER_ABBREVIATION'],
            'LAST_NAME' => $hasherValue['LAST_NAME'],
            'FIRST_NAME' => $hasherValue['FIRST_NAME'],
            'HOME_KENNEL' => $hasherValue['HOME_KENNEL'],
            'HOME_KENNEL_KY' => $hasherValue['HOME_KENNEL_KY'],
            'DECEASED' => $hasherValue['DECEASED'],
        );

        #Obtain the hasher name from the object
        $tempHasherName = $data['HASHER_NAME'];

        #Ensure the entry does not already exist
        $existsSql = "SELECT HASHER_NAME
          FROM HASHINGS
          JOIN HASHERS ON HASHERS.HASHER_KY = HASHINGS.HASHER_KY
          WHERE HASHERS.HASHER_KY = ? AND HASH_KY = ?;";

        #Retrieve the existing record
        $hasherToAdd = $app['db']->fetchAll($existsSql,array((int)$hasherKey,(int)$hashKey));
        if(count($hasherToAdd) < 1){

          #Define the sql insert statement
          $sql = "INSERT INTO HASHINGS (HASHER_KY, HASH_KY) VALUES (?, ?);";

          #Execute the sql insert statement
          $app['dbs']['mysql_write']->executeUpdate($sql,array($hasherKey,$hashKey));

          #Audit the activity

          # Declare the SQL used to retrieve this information
          $sql = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

          # Make a database call to obtain the hasher information
          $hashValue = $app['db']->fetchAssoc($sql, array((int) $hashKey));
          $tempKennelEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
          $tempKennelAbbreviation = $hashValue['KENNEL_ABBREVIATION'];

          $tempActionType = "Add Hound to Hash";
          $tempActionDescription = "Added $tempHasherName to $tempKennelAbbreviation # $tempKennelEventNumber";
          AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

          #Set the return message
          $returnMessage = "Success! $tempHasherName has been added as a hound.";
        } else {

          #Set the return message
          $returnMessage = "$tempHasherName has already added as a hound.";
        }

      } else{
        $returnMessage = "Something is wrong with the input.$hasherKey and $hashKey";
      }

      #Set the return value
      $returnValue =  $app->json($returnMessage, 200);
      return $returnValue;
    }

    #Test function
    public function addHashOrganizer (Request $request, Application $app){

      #Establish the return message
      $returnMessage = "This has not been set yet...";

      #Obtain the post values
      $hasherKey = $request->request->get('hasher_key');
      $hashKey = $request->request->get('hash_key');

      #Validate the post values; ensure that they are both numbers
      if(ctype_digit($hasherKey)  && ctype_digit($hashKey)){

        #Determine the hasher identity
        $hasherIdentitySql = "SELECT * FROM HASHERS WHERE HASHERS.HASHER_KY = ? ;";

        # Make a database call to obtain the hasher information
        $hasherValue = $app['db']->fetchAssoc($hasherIdentitySql, array((int) $hasherKey));

        #Obtain the object from the database results
        $data = array(
            'HASHER_KY' => $hasherValue['HASHER_KY'],
            'HASHER_NAME' => $hasherValue['HASHER_NAME'],
            'HASHER_ABBREVIATION' => $hasherValue['HASHER_ABBREVIATION'],
            'LAST_NAME' => $hasherValue['LAST_NAME'],
            'FIRST_NAME' => $hasherValue['FIRST_NAME'],
            'HOME_KENNEL' => $hasherValue['HOME_KENNEL'],
            'HOME_KENNEL_KY' => $hasherValue['HOME_KENNEL_KY'],
            'DECEASED' => $hasherValue['DECEASED'],
        );

        #Obtain the hasher name from the object
        $tempHasherName = $data['HASHER_NAME'];

        #Ensure the entry does not already exist
        $existsSql = "SELECT HASHER_NAME
          FROM HARINGS
          JOIN HASHERS ON HASHERS.HASHER_KY = HARINGS.HARINGS_HASHER_KY
          WHERE HASHERS.HASHER_KY = ? AND HARINGS.HARINGS_HASH_KY = ?;";

        #Retrieve the existing record
        $hareToAdd = $app['db']->fetchAll($existsSql,array((int)$hasherKey,(int)$hashKey));
        if(count($hareToAdd) < 1){

          #Define the sql insert statement
          $sql = "INSERT INTO HARINGS (HARINGS_HASHER_KY, HARINGS_HASH_KY) VALUES (?, ?);";

          #Execute the sql insert statement
          $app['dbs']['mysql_write']->executeUpdate($sql,array($hasherKey,$hashKey));

          #Add the audit statement
          # Declare the SQL used to retrieve this information
          $sql = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

          # Make a database call to obtain the hasher information
          $hashValue = $app['db']->fetchAssoc($sql, array((int) $hashKey));
          $tempKennelEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
          $tempKennelAbbreviation = $hashValue['KENNEL_ABBREVIATION'];

          $tempActionType = "Add Hare to Hash";
          $tempActionDescription = "Added $tempHasherName to $tempKennelAbbreviation # $tempKennelEventNumber";
          AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

          #Set the return message
          $returnMessage = "Success! $tempHasherName has been added as a hare.";

        } else {

          #Set the return message
          $returnMessage = "$tempHasherName has already added as a hare.";

        }

      } else{
        $returnMessage = "Something is wrong with the input.$hasherKey and $hashKey";
      }

      #Set the return value
      $returnValue =  $app->json($returnMessage, 200);
      return $returnValue;
    }


    #Delete a participant from a hash
    public function deleteHashParticipant (Request $request, Application $app){

      #Establish the return message
      $returnMessage = "This has not been set yet...";

      #Obtain the post values
      $hasherKey = $request->request->get('hasher_key');
      $hashKey = $request->request->get('hash_key');

      #Validate the post values; ensure that they are both numbers
      if(ctype_digit($hasherKey)  && ctype_digit($hashKey)){

        #Check if this exists
        $existsSql = "SELECT HASHER_NAME
          FROM HASHINGS
          JOIN HASHERS ON HASHERS.HASHER_KY = HASHINGS.HASHER_KY
          WHERE HASHERS.HASHER_KY = ? AND HASH_KY = ?;";

        #Retrieve the existing record
        $hasherToDelete = $app['db']->fetchAll($existsSql,array((int)$hasherKey,(int)$hashKey));
        if(count($hasherToDelete) > 0){

          #Obtain the name of the person being deleted
          $tempHasherName = $hasherToDelete[0];
          $tempHasherName = $tempHasherName['HASHER_NAME'];
          $returnMessage = "Success! Removed $tempHasherName as hasher at this event.";

          #Define the sql insert statement
          $sql = "DELETE FROM HASHINGS WHERE HASHER_KY = ? AND HASH_KY = ?;";

          #Execute the sql insert statement
          $app['dbs']['mysql_write']->executeUpdate($sql,array($hasherKey,$hashKey));

          #Add the audit statement
          # Declare the SQL used to retrieve this information
          $sql = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

          # Make a database call to obtain the hasher information
          $hashValue = $app['db']->fetchAssoc($sql, array((int) $hashKey));
          $tempKennelEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
          $tempKennelAbbreviation = $hashValue['KENNEL_ABBREVIATION'];

          $tempActionType = "Delete Hound From Event";
          $tempActionDescription = "Deleted $tempHasherName from $tempKennelAbbreviation # $tempKennelEventNumber";
          AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

        }  else{
          $returnMessage = "Record cannot be deleted; doesn't exist!";
        }
      } else{
        $returnMessage = "Something is wrong with the input.$hasherKey and $hashKey";
      }

      #Set the return value
      $returnValue =  $app->json($returnMessage, 200);
      return $returnValue;

    }


    #Delete a participant from a hash
    public function deleteHashOrganizer (Request $request, Application $app){

      #Establish the return message
      $returnMessage = "This has not been set yet...";

      #Obtain the post values
      $hasherKey = $request->request->get('hasher_key');
      $hashKey = $request->request->get('hash_key');

      #Validate the post values; ensure that they are both numbers
      if(ctype_digit($hasherKey)  && ctype_digit($hashKey)){

        #Check if this exists
        $existsSql = "SELECT HASHER_NAME
          FROM HARINGS
          JOIN HASHERS ON HASHERS.HASHER_KY = HARINGS.HARINGS_HASHER_KY
          WHERE HARINGS.HARINGS_HASHER_KY = ? AND HARINGS.HARINGS_HASH_KY = ?;";

        #Retrieve the existing record
        $hareToDelete = $app['db']->fetchAll($existsSql,array((int)$hasherKey,(int)$hashKey));
        if(count($hareToDelete) > 0){

          #Obtain the name of the person being deleted
          $tempHasherName = $hareToDelete[0];
          $tempHasherName = $tempHasherName['HASHER_NAME'];
          $returnMessage = "Success! Removed $tempHasherName as hare from this event.";

          #Define the sql insert statement
          $sql = "DELETE FROM HARINGS WHERE HARINGS_HASHER_KY = ? AND HARINGS_HASH_KY = ?;";

          #Execute the sql insert statement
          $app['dbs']['mysql_write']->executeUpdate($sql,array($hasherKey,$hashKey));

          #Add the audit statement
          # Declare the SQL used to retrieve this information
          $sql = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

          # Make a database call to obtain the hasher information
          $hashValue = $app['db']->fetchAssoc($sql, array((int) $hashKey));
          $tempKennelEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
          $tempKennelAbbreviation = $hashValue['KENNEL_ABBREVIATION'];

          $tempActionType = "Delete Hare From Event";
          $tempActionDescription = "Deleted $tempHasherName from $tempKennelAbbreviation # $tempKennelEventNumber";
          AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

        }  else{
          $returnMessage = "Record cannot be deleted; doesn't exist!";
        }
      } else{
        $returnMessage = "Something is wrong with the input.$hasherKey and $hashKey";
      }

      #Set the return value
      $returnValue =  $app->json($returnMessage, 200);
      return $returnValue;

    }

    #Obtain hashers for an event
    public function getHaresForEvent(Request $request, Application $app){

      #Obtain the post values
      $hashKey = $request->request->get('hash_key');

      #Define the SQL to execute
      $hareListSQL = "SELECT HASHER_KY, HASHER_NAME
        FROM HARINGS
        JOIN HASHERS ON HASHERS.HASHER_KY = HARINGS.HARINGS_HASHER_KY
        WHERE HARINGS.HARINGS_HASH_KY = ? ";

      #Obtain the hare list
      $hareList = $app['db']->fetchAll($hareListSQL,array((int)$hashKey));

      #Set the return value
      $returnValue =  $app->json($hareList, 200);
      return $returnValue;
    }

    #Obtain hashers for an event
    public function getHashersForEvent(Request $request, Application $app){

      #Obtain the post values
      $hashKey = $request->request->get('hash_key');

      #Define the SQL to execute
      $hareListSQL = "SELECT HASHERS.HASHER_KY AS HASHER_KY, HASHERS.HASHER_NAME AS HASHER_NAME
        FROM HASHINGS
        JOIN HASHERS ON HASHERS.HASHER_KY = HASHINGS.HASHER_KY
        WHERE HASHINGS.HASH_KY = ? ";

      #Obtain the hare list
      $hareList = $app['db']->fetchAll($hareListSQL,array((int)$hashKey));

      #Set the return value
      $returnValue =  $app->json($hareList, 200);
      return $returnValue;
    }





    #Define the action
    public function listHashesPreActionJson(Request $request, Application $app, string $kennel_abbreviation){

      # Establish and set the return value
      $returnValue = $app['twig']->render('hash_list_json.twig',array(
        'pageTitle' => 'The List of Hashes',
        'pageSubTitle' => '*Brown = Hyper Hash',
        #'theList' => $hasherList,
        'kennel_abbreviation' => $kennel_abbreviation,
        'pageCaption' => "",
        'tableCaption' => ""
      ));

      #Return the return value
      return $returnValue;

    }






    public function listHashesPostActionJson(Request $request, Application $app, string $kennel_abbreviation){

      #$app['monolog']->addDebug("Entering the function------------------------");

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
      $inputOrderColumnExtracted = "13";
      $inputOrderColumnIncremented = "13";
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
          KENNEL_EVENT_NUMBER AS KENNEL_EVENT_NUMBER,
          (SELECT COUNT(*) FROM HASHINGS WHERE HASHINGS.HASH_KY = HASHES.HASH_KY) AS HOUND_COUNT,
          (SELECT COUNT(*) FROM HARINGS WHERE HARINGS.HARINGS_HASH_KY = HASHES.HASH_KY) AS HARE_COUNT,
          EVENT_LOCATION AS EVENT_LOCATION,
          SPECIAL_EVENT_DESCRIPTION AS SPECIAL_EVENT_DESCRIPTION,
          EVENT_DATE AS EVENT_DATE,
          EVENT_CITY AS EVENT_CITY,
          EVENT_STATE AS EVENT_STATE,
          FORMATTED_ADDRESS,
          HASH_KY AS HASY_KY,
          KENNEL_KY AS KENNEL_KY,
          DATE_FORMAT(event_date,'%Y/%m/%d') AS EVENT_DATE_FORMATTED,
          DATE_FORMAT(event_date,'%Y/%m/%d %h:%i %p') AS EVENT_DATE_FORMATTED2,
          IS_HYPER AS IS_HYPER
        FROM HASHES
        WHERE
          KENNEL_KY = $kennelKy AND
          (
            KENNEL_EVENT_NUMBER LIKE ? OR
            EVENT_LOCATION LIKE ? OR
            SPECIAL_EVENT_DESCRIPTION LIKE ? OR
            EVENT_CITY LIKE ? OR
            EVENT_STATE LIKE ?)
        ORDER BY $inputOrderColumnIncremented $inputOrderDirectionExtracted
        LIMIT $inputStart,$inputLength";


      #Define the SQL that gets the count for the filtered results
      $sqlFilteredCount = "SELECT COUNT(*) AS THE_COUNT
        FROM HASHES
        WHERE
        KENNEL_KY = $kennelKy AND
        (
          KENNEL_EVENT_NUMBER LIKE ? OR
          EVENT_LOCATION LIKE ? OR
          SPECIAL_EVENT_DESCRIPTION LIKE ? OR
          EVENT_CITY LIKE ? OR
          EVENT_STATE LIKE ?)";

      #Define the sql that gets the overall counts
      $sqlUnfilteredCount = "SELECT COUNT(*) AS THE_COUNT FROM HASHES WHERE KENNEL_KY = $kennelKy";

      #-------------- End: Define the SQL used here   ----------------------------

      #-------------- Begin: Query the database   --------------------------------
      #Perform the filtered search
      $theResults = $app['db']->fetchAll($sql,array(
        (string) $inputSearchValueModified,
        (string) $inputSearchValueModified,
        (string) $inputSearchValueModified,
        (string) $inputSearchValueModified,
        (string) $inputSearchValueModified));

      #Perform the untiltered count
      $theUnfilteredCount = ($app['db']->fetchAssoc($sqlUnfilteredCount,array()))['THE_COUNT'];

      #Perform the filtered count
      $theFilteredCount = ($app['db']->fetchAssoc($sqlFilteredCount,array(
        (string) $inputSearchValueModified,
        (string) $inputSearchValueModified,
        (string) $inputSearchValueModified,
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





}
