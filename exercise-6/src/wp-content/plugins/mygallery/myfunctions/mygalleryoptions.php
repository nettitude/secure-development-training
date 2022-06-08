<?php 
//#################################################################

// check if user has the rights to use the function 

get_currentuserinfo();
if ($user_level < 6)
{
	return;
}

//#################################################################

if ($_POST[myaction]=='setoptions'){
	
	$search="/\\x5c/";
	$replace='';
	$mg_options[navigationfor]= preg_replace ($search, $replace, $_POST[navigationfor]);
	$mg_options[navigationback]=preg_replace ($search, $replace, $_POST[navigationback]);
	$mg_options[navigationup]=preg_replace ($search, $replace, $_POST[navigationup]);
	
	$mg_options[shrinkfit]=$_POST[shrinkfit];
	$mg_options[shrinkwidth]=$_POST[shrinkwidth];
	$mg_options[scalethumb]=$_POST[scalethumb];
	$mg_options[tumbwidth]=$_POST[tumbwidth];
	$mg_options[tumbheight]=$_POST[tumbheight];
	$mg_options[tumbwidth_a]=$_POST[tumbwidth_a];
	$mg_options[tumbwidth_b]=$_POST[tumbwidth_b];
	$mg_options[preserve]=$_POST[preserve];
	$mg_options[sortorder]=$_POST[sortorder];
	$mg_options[previewpic]=$_POST[previewpic];
	$mg_options[galdescrip]=$_POST[galdescrip];
	$mg_options[longnames]=$_POST[longnames];
	$mg_options[bigpicshowthumbs]=$_POST[bigpicshowthumbs];
	$mg_options[excludeoverview]=$_POST[excludeoverview];
	$mg_options[simplescale]=$_POST[simplescale];
	$mg_options[simplescalewidth]=$_POST[simplescalewidth];
	$mg_options[sortascdes]=$_POST[sortascdes];
	$mg_options[inlinepicdesc]=$_POST[inlinepicdesc];
	$mg_options[lightboxjs]=$_POST[lightboxjs];
	$mg_options[galpagebreak]=$_POST[galpagebreak];
	$mg_options[galpagesize]=$_POST[galpagesize];
	$mg_options[gal_sortascdes]=$_POST[gal_sortascdes];
	$mg_options[gal_sortorder]=$_POST[gal_sortorder];
	$mg_options[igsafewh]=$_POST[igsafewh];
	$mg_options[igpicdes]=$_POST[igpicdes];
	$mg_options[gallerybox]=$_POST[gallerybox];
	$mg_options[galbpicdes]=$_POST[galbpicdes];
	$mg_options[lightboxversion]=$_POST[lightboxversion];
	$mg_options[thumbstopages]=$_POST[thumbstopages];
	$mg_options[thumbsamount]=$_POST[thumbsamount];
	$mg_options[thumbscounterdisplay]=$_POST[thumbscounterdisplay];
	$mg_options[mypiccounter]=$_POST[mypiccounter];
	$mg_options[galcountdisplay]=$_POST[galcountdisplay];
	$mg_options[picturesingallery]=$_POST[picturesingallery];
	$mg_options[thumbquality]=$_POST[thumbquality];
	$mg_options[randombox]=$_POST[randombox];
	$mg_options[templatemode]=$_POST[templatemode];
	$mg_options[templatemodeid]=$_POST[templatemodeid];
	$mg_options[thumbicdes]=$_POST[thumbicdes];
	                                                                                                                          
	update_option('mygalleryoptions', $mg_options);
	
	echo '<div id="message" class="updated fade"><p><strong>'.__('Options updated.', 'myGallery').'</strong></p></div>';	
}
$mg_options=get_option('mygalleryoptions');

if ($mg_options[scalethumb]==1) {
	$myradio_scalethumb_1='checked="checked"';
}
else if ($mg_options[scalethumb]==2) {
	$myradio_scalethumb_2='checked="checked"';	
}
else {
	$myradio_scalethumb_0='checked="checked"';
}

