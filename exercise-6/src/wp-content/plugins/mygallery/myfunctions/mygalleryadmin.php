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

// used variables

$myaction=$_POST[myaction];
$myurl=get_bloginfo('wpurl').'/'.$mg_options[gallerybasepath];
$showgallery=$_POST[showgallery];
$galleryoptions=$_POST[galleryoptions];
$deleteok=$_POST[deleteok];
$mypage_id=$_POST[setpage_id];
$galdescrip=$_POST[galdescrip];
$previewpic=$_POST[previewpic];
$longname=$_POST[longname];
$exclude=$_POST[exclude];
$galsortordernr=$_POST[galsortordernr];


if ($_POST[myaction]=='modifygallery') {
	
	if ('' != $_POST['deleteselected']) {
		
		// delete selected pictures
		
		deltesomepictures($_POST[checkbox]);
		
		$message=__('Pictures where removed form gallery.', 'myGallery');
		
	}
	
	if ('' != $_POST['updatethumbs']) {
		
		// update thumbnails
		
		$mypictures=getmypictures($showgallery);
		$galleryname=getgalleryname($showgallery);
		$mypath=ABSPATH.$mg_options[gallerybasepath].$galleryname;
		
		if (is_array($mypictures)) {
			foreach ($mypictures as $stored_file) {
				generatethumbnail($mypath, $stored_file->picturepath);		
			}
		}
		$message=__('Thumbnails where updated.', 'myGallery');
	}	
	
	if ('' != $_POST['updatedescriptions']) {
		
		// update description of selcted pictures
		
		refreshpricturedescription($_POST[description],$_POST[ordernr], $_POST[alttitle],$_POST[excludebox], $showgallery);
		
		updategallerypageid($mypage_id,$showgallery,$galdescrip, $previewpic, $longname, $exclude, $galsortordernr);
		
		$message=__('Picture information updated.', 'myGallery');
		
	}
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
}
if (($myaction=='opengallery') AND ($galleryoptions == 2) AND $deleteok=='ok'){
	
	// delete a gallery
	
	if ($showgallery) deletemygallery($showgallery); // needs a new error messsage for else branch
	
	$message=__('Gallery was deleted.', 'myGallery');
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	$myaction='';
}


?>
<link rel="stylesheet" href="<?php bloginfo('wpurl') ?>/wp-content/plugins/mygallery/css/dbx_myg.css" type="text/css" />
<script type="text/javascript" src="../wp-includes/js/dbx.js"></script>
<script type="text/javascript" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/mygallery/jss/dbx-key.js"></script>
<div class="wrap">
<h2><?php _e('Gallery Management', 'myGallery') ;?></h2>
<form name="pageform" id="post" method="post" action="admin.php?page=mygallery/myfunctions/mygalleryadmin.php" ENCTYPE="multipart/form-data">
<input type="hidden" name="myaction" value="opengallery">
<fieldset class="options"><legend><?php _e('Gallery operations', 'myGallery') ;?></legend>
<?php _e('Select a gallery', 'myGallery') ;?> <select name="showgallery" id="showgallery">
<?php

$gallerys=getmygallerys();

if (is_array($gallerys)) {
	foreach ($gallerys as $x) {
		echo "\n\t<option value='$x->id' >$x->name</option>";
	}
}
?>
</select>
<br /><br /><input name="galleryoptions" id="galleryoptions" type="radio" value="1"  checked="checked" /> <?php _e('show selected gallery or', 'myGallery') ;?> <input name="galleryoptions" id="galleryoptions" type="radio" value="2" /> <?php _e('delete selected gallery', 'myGallery') ;?> <i>(<?php _e('type', 'myGallery') ;?> 'ok' <?php _e('in the box', 'myGallery') ;?>)</i> <input type="text" size="3" name="deleteok" value="" />
</fieldset>
<p class="submit"><input type="submit" value="ok"></p></form><br /><br /><?php


// show a gallery if one was selected switch opion should be used in future

