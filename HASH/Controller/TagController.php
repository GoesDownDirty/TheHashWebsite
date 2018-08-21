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

public function addNewEventTag(Request $request, Application $app){

        #Establish the return message
        $returnMessage = "This has not been set yet...";

        #Obtain the post values
        $theTagText = $request->request->get('tag_text');
        $theTagText = trim($theTagText);

        #Validate the post values; ensure that they are both numbers
        if(ctype_alnum(trim(str_replace(' ','',$theTagText)))){

          #Ensure the entry does not already exist
          $existsSql = "SELECT *
            FROM HASHES_TAGS
            WHERE TAG_TEXT = ? ;";

          #Retrieve the existing record
          $matchingTags = $app['db']->fetchAll($existsSql,array($theTagText));
          if(count($matchingTags) < 1){

            #Define the sql insert statement
            $sql = "INSERT INTO HASHES_TAGS (TAG_TEXT, CREATED_BY) VALUES (?, ?);";

            #Determine the username
            $token = $app['security.token_storage']->getToken();
            if (null !== $token) {
              $user = $token->getUser();
            }

            #Execute the sql insert statement
            $app['dbs']['mysql_write']->executeUpdate($sql,array($theTagText,$user));

            $tempActionType = "Created Event Tag";
            $tempActionDescription = "Created event tag: $theTagText";

            AdminController::auditTheThings($request, $app, $tempActionType, $tempActionDescription);

            #Set the return message
            $returnMessage = "Success! $theTagText has been created as an event tag.";
          } else {

            #Set the return message
            $returnMessage = "$theTagText already exists as an event tag.";
          }

        } else{
          $returnMessage = "Something is wrong with the input $theTagText";
        }

        #Set the return value
        $returnValue =  $app->json($returnMessage, 200);
        return $returnValue;


}










}