switch ($mg_options[sortorder]) {
	
	case 1: $myradio_sortorder_1='checked="checked"';
	break;
	case 2: $myradio_sortorder_2='checked="checked"';
	break;
	case 3: $myradio_sortorder_3='checked="checked"';
	break;	
}

switch ($mg_options[sortascdes]) {
	
	case 1: $myradio_sortascdes_1='checked="checked"';
	break;
	case 2: $myradio_sortascdes_2='checked="checked"';
	break;	
}


switch ($mg_options[gal_sortorder]) {
	
	case 1: $myradio_gal_sortorder_1='checked="checked"';
	break;
	case 2: $myradio_gal_sortorder_2='checked="checked"';
	break;
	case 3: $myradio_gal_sortorder_3='checked="checked"';
	break;	
}

switch ($mg_options[gal_sortascdes]) {
	
	case 1: $myradio_gal_sortascdes_1='checked="checked"';
	break;
	case 2: $myradio_gal_sortascdes_2='checked="checked"';
	break;	
}


switch ($mg_options[lightboxversion]) {
	
	case 1: $myradio_lightboxversion_1='checked="checked"';
	break;
	case 2: $myradio_lightboxversion_2='checked="checked"';
	break;	
}


// should be rebuild with a loop

if ($mg_options[shrinkfit]) $mycheckbox_shrinkfit='checked="checked"';
if ($mg_options[preserve]) $mycheckbox_preserve='checked="checked"';
if ($mg_options[simplescale]) $mycheckbox_simplescale='checked="checked"';
if ($mg_options[previewpic]) $mycheckbox_previewpic='checked="checked"';
if ($mg_options[galdescrip]) $mycheckbox_galdescrip='checked="checked"';
if ($mg_options[longnames]) $mycheckbox_longnames='checked="checked"';
if ($mg_options[bigpicshowthumbs]) $mycheckbox_bigpicshowthumbs='checked="checked"';
if ($mg_options[excludeoverview]) $mycheckbox_excludeoverview='checked="checked"';
if ($mg_options[inlinepicdesc]) $mycheckbox_inlinepicdesc='checked="checked"';
if ($mg_options[lightboxjs]) $mycheckbox_lightboxjs='checked="checked"';
if ($mg_options[galpagebreak]) $mycheckbox_galpagebreak='checked="checked"';
if ($mg_options[igsafewh]) $mycheckbox_igsafewh='checked="checked"';
if ($mg_options[igpicdes]) $mycheckbox_igpicdes='checked="checked"';
if ($mg_options[gallerybox]) $mycheckbox_gallerybox='checked="checked"';
if ($mg_options[galbpicdes]) $mycheckbox_galbpicdes='checked="checked"';
if ($mg_options[thumbstopages]) $mycheckbox_thumbstopages='checked="checked"';
if ($mg_options[thumbscounterdisplay]) $mycheckbox_thumbscounterdisplay='checked="checked"';
if ($mg_options[mypiccounter]) $mycheckbox_mypiccounter='checked="checked"';
if ($mg_options[galcountdisplay]) $mycheckbox_galcountdisplay='checked="checked"';
if ($mg_options[picturesingallery]) $mycheckbox_picturesingallery='checked="checked"';
if ($mg_options[randombox]) $mycheckbox_randombox='checked="checked"';
if ($mg_options[templatemode]) $mycheckbox_templatemode='checked="checked"';

