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



class TagController
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


    public function manageEventTagsPreAction(Request $request, Application $app){


      #Define the SQL to execute
      $eventTagListSQL = "SELECT TAG_TEXT, COUNT(HTJ.HASHES_KY) AS THE_COUNT
        FROM  HASHES_TAGS HT LEFT JOIN HASHES_TAG_JUNCTION HTJ ON HTJ.HASHES_TAGS_KY = HT.HASHES_TAGS_KY
        GROUP BY TAG_TEXT
        ORDER BY THE_COUNT DESC";

      #Execute the SQL statement; create an array of rows
      $eventTagList = $app['db']->fetchAll($eventTagListSQL);


      #Establish the return value
      $returnValue = $app['twig']->render('manage_event_tag_json.twig', array (
        'pageTitle' => "Event Tags",
        'pageSubTitle' => 'Create Event Tags. (Add them to the events sometime later).',
        'pageHeader' => 'Why is this so complicated ?',
        'tagList' => $eventTagList
      ));

      #Return the return value
      return $returnValue;

    }




public function getEventTagsWithCountsJsonAction(Request $request, Application $app){

  #Define the SQL to execute
  $tagListSQL = "SELECT TAG_TEXT, COUNT(HTJ.HASHES_KY) AS THE_COUNT
    FROM  HASHES_TAGS HT LEFT JOIN HASHES_TAG_JUNCTION HTJ ON HTJ.HASHES_TAGS_KY = HT.HASHES_TAGS_KY
    GROUP BY TAG_TEXT
    ORDER BY THE_COUNT DESC";

  #Obtain the hare list
  $tagList = $app['db']->fetchAll($tagListSQL);

  #Set the return value
  $returnValue =  $app->json($tagList, 200);
  return $returnValue;
}


public function getAllEventTagsJsonAction(Request $request, Application $app){

  #Define the SQL to execute
  $tagListSQL = "SELECT HASHES_TAGS_KY AS id, TAG_TEXT AS label, TAG_TEXT AS value
    FROM  HASHES_TAGS HT
    ORDER BY TAG_TEXT ASC";

  #Obtain the hare list
  $tagList = $app['db']->fetchAll($tagListSQL);

  #Set the return value
  $returnValue =  $app->json($tagList, 200);
  return $returnValue;
}




public function getMatchingEventTagsJsonAction(Request $request, Application $app){

  //Default the search term to an empty string
  $searchTerm = "";

  //Check the format of the search string
  if(isset($_GET['term'])  &&  ctype_alnum(trim(str_replace(' ','',$_GET['term'])))  ){
    $searchTerm = $_GET['term'];
    $searchTerm = "%$searchTerm%";
  }


  #Define the SQL to execute
  $tagListSQL = "SELECT HASHES_TAGS_KY AS id, TAG_TEXT AS label, TAG_TEXT AS value
    FROM  HASHES_TAGS HT
    WHERE TAG_TEXT LIKE ?
    ORDER BY TAG_TEXT ASC";

  #Obtain the tag list
  $tagList = $app['db']->fetchAll($tagListSQL,array((string) $searchTerm));

  #Set the return value
  $returnValue =  $app->json($tagList, 200);
  return $returnValue;
}


private function addNewEventTagAfterDbChecking(Request $request, Application $app, string $theTagText){

        #Define the sql insert statement
        $sql = "INSERT INTO HASHES_TAGS (TAG_TEXT, CREATED_BY) VALUES (?, ?);";

        #Determine the username
        $token = $app['security.token_storage']->getToken();
        if (null !== $token) {
          $user = $token->getUser();
        }

        #Execute the sql insert statement
        $app['dbs']['mysql_write']->executeUpdate($sql,array($theTagText,$user));

        #Audit the action
        $tempActionType = "Created Event Tag";
        $tempActionDescription = "Created event tag: $theTagText";
        AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

        #Set the return message
        $returnMessage = "Success! $theTagText has been created as an event tag.";

}

