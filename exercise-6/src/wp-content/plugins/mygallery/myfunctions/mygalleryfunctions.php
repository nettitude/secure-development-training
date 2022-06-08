<?php
// additional functions for myGallery

//#################################################################
function getmygallerys($myoffest=0,$myrow=0) {

	global $table_prefix, $wpdb;

	$thegallerys = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'mygallery');
	
	return $thegallerys;

}
//#################################################################

function getgalleryname($mygalleryid) {
	
	global $table_prefix, $wpdb;
	
	$mygalleryname=$wpdb->get_var('SELECT name FROM '.$table_prefix.'mygallery WHERE id ="'.$mygalleryid.'"');
	return $mygalleryname;
}

//#################################################################

function getgalleryid($mygalleryname) {
	
	global $table_prefix, $wpdb;
	
	$mygalleryid=$wpdb->get_var('SELECT id FROM '.$table_prefix.'mygallery WHERE name ="'.$mygalleryname.'"');
	return $mygalleryid;
}
//#################################################################

function getgallerypageid($mygalleryid) {
	
	global $table_prefix, $wpdb;
	
	$mygallerypage_id=$wpdb->get_var('SELECT pageid FROM '.$table_prefix.'mygallery WHERE id ="'.$mygalleryid.'"');
	if ($mygallerypage_id=='0') $mygallerypage_id='';
	return $mygallerypage_id;
}
//#################################################################

function getgallerydescrip($mygalleryid) {
	
	global $table_prefix, $wpdb;
	
	$mygallery_descrip=$wpdb->get_var('SELECT galdescrip FROM '.$table_prefix.'mygallery WHERE id ="'.$mygalleryid.'"');
	return $mygallery_descrip;
}
//#################################################################

function getpreviewpic($mygalleryid) {
	
	global $table_prefix, $wpdb;
	
	$mygallery_previewpic=$wpdb->get_var('SELECT previewpic FROM '.$table_prefix.'mygallery WHERE id ="'.$mygalleryid.'"');
	return $mygallery_previewpic;
}
//#################################################################

function getpicturepath($mypicture_id) {
	
	global $table_prefix, $wpdb;
	
	$mypicturepath=$wpdb->get_var('SELECT picturepath FROM '.$table_prefix.'mypictures WHERE '.$table_prefix.'mypictures.id = '.$mypicture_id);
	return $mypicturepath;
}
//#################################################################

function getgallerylongname($mygalleryid) {
	
	global $table_prefix, $wpdb;
	
	$mylongname=$wpdb->get_var('SELECT longname FROM '.$table_prefix.'mygallery WHERE id ="'.$mygalleryid.'"');
	return $mylongname;
}
//#################################################################

function getgalleryexclude($mygalleryid) {
	  global $table_prefix, $wpdb;
	  
	  $myexclude=$wpdb->get_var('SELECT excludegal FROM '.$table_prefix.'mygallery WHERE id ="'.$mygalleryid.'"');
	  return $myexclude;
}
//#################################################################

function getmygallerysnoex() {
	
	// get only those gallerys, wich are not exclude form overview
	
	global $table_prefix, $wpdb;

	$thegallerys = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'mygallery WHERE excludegal IS NULL OR excludegal=0');
	return $thegallerys;

}
//#################################################################

function getalttitle($mypicture_id) {
	
	global $table_prefix, $wpdb;
	
	$myalttitle=$wpdb->get_var('SELECT alttitle FROM '.$table_prefix.'mypictures WHERE '.$table_prefix.'mypictures.id = '.$mypicture_id);
	return $myalttitle;
}
//#################################################################

function getstoredpics($myid) {
	
	global $table_prefix, $wpdb;
	
	$finalpics=array();
	
	$thepictures = $wpdb->get_results('SELECT picturepath FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid AND '.$table_prefix.'mygprelation.gid = '.$myid.' AND '.$table_prefix.'mygallery.id ='.$myid);
	if (is_array($thepictures)) {
		foreach ($thepictures as $tmp) {
			array_push($finalpics, $tmp->picturepath);
			
		}
	}
	return $finalpics;
	
}
//#################################################################