?>
<link rel="stylesheet" href="<?php bloginfo('wpurl') ?>/wp-content/plugins/mygallery/css/dbx_myg.css" type="text/css" />
<script type="text/javascript" src="../wp-includes/js/dbx.js"></script>
<script type="text/javascript" src="<?php bloginfo('wpurl') ?>/wp-content/plugins/mygallery/jss/dbx-key.js"></script>
<div class="wrap">
<h2><?php _e('Picture and Thumbnail Options.', 'myGallery') ;?></h2>
<form name="pageform" id="post" method="post" action="admin.php?page=mygallery/myfunctions/mygalleryoptions.php" accept-charset="utf-8" ENCTYPE="multipart/form-data">
<input type="hidden" name="myaction" value="setoptions">
<div id="mgoptions" class="dbx-group" >
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Picture options', 'myGallery') ;?></h3>
<div class="dbx-content">
<input name="shrinkfit" type="checkbox" id="shrinkfit" value="1"  <?php echo $mycheckbox_shrinkfit;?> />
<?php _e('Scale orignal pictures to a with of', 'myGallery') ;?> <input type="text" size="3" name="shrinkwidth" value="<?php echo $mg_options[shrinkwidth]; ?>" /> (<?php _e('height will be generated', 'myGallery') ;?>).
<br /><input name="preserve" type="checkbox" id="preserve" value="1"  <?php echo $mycheckbox_preserve;?> /> <?php _e('Preserve portrait format', 'myGallery') ;?>
<br /><input name="simplescale" type="checkbox" id="simplescale" value="1"  <?php echo $mycheckbox_simplescale;?> />
<?php _e('Virtual Scale orignal pictures to a with of', 'myGallery') ;?> <input type="text" size="3" name="simplescalewidth" value="<?php echo $mg_options[simplescalewidth]; ?>" /> (<?php _e('height will be generated', 'myGallery') ;?>).
</div>
</fieldset>
</p>
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Thumbnail Options', 'myGallery') ;?></h3>
<div class="dbx-content">
<input name="scalethumb" id="scalethumb_1" type="radio" value="0" <?php echo $myradio_scalethumb_0; ?> /> <?php _e('Scale width and height and to', 'myGallery') ;?> <input type="text" size="3" name="tumbwidth" value="<?php echo $mg_options[tumbwidth]; ?>" /> x <input type="text" size="3" name="tumbheight" value="<?php echo $mg_options[tumbheight]; ?>" />
<br /><br /><input name="scalethumb" id="scalethumb_2" type="radio" value="1" <?php echo $myradio_scalethumb_1; ?>/>  <?php _e('Scale width to', 'myGallery') ;?> <input type="text" size="3" name="tumbwidth_a" value="<?php echo $mg_options[tumbwidth_a]; ?>" /> <?php _e('and set height automatically', 'myGallery') ;?>
<br /><br /><input name="scalethumb" id="scalethumb_2" type="radio" value="2" <?php echo $myradio_scalethumb_2; ?>/>  <?php _e('Make squarely thumbnails with a width of', 'myGallery') ;?> <input type="text" size="3" name="tumbwidth_b" value="<?php echo $mg_options[tumbwidth_b]; ?>" />
<br /><br /><?php _e('Thumbnail quality', 'myGallery') ;?> <input type="text" size="3" name="thumbquality" value="<?php echo $mg_options[thumbquality]; ?>" />%
</div>
</fieldset>
</p>
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Sorting Options', 'myGallery') ;?></h3>
<div class="dbx-content">
<input name="sortorder" id="sortorder_1" type="radio" value="1" <?php echo $myradio_sortorder_1;?> /> <?php _e('Sort pictures by ID', 'myGallery') ;?> <i>(<?php _e('default', 'myGallery') ;?>)</i><br />
<input name="sortorder" id="sortorder_2" type="radio" value="2"  <?php echo $myradio_sortorder_2;?> /> <?php _e('Sort pictures by name', 'myGallery') ;?><br />
<input name="sortorder" id="sortorder_3" type="radio" value="3"  <?php echo $myradio_sortorder_3;?> /> <?php _e('Sort pictures by number', 'myGallery') ;?><br />
<input name="sortascdes" id="sortascdes_1" type="radio" value="1"  <?php echo $myradio_sortascdes_1;?> /> <?php _e('Sort pictures ascending or', 'myGallery') ;?> <input name="sortascdes" id="sortascdes_3" type="radio" value="2"  <?php echo $myradio_sortascdes_2;?> /> <?php _e('descending', 'myGallery') ;?><br />
<input name="gal_sortorder" id="gal_sortorder_1" type="radio" value="1" <?php echo $myradio_gal_sortorder_1;?> /> <?php _e('Sort galleries by ID', 'myGallery') ;?> <i>(<?php _e('default', 'myGallery') ;?>)</i><br />
<input name="gal_sortorder" id="gal_sortorder_2" type="radio" value="2"  <?php echo $myradio_gal_sortorder_2;?> /> <?php _e('Sort galleries by name', 'myGallery') ;?><br />
<input name="gal_sortorder" id="gal_sortorder_3" type="radio" value="3"  <?php echo $myradio_gal_sortorder_3;?> /> <?php _e('Sort galleries by number', 'myGallery') ;?><br />
<input name="gal_sortascdes" id="gal_sortascdes_1" type="radio" value="1"  <?php echo $myradio_gal_sortascdes_1;?> /> <?php _e('Sort galleries ascending or', 'myGallery') ;?> <input name="gal_sortascdes" id="gal_sortascdes_3" type="radio" value="2"  <?php echo $myradio_gal_sortascdes_2;?> /> <?php _e('descending', 'myGallery') ;?>
</div>
</fieldset>
</p>
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Gallery Overview Options', 'myGallery') ;?></h3>
<div class="dbx-content">
<input name="previewpic" type="checkbox" id="previewpic" value="1"  <?php echo $mycheckbox_previewpic;?> /> <?php _e('Show preview picture', 'myGallery') ;?><br />
<input name="galdescrip" type="checkbox" id="galdescrip" value="1"  <?php echo $mycheckbox_galdescrip;?> /> <?php _e('Show gallery description', 'myGallery') ;?><br />
<input name="longnames" type="checkbox" id="longnames" value="1"  <?php echo $mycheckbox_longnames;?> /> <?php _e('Use long names as gallery title', 'myGallery') ;?><br />
<input name="excludeoverview" type="checkbox" id="excludeoverview" value="1"  <?php echo $mycheckbox_excludeoverview;?> /> <?php _e('Exclude selected gallerys from overview', 'myGallery') ;?><br />
<input name="galpagebreak" type="checkbox" id="galpagebreak" value="1"  <?php echo $mycheckbox_galpagebreak;?> /> <?php _e('Show only', 'myGallery') ;?> <input type="text" size="3" name="galpagesize" value="<?php echo $mg_options[galpagesize]; ?>" /> <?php _e('gallery(s) per page', 'myGallery') ;?><br>
<input name="galcountdisplay" type="checkbox" id="galcountdisplay" value="1"  <?php echo $mycheckbox_galcountdisplay;?> /> <?php _e('Show number of pages', 'myGallery') ;?><br />
<input name="picturesingallery" type="checkbox" id="picturesingallery" value="1"  <?php echo $mycheckbox_picturesingallery;?> /> <?php _e('Show number of thumbnails', 'myGallery') ;?><br />