if (($myaction=='opengallery') OR ($myaction=='modifygallery' ) ) {
	
	if ($showgallery) {
		
		$mg_options=get_option('mygalleryoptions');
		$thepictures=getmypictures($showgallery);
		$galleryname=getgalleryname($showgallery);
		$mypage_id=getgallerypageid($showgallery);
		$galdescrip=getgallerydescrip($showgallery);
		$previewpic=getpreviewpic($showgallery);
		$longname=getgallerylongname($showgallery);
		$galsortordernr=getgalleryordernr($showgallery);
		if (getgalleryexclude($showgallery)) $mycheckbox_exclude='checked="checked"';
		
		?>
		<form name="pageform" id="post" method="post" action="admin.php?page=mygallery/myfunctions/mygalleryadmin.php" ENCTYPE="multipart/form-data">
		<div id="mgadmin" class="dbx-group" >
		<fieldset class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Gallery setting for', 'myGallery') ;?> <i><?php echo $galleryname; ?></i></h3>
		<div class="dbx-content">
		<?php _e('gallery page_id:', 'myGallery') ;?> <input type="text" size="4" name="setpage_id" value="<?php echo $mypage_id; ?>" /> 
		<?php if ($mg_options[previewpic]) {  _e('Preview Picture:', 'myGallery') ;?>  <select name="previewpic">
			<?php if (is_array($thepictures)){foreach ($thepictures as $x) {
					if ($x->pid == $previewpic) {
						echo "\n\t<option value='$x->pid' selected>$x->picturepath</option>";
					}
					else {
						echo "\n\t<option value='$x->pid' >$x->picturepath</option>";
					}
			}
			}
		} ?></select>
		<?php if ($mg_options[galdescrip]) { _e('Gallery description:', 'myGallery') ;?> <input type="text" size="50"  name="galdescrip" value="<? echo $galdescrip; ?>"><?php } ?><br /><br />
		<?php if ($mg_options[longnames]) $mylongnamestring=__('Gallery long name:', 'myGallery').' <input type="text" size="20"  name="longname" value="'.$longname.'"> '; ?>
		<?php if ($mg_options[gal_sortorder]==3) $mygalsortorderstring=__('Gallery sort number:', 'myGallery').' <input type="text" size="3"  name="galsortordernr" value="'.$galsortordernr.'">'; ?>
		<?php echo $mylongnamestring.$mygalsortorderstring.'<br /><br />' ?>
		<?php if ($mg_options[excludeoverview]) { ?><input name="exclude" type="checkbox" id="exclude" value="1"  <?php echo $mycheckbox_exclude;?> /> <?php _e('Exclude gallery from overview', 'myGallery') ;?> <br /><br /><?php } else { ?><input type="hidden" name="exclude" value="<?php echo getgalleryexclude($showgallery); ?>"><?php }?>
		<input type="hidden" name="showgallery" value="<?php echo $_POST[showgallery]; ?>">
		<input type="hidden" name="myaction" value="modifygallery">
		</div>
		</fieldset>
		<fieldset class="dbx-box">
		<p class="submit"><input name="updatethumbs" type="submit" value="<?php _e('Create new thumbnails', 'myGallery') ;?>" /><input name="updatedescriptions" type="submit" value="<?php _e('Update gallery and picture data', 'myGallery') ;?>" /></p>
		<h3 class="dbx-handle"><?php _e('Gallery pictures from', 'myGallery') ;?> <i><?php echo $galleryname; ?></i></h3>
		<div class="dbx-content">
		<table width="100%" cellspacing="2" cellpadding="5" >
		<tr><th scope="col"><img src="<?php bloginfo('wpurl') ?>/wp-content/plugins/mygallery/images/delete.gif" alt="delete"></th><th scope="col">ID</th><th scope="col"><?php _e('Filename', 'myGallery') ;?></th><?php if ($mg_options[sortorder]==3) {?><th scope="col"><?php _e('Order Nr.', 'myGallery') ;?></th><?php }?><th scope="col"><?php _e('Thumbnail', 'myGallery') ;?></th><th scope="col"><?php _e('Description', 'myGallery') ;?></th><th scope="col">Alt &amp; Title <?php _e('Text', 'myGallery') ;?></th><th scope="col"><?php _e('exclude', 'myGallery') ;?></th></tr>
		<?php
		if (is_array($thepictures)) {
			foreach ($thepictures as $x) {
				if ($mg_options[sortorder]==3) $myorder='<td style="text-align:center"><input type="text" size="3" name="ordernr['.$x->pid.']" value="'.$x->picsort.'"></td>';	
				$class = ('alternate' == $class) ? '' : 'alternate';
				
				// check if picutures should be exclude from gallery 
				
				if ($x->picexclude) {
					$piccheckbox_exclude='checked="checked"';
				}
				else {
					$piccheckbox_exclude='';
				}
				echo '<tr valign="top" class="'.$class.'">';
				echo '<td style="text-align:center"><input name="checkbox[]" type="checkbox" value="'.$x->pid.'"  /> </td><td>'.$x->pid.'</td><td>'.$x->picturepath.'</td>'.$myorder.'<td style="text-align:center"> <img src="'.$myurl.$x->name.'/tumbs/tmb_'.$x->picturepath.'" ></td><td style="text-align:center"><textarea name="description['.$x->pid.']" cols="30%" rows="5" wrap="VIRTUAL">'.$x->description.'</textarea></td><td> <input type="text" size="20"  name="alttitle['.$x->pid.']" value="'.$x->alttitle.'"></td><td style="text-align:center"><input name="excludebox['.$x->pid.']" type="checkbox" value="1"  '.$piccheckbox_exclude.'/> </td>'."\n";
				echo '</tr>';
			}
		}
		?>
		</table>
		</div>
		<p class="submit"><input name="deleteselected" type="submit" value="<?php _e('Delete selected', 'myGallery') ;?>" /></p>
		</fieldset>
		</div>
		</form>
		<?php
	}	
}

