<?php
//#################################################################

// check if user has the rights to use the function 

get_currentuserinfo();
if ($user_level < 10)
{
	return;
}
//#################################################################

// used variables

$mg_options=get_option('mygalleryoptions');
$mystylepath=myGalleryPath.'css/'.$mg_options[stylefile];

// update settings

if ($_POST[myaction]=='setstyleoption'){

	$mg_options[includestyle]=$_POST[includestyle];
	$mg_options[stylefile]=$_POST[stylefile];
	
	update_option('mygalleryoptions', $mg_options);
	
	$newstyle = stripslashes($_POST[galleryystyle]);

	if (is_writeable($mystylepath)) {
		$f = fopen($mystylepath, 'w+');
		fwrite($f, $newstyle);
		fclose($f);
	}
	
	echo '<div id="message" class="updated fade"><p><strong>'.__('Stylesettings updated.', 'myGallery').'</strong></p></div>';

}

// read options and file


if ($mg_options[includestyle]) $mycheckbox_includestyle='checked="checked"';

switch ($mg_options[stylefile]) {
	
	case 'mygallery_default.css': $myradio_stylefile_1='checked="checked"';
	break;
	case 'mygallery.css': $myradio_stylefile_2='checked="checked"';
	break;
}


$mystle=implode("",file($mystylepath));


// HTML Part

?>
<div class="wrap">
<h2><?php _e('Style Options for myGallery', 'myGallery') ;?></h2>
<form name="pageform" id="post" method="post" action="admin.php?page=mygallery/myfunctions/mygallerystyle.php" ENCTYPE="multipart/form-data">
<input type="hidden" name="myaction" value="setstyleoption">
<p>
<fieldset class="options"><legend><?php _e('Style options', 'myGallery') ;?></legend>
<input name="includestyle" type="checkbox" id="includestyle" value="1"  <?php echo $mycheckbox_includestyle;?> /> <?php _e('Include myGallery style in activ theme', 'myGallery') ;?> <i>( <input name="stylefile" id="stylefile1" type="radio" value="mygallery_default.css"  <?php echo $myradio_stylefile_1;?> /> <?php _e('default Style or', 'myGallery') ;?> <input name="stylefile" id="stylefile2" type="radio" value="mygallery.css"  <?php echo $myradio_stylefile_2;?> /> <?php _e('3D border style', 'myGallery') ;?></i>)<br />
</fieldset>
</p>
<p>
<fieldset class="options"><legend><?php _e('myGallery style settings','myGallery') ;?></legend>
<textarea cols="70" rows="25" name="galleryystyle" id="galleryystyle" tabindex="1"><?php  echo $mystle; ?></textarea>
<p><em><?php if (!(is_writable ( $mystylepath ))) _e('If this file was writable you could edit it','myGallery'); ?></em></p>
</fieldset>
</p>
<div class="submit"><input type="submit" value="<?php _e('save changes', 'myGallery') ;?>"></div>
</form></div>
<?php

?>