public function addNewEventTag(Request $request, Application $app){

        #Establish the return message
        $returnMessage = null;

        #Obtain the post values
        $theTagText = $request->request->get('tag_text');
        $theTagText = trim($theTagText);

        #Validate the post values; ensure that they are both numbers
        if(ctype_alnum(trim(str_replace(' ','',$theTagText)))){

          if(($this->doesTagTextExistAlready($request,$app,$theTagText))){
            #Set the return value
            $returnMessage = "Uh oh! This tag already exists: $theTagText";

          }else{
            #Add the tag into the tags table
            $this->addNewEventTagAfterDbChecking($request, $app, $theTagText);

            #Set the return value
            $returnMessage = "Success! You've created the tag: $theTagText";
          }
        } else{
          $returnMessage = "Something is wrong with the input $theTagText";
        }

        #Set the return value
        $returnValue =  $app->json($returnMessage, 200);
        return $returnValue;


}

  private function doesTagTextExistAlready(Request $request, Application $app, string $theTagText){

    #Ensure the entry does not already exist
    $existsSql = "SELECT * FROM HASHES_TAGS WHERE TAG_TEXT = ? ;";

    #Retrieve the existing record
    $matchingTags = $app['db']->fetchAll($existsSql,array($theTagText));

    #Check if there are 0 results
    if(count($matchingTags) < 1){
        return false;
    }else{
        return true;
    }

  }



    #Define action
    public function showEventForTaggingPreAction(Request $request, Application $app, int $hash_id){


      #Define the SQL to execute
      $eventTagListSQL = "SELECT TAG_TEXT
        FROM  HASHES_TAGS HT JOIN HASHES_TAG_JUNCTION HTJ ON HTJ.HASHES_TAGS_KY = HT.HASHES_TAGS_KY
        WHERE HTJ.HASHES_KY = ?";

      #Execute the SQL statement; create an array of rows
      $eventTagList = $app['db']->fetchAll($eventTagListSQL, array((int) $hash_id));

      # Declare the SQL used to retrieve this information
      $sql = "SELECT * ,date_format(event_date, '%Y-%m-%d' ) AS EVENT_DATE_DATE, date_format(event_date, '%k:%i:%S') AS EVENT_DATE_TIME FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

      # Make a database call to obtain the hasher information
      $hashValue = $app['db']->fetchAssoc($sql, array((int) $hash_id));


      $returnValue = $app['twig']->render('show_hash_for_tagging.twig', array(
        'pageTitle' => 'Tag this hash event!',
        'pageHeader' => '(really)',
        'hashValue' => $hashValue,
        'hashKey' => $hash_id,
        'tagList' => $eventTagList
      ));

      #Return the return value
      return $returnValue;

    }




    public function addTagToEventJsonAction(Request $request, Application $app){

      #Establish the return message
      $returnMessage = "";

      #Obtain the post values
      $theTagText = trim($request->request->get('tag_text'));
      $theEventKey = intval($request->request->get('event_key'));

      #Determine if the tag text is valid (as in, doesn't have sql injection in it)
      $tagTextIsValid = $this->isTagTextValid($theTagText);

      #Determine if the event key is valid
      $eventKeyIsValid = $this->isEventKeyValid($app, $theEventKey);

      if($tagTextIsValid && $eventKeyIsValid ){

        #If the tag doesn't already exist, create it
        if(!($this->doesTagTextExistAlready($request,$app,$theTagText))){
          #Add the tag into the tags table
          $this->addNewEventTagAfterDbChecking($request, $app, $theTagText);
        }

        #Obtain the tag key
        $tagKey = $this->getTagTextKey($app, $theTagText);

        #Add the event/tag pair into the junction table
        $junctionInsertSql = "INSERT INTO HASHES_TAG_JUNCTION (HASHES_KY, HASHES_TAGS_KY, CREATED_BY) VALUES (?, ?, ?);";

        #Get the user name
        $user = $this->getUserName($app);

        #Execute the sql insert statement
        $app['dbs']['mysql_write']->executeUpdate($junctionInsertSql,array((int)$theEventKey,(int)$tagKey,(string)$user));

        # Declare the SQL used to retrieve this information
        $hashValueSql = "SELECT * ,date_format(event_date, '%Y-%m-%d' ) AS EVENT_DATE_DATE, date_format(event_date, '%k:%i:%S') AS EVENT_DATE_TIME FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

        # Make a database call to obtain the hasher information
        $hashValue = $app['db']->fetchAssoc($hashValueSql, array((int) $theEventKey));

        #Audit the action
        $kennelAbbreviation = $hashValue['KENNEL_ABBREVIATION'];
        $kennelEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
        $tempActionType = "Create Event Tagging";
        $tempActionDescription = "Create event tagging: $theTagText on $kennelAbbreviation:$kennelEventNumber";
        AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

        #Set the return message
        $returnMessage = "Success! $theTagText has been added as a tag for this event.";

      }else{
        #Set the return message
        $returnMessage =  "Something is up";
      }



      #Set the return value
      $returnValue =  $app->json($returnMessage, 200);
      return $returnValue;



    }



    public function removeTagFromEventJsonAction(Request $request, Application $app){

            #Establish the return message
            $returnMessage = "This has not been set yet...";

            #Obtain the post values
            $theTagText = trim($request->request->get('tag_text'));
            $theEventKey = intval($request->request->get('event_key'));

            #Determine if the tag text is valid (as in, doesn't have sql injection in it)
            $tagTextIsValid = $this->isTagTextValid($theTagText);

            #Obtain the tag key
            $tagKey = $tagTextIsValid ? ($this->getTagTextKey($app, $theTagText)) : null;

            #Determine if the event key is valid
            $eventKeyIsValid = $this->isEventKeyValid($app, $theEventKey);

            if($tagTextIsValid && (!(is_null($tagKey))) && $eventKeyIsValid ){

              #Define the sql delete statement
              $sql = "DELETE FROM HASHES_TAG_JUNCTION WHERE HASHES_KY= ? AND HASHES_TAGS_KY = ?;";

              #Execute the sql insert statement
              $app['dbs']['mysql_write']->executeUpdate($sql,array($theEventKey,$tagKey));

              #Get the user name
              #$user = $this->getUserName($app);

              # Declare the SQL used to retrieve this information
              $hashValueSql = "SELECT * ,date_format(event_date, '%Y-%m-%d' ) AS EVENT_DATE_DATE, date_format(event_date, '%k:%i:%S') AS EVENT_DATE_TIME FROM HASHES JOIN KENNELS ON HASHES.KENNEL_KY = KENNELS.KENNEL_KY WHERE HASH_KY = ?";

              # Make a database call to obtain the hasher information
              $hashValue = $app['db']->fetchAssoc($hashValueSql, array((int) $theEventKey));

              #Audit the action
              $kennelAbbreviation = $hashValue['KENNEL_ABBREVIATION'];
              $kennelEventNumber = $hashValue['KENNEL_EVENT_NUMBER'];
              $tempActionType = "Delete Event Tagging";
              $tempActionDescription = "Delete event tagging: $theTagText on $kennelAbbreviation:$kennelEventNumber";
              AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

              #Set the return message
              $returnMessage = "Success! $theTagText has been removed as a tag from this event.";

            }else{
              #Set the return message
              $returnMessage =  "Something is up";
            }



            #Set the return value
            $returnValue =  $app->json($returnMessage, 200);
            return $returnValue;


    }

    private function isTagTextValid(string $tagText){

      #Establish the return value
      $returnValue = FALSE;

      #Set the return value
      $returnValue = (ctype_alnum(trim(str_replace(' ','',$tagText))));

      #Return the return value
      return $returnValue;
    }

    private function isEventKeyValid(Application $app, int $eventKey){

      #Establish the return value
      $returnValue = FALSE;

      #Query the database for the event
      $getEventValueSql = "SELECT * FROM HASHES WHERE HASH_KY = ? ;";
      $eventValues = $app['db']->fetchAll($getEventValueSql,array((int) $eventKey));

      #Determine if the event exists
      if(count($eventValues) > 0){
        $returnValue = TRUE;
      }

      #Return the return value
      return $returnValue;
    }

    private function getTagTextKey(Application $app,string $tagText){

      #Establish the return value
      $returnValue = null;

      #Set the return value
      $getTagValueSql = "SELECT * FROM HASHES_TAGS WHERE TAG_TEXT = ? ;";
      //$hashValue = $app['db']->fetchAssoc($sql, array((int) $hash_id));

      #Retrieve the existing record
      $matchingTagValue = $app['db']->fetchAssoc($getTagValueSql,array((string) $tagText));
      if(!(is_null($matchingTagValue))){
        $returnValue = $matchingTagValue['HASHES_TAGS_KY'];
      }


      #Return the return value
      return $returnValue;
    }

    private function getUserName(Application $app){
      #Set the return value
      $returnValue = null;

      #Establish the return value
      $token = $app['security.token_storage']->getToken();
      if (null !== $token) {
        $returnValue = $token->getUser();
      }

      #Return the return value
      return $returnValue;
    }

    public function listHashesByEventTagAction(Request $request, Application $app, int $event_tag_ky, string $kennel_abbreviation){

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
            IS_HYPER
      FROM
        HASHES JOIN HASHES_TAG_JUNCTION ON HASHES.HASH_KY = HASHES_TAG_JUNCTION.HASHES_KY
      WHERE
        HASHES_TAGS_KY = ? AND KENNEL_KY = ?
      ORDER BY HASHES.EVENT_DATE DESC";

      #Execute the SQL statement; create an array of rows
      $hashList = $app['db']->fetchAll($sql,array((int) $event_tag_ky, (int)$kennelKy));

      # Declare the SQL used to retrieve this information
      $sql_for_tag_lookup = "SELECT * FROM HASHES_TAGS WHERE HASHES_TAGS_KY = ?";

      # Make a database call to obtain the hasher information
      $eventTag = $app['db']->fetchAssoc($sql_for_tag_lookup, array((int) $event_tag_ky));

      # Establish and set the return value
      #$hasherName = $hasher['HASHER_NAME'];
      $tagText = $eventTag['TAG_TEXT'];
      $pageSubtitle = "Hashes with the tag: $tagText";
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







}
