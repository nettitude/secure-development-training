<?php
//#################################################################

// get and set path of function

if (!$_POST){
	$mypath=$_GET['myPath'];

}
else {
	$mypath=$_POST['myPath'];
	
	
}
require_once($mypath.'/wp-config.php');
require_once($mypath.'/wp-admin/admin.php');		

define ('myscriptpath',get_settings('siteurl').'/wp-content/plugins/mygallery/myfunctions/');


//#################################################################
// check if user has the rights to use the function 

get_currentuserinfo();
if ($user_level < 6)
{
	return;
}

//#################################################################

// load language

if ($mg_options[language]) load_textdomain('myGallery', myGalleryPath.'/languages/'.$mg_options[language].'.mo');

//#################################################################

if (!function_exists('getmygallerys')) {
	_e('Function library mygalleryfunctions.php was not found.', 'myGallery');
	die;
}

//#################################################################

// used variables

// $myaction=$_POST[myaction];
$myurl=get_bloginfo('wpurl').'/'.$mg_options[gallerybasepath];
$showgallery=$_POST[showgallery];
$uploadpic=$_POST[uploadpic];

if ($uploadpic) {
	$message=popbrowserupload($showgallery);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>myGallery Browser</title>
<link rel="stylesheet" href="<?php echo get_settings('siteurl') ?>/wp-admin/wp-admin.css?version=<?php bloginfo('version'); ?>" type="text/css" />
<script type="text/javascript" src="<?php echo get_settings('siteurl').'/wp-content/plugins/mygallery/jss/myinsert.js'?>">
</script>
</head>
<body>
<?php if ($message) { echo '<div class="updated"><p><strong>'.$message.'</strong></p></div>';  $message=''; }?>
<div class="wrap">
<h2><?php _e('myGallery Browser', 'myGallery') ;?></h2>
<form name="selectgal" id="post" method="post" action="<?php echo myscriptpath ?>mygallerybrowser.php" ENCTYPE="multipart/form-data">
<input type="hidden" name="myPath" value="<?php echo $mypath ?>">
<fieldset class="options">
<select name="showgallery" id="showgallery" onchange=selectgal.submit()>
<?php

$gallerys=getmygallerys();

if (is_array($gallerys)) {
	echo "\n\t<option>".__('select gallery', 'myGallery')."</option>";
	foreach ($gallerys as $x) {
		echo "\n\t<option value='$x->id' >$x->name</option>";
	}
}
?>
</select>
<input id="suf" type="Button" value="<?php _e('show upload Form', 'myGallery') ?>" onClick="showfieldset('uploadform','huf','suf')" disabled='disabled' />
<input id="huf" type="Button" value="<?php _e('hide upload Form', 'myGallery') ?>" onClick="hidefieldset('uploadform','suf','huf')" style="display:none"/>
<input id="smt" type="Button" value="<?php _e('show myGallery Tags', 'myGallery') ?>" onClick="showfieldset('mygaltag','hmt','smt')"  disabled='disabled' />
<input id="hmt" type="Button" value="<?php _e('hide myGallery Tags', 'myGallery') ?>" onClick="hidefieldset('mygaltag','smt','hmt')"style="display:none" />
<br />
</fieldset>
</form>
<?php

// show slelected gallery 

if ($showgallery) {
	
	$mg_options=get_option('mygalleryoptions');
	$thepictures=getmypictures($showgallery);
	$galleryname=getgalleryname($showgallery);
	
	?>
	<script type="text/javascript">
	document.getElementById('suf').disabled = false;
	document.getElementById('smt').disabled = false;
	</script>
	<form name="pageform" id="post" method="post" action="<?php echo myscriptpath ?>mygallerybrowser.php" ENCTYPE="multipart/form-data">
	<input type="hidden" name="myPath" value="<?php echo $mypath ?>">
	<input type="hidden" name="showgallery" value="<?php echo $showgallery ?>">
	<input type="hidden" name="uploadpic" value="1">
	<fieldset class="options" id="uploadform" style="display:none">
	<legend><?php _e('Upload a picture', 'myGallery') ;?></legend> <br />
	<input type="file" name="picturefile" id="picturefile" size="35" class="uploadform"><input type="submit" value="<?php _e('upload', 'myGallery') ;?>">
	<br /><br />Alt &amp; Title <?php _e('Text', 'myGallery') ;?>: <input type="text" size="20"  name="alttitle" value="">
	</fieldset>
	</form>
	<fieldset class="options" id="mygaltag" style="display:none">
	<legend>myGallery Tags</legend> <br />
	<input type="Button" value="<?php _e('gallery overview', 'myGallery') ;?>" onClick="insert_at_position('[mygallistgal]')" />
	<input type="Button" value="<?php _e('gallery ', 'myGallery') ;?>" onClick="insert_at_position('[mygal=<?php echo $galleryname ?>]')" />
	<input type="Button" value="<?php _e('inlinegallery', 'myGallery') ;?>" onClick="insert_at_position('[myginpage=<?php echo $galleryname ?>]')" />
	</fieldset
	<fieldset class="options">
	<legend><?php _e('Gallery pictures from', 'myGallery') ;?> <b><?php echo $galleryname; ?></b></legend> <br />
	<form name="picsettings">
	<table width="100%" cellspacing="2" cellpadding="5" >
	<tr>
	<th scope="col"><?php _e('Thumbnail', 'myGallery') ;?></th><th scope="col"><?php _e('Align', 'myGallery') ;?></th><th scope="col"><?php _e('Popup', 'myGallery') ;?></th><th scope="col"><?php _e('Size', 'myGallery') ;?></th><th scope="col"><?php _e('Lightbox group', 'myGallery') ;?></th><th scope="col"><?php _e('Tag', 'myGallery') ;?></th>
	</tr>
	<?php
	if (is_array($thepictures)) {
		foreach ($thepictures as $x) {
			
			$class = ('alternate' == $class) ? '' : 'alternate';
			echo '<tr valign="top" class="'.$class.'">';
			echo '<td style="text-align:center"> <img src="'.$myurl.$x->name.'/tumbs/tmb_'.$x->picturepath.'"  alt="'.$x->alttitle.'" title="'.$x->alttitle.'" /></td>';
			echo '<td>
			<select name="picalign['.$x->pid.'"] id="picalign['.$x->pid.']" >
			<option value="none">'.__('none', 'myGallery').'</option>
			<option value="left">'.__('left', 'myGallery').'</option>
			<option value="right">'.__('right', 'myGallery').'</option>
			<option value="leftclear">'.__('leftclear', 'myGallery').'</option>
			<option value="rightclear">'.__('rightclear', 'myGallery').'</option>
			</select>
			</td>';
			echo '<td>
			<select name="popup['.$x->pid.']" id="popup['.$x->pid.']">
			<option value="none">'.__('none', 'myGallery').'</option>
			<option value="fullscreen">'.__('fullscreen', 'myGallery').'</option>
			<option value="gal">'.__('gal', 'myGallery').'</option>
			<option value="http://">'.__('URL', 'myGallery').'</option>
			</select>
			</td>';
			echo '<td>
			<input name="picsize'.$x->pid.'"  type="radio" value="none" checked="checked" />'.__('none', 'myGallery') .'<br />
			<input name="picsize'.$x->pid.'"  type="radio" value="thumb" />'.__('thumb', 'myGallery').'<br />
			<input name="picsize'.$x->pid.'"  type="radio" value="scale" /> '.__('scaled by', 'myGallery') .' <input type="text" size="3" id="picsize['.$x->pid.']" value="" /> 
			</td>';
			echo '<td>
			<input name="lightboxgroup'.$x->pid.'"  type="radio" value="none" checked="checked" /> '.__('none', 'myGallery') .'<br />
			<input name="lightboxgroup'.$x->pid.'"  type="radio" value="group" /> <input type="text" size="8" id="lightboxgroup['.$x->pid.']" value="" /> 
			</td>';
			echo '<td>
			<input type="Button" value="'.__('picture', 'myGallery').'" onClick="insert_wrapper('.$x->pid.')" />
			<br />
			<input type="Button" value="'.__('link', 'myGallery').'" onClick="linktext_wrapper('.$x->pid.',\''.__('Your link text', 'myGallery').'\')" />
			</td>'."\n";	
			echo '</tr>';
		}
	}
	?>
	</table>
	</form>
	</fieldset>
	<?php
}	