function myinlinepicture($mypicture_id,$my_align,$my_popoption,$my_scaleorethumb,$my_lightboxgroup)  {
	
	global $table_prefix, $wpdb, $myurl,$wp_rewrite, $mg_options;
	
	// read database
	
	$result=$wpdb->get_row('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$mypicture_id.' AND '.$table_prefix.'mygprelation.pid = '.$mypicture_id.' AND '.$table_prefix.'mygallery.id ='.$table_prefix.'mygprelation.gid' );
	
	// trim align option and set img align
	
	$my_align = ltrim($my_align,',');
	
	switch ($my_align) {
		
		case 'left': $float='myinlinepictureleft';
		break;
		
		case 'right': $float='myinlinepictureright';
		break;
		
		case 'leftclear': 
		$flag='<br style="clear:both"/>';
		$float='myinlinepictureleftclear';
		break;
		
		case 'rightclear': 
		$flag='<br style="clear:both"/>';
		$float='myinlinepicturerightclear';
		break;
		
		default: 
		$flag='<br style="clear:both"/>';
		$float='myinlinepicture';
	}
	
	// trim scaleoption and check if the user want's a thumbnail or a scaled picture
	
	$my_scaleorethumb= ltrim($my_scaleorethumb,',');
	
	if ($my_scaleorethumb=='thumb') {
		$prefix='/tumbs/tmb_';
	}
	
	else {
		
		settype($my_scaleorethumb, "integer");
	}
	
	// set width and height of the picture 
	
	$image_data = getimagesize(ABSPATH.$mg_options[gallerybasepath].$result->name.'/'.$prefix.$result->picturepath);
	
	
	if (is_int($my_scaleorethumb) AND $my_scaleorethumb >0) {
		$mybigimg_width=$my_scaleorethumb;
		$mybigimg_height = (int) (($my_scaleorethumb/ $image_data[0]) * $image_data[1]);
	}
	
	else {
		$mybigimg_width = $image_data[0]; 
		$mybigimg_height = $image_data[1]; 	
	}
	
	// include picture description
	
	if ($result->description AND $mg_options[inlinepicdesc]) {
		$mydescription='<div class="myinlinepicdescription"><span>'.$result->description.'</span></div>';
	}
	
	// check for lighbox 2.0 gallery group
	
	$my_lightboxgroup= ltrim($my_lightboxgroup,',:');
	
	
	// trim reference option and set reference 
	
	$my_popoption=ltrim($my_popoption,',');
	
	if ($my_popoption=='fullscreen') {
		
		if ($mg_options[lightboxjs] AND $my_lightboxgroup AND ($mg_options[lightboxversion]==2) )$mylightboxref='rel="lightbox['.$my_lightboxgroup.']"';
		else if ($mg_options[lightboxjs])  $mylightboxref='rel="lightbox"';
		$fullref_pre='<a '.$mylightboxref.' href="'.$myurl.$result->name.'/'.$result->picturepath.'"  title="'.$result->alttitle.'">';
		$fullref_post='</a>';
	}
	else if (($my_popoption=='gal') AND($result->pageid) ) {
		
		$myreference=get_permalink($result->pageid); 
		
		if ($wp_rewrite->using_permalinks()) {
			$myreference=$myreference.'?picture_id=';
		}
		else {
			$myreference=$myreference.'&picture_id=';
		}
		
		$fullref_pre='<a href="'.$myreference.$result->pid.'">';
		$fullref_post='</a>';
	}	
	else if (strchr($my_popoption,'http')) {
		$fullref_pre='<a href="'.$my_popoption.'">';
		$fullref_post='</a>';
	}
	


	// build the final string

	$my_string='<div class="'.$float.'" style="width:'.$mybigimg_width.'px"><div class="myinlineborder"  style="width:'.$mybigimg_width.'px">'.$fullref_pre.'<img class="myinlinepictureimg" src="'.$myurl.$result->name.'/'.$prefix.$result->picturepath.'" alt="'.$result->alttitle.'" title="'.$result->alttitle.'" width="'.$mybigimg_width.'" height="'.$mybigimg_height.'"  />'.$fullref_post.'</div>'.$mydescription.'</div>'.$flag;
	
	return $my_string;
}
//#################################################################

function galleryexists($mydirectory) {
	
	global $table_prefix, $wpdb;
	if ($mydirectory) {
		$mygalleryid=$wpdb->get_var('SELECT id FROM '.$table_prefix.'mygallery WHERE name ="'.$mydirectory.'"');
	}
	
	return $mygalleryid;
}

//#################################################################

function nasty_p_filter($mystring) {

	$search = "/<p>(|\s)(\[mygal=\w+\]|\[mygallistgal\]|\[myginpage=\w+\]|\[inspic=(\d+)(|,\w+|,)(|,fullscreen|,gal|,)(|,thumb|,\d+)\])(|\s)<\/p>/";
	$replace = "$2";
	$mystring = preg_replace ($search, $replace, $mystring);
	
	$search="/<p>(|\s)(\[mygal=\w+\]|\[mygallistgal\]|\[myginpage=\w+\]|\[inspic=(\d+)(|,\w+|,)(|,fullscreen|,gal|,)(|,thumb|,\d+)\])(|\s||<br \/>\s)(.*)(|\s)<\/p>/";
	$replace = "$2<p>$8</p>";
	$mystring = preg_replace ($search, $replace, $mystring);
	
	$search="/<p>(.*|.*\n)(\[mygal=\w+\]|\[mygallistgal\]|\[myginpage=\w+\]|\[inspic=(\d+)(|,\w+|,)(|,fullscreen|,gal|,)(|,thumb|,\d+)\])(|\s)(.*|.*\n.*\n)(|\s)<\/p>/";
	$replace = "<p>$1</p>$2<p>$8</p>";
	$mystring = preg_replace ($search, $replace, $mystring);
	
	$search="/<p>(.*|.*\n)(\[mygal=\w+\]|\[mygallistgal\]|\[myginpage=\w+\]|\[inspic=(\d+)(|,\w+|,)(|,fullscreen|,gal|,)(|,thumb|,\d+)\])(|\s)<\/p>/";
	$replace = "<p>$1</p>$2";
	$mystring = preg_replace ($search, $replace, $mystring);
	
	$search="/<p>(|\s)(\[mygal=\w+\]|\[mygallistgal\]|\[myginpage=\w+\]|\[inspic=(\d+)(|,\w+|,)(|,fullscreen|,gal|,)(|,thumb|,\d+)\])(.*)(\s<br \/>|<br \/>)/";
	// changed on 15.02.2006 - <br /> causes problems $replace = "$2<br /><p>$7";  //modified on 24.01.2006 18:43 maybe the <br /> is not usefull
	$replace = "$2<p>$7";
	
	$search="/<p>(.*)(\s<br \/>|<br \/>)/";
	$replace="<p>$2</p>";
	$mystring = preg_replace ($search, $replace, $mystring);
	
	return $mystring;
	
}

//#################################################################

function spreedgallerytopages($mypage=0,$gallerynamearray=0) {
	
	
	global $table_prefix, $wpdb,$mg_options,$wp_rewrite;
		
	if ( $wp_rewrite->using_permalinks() ) {
		$mygalleryavigation=get_permalink().'?galpage=';
	}
	else {
		$mygalleryavigation=get_permalink().'&galpage=';
	}
	
	if ($mg_options[excludeoverview]) {
		$mycount = $wpdb->get_var('SELECT COUNT(id) FROM ' . $table_prefix . 'mygallery WHERE excludegal IS NULL OR excludegal=0');
		$myexclude=' WHERE excludegal IS NULL OR excludegal=0';
	}
	
	
	else {
		$mycount = $wpdb->get_var('SELECT COUNT(id) FROM ' . $table_prefix . 'mygallery');
	}
	
	if (is_array($gallerynamearray)) {
		
		$gallerynamearray= arraytostringlist($gallerynamearray);
		$mycount = $wpdb->get_var('SELECT COUNT(id) FROM ' . $table_prefix . 'mygallery WHERE id IN ('.$gallerynamearray.')');
		$myexclude=' WHERE id IN ('.$gallerynamearray.')';
	}
	
	
	if ($mg_options[gal_sortorder]==1) $mysort='id';
	if ($mg_options[gal_sortorder]==2) $mysort='name';
	if ($mg_options[gal_sortorder]==3) $mysort=' gallsortnr';
	if ($mg_options[gal_sortascdes]==2) $sortorder=' DESC';
	
	if ((($mg_options[galpagesize]*($mypage-1))< $mycount) AND ($mypage > 0)) {
		
		if ($mg_options[galpagesize])$gallerytotal= ceil ($mycount/$mg_options[galpagesize]);
		if ($mg_options[galpagebreak] AND $mg_options[galcountdisplay]) $mygalcounter='<div class="galcounter">'.__('page', 'myGallery')." $mypage ".__('of', 'myGallery')." $gallerytotal".'</div>';
		
		$mygallerys = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'mygallery'.$myexclude.' ORDER BY '.$table_prefix.'mygallery.'.$mysort.$sortorder .' LIMIT '.($mg_options[galpagesize]*($mypage-1)).','.$mg_options[galpagesize]);
		
		if (($mg_options[galpagesize]*$mypage) < $mycount) {
			$myfor='<div class="galleryfor"><a href="'.$mygalleryavigation.($mypage+1).'" title="'.__('forward', 'myGallery').'">'.$mg_options[navigationfor].'</a></div>';
		}
		
		if ($mypage > 1) {
			$myback='<div class="galleryback"><a href="'.$mygalleryavigation.($mypage-1).'" title="'.__('back', 'myGallery').'">'.$mg_options[navigationback].'</a></div>';
		}
		
		$mynavigation='<div class="gallerynav">'.$myback.$myfor.'</div>'.$mygalcounter;
	}
	else {
		$mygallerys = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'mygallery'.$myexclude.' ORDER BY '.$table_prefix.'mygallery.'.$mysort.$sortorder );
	}
	

	return  array ($mygallerys, $mynavigation);
	
}
//#################################################################

