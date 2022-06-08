<?php

//I don't like that get and post are out of sync!  use js onclick with ajax to fix form action!
//Handle ss_buttons from form instead?
   
  if (isset($_GET['ss_id'])) {    //if called with no ss_id, die
  $id = $_GET['ss_id'];
    if (isset($_POST['ss_id'])) {
      if ($_POST['ss_id'] > 0 and $_POST['ss_id'] != $id) {  //if _GET and POST differ
        $_GET['ss_id'] = $_POST['ss_id'];    //make get = to post 
        $id = $_POST['ss_id']; 
    //    echo "ss_handler.php called with different ss_id: $id.";
      }
    } 
    else {
      $_POST['ss_load'] = TRUE; //always make task load ss if $POST ss_id is not set
    }
  } else {
    echo 'Wordpress Spreadsheet: No ss_id in URL'; 
  }
    
  require_once( '../../../wp-config.php');
  require_once('ss_functions.php');

  //handle possible spreadsheet functions from name of submit button in form
  if ($_POST['ss_save']) {
      ss_save(); 
  } else if ($_POST['ss_load']) {
      ss_load ($id); //saved, so reload
  } else if ($_POST['ss_clear']) {
      ss_clear ($id);  //erase spreadsheet, save and reload
  }  

?>