echo '</div><body></html>';


//#################################################################

function getmypictures($myid) {
	
	global $table_prefix, $wpdb,$mg_options;
	
	// get order option
	
	$mg_options=get_option('mygalleryoptions');
	if ($mg_options[sortorder]==1) $mysort='id';
	if ($mg_options[sortorder]==2) $mysort='picturepath';
	if ($mg_options[sortorder]==3) $mysort=' picsort';
	if ($mg_options[sortascdes]==2) $sortorder=' DESC';
	
	
	$thepictures = $wpdb->get_results('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid AND '.$table_prefix.'mygprelation.gid = '.$myid.' AND '.$table_prefix.'mygallery.id ='.$myid. ' ORDER BY '.$table_prefix.'mypictures.'.$mysort.$sortorder );
	
	return $thepictures;
	
}

//#################################################################

function getmypicture_ids($myid) {
	
	global $table_prefix, $wpdb;
	
	$thepictures = $wpdb->get_results('SELECT '.$table_prefix.'mypictures.id FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid AND '.$table_prefix.'mygprelation.gid = '.$myid.' AND '.$table_prefix.'mygallery.id ='.$myid);
	
	return $thepictures;
	
}

//#################################################################


function popbrowserupload($mygallery_id) {
	
	global $table_prefix, $wpdb, $mypath;

	// used variables
	
	$uploadfile=$_FILES;
	$mg_options=get_option('mygalleryoptions');
	$zipdir=$mypath.'/'.$mg_options[gallerybasepath].getgalleryname($mygallery_id);
	$shrinkfit=$mg_options[shrinkfit];
	$myalttitle=$_POST['alttitle'];
	
	// get extension of uploaded file
	
	$my_extension = strtolower(end(explode('.', $uploadfile['picturefile']['name'])));
	
	if (!$uploadfile['picturefile']['tmp_name']) return __('No file was submitted', 'myGallery');
	
	
	
	if (in_array ($my_extension, $mg_options[allowedfiletypes])) {
		
		// move picture to destination
		
		@move_uploaded_file($uploadfile['picturefile']['tmp_name'],$zipdir.'/'.$uploadfile['picturefile']['name']) or die  ('<div class="updated"><p><strong>'.__('Unable to move file ', 'myGallery').$uploadfile.'!</strong></p></div>');
		
		$my_images= array ($uploadfile['picturefile']['name']);
		@chmod ($zipdir.'/'.$uploadfile['picturefile']['name'], file_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for file ', 'myGallery').$zipdir.'/'.$uploadfile['picturefile']['name'].'!</strong></p></div>');
		
	}
	else {
		
		// if filetype was not allowed
		
		$message=__('Not a supported file format', 'myGallery');
		
		// delete tmp-file
		
		@unlink($my_zipfile) or die  ('<div class="updated"><p><strong>'.__('Unable to unlink unsupported file ', 'myGallery').$my_zipfile.'!</strong></p></div>');
		
		return $message;
	}
	
	$message=__('Picture added to gallery.', 'myGallery');
	
	if (is_array($my_images)) {
		
		foreach ($my_images as $stored_file) {
			
			$my_extension = strtolower(end(explode('.', $stored_file)));
			
			// put picture in into the database
			
			$wpdb->query('INSERT INTO '.$table_prefix.'mypictures (picturepath,alttitle) VALUES ("'.$stored_file.'","'.$myalttitle.'")');
			$mypicture_id=$wpdb->get_var('SELECT LAST_INSERT_ID()');
			$wpdb->query('INSERT INTO '.$table_prefix.'mygprelation (gid, pid) VALUES ("'.$mygallery_id.'","'.$mypicture_id.'")');
			generatethumbnail($zipdir, $stored_file,$shrinkfit);
		}
	}
	
	return $message;
}

//#################################################################
?>