function generatethumbnail($mypath, $stored_file, $shrinkfit=0) {
	
	$mg_options=get_option('mygalleryoptions');
	
	// properties of thumbnails
	
	switch ($mg_options[scalethumb]) {
		
		case 1: $tmb_width=$mg_options[tumbwidth_a];
		break;
		
		case 2: $tmb_width=$mg_options[tumbwidth_b];
		break;
		
		default: $tmb_width=$mg_options[tumbwidth];
		
	}
	
	if (!$tmb_width) $tmb_width=100; // set aus default
	
	$tmb_height=$mg_options[tumbheight];
	$quality=$mg_options[thumbquality];
	$prefix='tmb_';
	
	if (!$tmb_height) $tmb_height=75; // set aus default
	
	
	$my_extension = strtolower(end(explode('.', $stored_file)));
	
	switch ($my_extension) {
		
		case "jpg": $myimg = imagecreatefromjpeg("$mypath/$stored_file");
		break;
		
		case "png": $myimg = imagecreatefrompng("$mypath/$stored_file");
		break;
		
		case "gif": $myimg = imagecreatefromgif("$mypath/$stored_file");
		break;
		
	}
	$myimg_width = imageSX($myimg);
	$myimg_height = imageSY($myimg);
	
	if ($mg_options[scalethumb]==1) {
		
		// check if picture is in potrait format
		
		if (($myimg_height > $myimg_width) AND ($mg_options[preserve]==1)) {
			$tmb_height=$tmb_width;
			$new_tmb_width = (int) (($tmb_height / $myimg_height) * $myimg_width);
			if ($my_extension=='gif') {
				$new_img = imagecreate($new_tmb_width,$tmb_height);
			}
			else {
				$new_img = imagecreatetruecolor($new_tmb_width,$tmb_height);
			}
			imagecopyresampled($new_img, $myimg, 0, 0, 0, 0, $new_tmb_width, $tmb_height, $myimg_width, $myimg_height);
			
		}
		else {
			$tmb_height = (int) (($tmb_width / $myimg_width) * $myimg_height);
			if ($my_extension=='gif') {
				$new_img = imagecreate($tmb_width,$tmb_height);
			}
			else {
				$new_img = imagecreatetruecolor($tmb_width,$tmb_height);
			}
			imagecopyresampled($new_img, $myimg, 0, 0, 0, 0, $tmb_width, $tmb_height, $myimg_width, $myimg_height);
			
		}
		
	}
	else if ($mg_options[scalethumb]==2){
		
		$tmb_x=(int) (($myimg_width/2)-($tmb_width/2));
		$tmb_y=(int) (($myimg_height/2)-($tmb_width/2));
		
		if ($my_extension=='gif') {
			$new_img = imagecreate($tmb_width,$tmb_width);
		}
		else {
			$new_img = imagecreatetruecolor($tmb_width,$tmb_width);
		}
		
		imagecopy ( $new_img,$myimg,0,0,$tmb_x,$tmb_y,$tmb_width,$tmb_width);
		
	}
	else {
		
		// check if picture is in potrait format
		
		if (($myimg_height > $myimg_width) AND ($mg_options[preserve]==1)) {
			if ($my_extension=='gif') {
				$new_img = imagecreate($tmb_height,$tmb_width);
			}
			else {
				$new_img = imagecreatetruecolor($tmb_height,$tmb_width);
			}
			
			imagecopyresampled($new_img, $myimg, 0, 0, 0, 0, $tmb_height,$tmb_width, $myimg_width, $myimg_height);
		}
		else {
			if ($my_extension=='gif') {
				$new_img = imagecreate($tmb_width,$tmb_height);
			}
			else {
				$new_img = imagecreatetruecolor($tmb_width,$tmb_height);
			}
			
			imagecopyresampled($new_img, $myimg, 0, 0, 0, 0, $tmb_width, $tmb_height, $myimg_width, $myimg_height);
		}
		
		
	}
	
	switch ($my_extension) {
		
		case "jpg": imagejpeg($new_img, "$mypath/tumbs/$prefix$stored_file",$quality);
		break;
		
		case "png": imagepng($new_img, "$mypath/tumbs/$prefix$stored_file",$quality);
		break;
		
		case "gif": imagegif($new_img, "$mypath/tumbs/$prefix$stored_file",$quality);
		break;
		
	}
	@chmod ("$mypath/tumbs/$prefix$stored_file", file_permissions) or die ('<div class="updated"><p><strong>'.__('Unable to change permissions for picture ', 'myGallery').$mypath.'/tumbs/'.$prefix.$stored_file.'!</strong></p></div>');
	
	
	// shrinks pictures to fit into theme if option was set
	
	if ($shrinkfit) {
		
		// check if picture is in potrait format
		
		if (($myimg_height > $myimg_width) AND ($mg_options[preserve]==1)) {
			$new_height=$mg_options[shrinkwidth];
			$new_width = (int) (($new_height / $myimg_height) * $myimg_width);
		}
		else {
			$new_width=$mg_options[shrinkwidth];
			$new_height = (int) (($new_width / $myimg_width) * $myimg_height);
		}
		
		if ($my_extension=='gif') {
			$new_img = imagecreate($new_width,$new_height);
		}
		else {
			$new_img = imagecreatetruecolor($new_width,$new_height);
		}
		
		imagecopyresampled($new_img, $myimg, 0, 0, 0, 0, $new_width, $new_height, $myimg_width, $myimg_height);
		
		switch ($my_extension) {
			
			case "jpg":imagejpeg($new_img, "$mypath/$stored_file",$quality);
			break;
			
			case "png": imagepng($new_img, "$mypath/$stored_file",$quality);
			break;
			
			case "gif": imagegif($new_img, "$mypath/$stored_file",$quality);
			break;
			
		}
		
		imagedestroy($new_img);
	}
	
	imagedestroy($myimg);	
}

