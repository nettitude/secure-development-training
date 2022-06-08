<?php
//#################################################################

// check if user has the rights to use the function 

get_currentuserinfo();
if ($user_level < 6)
{
	return;
}
//#################################################################

if (!function_exists('getmygallerys')) {
	_e('Function library mygalleryfunctions.php was not found.', 'myGallery');
	die;
}

//#################################################################

function createmygallery() {
	
	global $table_prefix, $wpdb, $allowedfiletypes;
	
	
	// used variables 
	
	$mg_options=get_option('mygalleryoptions');
	
	$myaction=$_POST[myaction];
	$shrinkfit=$mg_options[shrinkfit];
	$uploadfile=$_FILES;
	$mypath=ABSPATH.$mg_options[gallerybasepath];
	$scalethumb=$mg_options[scalethumb];
	$datasource=$_POST[datasource];
	$picturefolder=$_POST[picturefolder];
	$newgallery=$_POST[newgallery];
	$allowedfiletypes=$mg_options[allowedfiletypes];
	$mywebserver_mod=ini_get('safe_mode');
	
	if ($_POST[galleryselect]) $zipdir=$mypath.getgalleryname($_POST[galleryselect]);
	$mygallery_id=$_POST[galleryselect];
		
	if (($myaction) AND ($datasource != 4)) {
		
		switch ($datasource) {
			
			case 1:
			
			// a zipfile for a new gallery was uploaded
			
			$my_zipfile = $uploadfile['zipfile']['tmp_name'];
			$my_zipname = $uploadfile['zipfile']['name']; 
			
			// check if there is a file
			
			if (!$my_zipfile) return '<div class="updated"><p><strong>'.__('No file was submitted', 'myGallery').'.</strong></p></div>';
			
			// check if file is a zip file
			
			$my_extension = strtolower(end(explode('.', $uploadfile['zipfile']['name'])));
			if ($my_extension<>'zip') return '<div class="updated"><p><strong>'.__('Uploaded file was no zip file', 'myGallery').'!</strong></p></div>';
			
			// make new directory an get the name - convert upercase and spaces
		
			$zipdir = $mypath.preg_replace ("/(\s+)/", '-', strtolower(strtok ($my_zipname,'.')));
			$zipdirname=preg_replace ("/(\s+)/", '-', strtolower(strtok ($my_zipname,'.')));
			
			// check if directory exists
			
			if (!is_dir($zipdir)) {
				@mkdir ("$zipdir",directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to create directory ', 'myGallery').$zipdir.'!</strong></p></div>');
			}
			else {
				return '<div class="updated"><p><strong>'.__('Directory', 'myGallery').' '.$zipdirname.' '.__('exists', 'myGallery').'!</strong></p></div>';	
			}
			
			// unzip the file
			
			exec ("unzip -j $my_zipfile -d $zipdir") or die('<div class="updated"><p><strong>'.__('Unable to unzip!', 'myGallery').'</strong></p></div>');
			
			// change rights and delete tmp-file
			
			@chmod ($zipdir, directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for directory ', 'myGallery').$zipdir.'!</strong></p></div>');
			@unlink($my_zipfile)  or die  ('<div class="updated"><p><strong>'.__('Unable to unlink file ', 'myGallery').$my_zipfile.'!</strong></p></div>');
			
			// get pictures in folder
			
			list ($my_images, $mygallery_id)= getpicturesinfolder($zipdirname, $zipdir, 1);
			
			$message='<div class="updated"><p><strong>'.__('Gallery is ready!', 'myGallery').'</strong>  '.__('Use', 'myGallery').' [mygal='.$zipdirname.'] '.__('to insert gallery into a page', 'myGallery').'.</p></div>';
			
			break;
			
			case 2:
			
			// check if a folder name was submitted
			
			//$picturefolder=preg_replace ("/(\/|)(\[A-Za-z0-9\-]+)(.*|)/", "$2", $picturefolder); // clear folder name
			
			$picturefolder=preg_match("/[A-Za-z0-9\-\_]+/", $picturefolder,$results);
			
			if (is_array($results)) {
			    $picturefolder=$results[0];
			}

			if (!$picturefolder) return '<div class="updated"><p><strong>'.__('No folder name submitted', 'myGallery').'.</strong></p></div>';
			
			$zipdir = $mypath.$picturefolder;
			
			// check if directory exists
			
			if (!is_dir($zipdir)) return '<div class="updated"><p><strong>'.__('Directory', 'myGallery').' </strong>'.$picturefolder.' <strong>'.__('doesn&#96;t exist', 'myGallery').'!</strong></p></div>';
			
			if (!is_dir($zipdir.'/tumbs') AND ($mywebserver_mod)) return '<div class="updated"><p><strong>'.__('Directory', 'myGallery').' </strong>'.$picturefolder.'/tumbs  <strong>'.__('doesn&#96;t exist', 'myGallery').'!</strong></p></div>';
			
			// get pictures in folder
			
			list ($my_images, $mygallery_id)= getpicturesinfolder($picturefolder, $zipdir, 0);
			
			$oldgallery_id=galleryexists($picturefolder);
			
			if ($oldgallery_id) {
				$my_old_images=getstoredpics($oldgallery_id);
				if (is_array($my_old_images) AND is_array ($my_images)) $my_images=array_diff($my_images, $my_old_images);	
			}
			
			$message='<div class="updated"><p><strong>'.__('Gallery is ready!', 'myGallery').'</strong>'. __('Use', 'myGallery').' [mygal='.$picturefolder.'] '.__('to insert gallery into a page', 'myGallery').'.</p></div>';
			
			break;
			
			case 3:
			
			// get extension of uploaded file
			
			$my_extension = strtolower(end(explode('.', $uploadfile['picturefile']['name'])));
			
			if (!$uploadfile['picturefile']['tmp_name']) return '<div class="updated"><p><strong>'.__('No file was submitted', 'myGallery').'.</strong></p></div>';
			
			if (!$zipdir) return '<div class="updated"><p><strong>'.__('No gallery selected', 'myGallery').'!</strong></p></div>';
			
			if ($my_extension=='zip' AND (!$mywebserver_mod)) {
				
				// if file is a zipfile
				
				// creat tmp-dir for unzip
				
				$tmp_zipdir=$zipdir.'/newpics';
				@mkdir ("$tmp_zipdir",directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to creat temporary directory ', 'myGallery').$tmp_zipdir.'!</strong></p></div>');
				@chmod ("$tmp_zipdir", directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for temporary directory ', 'myGallery').$tmp_zipdir.'!</strong></p></div>');
				$tmp_zipdir=$tmp_zipdir.'/';
				
				$my_zipfile = $uploadfile['picturefile']['tmp_name'];
				
				// unzip the file
				
				exec ("unzip -j $my_zipfile -d $tmp_zipdir.'/'") or die('<div class="updated"><p><strong>'.__('Unable to unzip!', 'myGallery').'</strong></p></div>');
				
				// delete tmp-file
				
				@unlink($my_zipfile) or die  ('<div class="updated"><p><strong>'.__('Unable to remove file ', 'myGallery').$my_zipfile.'!</strong></p></div>');
				
				// read pictures
				
				$my_images = readtmpdir($tmp_zipdir);
				
				// compare with pictures in database
				
				$myoldpics=getstoredpics($mygallery_id);
				
				if (is_array($myoldpics)) $my_images=array_diff ($my_images, $myoldpics);
				
				
				// move pictures to gallery
				
				if (is_array($my_images)){
					foreach ($my_images as $mytmpfile) {
						rename($tmp_zipdir.$mytmpfile, $zipdir.'/'.$mytmpfile); 	
					}
				}
				// remove double pictures in tmp-folder
				
				$trashpics=readtmpdir($tmp_zipdir);
				
				if($trashpics) {
					if (is_array($trashpics)){
						foreach ($trashpics as $tmp) {
							@unlink($tmp_zipdir.$tmp) or die  ('<div class="updated"><p><strong>'.__('Unable to remove file ', 'myGallery').$tmp_zipdir.$tmp.'!</strong></p></div>');
						}
					}
				}
				
				// remove tmp-dir
				
				@rmdir($tmp_zipdir) or die  ('<div class="updated"><p><strong>'.__('Unable to unlink directory ', 'myGallery').$tmp_zipdir.'!</strong></p></div>');
			}
			
			else if (in_array ($my_extension, $allowedfiletypes)) {
				
				// if file is a single picture
				
				// move picture to destination
				
				@move_uploaded_file($uploadfile['picturefile']['tmp_name'],$zipdir.'/'.$uploadfile['picturefile']['name']) or die  ('<div class="updated"><p><strong>'.__('Unable to move file ', 'myGallery').$uploadfile.'!</strong></p></div>');
				
				$my_images= array ($uploadfile['picturefile']['name']);
				
				if (!$mywebserver_mod) {
					@chmod ($zipdir.'/'.$uploadfile['picturefile']['name'], file_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for file ', 'myGallery').$zipdir.'/'.$uploadfile['picturefile']['name'].'!</strong></p></div>');
				}
			}
			else {
				
				// if file was no zip or jpg delete tmp-file
				
				if (!$mywebserver_mod) {
					@unlink($uploadfile['picturefile']['tmp_name']) or die  ('<div class="updated"><p><strong>'.__('Unable to unlink unsupported file ', 'myGallery').$uploadfile['picturefile']['tmp_name'].'!</strong></p></div>');
				}
				return '<div class="updated"><p><strong>'.__('Not a supported file format', 'myGallery').'.</strong></p></div>';
			}
			$message='<div class="updated"><p><strong>'.__('Picture(s) added to gallery!', 'myGallery').'</strong></p></div>';
		}
		
		
		// generate thumbnails in new subfolder of the orignal folder - checks if folder exists
		
		if (!is_dir("$zipdir/tumbs")AND (!$mywebserver_mod)) {
			
			@mkdir ("$zipdir/tumbs",directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to creat folder for thumbnails ', 'myGallery').$zipdir.'/tumbs !</strong></p></div>');
			@chmod ("$zipdir/tumbs", directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for thumbnail folder ', 'myGallery').$zipdir.'/tumbs !</strong></p></div>');
			
		}
		else if (!is_dir("$zipdir/tumbs")) {
			return '<div class="updated"><p><strong>'.__('Create the folder <i>tumbs</i> in your gallery folder first.', 'myGallery').'.</strong></p></div>';
		}
		
		if (is_array($my_images)) {
			
			foreach ($my_images as $stored_file) {
				
				$my_extension = strtolower(end(explode('.', $stored_file)));
				
				// put picture in into the database
				
				$wpdb->query('INSERT INTO '.$table_prefix.'mypictures (picturepath) VALUES ("'.$stored_file.'")');
				
				$mypicture_id=$wpdb->get_var('SELECT LAST_INSERT_ID()');
				
				$wpdb->query('INSERT INTO '.$table_prefix.'mygprelation (gid, pid) VALUES ("'.$mygallery_id.'","'.$mypicture_id.'")');
				
				// do some other stuff
				
				generatethumbnail($zipdir, $stored_file,$shrinkfit);
			
		}
	}
	}
	
	if (($myaction) AND ($datasource ==4)) {
		
		$newgallery=strtolower(preg_replace ("/(\s+)/", '-',$newgallery));//remove spaces, convert to lowercase
		
		preg_match("/[A-Za-z0-9\-\_]+/", $newgallery,$results);
			
			if (is_array($results)) {
			    $newgallery=$results[0];
			}
	
		//$newgallery=preg_replace ("/(\/|)(\w+)(.*|)/", "$2", $newgallery); // clear folder name from path
		
		if (!$newgallery) return '<div class="updated"><p><strong>'.__('No valid gallery name!', 'myGallery'). '</strong></p></div>';	
		
		if (is_dir($mypath.$newgallery))return '<div class="updated"><p><strong>'.__('Directory', 'myGallery').' </strong>'.$newgallery.' <strong>'.__('exists!', 'myGallery').'</strong></p></div>';	
		
		// create new directories
		
		@mkdir ($mypath.$newgallery,directory_permissions)  or die  ('<div class="updated"><p><strong>'.__('Unable to create directory ', 'myGallery').$mypath.$newgallery.'!</strong></p></div>');
		@chmod ($mypath.$newgallery, directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for directory ', 'myGallery').$mypath.$newgallery.'!</strong></p></div>');
		@mkdir ($mypath.$newgallery.'/tumbs',directory_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to create directory ', 'myGallery').$mypath.$newgallery.'/tumbs !</strong></p></div>');
		chmod ($mypath.$newgallery.'/tumbs', directory_permissions)  or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for directory ', 'myGallery').$mypath.$newgallery.'/tumbs !</strong></p></div>');
		
		// add new galery to database
		
		$message =newgallery($newgallery);
	}
	return $message;
}

//#################################################################

if ($_POST[language]) {
	$mg_options[language]=$_POST[language];
	$mg_options[gallerybasepath]=$_POST[gallerybasepath];
	update_option('mygalleryoptions', $mg_options);	
	load_textdomain('myGallery', myGalleryPath.'/languages/'.$mg_options[language].'.mo');
}

if (!$mg_options[language]) firsttimerun();

$mypath=ABSPATH.$mg_options[gallerybasepath];

$mymessage=createmygallery();
echo $mymessage;

$gallerys=getmygallerys();
$mywebserver_mod=ini_get('safe_mode');
if ((gettype($mywebserver_mod) == 'string') && ($mywebserver_mod == 'off')) $mywebserver_mod = false;

?>
<div class="wrap">
<h2><?php _e('myGallery Generator', 'myGallery') ;?></h2>
<form name="pageform" id="post" method="post" action="admin.php?page=mygallery/myfunctions/mygallerymain.php" ENCTYPE="multipart/form-data">
<input type="hidden" name="myaction" value="unzip">
<p>
<fieldset class="options">
<legend align=top><?php _e('Select operation', 'myGallery') ;?></legend>
<?php if (!$mywebserver_mod){ ?>
<input name="datasource" type="radio" value="1"  checked="checked" /> <?php _e('Upload a zip file with images from my harddrive:', 'myGallery') ;?> <input type="file" name="zipfile" id="zipfile" size="35" class="uploadform"><br /><br />
<?php } ?>
<input name="datasource" type="radio" value="2"  /> <?php _e('Import pictures from a folder on the server:', 'myGallery') ;?> <code><?php echo $mypath ?></code> <input type="text" size="15" name="picturefolder" value="" />
<br /><br /><input name="datasource" type="radio" value="3" /> <?php _e('Upload to gallery', 'myGallery') ;?> <select name="galleryselect"><?php

if (is_array($gallerys)){
	foreach ($gallerys as $x) {
		echo "\n\t<option value='$x->id' >$x->name</option>";
	}
}
?></select> <?php _e('a single picture ', 'myGallery'); if (!$mywebserver_mod) _e(' or zip file', 'myGallery');?> <input type="file" name="picturefile" id="picturefile" size="35" class="uploadform"> 
<br /><br />
<?php if (!$mywebserver_mod){ ?>
<input name="datasource" type="radio" value="4" /> <?php _e('Create', 'myGallery') ;?> <input type="text" size="15" name="newgallery" value="" /> <?php _e('as new, empty gallery', 'myGallery') ;?>
<?php } ?>
</fieldset>
</p>
<p>
<i>( <?php _e('Authorized characters for file and folder names are', 'myGallery') ;?>: a-z, A-Z, 0-9, -, _ )</i>
</p>
<div class="submit"><input type="submit" value="<?php _e('submit', 'myGallery') ;?>"></div>
</form>
</div>
<?php
//#################################################################

function newgallery($mygalleryname) {
	
	global $table_prefix, $wpdb;
	
	// check if gallery exists in database
	
	$result=$wpdb->get_var('SELECT name FROM '.$table_prefix.'mygallery WHERE name ="'.$mygalleryname.'"');
	
	if ($result) return '<div class="updated"><p><strong>'.__('Gallery', 'myGallery').'</strong>'.$mygalleryname.'<strong> exists.</strong></p></div>';
	
	$wpdb->query('INSERT INTO '.$table_prefix.'mygallery (name) VALUES ("'.$mygalleryname.'")');
	
	return '<div class="updated"><p><strong>'.__('Gallery was created!', 'myGallery').'</strong></p></div>';

}

//#################################################################

function getpicturesinfolder($myzipdirname, $myzipdir, $myzipflag) {

	global $table_prefix, $wpdb, $allowedfiletypes;
	
	$mywebserver_mod=ini_get('safe_mode');
	
	// put directoryname into database
	
	if (!galleryexists($myzipdirname))$wpdb->query('INSERT INTO '.$table_prefix.'mygallery (name) VALUES ("'.$myzipdirname.'")');
		
	// get id of the new gallery
		
	$mygallery_id=$wpdb->get_var('SELECT id FROM '.$table_prefix.'mygallery WHERE name ="'.$myzipdirname.'"');

	// read directory and save  files in array, change rights of every picture
		
	$my_images =array ();
		
	if (is_dir($myzipdir)) {
		if ($dh = opendir($myzipdir)) {
			while (($file = readdir($dh)) !== false) {
				$extension = strtolower(end(explode('.', $file)));
				if ((in_array ($extension, $allowedfiletypes) AND (!preg_match ('/^\._/',$file)))){
					array_push($my_images,"$file");
					if ($myzipflag AND (!$mywebserver_mod)) @chmod ($myzipdir.'/'.$file, file_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for file ', 'myGallery').$myzipdir.'/'.$file.'!</strong></p></div>');

				}
				else {
					if (!is_dir($myzipdir.'/'.$file) AND (!$mywebserver_mod)) @unlink($myzipdir.'/'.$file)  or die  ('<div class="updated"><p><strong>'.__('Unable to remove unsupported file ', 'myGallery').$myzipdir.'/'.$file.'!</strong></p></div>');
				}
			}
			closedir($dh);
		}
	}
	
	return  array ($my_images, $mygallery_id);
}


//#################################################################

function readtmpdir($mytmpdir) {
	
	global $allowedfiletypes;
	
	// reads tmp-dir and filenames names into array
	
	$my_images =array ();
	
	if (is_dir($mytmpdir)) {
		if ($dh = opendir($mytmpdir)) {
			while (($file = readdir($dh)) !== false) {
				$extension = strtolower(end(explode('.', $file)));
				if ((in_array ($extension, $allowedfiletypes) AND (!preg_match ('/^\._/',$file)))){
					array_push($my_images,"$file");
					@chmod ($mytmpdir.'/'.$file, file_permissions) or die  ('<div class="updated"><p><strong>'.__('Unable to set permissions for file ', 'myGallery').$mytmpdir.'/'.$file.'!</strong></p></div>');
				}
				else {
					if (!is_dir($file)) @unlink($mytmpdir.'/'.$file) or die   ('<div class="updated"><p><strong>'.__('Unable to remove unsupported file ', 'myGallery').$mytmpdir.'/'.$file.'!</strong></p></div>');
				}
			}
			closedir($dh);
		}
	}
	
	return $my_images; 
}

//#################################################################

function firsttimerun() {
	
	global $mg_options;
	
	$mywebserver_mod=ini_get('safe_mode');
	if ((gettype($mywebserver_mod) == 'string') && ($mywebserver_mod == 'off')) $mywebserver_mod = false;	
	
	$changelog_url=get_bloginfo('wpurl').'/wp-content/plugins/mygallery/changelog.txt';
	?>
	<div class="wrap">
	<h2>First time run &ndash; Erststart</h2>
	<form name="pageform" id="post" method="post" action="admin.php?page=mygallery/myfunctions/mygallerymain.php" ENCTYPE="multipart/form-data">
	<p>
	<fieldset class="options">
	<legend align=top>Please choose your language - bitte Sprache ausw&auml;hlen</legend>
	<input name="language" type="radio" value="myGallery"  checked="checked" /> English <input name="language" type="radio" value="myGallery-de_DE"  /> Deutsch <input name="language" type="radio" value="myGallery-fr_FR"  /> French
	</fieldset>
	</p>
	<p>
	<fieldset class="options">
	<legend align=top>Set path for gallerys - bitte Pfad f&uuml;r Galerien festlegen</legend>
	<?php _e('save gallerys in folder:', 'myGallery') ;?> <code><?php echo ABSPATH ?></code> <input type="text" size="25" name="gallerybasepath" value="<?php echo $mg_options[gallerybasepath]; ?>" />
	</fieldset>
	</p>
	<div class="submit"><input type="submit" value="ok"></div>
	</form>
	<p>
	Don't forget to read the <a href="<?php echo $changelog_url; ?>" target="_blank" >changelog.txt</a> file!
	</p>
	<p>
	Translation: <i>Michael Langlois (French)</i>
	</p>
	<?php if ($mywebserver_mod) { ?>
	<p style="color:red;"><b>Warning:</b> Your webserver runs with PHP safe-mode on!</p>
	<?php } ?>
	<p style="color:green;"><b>Notice:</b> Maximum file upload size for your server is <?php echo ini_get('upload_max_filesize'); ?>B.</p>
	</div>
	<?php
exit;
}

//#################################################################
?>