echo '</div>';


//#################################################################

function getmypictures($myid) {
	
	global $table_prefix, $wpdb;
	
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

function deltesomepictures($mycheckbox) {
	
	global $table_prefix, $wpdb, $mg_options;
	
	
	$mypath=ABSPATH.$mg_options[gallerybasepath];
	
	if (is_array($mycheckbox)) {
		foreach ($mycheckbox as $mypciture_id) {
			$result=$wpdb->get_row('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$mypciture_id.' AND '.$table_prefix.'mygprelation.pid = '.$mypciture_id.' AND '.$table_prefix.'mygallery.id ='.$table_prefix.'mygprelation.gid' );
			
			// delete file
			
			unlink($mypath.$result->name.'/'.$result->picturepath);
			unlink($mypath.$result->name.'/tumbs/tmb_'.$result->picturepath);
			
			// delete db entries
			
			$wpdb->query('DELETE FROM '.$table_prefix.'mygprelation WHERE gid = '.$result->gid.' AND pid ='.$result->pid);
			$wpdb->query('DELETE FROM '.$table_prefix.'mypictures WHERE id = '.$result->pid);
			
		}
	}
}

//#################################################################

function refreshpricturedescription($mydescription, $mypictureordernr, $myalttitle,$myexclude, $mygalid) {
	
	global $table_prefix, $wpdb;
	
	if (is_array($mydescription)) {
		foreach($mydescription as $key=>$value) {
			$myresult=$wpdb->query('UPDATE ' .$table_prefix.'mypictures SET description = "'.$value.'" WHERE id = '.$key);
		}
	}
	if (is_array( $mypictureordernr)){
		foreach($mypictureordernr as $key=>$value) {
			$myresult=$wpdb->query('UPDATE ' .$table_prefix.'mypictures SET picsort = "'.$value.'" WHERE id = '.$key);
		}
	}
	if (is_array( $myalttitle)){
		foreach($myalttitle as $key=>$value) {
			$myresult=$wpdb->query('UPDATE ' .$table_prefix.'mypictures SET alttitle = "'.$value.'" WHERE id = '.$key);
		}
	}
	if (is_array($myexclude)){
		foreach($myexclude as $key=>$value) {
			$myresult=$wpdb->query('UPDATE ' .$table_prefix.'mypictures SET picexclude = 1 WHERE id = '.$key);
		}
	}
	
	$mypcicture_ids=getmypicture_ids($mygalid);
	
	if (is_array($mypcicture_ids)){
		foreach($mypcicture_ids as $myid){
			if (is_array($myexclude)){
				if (array_key_exists($myid->id, $myexclude)) {
					$myresult=$wpdb->query('UPDATE ' .$table_prefix.'mypictures SET picexclude = 1 WHERE id = '.$myid->id);
				}
				else {
					$myresult=$wpdb->query('UPDATE ' .$table_prefix.'mypictures SET picexclude = 0 WHERE id = '.$myid->id);
				}   
			}
			else {
				$myresult=$wpdb->query('UPDATE ' .$table_prefix.'mypictures SET picexclude = 0 WHERE id = '.$myid->id);
			}   
		}
	}
}

//#################################################################

function deletemygallery($mygallery_id) {
	
	global $table_prefix, $wpdb, $mg_options;
	
	// used variables
	
	$mypictures=array();
	$mypath=ABSPATH.$mg_options[gallerybasepath];
	
	// get all picture_ids from the gallery -  needs test if files and folders exist
	
	$thepictures=$wpdb->get_results('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid AND '.$table_prefix.'mygprelation.gid = '.$table_prefix.'mygallery.id AND '.$table_prefix.'mygallery.id = "'.$mygallery_id.'"');
	
	if (is_array($thepictures)) {
		foreach ($thepictures as $tmp) {
			array_push ($mypictures, $tmp->pid);
		}
	}
	// delete all pictures
	
	deltesomepictures($mypictures);
	
	// delete gallery
	
	$mygallery_name=getgalleryname($mygallery_id);
	$wpdb->query('DELETE FROM '.$table_prefix.'mygallery WHERE id = '.$mygallery_id);
	
	// unlink directories
	
	@rmdir($mypath.$mygallery_name.'/tumbs');
	@rmdir($mypath.$mygallery_name);
	
	
}

//#################################################################

function updategallerypageid($mypage_id,$mygallery_id, $mygaldescrip, $mypreviewpic, $mylongname, $myexclude, $mygalsortordernr) {
	
	global $table_prefix, $wpdb,$mg_options;
	
	$wpdb->query('UPDATE ' .$table_prefix.'mygallery SET pageid = "'.$mypage_id.'" WHERE id = '.$mygallery_id);
	if ($mg_options[galdescrip]) $wpdb->query('UPDATE ' .$table_prefix.'mygallery SET galdescrip = "'.$mygaldescrip.'" WHERE id = '.$mygallery_id);
	if ($mg_options[previewpic]) $wpdb->query('UPDATE ' .$table_prefix.'mygallery SET previewpic = "'.$mypreviewpic.'" WHERE id = '.$mygallery_id);
	if ($mg_options[longnames])$wpdb->query('UPDATE ' .$table_prefix.'mygallery SET longname = "'.$mylongname.'" WHERE id = '.$mygallery_id);
	if ($mg_options[excludeoverview])$wpdb->query('UPDATE ' .$table_prefix.'mygallery SET excludegal = "'.$myexclude.'" WHERE id = '.$mygallery_id);
	if ($mg_options[gal_sortorder]==3)$wpdb->query('UPDATE ' .$table_prefix.'mygallery SET gallsortnr = "'.$mygalsortordernr.'" WHERE id = '.$mygallery_id);
}

//#################################################################

function getmypicture_ids($myid) {
	
	global $table_prefix, $wpdb;
	
	$thepictures = $wpdb->get_results('SELECT '.$table_prefix.'mypictures.id FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid AND '.$table_prefix.'mygprelation.gid = '.$myid.' AND '.$table_prefix.'mygallery.id ='.$myid);
	
	return $thepictures;
	
}

//#################################################################
?>