<?php

//TODO:
//error trapping ss_id to just numbers in js?
//load errors when ss_id doesn't exist
//Save, Save as behavior!  Should warn when overwriting an existing ss id
//list spreadsheets button, +in admin page, routine?  maybe someday
//better solution than allowing the author to change the _ss_id, because
//otherwise must error trap SQL load+save

//-------------------------------------------------------------

function wpSS_blank() {
    include ("wpSS_blank.php");
    return $htmlspreadsheet;
}

//-------------------------------------------------------------

function ss_clean ($usertext) { 

//  if ( eregi ("\"", $tabletext) )  { echo  "<p>Yesss!</p>"; }
//  print  "<p>Test: ".eregi ( "<script", $tabletext )." (should give ereg output)</p>";

//What about cases where multiple classes?
  $pattern = 'spreadsheetCellActive'; 
  $replacement = '';
  $usertext = eregi_replace($pattern, $replacement, $usertext);

//remove any auto_locked rows or columns
  $pattern = 'auto_locked';
  $replacement = '';
  $usertext = eregi_replace($pattern, $replacement, $usertext);

//remove any empty class statements
  $pattern = 'class="([[:space:]]*)"';
  $replacement = '';
  $usertext = eregi_replace($pattern, $replacement, $usertext);

//debug regex -- leave here
//echo "pattern: $pattern  ".htmlspecialchars(($usertext), ENT_QUOTES); 
//die;
  
  // Disable any attempt inject a script into the database
  $usertext = eregi_replace ( "<script", "<DISABLEDscript", $usertext ); 

  // Disable any attempt inject php into the database (ajaxed, but be sure)
  $usertext = eregi_replace ( "<[\?]", "<DISABLED?", $usertext ); 
  
return $usertext;
}

//-------------------------------------------------------------

function ss_save() {
    $id = $_GET['ss_id'];

    global $wpdb;
    $table_name = $wpdb->prefix . "spreadsheet";

//must strip slashes for regex in ss_clean to work  
  $ss_tablehtml = stripslashes($_POST['ss_tablehtml']);
    $ss_tablehtml = ss_clean ($ss_tablehtml);
    $ss_tablehtml = $wpdb->escape ($ss_tablehtml);

  $ss_name = stripslashes($_POST['ss_name']);
    $ss_name = ss_clean ($ss_name);
    $ss_name = $wpdb->escape ($ss_name);

  $ss_description = stripslashes($_POST['ss_description']);
    $ss_description = ss_clean ($ss_description);
    $ss_description = $wpdb->escape ($ss_description);
     
   $update = "UPDATE ".$table_name 
             . " SET ss_tablehtml = '$ss_tablehtml', "
              	  . "ss_name = '$ss_name', "
              	  . "ss_description = '$ss_description'" 
             . " WHERE id = $id LIMIT 1";
  
    $results = $wpdb->query( $update );

    if ($results == "1") { 
      ss_load($id); //saved, so reload
    } else {
      // no error and no results returned, so it likely failed because id=x does not exist, 
      // try inserting instead
      $wpdb->hide_errors();
      $insert = "INSERT INTO ".$table_name.
                " (ss_tablehtml, ss_name, ss_description, id) ".
                " VALUES ('$ss_tablehtml', '$ss_name', '$ss_description', '$id');";
                          
      $results = $wpdb->query( $insert ); 
      $wpdb->show_errors();

      // if successful or inserting errored due to a duplicate entry, great--otherwise print error
      if ($results == "1" or mysql_errno($wpdb->dbh) == 1062) { 
        ss_load($id); //saved, so reload
      } else {
    		$query = htmlspecialchars($wpdb->last_query, ENT_QUOTES);
        $str = htmlspecialchars(mysql_error($wpdb->dbh), ENT_QUOTES);
  			print "<div id='error'>
	     	 <p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
			   <code>$query</code></p>
			   </div>";
//      debug stuff: print_r( "EZ-sql: " . mysql_errno($wpdb->dbh ) . "    raw: ".$wpdb->dbh ); 
        die;
      }
    }
}

//-------------------------------------------------------------

function ss_clear ($id) {
    if ($id == '') {$id = $_GET['ss_id'];}

    global $wpdb;
    $table_name = $wpdb->prefix . "spreadsheet";
   
    $htmlspreadsheet = wpSS_blank();

    $htmlspreadsheet = $wpdb->escape ($htmlspreadsheet);

    $update = "UPDATE ".$table_name.
            " SET ss_tablehtml = '".$htmlspreadsheet."'".
            ", ss_name = 'Cleared'".
            ", ss_description = 'Empty spreadsheet'".
            " WHERE id = $id LIMIT 1";
                
    $results = $wpdb->query( $update );

    ss_load($id); //reload cleared spreadsheet page
}


