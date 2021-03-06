<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_sam_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function hello_world_parameters() {
        return new external_function_parameters(
                array('welcomemessage' => new external_value(PARAM_TEXT, 'The welcome message. By default it is "Hello world,"', VALUE_DEFAULT, 'Hello world, '))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function hello_world($welcomemessage = 'Hello world, ') {
        global $USER;
        global $DB;
/*
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::hello_world_parameters(),
                array('welcomemessage' => $welcomemessage));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
*/
        $params = self::validate_parameters(self::hello_world_parameters(),
        array('welcomemessage' => $welcomemessage));
$json = '';
$json .= '{
  "docs": [
  ';
/*header('Content-Type: text/plain');
*/
$con=mysqli_connect("localhost","root","","vanlmsdb");

// Check connection
if (mysqli_connect_errno($con))
  {
  $json .= "Failed to connect to MySQL: " . mysqli_connect_error();
  }else{
    mysqli_set_charset($con, "utf8");
/**
FETCHING CIURSE FIRST BASED ON ID OR NAME
*/
    /********************
    1) Getting The The id Of the Course
    *********************/
/*$courseid = 5;*/
$courseid = $params['welcomemessage'];
$coursename = '';
if ($courseid != '' || $coursename!= '' ) {
    if ($coursename!= ''){
        $coursequery = mysqli_query($con,'SELECT id FROM lms_course  WHERE fullname ="'.$coursename.'" ');
        while($coursefetch = mysqli_fetch_array($coursequery)){
            $courseid = $coursefetch['id'];
        }
    }
    
    $coursequery2 = mysqli_query($con,'SELECT fullname FROM lms_course  WHERE id ="'.$courseid.'" ');
    while($coursefetch2 = mysqli_fetch_array($coursequery2)){
        $assessName = $coursefetch2['fullname'];
    }
    $allRand = mt_rand();
    $user.="assessmentpart  ";
    $json .= '{
        "_id": "moodle-'.$courseid.'-'.$allRand.'",
        "assessmentId": "moodle-'.$courseid.'",
        "archived": false,
        "name": "'.$assessName.'",
        "group": "RUBABtest2013",
        "collection": "assessment",
        "sequences": [
            [
              null
            ]
        ]
    },
    ';

    /********************
    2) NoW Getting The list of Quiz For the Course ANd Making Survey Pages
    *********************/
    $quizquery = mysqli_query($con,'SELECT id, name FROM lms_quiz WHERE course ="'.$courseid.'" ');
    $quizcount = mysqli_num_rows($quizquery);
    $quizcheck = 1;
    $quizIdArray = array();
    while($quizfetch = mysqli_fetch_array($quizquery)){
        $user.="quiz part".$quizfetch['id'].$quizcheck;
        $json .= '{
            "_id": "moodle-survey-'.$quizfetch['id'].'-'.$allRand.'",
            "studentDialog": "",
            "enumeratorHelp": "",
            "order": '.$quizcheck.',
            "skippable": false,
            "prototype": "survey",
            "gridLinkId": "",
            "name": "'.$quizfetch['name'].'",
            "assessmentId": "moodle-'.$courseid.'-'.$allRand.'",
            "collection": "subtest",
            "transitionComment": "",
            "autostopLimit": 0
    },';
    array_push($quizIdArray, $quizfetch['id']);
    }
    /********************
    3) Time To Get Questions For Each Quiz
    *********************/

    $lastquiz = count($quizIdArray);
    $quizcount = 1;
    foreach ($quizIdArray as $value) {
        $quizQuestionquery = mysqli_query($con,'SELECT question FROM lms_quiz_question_instances WHERE quiz ="'.$value.'" ');
        $number2 = mysqli_num_rows($quizQuestionquery);
        $lastQuestionCheck = 0;
        while($quizQuestion = mysqli_fetch_array($quizQuestionquery)){
            $question = mysqli_query($con,'SELECT DISTINCT * FROM lms_question  WHERE id = "'.$quizQuestion['question'].'" ');
            $count2 = 1;
          
            while($row = mysqli_fetch_array($question)){
                $lastQuestionCheck++;
                if($row['qtype']== "multichoice"){$type = "multiple";
                }else{
                    $type = "open";
                }
                $question_answer = mysqli_query($con,"SELECT * FROM lms_question_answers WHERE question =".$row['id']);
                $json .= '
                  { ';
/*                  $stage0 = str_replace('\ ','',$row['questiontext']);
                  $stage3 = str_replace('\f','f',$stage0);
                  $stage5 = str_replace(' \div', '&#92 div', $stage3 );
                  $stage4 = trim(preg_replace('/\s\s+/', ' ', $stage5));
                  $stage1 =  preg_replace('/"/', '\\\\\\\\\\\\"', $stage4);*/
                  $questionPrompt = mysqli_query($con,'SELECT DISTINCT * FROM lms_question  WHERE id = "'.$row['id'].'" '); 
                        $varName = array('');
                        $randNumber = array('');
                  while($questionPromptResult = mysqli_fetch_array($questionPrompt)){
                    $prompt = $questionPromptResult['questiontext'];
/*                        $prompt = $questionPromptResult['questiontext'];
                        $prompt = str_replace('\times', 'multiplysign', $prompt);*/
                        /*$prompt = "<p style='text-align: right;'><strong>ضرب دیں</strong></p> <p style='text-align: right;'><strong>$$ 1frac{2}{4} multiplysign2frac{5}{8}$$<br /></strong></p>";*/

/*                        require('times.php');*/
                        /*$prompt = "<p style='text-align: right;'><span style='font-size: medium;'><strong>حل کریں۔</strong></span></p> <p style='text-align: right;'><span style='font-size: medium;'>$$ frac{1}{5} multiplysign  3 $$</span></p>";*/
                        /*$prompt = str_replace("$$", ' ', $prompt);*/
                        $questionDatasets = mysqli_query($con,'SELECT * FROM `lms_question_datasets`  WHERE question = "'.$row['id'].'" ');
                        /*$datasetCount = mysqli_num_rows($questionDatasets);*/
                        $datasetCounter = 0;
                        while($DatasetId = mysqli_fetch_array($questionDatasets)){
                          /*$id2 .= $DatasetId['datasetdefinition'].' \n';*/
                          $DatasetsOption = mysqli_query($con,'SELECT * FROM `lms_question_dataset_definitions` WHERE id = "'.$DatasetId['datasetdefinition'].'" ');
                          while($DatasetFields = mysqli_fetch_array($DatasetsOption)){
                            $varOption = $DatasetFields['options'];
                            $arrayOption = explode(":",$varOption);
                            if (strpos($arrayOption['1'],'.') !== false || strpos($arrayOption['2'],'.') !== false) {
                              $min = intval($arrayOption['1']);
                              $max = intval($arrayOption['2']);
                              $randNumber[$datasetCounter] = $min + mt_rand() / mt_getrandmax() * ($max - $min);
                              $randNumber[$datasetCounter] = substr($randNumber[$datasetCounter], 0, 5);
                            }else{
                              $randNumber[$datasetCounter] = mt_rand($arrayOption['1'], $arrayOption['2']);
                            }
                            $varName[$datasetCounter] = $DatasetFields['name'];
                            /*$id2 .= $DatasetFields['name'].'\n'.$prompt.'<br>';*/
                            if(count($varName)==2 ){

                              /* $$ \frac{{'.$varName['0'].'}}{{'.$varName['1'].'}}\ $$ */
                              $seq1 = '$$ \frac{{'.$varName['0'].'}}{{'.$varName['1'].'}}\ $$';
                              $seq2 = '$$ \frac{{'.$varName['1'].'}}{{'.$varName['0'].'}}\ $$';
                              $reqSeq1 = '<div class=\'clear:both\'></div><div style=\'float:left;text-align:center;\'><div style=\'border-bottom:2px solid;\'>'.$randNumber['0'].'</div><div>'.$randNumber['1'].'</div></div><div class=\'clear:both\'></div>';
                              $prompt = str_replace($seq1 ,$reqSeq1 , $prompt);
                              $prompt = str_replace($seq2 ,$reqSeq1 , $prompt);

                              /* {'.$varName['0'].'} + {'.$varName['1'].'} */
                              $seq3 = '{'.$varName['0'].'} + {'.$varName['1'].'}';
                              $seq4 = '{'.$varName['1'].'} + {'.$varName['0'].'}';
                              $reSeq2 = $randNumber['0'].' + '.$randNumber['1'];
                              $prompt = str_replace($seq3 , $reSeq2 , $prompt);
                              $prompt = str_replace($seq4 , $reSeq2 , $prompt);

                              /* {'.$varName['0'].'} */
                              $seq5 = '{'.$varName['0'].'}';
                              $seq6 = '{'.$varName['1'].'}';
                              $reSeq3 = $randNumber['0'];
                              $reSeq4 = $randNumber['1'];
                              $prompt = str_replace($seq5 , $reSeq3 , $prompt);
                              $prompt = str_replace($seq6 , $reSeq4 , $prompt);

                              /* frac{{'.$varName['0'].'}}{{'.$varName['1'].'}} */
                              $seq7 = 'frac{{'.$varName['0'].'}}{{'.$varName['1'].'}}';
                              $seq8 = 'frac{{'.$varName['1'].'}}{{'.$varName['0'].'}}';
                              $reSeq5 = 'frac{{'.$randNumber['0'].'}}{{'.$randNumber['1'].'}}';
                              $prompt = str_replace($seq7 , $reSeq5 , $prompt);
                              $prompt = str_replace($seq8 , $reSeq5 , $prompt);

                            }
                          }
                          $datasetCounter++;
                          $datasetCount = $varName['0'].'  \n '.$varName['1'];
                          /*$prompt = str_replace('{'.$varName.'}' , $randNumber , $prompt);*/
                        }
                        

                        $prompt = str_replace('\ ','',$prompt);/*$$ \frac{2}{5}\ \div \frac{3}{5}\ $$$$\frac{3}{5}\ + \frac{1}{5}\ $$*/
                        $prompt = str_replace('\f','f',$prompt);

                        /*$prompt = str_replace('\frac{1}{5}\ ', '');*/
                        /*$prompt1 = trim($prompt,chr(0xC2).chr(0xA0));;*/
                        require('times.php');
                        require('plus.php');
                        $prompt = str_replace('\times', ' <span style=\'margin: 0px 10px;margin: 0px 2px 0px 15px;\'> x </span> ', $prompt);
                        $prompt = str_replace('\div', ' &divide; ', $prompt);
                        $prompt = str_replace('$$', ' ', $prompt);
                        $prompt = str_replace('÷', '&divide;', $prompt);
                        $prompt1 = preg_replace('/^|\n|\r+$/m', '', $prompt);
                        /*$prompt1 =  preg_replace('/"/', '\\\\\\\\\\\"', $prompt1);*/
                        $prompt1 =  str_replace('"', '\'', $prompt1);
                  }     
                  $json .= '
                  "_id" : "question-'.$row['id'].'-'.mt_rand().'",
                  "prompt" : "'.$prompt1.'",
                  "name" :"'.$row['name'].'",
                  "type" : "'.$type.'",
                  "assessmentId": "moodle-'.$courseid.'-'.$allRand.'",
                  "collection": "question",
                  "subtestId":"moodle-survey-'.$value.'-'.$allRand.'",
                  "skipLogic": "",
                  "skippable": false,
                  "customValidationCode": "",
                  "customValidationMessage": "",
                  "options" : [
                        ';
                    if($type == "multiple"){
                    $number = mysqli_num_rows($question_answer);
                  $count = 1;
                    while($row2 = mysqli_fetch_array($question_answer)){
                      $label = str_replace('\ ','',$row2['answer']);
                      $label = str_replace('\f','f', $label);
                      if(preg_match("/[0-9] frac{/", $label)){
                        if(preg_match("/[0-9][0-9] frac{/", $label)){
                          $label = str_replace(' frac{', 'frac{', $label);
                        }
                        $label = str_replace(' frac{', 'frac{', $label);
                      }
                      require('times-label.php');
                      $label = str_replace('$$',' ', $label);
/*                      $ltest .= $label.'
                      ';*/
                      /*$label = str_replace('\div', ' + ', $label);*/
                      /*$label = str_replace('}\div', '&#92 div', $label);*/
                      $label =  preg_replace('/"/', '\\\\\\\\\\\"', $label);
                      $json .= ' {
                            "label" : "'.$label.'",
                            "value" : "'.intval($row2['fraction']).'"
                          ';
                    if ($count < $number)
                       {
                           $json .= '},';
                       }else{
                          $json .= '}';
                       }
                       $count++;
                    }
                }
                  $json .= '
                    ]
                  ';
                  if ($quizcount < $lastquiz)
                     {
                         $json .= "},";
                     }else{
                        if($lastQuestionCheck < $number2 ){
                            $json .= '},';
                        }else{
                            $json .= '}';
                        }

                     }
            $count2++;
            }
        }
        $quizcount++;   
    }
}
$json .= '  ]
}';  
  }// For Else If Connected to database
  function TrimStr($str) 
{ 
    $str = trim($str); 
    for($i=0;$i < strlen($str);$i++) 
    { 

        if(substr($str, $i, 1) != " ") 
        { 

            $ret_str .= trim(substr($str, $i, 1)); 

        } 
        else 
        { 
            while(substr($str,$i,1) == " ") 
           
            { 
                $i++; 
            } 
            $ret_str.= " "; 
            $i--; // *** 
        } 
    } 
    return $ret_str; 
} 
        return $json;;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_RAW, 'The welcome message + user first name');
    }



}