</div>
</fieldset>
</p>
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Viewmode Options', 'myGallery') ;?></h3>
<div class="dbx-content">
<input name="bigpicshowthumbs" type="checkbox" id="previewpic" value="1"  <?php echo $mycheckbox_bigpicshowthumbs;?> /> <?php _e('Show thubnails in fullsize mode', 'myGallery') ;?><br />
<input name="mypiccounter" type="checkbox" id="mypiccounter" value="1"  <?php echo $mycheckbox_mypiccounter;?> /> <?php _e('Show number of pictures in fullsize mode', 'myGallery') ;?><br />
<input name="inlinepicdesc" type="checkbox" id="inlinepicdesc" value="1"  <?php echo $mycheckbox_inlinepicdesc;?> /> <?php _e('Show description for inline pictures', 'myGallery') ;?><br />
<input name="lightboxjs" type="checkbox" id="lightboxjs" value="1"  <?php echo $mycheckbox_lightboxjs;?> /> <?php _e('Use Lightbox JS for inlinpictures and virtual scaled pictures', 'myGallery') ;?><br />
<input name="gallerybox" type="checkbox" id="gallerybox" value="1"  <?php echo $mycheckbox_gallerybox;?> /> <?php _e('Use Lightbox JS for gallerie pictures', 'myGallery') ;?><br />
<input name="randombox" type="checkbox" id="randombox" value="1"  <?php echo $mycheckbox_randombox;?> /> <?php _e('Use Lightbox JS for random pictures', 'myGallery') ;?><br />
<input name="templatemode" type="checkbox" id="templatemode" value="1"  <?php echo $mycheckbox_templatemode;?> /> <?php _e('Use myGallery template function in theme <i>(for random pictures)</i> on page <i>(id)</i>', 'myGallery') ;?><input type="text" size="5" name="templatemodeid" value="<?php echo $mg_options[templatemodeid]; ?>" /><br />
<input name="lightboxversion" id="lightboxversion_1" type="radio" value="1"  <?php echo $myradio_lightboxversion_1;?> /> <?php _e('Use Lightbox Version 1', 'myGallery') ;?> <input name="lightboxversion" id="lightboxversion_2" type="radio" value="2"  <?php echo $myradio_lightboxversion_2;?> /> <?php _e('Use Lightbox Version 2', 'myGallery') ;?> <br />
<input name="igsafewh" type="checkbox" id="igsafewh" value="1"  <?php echo $mycheckbox_igsafewh;?> /> <?php _e('Refresh height und weight for inline gallery pictures', 'myGallery') ;?><br />
<input name="igpicdes" type="checkbox" id="igpicdes" value="1"  <?php echo $mycheckbox_igpicdes;?> /> <?php _e('Show picture description for inline galleries ', 'myGallery') ;?><br />
<input name="galbpicdes" type="checkbox" id="galbpicdes" value="1"  <?php echo $mycheckbox_galbpicdes;?> /> <?php _e('Show picture description for gallery picture ', 'myGallery') ;?><br />
<input name="thumbstopages" type="checkbox" id="thumbstopages" value="1"  <?php echo $mycheckbox_thumbstopages;?> /> <?php _e('Show', 'myGallery') ;?> <input type="text" size="3" name="thumbsamount" value="<?php echo $mg_options[thumbsamount]; ?>" /> <?php _e('thumbs per page', 'myGallery') ;?><br />
<input name="thumbscounterdisplay" type="checkbox" id="thumbscounterdisplay" value="1"  <?php echo $mycheckbox_thumbscounterdisplay; ?> /> <?php _e('Show number of pages for gallery thumbs', 'myGallery') ;?><br />
</div>
</fieldset>
</p>
<p>
<fieldset class="dbx-box">
<h3 class="dbx-handle"><?php _e('Gallery Navigation Options', 'myGallery') ;?></h3>
<div class="dbx-content">
<?php _e('up', 'myGallery') ;?>: <input type="text" size="10" name="navigationup" value="<?php echo $mg_options[navigationup]; ?>" /> <?php _e('back', 'myGallery') ;?>: <input type="text" size="10" name="navigationback" value="<?php echo $mg_options[navigationback]; ?>" /> <?php _e('forward', 'myGallery') ;?>: <input type="text" size="10" name="navigationfor" value="<?php echo $mg_options[navigationfor]; ?>" />
</div>                                                                                                                                                                                                                                                                   
</fieldset>
</div>
</p>
<div class="submit"><input type="submit" value="<?php _e('save options', 'myGallery') ;?>"></div>
</form></div>
<?php
?>
