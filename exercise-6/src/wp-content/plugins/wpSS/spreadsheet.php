<?php

/*
Plugin Name: Spreadsheet for WordPress 
Plugin URI: http://timrohrer.com/blog/?page_id=71
Description: This is a simple spreadsheet plugin 
Author: Tim Rohrer (report bugs to bugs[at]timrohrer.com) 
Version: 0.6
Author URI: http://timrohrer.com/blog/#
*/

$wpSSdir = 'wpSS';

add_action('activate_'.$wpSSdir.'/spreadsheet.php','ss_install');
add_action('admin_menu', 'wpSS_add_page');
add_filter('the_content','ss_filter_spreadsheet_tag');

//-------------------------------------------------------------

function ss_install () {
   global $wpdb;

   $table_name = $wpdb->prefix . "spreadsheet";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE ".$table_name." (
              id mediumint(9) NOT NULL,
              ss_tablehtml TEXT, 
              ss_name TEXT,
              ss_description TEXT,
      	      UNIQUE KEY id (id)
      );";

      require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      dbDelta($sql);
      
    $htmlspreadsheet = wpss_initial();

    $htmlspreadsheet = $wpdb->escape ($htmlspreadsheet);
  
    $insert = "INSERT INTO ".$table_name.
              " (ss_tablehtml, ss_name, ss_description, id) ".
              "VALUES ('".$htmlspreadsheet."', 'New', 'Blank spreadsheet', '1');";
                          
    $results = $wpdb->query( $insert );

    }
}

//-------------------------------------------------------------

function ss_filter_spreadsheet_tag( $content ) {
  $spreadsheet_tag = "#{spreadsheet\s*.*?}#s";
  
  $content = preg_replace_callback ($spreadsheet_tag, 'ss_parser', $content);

return $content;
}

//-------------------------------------------------------------

/**
* Callback that replaces spreadsheet tags (in $matches array} with an iframe
* @param array An array of matches (see preg_match_all)
* @returns html_code
*/
function ss_parser( &$matches ) {
   global $wpdb;
   global $wpSSdir;

  $table_name = $wpdb->prefix . "spreadsheet";

	// Check if default information i=needs to be replaced
	$tipline = $matches[0];
	$pos = strpos( $tipline, '}' );
	$tipline = substr( $tipline, 0, $pos );
	$lineparams = explode( ' ', $tipline );

	for( $i=0; $i<count($lineparams); $i++ ) {
		$line_param = explode( '=', $lineparams[$i] );

		switch( $line_param[0] )
		{
            case 'display':
                $display = $line_param[1];
                break;

            case 'id':
                $id = $line_param[1];
                break;
		}
	}

// if id is set and not id=x as in example...
	if( $id != '' and $id != 'x') {
    $nl = "\n";
    $text = $nl.$nl."        ";
    
	  switch($display ) {
  	  //if interactive, ss has save, clear, reload buttons (set in ss_load.php)
      case 'interactive':  
        $text .= "<iframe onlad='iFrameHeight()' height='600' class='wrapper1' src='" ;
        $text .= "wp-content/plugins/$wpSSdir/ss_load.php?ss_id=$id&display=interactive";
        $text .= "' name='iframe' align='top' id='SS_iframe' width='100%' marginwidth='0' marginheight='0' scrolling='no' frameborder='15'><b>Error:</b> Inline frames must be enabled to use WordPress SpreadSheet</iframe>";
        break;
    
      //if display=table, just render ss as html table
      case 'table':
        $sql = "SELECT ss_tablehtml FROM $table_name WHERE id = $id;";
        $ss_tablehtml = $wpdb->get_var( $sql);
        $ss_tablehtml = stripslashes( $ss_tablehtml );

        $text =  preg_replace( '#TABLE width="(\d)*"#', 'TABLE width="100%"',$ss_tablehtml); 
        $text =  str_replace( 'border="0"', 'border="1"', $text); 
        break;

      //if limited, plain or default, no save-clear-load buttons, minor interactivity
      case 'limited':
      case 'plain':
      default:  
//  		  $text .= ' plain ';
        $text .= "<iframe onlad='iFrameHeight()' height='540' class='wrapper1' src='" ;
        $text .= "wp-content/plugins/$wpSSdir/ss_load.php?ss_id=$id&display=plain";
        $text .= "' name='iframe' align='top' id='SS_iframe' width='100%' marginwidth='0' marginheight='0' scrolling='no' frameborder='0'><b>Error:</b> Inline frames must be enabled to use WordPress SpreadSheet</iframe>";
        break;    
		}

  //else warn that id is not set
	} else {
		  if ($id == 'x') {
        $text = $matches[0];
      } else {
        $text = "<b>WordPress Spreadsheet: The {spreadsheet} tag is missing the id=x parameter!</b>";
      }
	}

	return $text;
}

//-------------------------------------------------------------

// add SpreadSheet editing page to WordPress
function wpSS_add_page()
{
    $wpSS_subpanel_title = "wpSpreadSheets";
    add_submenu_page('post.php', $wpSS_subpanel_title, $wpSS_subpanel_title, 6, __FILE__, 'wpSS_editing_page');
    add_submenu_page('options-general.php', 'wpSpreadSheets', 'wpSpreadSheets', 10, __FILE__, 'wpSS_options_page');
}

//-------------------------------------------------------------

