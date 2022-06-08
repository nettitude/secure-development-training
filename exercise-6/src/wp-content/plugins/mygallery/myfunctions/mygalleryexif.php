<?php 
//#################################################################

// check if user has the rights to use the function 

get_currentuserinfo();
if ($user_level < 6)
{
	return;
}

//#################################################################

// Change exif options

if ($_POST[myaction]=='setexifoptions'){
	
	$mg_exifoptions[showexif]=$_POST['showexif'];
	$mg_exifoptions[make]=$_POST['make'];
	$mg_exifoptions[model]=$_POST['model'];
	$mg_exifoptions[exposuretime]=$_POST['exposuretime'];
	$mg_exifoptions[afnumber]=$_POST['afnumber'];
	$mg_exifoptions[focall]=$_POST['focall'];
	$mg_exifoptions[iso]=$_POST['iso'];
	$mg_exifoptions[flash]=$_POST['flash'];
	$mg_exifoptions[datime]=$_POST['datime'];
	$mg_exifoptions[expro]=$_POST['expro'];
	$mg_exifoptions[exifskipempty]=$_POST['exifskipempty'];
	$mg_exifoptions[shutterspeed]=$_POST['shutterspeed'];
	
	update_option('mygexifoptions', $mg_exifoptions);
	
	echo '<div id="message" class="updated fade"><p><strong>'.__('Options updated.', 'myGallery').'</strong></p></div>';	
}

// Get exif settings

$mg_exifoptions=get_option('mygexifoptions');

if ($mg_exifoptions[showexif]) $mycheckbox_showexif='checked="checked"';
if ($mg_exifoptions[make]) $mycheckbox_make='checked="checked"';
if ($mg_exifoptions[model]) $mycheckbox_model='checked="checked"';
if ($mg_exifoptions[exposuretime]) $mycheckbox_exposuretime='checked="checked"';
if ($mg_exifoptions[afnumber]) $mycheckbox_afnumber='checked="checked"';
if ($mg_exifoptions[focall]) $mycheckbox_focall='checked="checked"';
if ($mg_exifoptions[iso]) $mycheckbox_iso='checked="checked"';
if ($mg_exifoptions[flash]) $mycheckbox_flash='checked="checked"';
if ($mg_exifoptions[datime]) $mycheckbox_datime='checked="checked"';
if ($mg_exifoptions[expro]) $mycheckbox_expro='checked="checked"';
if ($mg_exifoptions[exifskipempty]) $mycheckbox_exifskipempty='checked="checked"';
if ($mg_exifoptions[shutterspeed]) $mycheckbox_shutterspeed='checked="checked"';

// HTML-Form

?>
<link rel="stylesheet" href="<?php bloginfo('wpurl') ?>/wp-content/plugins/mygallery/css/dbx_myg.css" type="text/css" />
<script type="text/javascript" src="../wp-includes/js/dbx.js"></script>
<script type="text/javascript" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/mygallery/jss/dbx-key.js"></script>
<div class="wrap">
<h2><?php _e('Exif Settings', 'myGallery') ;?></h2>
<form name="pageform" id="post" method="post" action="admin.php?page=mygallery/myfunctions/mygalleryexif.php" accept-charset="utf-8" ENCTYPE="multipart/form-data">
<input type="hidden" name="myaction" value="setexifoptions">
<div id="mgexif" class="dbx-group" >
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Show Exif Informations', 'myGallery') ;?></h3>
<div class="dbx-content">
<input name="showexif" type="checkbox" id="showexif" value="1"  <?php echo $mycheckbox_showexif;?> /> <?php _e('Enable Exif for gallery pictures', 'myGallery') ;?><br />
<input name="exifskipempty" type="checkbox" id="exifskipempty" value="1"  <?php echo $mycheckbox_exifskipempty;?> /> <?php _e('Skip empty values', 'myGallery') ;?><br />
</div>
</fieldset>
</p>
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Show values for', 'myGallery') ;?></h3>
<div class="dbx-content">
<input name="make" type="checkbox" id="make" value="1"  <?php echo $mycheckbox_make;?> /> <?php _e('Make', 'myGallery') ;?><br />
<input name="model" type="checkbox" id="model" value="1"  <?php echo $mycheckbox_model;?> /> <?php _e('Model', 'myGallery') ;?><br /> 
<input name="exposuretime" type="checkbox" id="exposuretime" value="1"  <?php echo $mycheckbox_exposuretime;?> /> <?php _e('Exposure Time', 'myGallery') ;?><br />
<input name="afnumber" type="checkbox" id="afnumber" value="1"  <?php echo $mycheckbox_afnumber;?> /> <?php _e('Aperture FNumber', 'myGallery') ;?><br /> 
<input name="focall" type="checkbox" id="focall" value="1"  <?php echo $mycheckbox_focall;?> /> <?php _e('Focal Length', 'myGallery') ;?><br /> 
<input name="iso" type="checkbox" id="iso" value="1"  <?php echo $mycheckbox_iso;?> /> <?php _e('ISO Speed Ratings', 'myGallery') ;?><br />
<input name="flash" type="checkbox" id="flash" value="1"  <?php echo $mycheckbox_flash;?> /> <?php _e('Flash', 'myGallery') ;?><br /> 
<input name="datime" type="checkbox" id="datime" value="1"  <?php echo $mycheckbox_datime;?> /> <?php _e('Date and Time', 'myGallery') ;?><br /> 
<input name="expro" type="checkbox" id="expro" value="1"  <?php echo $mycheckbox_expro;?> /> <?php _e('Exposure Program', 'myGallery') ;?><br /> 
<input name="shutterspeed" type="checkbox" id="shutterspeed" value="1"  <?php echo $mycheckbox_shutterspeed;?> /> <?php _e('Shutter Speed', 'myGallery') ;?><br /> 
</div>
</fieldset>
</p>
</div>
<div class="submit"><input type="submit" value="<?php _e('save changes', 'myGallery') ;?>"></div>
</form>
</div>

<?php



?>
