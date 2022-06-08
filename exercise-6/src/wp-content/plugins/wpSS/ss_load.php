<?php 

  require_once( '../../../wp-config.php');
  
  $id = $_GET['ss_id'];
  $plain = 0;
  if ($_GET['display'] == 'plain' or $_GET['display'] == 1) { $plain = TRUE; };
//  echo "display: ".$_GET['display'].", plain in ssload: $plain"; 

  //if called with no ss_id, assume first spreadsheet
  if ($id == '') {$id = 1;}  

  require_once('ss_functions.php');

  ss_load ($id, $plain);

?>
