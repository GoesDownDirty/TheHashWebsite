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


    public function hashParticipationAction(Request $request, Application $app, int $hash_id){


      #Define the SQL to execute
      $hasherListSQL = "SELECT *
        FROM HASHINGS
        JOIN HASHERS ON HASHINGS.HASHER_KY = HASHERS.HASHER_KY
        WHERE HASHINGS.HASH_KY = ? ";

      $hareListSQL = "SELECT *
        FROM HARINGS
        JOIN HASHERS ON HARINGS.HARINGS_HASHER_KY = HASHERS.HASHER_KY
        WHERE HARINGS.HARINGS_HASH_KY = ?";

      $allHashersSQL = "SELECT
        HASHER_KY, HASHER_NAME, LAST_NAME, FIRST_NAME, EMAIL
        FROM HASHERS
        ORDER BY HASHER_NAME";

      #Obtain hash event information
      $hashEventInfoSQL = "SELECT * FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

      #Execute the SQL statement; create an array of rows
      $hasherList = $app['db']->fetchAll($hasherListSQL,array((int)$hash_id));
      $hareList = $app['db']->fetchAll($hareListSQL,array((int)$hash_id));
      $allHashersList = $app['db']->fetchAll($allHashersSQL);
      $hashEvent = $app['db']->fetchAssoc($hashEventInfoSQL,array((int)$hash_id));

      $kennelAbbreviation = $hashEvent['KENNEL_ABBREVIATION'];
      $kennelEventNumber = $hashEvent['KENNEL_EVENT_NUMBER'];
      $eventDate = $hashEvent['EVENT_DATE'];
      $pageTitle = "Participation: $kennelAbbreviation # $kennelEventNumber ($eventDate)";

      #Establish the return value
      $returnValue = $app['twig']->render('event_participation.twig', array (
        'pageTitle' => $pageTitle,
        'pageSubTitle' => 'Not Sure',
        'pageHeader' => 'Why is this so complicated ?',
        'hasherList' => $hasherList,
        'hareList' => $hareList,
        'allHashersList' => $allHashersList,
        'hash_key'=> $hash_id
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
            'EMAIL' => $hasherValue['EMAIL'],
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
            'EMAIL' => $hasherValue['EMAIL'],
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

}