function wpSS_editing_page()
{
   global $wpSSdir;

//options here
  $id = get_option('wpSS_default_id');
  if ($id == 0) { $id = 1; }

  print '
    <div class="wrap">
      <h2>Editing WordPress SpreadSheets</h2>
      <P>Remember: This editing page never displays columns or rows as autolocked.  You can set up autolocking, as well as the default SS_ID for this page from the wpSpreadSheet page under the Options menu.</P>
      ';
// insert interactive iframe

    $nl = "\n";
    $text .= $nl.$nl."      <iframe height='600' class='wrapper1' src='" ;
    $text .= "../wp-content/plugins/$wpSSdir/ss_load.php?ss_id=$id&display=interactive&edit=true";
    $text .= "' name='EditwpSS' align='top' id='SS_iframe' width='100%' marginwidth='0' marginheight='0' scrolling='no' frameborder='0'><b>Error:</b> Inline frames must be enabled to use WordPress SpreadSheet</iframe>";

  print $text;

  print "</div>";

}

//-------------------------------------------------------------

function wpSS_options_page() {

  $wpSS_default_id = get_option('wpSS_default_id');
  if ($wpSS_default_id == "") { $wpSS_default_id = 1; }

  $wpSS_autolock = get_option('wpSS_autolock');

  if ( $_POST['submit'] == "Retrieve Options" ) {
      $wpSS_autolock_id = $_POST['wpSS_autolock_id'];
      $wpSS_autolock_rows = $wpSS_autolock[$wpSS_autolock_id.'_rows'];
      $wpSS_autolock_cols = $wpSS_autolock[$wpSS_autolock_id.'_cols'];      
  }
  elseif ( $_POST['submit'] == "Update Options" ) {
      update_option('wpSS_default_id', $_POST['wpSS_default_id']);
      $wpSS_default_id = get_option('wpSS_default_id');
      if ($wpSS_default_id == "") { $wpSS_default_id = 1; }

      $wpSS_autolock_id = $_POST['wpSS_autolock_id'];
      // put new options into array
      $wpSS_autolock[$wpSS_autolock_id] = $_POST['wpSS_autolock_id'];
      $wpSS_autolock[$wpSS_autolock_id.'_rows'] = $_POST['wpSS_autolock_rows'];
      $wpSS_autolock[$wpSS_autolock_id.'_cols'] = $_POST['wpSS_autolock_cols'];

      update_option('wpSS_autolock', $wpSS_autolock);
      
      $wpSS_autolock_id = $_POST['wpSS_autolock_id'];
      $wpSS_autolock_rows = $wpSS_autolock[$wpSS_autolock_id.'_rows'];
      $wpSS_autolock_cols = $wpSS_autolock[$wpSS_autolock_id.'_cols'];      
  }
  else {
      if ($wpSS_autolock_id == "" ) {$wpSS_autolock_id = $wpSS_default_id; }
      $wpSS_autolock_rows = $wpSS_autolock[$wpSS_autolock_id.'_rows'];
      $wpSS_autolock_cols = $wpSS_autolock[$wpSS_autolock_id.'_cols'];
  }
  
  print '  

    <div class="wrap">
      <h2>WordPress SpreadSheet Options</h2>';

  print '  

      <form method="post" action="">
      
        <div style="clear: both;padding-top:10px;">
          <label style="float:left;width:350px;text-align:right;padding-right:6px;padding-top:7px;" for="wpSS_default_id">Spreadsheet id # on edit wpSS page defaults to: </label>
          <div style="float:left;">
            <input type="text" id="wpSS_default_id" name="wpSS_default_id" size="5" maxlength="127" value="' . $wpSS_default_id . '" /> 
          </div>
        </div>
       
<p> &nbsp;</p>       
<div class="wrap">

        <div style="clear: both;padding-top:10px;">
          <label style="float:left;width:350px;text-align:right;padding-right:6px;padding-top:7px;" for="wpSS_autolock_id">Modify autolock settings for spreadsheet id (# or all): </label>
          <div style="float:left;">
            <input type="text" id="wpSS_autolock_id" name="wpSS_autolock_id" size="5" maxlength="127" value="' . $wpSS_autolock_id . '" /> 
          </div>
        </div>

        <div style="clear: both;padding-top:10px;">
          <label style="float:left;width:350px;text-align:right;padding-right:6px;padding-top:7px;" for="wpSS_autolock_rows">Auto-lock these rows (e.g.: 1,2,5): </label>
          <div style="float:left;">
            <input type="text" id="wpSS_autolock_rows" name="wpSS_autolock_rows" size="30" maxlength="127" value="'. $wpSS_autolock_rows . '" />
          </div>
        </div>

        <div style="clear: both;padding-top:10px;">
          <label style="float:left;width:350px;text-align:right;padding-right:6px;padding-top:7px;" for="wpSS_autolock_cols">Auto-lock these columns (e.g.: for B,C,E enter 2,3,5): </label>
          <div style="float:left;">
            <input type="text" id="wpSS_autolock_cols" name="wpSS_autolock_cols" size="30" maxlength="127" value="'. $wpSS_autolock_cols . '"; />
          </div>
        </div>
   
<p> &nbsp;</p>       
</div>  
        <div style="clear: both;padding-top:10px;text-align:center;">
          <p class="submit">
            <input type="submit" name="submit" value="Retrieve Options" />
            <input type="submit" name="submit" value="Update Options" />
          </p>
        </div>
          
      </form>
  </div>
';
}

//-------------------------------------------------------------

function wpSS_initial() {
    include ("wpSS_blank.php");
    return $htmlspreadsheet;
}

?>