//#################################################################

function getexifinfo($myimgpath){
	
	$mg_exifoptions=get_option('mygexifoptions');
	$mg_options = get_option('mygalleryoptions');
	$exif = @exif_read_data($myimgpath, 0, true);
	
	if ($mg_options[language]) load_textdomain('myGallery', myGalleryPath.'/languages/'.$mg_options[language].'.mo');
	
	$exposureprogram= array (0=>__('Unidentified', 'myGallery'), 1=>__('Manual', 'myGallery'), 2=> __('Normal', 'myGallery'), 3=>__('Aperture priority', 'myGallery'), 4=>__('Shutter priority', 'myGallery'),5=>__('Creative', 'myGallery'),6=>__('Action', 'myGallery'),7=>__('Portrait mode', 'myGallery'),8=>__('Landscape mode', 'myGallery'));
	$myexifinfo='<div class="exifmetalabel">'.__('Metadata', 'myGallery').'</div>';
	$myexifinfo=$myexifinfo.'<div class="exifbox">';
	
	if ($mg_exifoptions[make] AND ($exif['IFD0']['Make'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Make', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['IFD0']['Make'].'</span></div>';
	}
	if ($mg_exifoptions[model] AND ($exif['IFD0']['Model'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Model', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['IFD0']['Model'].'</span></div>';
	}
	if ($mg_exifoptions[exposuretime] AND ($exif['EXIF']['ExposureTime'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Exposure Time', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['EXIF']['ExposureTime'].'</span></div>';	
	}
	if ($mg_exifoptions[shutterspeed] AND ($exif['EXIF']['ShutterSpeedValue'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Shutter Speed', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['EXIF']['ShutterSpeedValue'].'</span></div>';
	}
	if ($mg_exifoptions[afnumber] AND ($exif['COMPUTED']['ApertureFNumber'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Aperture FNumber', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['COMPUTED']['ApertureFNumber'].'</span></div>';
	}
	if ($mg_exifoptions[focall] AND ($exif['EXIF']['FocalLength'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Focal Length', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['EXIF']['FocalLength'].'</span></div>';
	}
	if ($mg_exifoptions[iso]  AND ($exif['EXIF']['ISOSpeedRatings'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('ISO Speed Ratings', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['EXIF']['ISOSpeedRatings'].'</span></div>';
	}
	if ($mg_exifoptions[datime] AND ($exif['IFD0']['DateTime'] OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Date and Time', 'myGallery').': <span class="exifdata'.$class.'">'.$exif['IFD0']['DateTime'].'</span></div>';
	}
	$tmp_ep=$exif['EXIF']['ExposureProgram'];
	if (array_key_exists($tmp_ep, $exposureprogram)) $tpm_epv=$exposureprogram[$tmp_ep];
	
	if ($mg_exifoptions[expro] AND ($tpm_epv OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div class="exiflabel'.$class.'">'.__('Exposure Program', 'myGallery').': <span class="exifdata'.$class.'">'.$tpm_epv.'</span></div>';
	}
	$tmp_flash=getbit(decbin($exif['EXIF']['Flash']),0);
	if ($tmp_flash=='0') { 
		$tmp_flashvalue=__('no', 'myGallery');
	}
	else if ($tmp_flash=='1') {
		$tmp_flashvalue=__('yes', 'myGallery');
	}
	if ($mg_exifoptions[flash] AND ($tmp_flashvalue OR !$mg_exifoptions[exifskipempty])) {
		$class = ('one' == $class) ? 'two' : 'one';
		$myexifinfo=$myexifinfo.'<div  class="exiflabel'.$class.'">'.__('Flash', 'myGallery').': <span  class="exifdata'.$class.'">'.$tmp_flashvalue.'</span></div>';
	}
	$myexifinfo=$myexifinfo.'</div>';
	
	return $myexifinfo;
}
//#################################################################

function getbit($bits,$i){
 
 if (($i <= strlen($bits)-1) && ($i >= 0)) {
  $bit = $bits[strlen($bits)-1-$i];
 } 
 return $bit;
}

//#################################################################

function getgalleryordernr($mygalleryid) {
	
	global $table_prefix, $wpdb;
	
	$myordernr=$wpdb->get_var('SELECT gallsortnr FROM '.$table_prefix.'mygallery WHERE id ="'.$mygalleryid.'"');
	return $myordernr;
}
//#################################################################

function mytextpiclink($mypicture_id,$mytext) {
	
	global $table_prefix, $wpdb, $myurl;
	
	// read database
	
	$result=$wpdb->get_row('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$mypicture_id.' AND '.$table_prefix.'mygprelation.pid = '.$mypicture_id.' AND '.$table_prefix.'mygallery.id ='.$table_prefix.'mygprelation.gid' );
	
	$myrefstring='<a rel="lightbox" href="'.$myurl.$result->name.'/'.$result->picturepath.'"  title="'.$result->alttitle.'">'.$mytext.'</a>';
	
	return $myrefstring;
}


//#################################################################

function spreedthumbstopages($mycurrentthumbpage,$mygallery,$mypicturelist=0) {
	
	global $table_prefix, $wpdb,$mg_options,$wp_rewrite;
	
	
	if ( $wp_rewrite->using_permalinks() ) {
		$mythumbnavigation=get_permalink().'?thumbpage=';
	}
	else {
		$mythumbnavigation=get_permalink().'&thumbpage=';
	}
	
	$mygallery_id=$wpdb->get_var('SELECT id FROM '.$table_prefix.'mygallery WHERE name = "'.$mygallery.'"');

	$mycount=countthumbs($mygallery);
	
	if ($mg_options[sortorder]==1) $mysort='id';
	if ($mg_options[sortorder]==2) $mysort='picturepath';
	if ($mg_options[sortorder]==3) $mysort=' picsort';
	if ($mg_options[sortascdes]==2) $sortorder=' DESC';
	if ($mypicturelist) $picturesoflistonly=' AND '.$table_prefix.'mygprelation.pid IN '.$mypicturelist.' ';
	
	if ((($mg_options[thumbsamount]*($mycurrentthumbpage-1))< $mycount) AND ($mycurrentthumbpage > 0)) {
		
		if ($mg_options[thumbsamount])$pagestotal= ceil ($mycount/$mg_options[thumbsamount]);
		
		if ($mg_options[thumbscounterdisplay]) $mythumbscounter='<div class="thumbscounter">'.__('page', 'myGallery')." $mycurrentthumbpage ".__('of', 'myGallery')." $pagestotal".'</div>';
		
		$mythumbs = $thepictures=$wpdb->get_results('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid AND '.$table_prefix.'mygprelation.gid = '.$table_prefix.'mygallery.id '.$picturesoflistonly.'AND '.$table_prefix.'mygallery.name = "'.$mygallery.'" AND ('.$table_prefix.'mypictures.picexclude IS NULL OR '.$table_prefix.'mypictures.picexclude=0)  ORDER BY '.$table_prefix.'mypictures.'.$mysort.$sortorder .' LIMIT '.($mg_options[thumbsamount]*($mycurrentthumbpage-1)).','.$mg_options[thumbsamount]);
		
		if (($mg_options[thumbsamount]*$mycurrentthumbpage) < $mycount) {
			$myfor='<div class="thumbsfor"><a href="'.$mythumbnavigation.($mycurrentthumbpage+1).'" title="'.__('forward', 'myGallery').'">'.$mg_options[navigationfor].'</a></div>';
		
		}
		
		if ($mycurrentthumbpage > 1) {
			$myback='<div class="thumbsback"><a href="'.$mythumbnavigation.($mycurrentthumbpage-1).'" title="'.__('back', 'myGallery').'">'.$mg_options[navigationback].'</a></div>';
		}
		
		$mynavigation='<div class="thumbsnav">'.$myback.$myfor.'</div>'.$mythumbscounter;
	}
	else {
		$mythumbs = $thepictures=$wpdb->get_results('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid '.$picturesoflistonly.'AND '.$table_prefix.'mygprelation.gid = '.$table_prefix.'mygallery.id AND '.$table_prefix.'mygallery.name = "'.$mygallery.'" AND ('.$table_prefix.'mypictures.picexclude IS NULL OR '.$table_prefix.'mypictures.picexclude=0)  ORDER BY '.$table_prefix.'mypictures.'.$mysort.$sortorder );
	}
	
	return  array ($mythumbs, $mynavigation);
	
}

//#################################################################

function countthumbs($mygallery, $mypicturelist=0) {
	
	global $table_prefix, $wpdb;
	
	if ($mypicturelist) $picturesoflistonly=' AND '.$table_prefix.'mygprelation.pid IN '.$mypicturelist.' ';
	
	$mycount = $wpdb->get_var('SELECT COUNT(pid) FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid '.$picturesoflistonly.'AND '.$table_prefix.'mygprelation.gid = '.$table_prefix.'mygallery.id AND '.$table_prefix.'mygallery.name = "'.$mygallery.'" AND ('.$table_prefix.'mypictures.picexclude IS NULL OR '.$table_prefix.'mypictures.picexclude=0)');
	
	return $mycount;

}
//#################################################################

function gallistgal($somegallerynames=0) {

	$mg_options=get_option('mygalleryoptions');
	$myurl=get_bloginfo('wpurl').'/'.$mg_options[gallerybasepath];
		
	$replace='<div class="mygalleryoverview">';
	
	// get gallerys 
	
	if (!$_GET[galpage] AND $mg_options[galpagebreak]) {
		$mygalpage=1;
	} 
	else if ($mg_options[galpagebreak])
	{
		$mygalpage=$_GET[galpage];
	}
	
	list ($gallerys,$gallerynavigation)=spreedgallerytopages($mygalpage,$somegallerynames);
	
	if (is_array($gallerys)) {
		foreach ($gallerys as $x) {
			$longname='';
			if ($mg_options[longnames]) $longname=getgallerylongname($x->id);
			if (!$longname) $longname=$x->name;
			
			$replace=$replace. '<div class="mygallerygallery"><div class="mygallerynames"><a href="'.gallery_reference($x->pageid,$x->name).'">'.$longname.'</a>';
			if ($mg_options[picturesingallery]) $replace=$replace.'<span class="picturesingallery"> ('.countthumbs($x->name).')</span>';
			$replace=$replace.'</div><div class="mygallpicdesbord ">';
			
			if ($mg_options[previewpic] AND ($x->previewpic)) {
				$mypicturepath=getpicturepath($x->previewpic);
				$myalttitle=getalttitle($x->previewpic);
				$replace=$replace.'<div class="mygallerypreviewpics"><a href="'.gallery_reference($x->pageid,$x->name).'"><img src="'.$myurl.$x->name.'/tumbs/tmb_'.$mypicturepath.'" alt="'.$myalttitle.'" title="'.$myalttitle.'"/></a></div>'; // Bezug zum Bild fehlt
			}
			if ($mg_options[galdescrip]) {
				$mydescription=getgallerydescrip($x->id);
				$replace=$replace.'<div class="mygallerydescription">'.$mydescription.'</div>';
			}
			$replace=$replace.'</div></div>';		
		}
	}
	$replace=$replace.'</div>'.$gallerynavigation;	
	
	return $replace;
	
}

//#################################################################

function gallery_reference($mypage_id,$mygalleryname) {
	
	global $wp_rewrite;
	
	if ($mypage_id) {
		$myreference=get_permalink($mypage_id);
	}
	else {
		if ( $wp_rewrite->using_permalinks() ) {
			$myreference=get_permalink().'?gallery='.$mygalleryname;
		}
		else {
			$myreference=get_permalink().'&amp;gallery='.$mygalleryname;
		}
	}

return $myreference;
}
//#################################################################

// gets the tumbnail gallery for a folder

function showtumbs($mygallery, $myrefmethod=0,$mypicturelist=0) {
	global $table_prefix, $wpdb, $wp_rewrite, $mg_options;
	
	// used variables 
	
	$myurl=get_bloginfo('wpurl').'/'.$mg_options[gallerybasepath].$mygallery.'/';
	
	$mydir=ABSPATH.$mg_options[gallerybasepath].$mygallery.'/tumbs/tmb_';
	
	$my_tumbs =array ();
	$myreference=get_permalink();
	
	if ( $wp_rewrite->using_permalinks() ) {
		$myreference=$myreference.'?picture_id=';
	}
	else {
		$myreference=$myreference.'&amp;picture_id=';
	}
	
	$mytumbs='<div class="mypicsgallery">';
	
	if ($myrefmethod) $mytumbs='<div class="mypicsgalleryentry">';

	// get thumbs		
	
	if (!$_GET[thumbpage] AND $mg_options[thumbstopages]) {
		$mycurrentthumbpage=1;
	} 
	else if ($mg_options[thumbstopages])
	{
		$mycurrentthumbpage=$_GET[thumbpage];
	}
	
	list ($thepictures, $thenavigation)=spreedthumbstopages($mycurrentthumbpage,$mygallery,$mypicturelist);
	
	$mypage_id=$wpdb->get_var('SELECT pageid FROM '.$table_prefix.'mygallery WHERE name = "'.$mygallery.'"');
	
	
	if (is_array ($thepictures)){
		foreach ($thepictures as $tmp) {	
			
			$image_data = getimagesize("$mydir$tmp->picturepath");
			$myimg_width = $image_data[0]; 
			$myimg_height = $image_data[1];
			
			//check for JavaScript reference method
			
			//if (!$myfirstpic AND $myrefmethod ) {
				
				// get size of big picture
				
				$image_databig = getimagesize(ABSPATH.$mg_options[gallerybasepath].$tmp->name.'/'.$tmp->picturepath);
				$mybigimg_width = $image_databig[0];
				
				if ($mg_options[simplescale] AND ($mybigimg_width > $mg_options[simplescalewidth]))  {
					$mybigimg_width=$mg_options[simplescalewidth];
					$mybigimg_height = (int) (($mg_options[simplescalewidth] / $image_databig[0]) * $image_databig[1]);
					if ($mg_options[lightboxjs]) $mylightboxref='rel="lightbox"';
					$fullref_pre='<a '.$mylightboxref.' href="'.$myurl.$tmp->picturepath.'" id="bmg'.$mygallery.'" alt="'.$result->alttitle.'">';
					$fullref_post='</a>';
				}
				
				else {
					$mybigimg_height = $image_databig[1];		
				}
				
				if (!$myfirstpic AND $myrefmethod ) {	
					$myfirstpic='<div class="mypicboxentry"><div class="mypictureentry" style="width: '.$mybigimg_width.'px"><div class="mypictureentryborder" style="width: '.$mybigimg_width.'px">'.$fullref_pre.'<img class="mypictureentryimg" src="'.$myurl.$tmp->picturepath.'" id="mg'.$mygallery.'" alt="'.$tmp->alttitle.'" title="'.$tmp->alttitle.'" width="'.$mybigimg_width.'" height="'.$mybigimg_height.'" />'.$fullref_post.'</div></div>';	
					
					if  ($mg_options[igpicdes]) {
						$myfirstpic=$myfirstpic.'<div class="myfooter"><span  id="mg'.$mygallery.'ft">'.$tmp->description.'</span></div>';
						$myfirstpic=$myfirstpic.'<div style="display: none;" id="picid'.$tmp->pid.'">'.$tmp->description.'</div>';
					}
				}
				
				if ($myrefmethod) {
					$mypicture_ref="javascript:changePicture('".$myurl.$tmp->picturepath."','mg".$mygallery."','".$tmp->alttitle."',$mybigimg_width,$mybigimg_height,'picid".$tmp->pid."')";
					$myclass='class="mygallpicentry"';
				}
				
				else if ($mg_options[gallerybox]) {
					$mypicture_ref=$myurl.$tmp->picturepath;
					
					if ($mg_options[lightboxversion]==2) {
						$mygalbox='rel="lightbox['.$tmp->name.']"';
					}
					else {
						$mygalbox='rel="lightbox"';
					}
				}
				
				else {
					$mypicture_ref=$myreference.$tmp->pid;
					$myclass='class="mygallpic"';
				}
				
				$mytumbs=$mytumbs.'<a '.$mygalbox.' href="'.$mypicture_ref.'"  title="'.$tmp->alttitle.'"><img '.$myclass.' width="'.$myimg_width.'" height="'.$myimg_height.'" src="'.$myurl.'tumbs/tmb_'.$tmp->picturepath.'" alt="'.$tmp->alttitle.'" title="'.$tmp->alttitle.'" /></a>';
								
				if  ($mg_options[igpicdes]) {
					$mytumbs=$mytumbs.'<span style="display: none;" id="picid'.$tmp->pid.'">'.$tmp->description.'</span>';
				}
		}
	}
	if ($myrefmethod) $endtag='</div>';
	
	$mytumbs=$mytumbs.'</div>';
	$mytumbs=$myfirstpic.$mytumbs.$endtag.$thenavigation;
	
	return $mytumbs;
}
//#################################################################

function myshowgallerys($somegallerynames=0) {
	
	global $mg_options;
	
	if ($_GET['gallery']) {
		echo showtumbs($_GET['gallery']); 
	}
	
	else if ($_GET['picture_id']) {
		$result=getpicture($_GET['picture_id']);
		if ($result) {
			$mygalleryid=getgalleryidformpictureid($_GET['picture_id']);
			$mygalleryname=getgalleryname($mygalleryid);
			$myreference=gallery_reference(0,$mygalleryname);
		
			echo createhtmlforbigpicture($result,$_GET['picture_id'],$myreference);
			
			if ($mg_options[bigpicshowthumbs])  {
				echo showtumbs($mygalleryname);
			}
		}
	}
	
	else {
		echo gallistgal($somegallerynames);	
	}
}
//#################################################################

function myshowname($mydefault) {
	
	global $mg_options;
		
	if ($_GET['gallery']){
		$myid=getgalleryid($_GET['gallery']);
		$mystring=getgallerylongname($myid);
		if ($mg_options[longnames] AND $mystring) {
			echo $mystring;
		}
		else {
			echo $_GET['gallery'];	
		}
		
	}
	else if ($_GET['picture_id']){
		$myid=getgalleryidformpictureid($_GET['picture_id']);
		$mystring=getgallerylongname($myid);
		if ($mg_options[longnames] AND $mystring) {
			echo $mystring;		
		}
		else {
			echo getgalleryname($myid);
		}
		
	}
	else {
		echo $mydefault;
	}
	
}
//#################################################################

function getpicture($mypicture_id) {
	
	global $table_prefix, $wpdb;
	
	$result=$wpdb->get_row('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$mypicture_id.' AND '.$table_prefix.'mygprelation.pid = '.$mypicture_id.' AND '.$table_prefix.'mygallery.id ='.$table_prefix.'mygprelation.gid' );
	
	return $result;
}
//#################################################################

function getpicturenavigation($result,$mypicture_id, $mypicturelist=0) {
	
	global $table_prefix, $wpdb, $mg_options,$wp_rewrite;
	
	if ($mypicturelist) $picturesoflistonly=' AND '.$table_prefix.'mygprelation.pid IN '.$mypicturelist.' ';
	
	if ($mg_options[language]) load_textdomain('myGallery', myGalleryPath.'/languages/'.$mg_options[language].'.mo');
	
	$myreference=get_permalink();
	
	if ( $wp_rewrite->using_permalinks() ) {
		$mypicturenavigation=$myreference.'?picture_id=';
	}
	else {
		$mypicturenavigation=$myreference.'&amp;picture_id=';
	}
	
	
	// get order option
	
	if ($mg_options[sortorder]==1) $mysort='id';
	if ($mg_options[sortorder]==2) $mysort='picturepath';
	if ($mg_options[sortorder]==3) $mysort=' picsort';
	if ($mg_options[sortascdes]==2) $sortorder=' DESC';
	
	$my_foto_list=$wpdb->get_results('SELECT id FROM '.$table_prefix.'mypictures, '.$table_prefix.'mygprelation WHERE '.$table_prefix.'mygprelation.gid ='.$result->gid.' AND '.$table_prefix.'mypictures.id = '.$table_prefix.'mygprelation.pid '.$picturesoflistonly.'AND ('.$table_prefix.'mypictures.picexclude IS NULL OR '.$table_prefix.'mypictures.picexclude=0)ORDER BY '.$table_prefix.'mypictures.'.$mysort.$sortorder );
	
	// set pointer to the start of the array find position and build naviagtion
	
	$picturecounter=0;
	
	reset ($my_foto_list);
	$mep=current($my_foto_list);
	$mep_id=$mep->id;
	
	while($mep_id != $mypicture_id){  
		$mep=next ($my_foto_list);
		$mep_id=$mep->id;
		$picturecounter++;
	}
	
	if (next ($my_foto_list)) {
		$mep=current($my_foto_list);
		$mep_id=$mep->id;
		$next='<a href="'.$mypicturenavigation.$mep_id.'" title="'.__('forward', 'myGallery').'">'.$mg_options[navigationfor].'</a>' ;
		prev ($my_foto_list);	
	}
	else {
		end ($my_foto_list);
	}
	
	if (prev ($my_foto_list)) {
		$mep=current($my_foto_list);
		$mep_id=$mep->id;
		$previous='<a href="'.$mypicturenavigation.$mep_id.'" title="'.__('back', 'myGallery').'">'.$mg_options[navigationback].'</a>';	
	}
	
	
	return array ($next, $previous,$picturecounter);

}
//#################################################################

function getsizeofbigpicture($result) {
	
	global $mg_options, $myurl;
	
	$mybigimg_data = getimagesize(ABSPATH.$mg_options[gallerybasepath].$result->name.'/'.$result->picturepath);
	$mybigimg_width= $mybigimg_data[0];
	

	
	if ($mg_options[simplescale] AND ($mybigimg_width > $mg_options[simplescalewidth]))  {
		$mybigimg_width=$mg_options[simplescalewidth];
		$mybigimg_height = (int) (($mg_options[simplescalewidth] / $mybigimg_data[0]) * $mybigimg_data[1]);
		if ($mg_options[lightboxjs]) $mylightboxref='rel="lightbox"';
		$fullref_pre='<a '.$mylightboxref.' href="'.$myurl.$result->name.'/'.$result->picturepath.'" title="'.$result->alttitle.'">';
		$fullref_post='</a>';
	}
	
	else {
		$mybigimg_height = $mybigimg_data[1];		
	}
	
	return array ($mybigimg_width,$mybigimg_height,$fullref_pre,$fullref_post);
	
}
//#################################################################

function createhtmlforbigpicture($result,$mypicture_id,$myreference,$mymatches=0) {
	
	global $mg_options, $mg_exifoptions;
	
	$myurl=get_bloginfo('wpurl').'/'.$mg_options[gallerybasepath];
	
	list ($next, $previous,$picturecounter)=getpicturenavigation($result,$mypicture_id,$mymatches);
	list ($mybigimg_width,$mybigimg_height,$fullref_pre,$fullref_post)=getsizeofbigpicture($result);
	
	$replace='
	<div class="mypicbox">
	<div class="mypicup"><a href="'.$myreference.'" title="'.__('up', 'myGallery').'">'.$mg_options[navigationup].'</a></div>
	<div class="mypicture" style="width: '.$mybigimg_width.'px"><div class="mypictureborder" style="width: '.$mybigimg_width.'px">'.$fullref_pre.'<img class= "mypictureimg" src="'.$myurl.$result->name.'/'.$result->picturepath.'" alt="'.$result->alttitle.'" title="'.$result->alttitle.'" width="'.$mybigimg_width.'" height="'.$mybigimg_height.'" />'.$fullref_post.'</div></div>';
	
	if ($mg_options[galbpicdes]) {
		$replace=$replace.'<div class="myfooter"><span>'.$result->description.'</span></div>';
	}
	
	$picturestotal= countthumbs($result->name,$mymatches);
	
	
	$replace=$replace.'<div class="mypicback">'.$previous.'</div>
	<div class="mypicfor">'.$next.'</div>';
	if ($mg_options[mypiccounter]) $replace=$replace.'<div class="mypiccounter">'.__('picture' , 'myGallery').' '.($picturecounter+1).' ' .__('of' , 'myGallery')." $picturestotal".'</div>';
	$replace=$replace.'</div>'; 
	
	if ($mg_exifoptions[showexif]) $replace=$replace.getexifinfo(ABSPATH.$mg_options[gallerybasepath].$result->name.'/'.$result->picturepath);
	
	return $replace;
}
//#################################################################

function getgalleryidformpictureid($mypictureid) {
	global $table_prefix, $wpdb;
	
	$mygalleryid=$wpdb->get_var('SELECT gid FROM '.$table_prefix.'mygprelation WHERE pid ="'.$mypictureid.'"');
	return $mygalleryid;
}
//#################################################################

function arraytostringlist($myarray) {

	// can't be used for multidimensional arrays
	
	if (is_array($myarray)){
		foreach ($myarray as $value) {	
			if ($mystring) $mystring=$mystring.',';	
			$mystring=$mystring.getgalleryid($value);	
		}
	    }
	return $mystring;
}
//#################################################################
?>