//-------------------------------------------------------------

function ss_new ($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . "spreadsheet";
   
    $htmlspreadsheet = wpSS_blank();

    $htmlspreadsheet = $wpdb->escape ($htmlspreadsheet);
  
    $insert = "INSERT INTO ".$table_name.
              " (ss_tablehtml, ss_name, ss_description) ".
              "VALUES ('".$htmlspreadsheet."', 'New', 'Blank spreadsheet');";
                          
    $results = $wpdb->query( $insert );  
}

//-------------------------------------------------------------

function ss_load ($id, $plain=FALSE) {
   if ($id == '') {$id = $_GET['ss_id'];}

   global $wpdb;
   $table_name = $wpdb->prefix . "spreadsheet";

   
    //check to see if spreadsheet #id exists
    if ($wpdb->query("SELECT * FROM $table_name WHERE id='$id'") == 0) {
       //no, so insert a blank spreadsheet
       ss_new ($id);
    }

   
      $sql = "SELECT * FROM $table_name WHERE id = $id;";
      $ss_row = $wpdb->get_row( $sql );
    
      $ss_tablehtml = stripslashes ($ss_row->ss_tablehtml);
      $ss_name = stripslashes ($ss_row->ss_name);
      $ss_description = stripslashes ($ss_row->ss_description);

//only apply autolocking if... we are not referred from the wp-admin directory
// e.g. (editing wpss from within the Write admin menu)
// or if &edit=true (set only from within editpage routine)... 
// debug: print $_SERVER['HTTP_REFERER'] . "  <p> " ;
// I'd also thought about not applying it to certain roles... could still checkbox this...
//  if (!current_user_can('administrator')) {

  //get locked cols options
  $wpSS_autolock = get_option('wpSS_autolock');

//new problem with autolocking after I hit the save/reload  button twice!!!!
//debug: print (stristr ($_SERVER['HTTP_REFERER'], '/wp-admin/'));
//debug: print stristr($_SERVER['HTTP_REFERER'], 'edit=true');
  $editpage = false;
  if (  !(stristr($_SERVER['HTTP_REFERER'], '/wp-admin/') === FALSE) or 
        !(stristr($_SERVER['HTTP_REFERER'], 'edit=true') === FALSE) ) 
  { $editpage = "true";}  //set to pass &edit=true param 
  // don't do autolocking if either was true (we came from the edit wpss admin page...)
  else {    

    if ($wpSS_autolock[$id.'_rows'] != "" or $wpSS_autolock['all_rows'] != "" ) {
      $wpSS_autolock_rows = $wpSS_autolock[$id.'_rows'];
      if ($wpSS_autolock['all_rows'] != "") { $wpSS_autolock_rows .= "," .$wpSS_autolock['all_rows']; } 

      $locked_row = explode(",", $wpSS_autolock_rows);
    
      //locks entire rows from modification
      if ($locked_row != "") {  
        foreach  ($locked_row as $pattern) {
          $pattern = 's1_'.$pattern.'x([0-9]*)';
          $replacement = '\0" class="auto_locked';
          $ss_tablehtml = ereg_replace($pattern, $replacement, $ss_tablehtml);
        }
      }
    }
  
    if ($wpSS_autolock[$id.'_cols'] != "" or $wpSS_autolock['all_cols'] != "" ) {
  
      $wpSS_autolock_cols = $wpSS_autolock[$id.'_cols'];
      if ($wpSS_autolock['all_cols'] != "") { $wpSS_autolock_cols .= "," .$wpSS_autolock['all_cols']; } 

      $locked_col = explode(",",$wpSS_autolock_cols);
    
      //locks entire columns from modification
      if ($locked_col != "") {  
        foreach  ($locked_col as $pattern) {
          $pattern = 's1_([0-9]*)x'.$pattern;
          $replacement = '\0" class="auto_locked';
          $ss_tablehtml = ereg_replace($pattern, $replacement, $ss_tablehtml);
        }
      }
    }
  }
//debug SQL stuff  
//    echo "pattern: $pattern";
//    echo htmlspecialchars(($ss_tablehtml), ENT_QUOTES);
// die;

    $ss_location="";

    include ("SS_header.php");
    
    echo $ss_tablehtml;
 
    include ("SS_footer.php");
}

//-------------------------------------------------------------

//check for id in table and return TRUE or FALSE
function ss_idExists($id, $table_name) {
  global $wpdb;
    $wpdb->hide_errors();
      $result = $wpdb->query("SELECT * FROM '$table_name' WHERE id='$id'");
    $wpdb->show_errors();
  return $result;
}

?>
