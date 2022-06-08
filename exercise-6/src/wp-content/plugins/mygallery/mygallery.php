<?php
/*
Plugin Name: myGallery
Plugin URI: http://www.wildbits.de/mygallery/
Description: Yet another gallery.
Author: Thomas Boley
Version: 1.2

Author URI: http://www.wildbits.de


Copyright 2006  Thomas Boley  (email : tboley@web.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/ 

//#################################################################

define('myGalleryPath',dirname(__FILE__).'/');
define('myGalleryURL',get_bloginfo('wpurl').'/wp-content/plugins/mygallery/');

// read-write permissions for directorys and files

define('file_permissions',0666);
define('directory_permissions',0777);


@include_once(myGalleryPath. 'myfunctions/mygalleryfunctions.php');

if (!function_exists('getmygallerys')) {
	_e('Function library mygalleryfunctions.php was not found.', 'myGallery');
	die;
}

//#################################################################

//Fixes a bug in l10n.php where some guy decided there was no reason
// to load files which are required for their l10n functions if no language is defined in Wordpress.
// thanks to guys from WeatherIcon for the hint

require_once(ABSPATH . 'wp-includes/streams.php');
require_once(ABSPATH . 'wp-includes/gettext.php');

//#################################################################

function mygallery ($content) {
	
	global $table_prefix, $wpdb,$wp_rewrite, $myurl, $mg_options;
	
	
	// load language
	
	if ($mg_options[language]) load_textdomain('myGallery', myGalleryPath.'/languages/'.$mg_options[language].'.mo');
	
	// used variables 
	
	$mg_options = get_option('mygalleryoptions');
	$mg_exifoptions= get_option('mygexifoptions');
	$myreference=get_permalink();
	
	$myurl=get_bloginfo('wpurl').'/'.$mg_options[gallerybasepath];
	$mypicture_id=$_GET[picture_id];

	$search = "/\[mygal=([A-Za-z0-9\-\_]+)(|\([0-9\,]+\))\]/";
	
	if ($mypicture_id) {
		
		if (preg_match($search, $content,$matches)){
			$result=getpicture($mypicture_id);
			
			// exit if there is no pictures with this id
			
			if (!$result) {
				return;	
			}
			
			$search = "/\[mygal=".$result->name."(|\([0-9\,]+\))]/";
			$replace = "[mybigpic][mygal=".$result->name."$1]";
			$content = preg_replace ($search, $replace, $content);
			// show thumbs under bgc picture
			
			if ($mg_options[bigpicshowthumbs])  {
				$content=conv_tag($content); 
			}
			else {
				$search = "/\[mygal=([A-Za-z0-9\-\_]+)(|\([0-9\,]+\))\]/";
				$replace='';
				$content = preg_replace ($search, $replace, $content);	
			}
			
			// create HTML for big picture
			
			$search = "/\[mybigpic\]/";
			$replace=createhtmlforbigpicture($result,$mypicture_id,$myreference,$matches[2]);

			$content = preg_replace ($search, $replace, $content);
			
		}	
	}
	
	else {
		
		// if there is no gallery_id match, function returns a empty page
		
		$content=conv_tag($content);
	}
	
	return $content;
}


//#################################################################

// reads all mygallery-tags an replaces the tags with content

function conv_tag($mystring) {
	
	// used variables 
	

	$search = "/\[mygal=([A-Za-z0-9\-\_]+)(|\([0-9\,]+\))\]/";
	$mg_options=get_option('mygalleryoptions');
	
	global $myurl; 
	
	if (preg_match($search, $mystring)){
		
		preg_match_all($search, $mystring, $temp_array);
		if (is_array ($temp_array[1])) {
			foreach ($temp_array[1] as $key=>$v0) {
				$search = "/\[mygal=".$v0.addcslashes ($temp_array[2][$key],'\(\)')."\]/";
				$replace= showtumbs($v0,0,$temp_array[2][$key]);
				$mystring= preg_replace ($search, $replace, $mystring);
			}
			
		}
	}
	$search = "/\[mygallistgal\]/";
	
	if (preg_match($search, $mystring)){
		
		if (preg_match($search, $mystring)){
			$replace=gallistgal();
			$search = "/\[mygallistgal\]/";
			$mystring= preg_replace ($search, $replace, $mystring);
		}
		
	}
	
	$search = "/\[myginpage=(\w+)\]/";
	
	if (preg_match($search, $mystring)){
		
		preg_match_all($search, $mystring, $temp_array);
		
		if (is_array($temp_array[1])) {
			foreach ($temp_array[1] as $v0) {
				$search = "/\[myginpage=".$v0."\]/";
				$replace=showtumbs($v0,1);
				$mystring= preg_replace ($search, $replace, $mystring);
			}
			
		}
	}
	
	
	$search = "/\[inspic=(\d+)(|,\w+|,)(|,http:\/\/[^,]+|,fullscreen|,gal|,)(|,thumb|,\d+|,)(|,:\w+)\]/";
	
	if (preg_match($search, $mystring,$sresult)){
		
		preg_match_all($search, $mystring, $temp_array);
		
		if (is_array($temp_array)) {
			foreach ($temp_array[1] as $key =>$v0) {
				$search = "/\[inspic=".$v0.$temp_array[2][$key].addcslashes($temp_array[3][$key],'\/').$temp_array[4][$key].$temp_array[5][$key]."\]/";
				$replace=myinlinepicture($v0,$temp_array[2][$key],$temp_array[3][$key],$temp_array[4][$key],$temp_array[5][$key]);
				$mystring= preg_replace ($search, $replace, $mystring);
			}	
		}
	}
	
	$search = "/\[mypicref=(\d+)\](.*)\[\/mypicref\]/U";	
	
	if (preg_match($search, $mystring)){
		
		preg_match_all($search, $mystring, $temp_array);
		
		if (is_array($temp_array[1])) {
			foreach ($temp_array[1] as $key => $v0) {
				$search = "/\[mypicref=".$v0."\](.*)\[\/mypicref\]/U";	
				$replace=mytextpiclink($v0,$temp_array[2][$key]);
				$mystring= preg_replace ($search, $replace, $mystring);
			}	
		}
	}
	
	$mystring=" \t".$mystring;
	return $mystring;
}
//#################################################################

// adminpanel functions

function mygallery_menu() {
	if (function_exists('add_menu_page')) {
		
		add_menu_page('mygallery Generator', 'myGallery',6,dirname(__FILE__).'/myfunctions/mygallerymain.php');	
		
	}
	
	if (function_exists('add_submenu_page')) {
		$mg_options=get_option('mygalleryoptions');
		
		if ($mg_options[language]) load_textdomain('myGallery', myGalleryPath.'/languages/'.$mg_options[language].'.mo');
		add_submenu_page( dirname(__FILE__).'/myfunctions/mygallerymain.php', __('Gallery Management', 'myGallery'), __('Gallery Management', 'myGallery'), 6,dirname(__FILE__).'/myfunctions/mygalleryadmin.php');
		add_submenu_page( dirname(__FILE__).'/myfunctions/mygallerymain.php', __('Options', 'myGallery'), __('Options', 'myGallery'), 6,dirname(__FILE__).'/myfunctions/mygalleryoptions.php');
		add_submenu_page( dirname(__FILE__).'/myfunctions/mygallerymain.php', __('Gallery Style', 'myGallery'),__('Gallery Style', 'myGallery'), 10,dirname(__FILE__).'/myfunctions/mygallerystyle.php');
		add_submenu_page( dirname(__FILE__).'/myfunctions/mygallerymain.php', __('Exif Settings', 'myGallery'), __('Exif Settings', 'myGallery'), 6,dirname(__FILE__).'/myfunctions/mygalleryexif.php');
		add_submenu_page( dirname(__FILE__).'/myfunctions/mygallerymain.php', __('Info', 'myGallery'), __('Info', 'myGallery'), 6,dirname(__FILE__).'/myfunctions/mygallinfo.php');
		
	}
}

//#################################################################

function myrandompic($myamount=1) {
	
	global $table_prefix, $wpdb,$wp_rewrite,$mg_options;
	
	// used variables 
	
	$mybaseurl=get_bloginfo('wpurl');
	$mg_options=get_option('mygalleryoptions');
	$myurl=$mybaseurl.'/'.$mg_options[gallerybasepath];
	
	// read pictures table into array
	
	$mg_options=get_option('mygalleryoptions');
	
	
	if ($mg_options[excludeoverview]) {
		$mypicturesarray = $wpdb->get_results('SELECT '.$table_prefix.'mypictures.id FROM '.$table_prefix.'mypictures, '.$table_prefix.'mygallery, '.$table_prefix.'mygprelation WHERE ' .$table_prefix.'mypictures.id = '.$table_prefix. 'mygprelation.pid AND '.$table_prefix.'mygprelation.gid = '.$table_prefix.'mygallery.id AND ('.$table_prefix.'mygallery.excludegal IS NULL OR '.$table_prefix.'mygallery.excludegal=0 ) AND ('.$table_prefix.'mypictures.picexclude IS NULL OR '.$table_prefix.'mypictures.picexclude=0)');
		
	}
	
	else {
		
		$mypicturesarray = $wpdb->get_results('SELECT id FROM '.$table_prefix.'mypictures WHERE '.$table_prefix.'mypictures.picexclude IS NULL OR '.$table_prefix.'mypictures.picexclude=0');
	}
	
	// exit if no gallery pictures are found
	
	if (!$mypicturesarray) {
		return;	
	}
	// get random element(s)
	
	for ($i =1; $i<=$myamount; $i++){
		srand();
		$myrandomid=$mypicturesarray[ array_rand ($mypicturesarray)]->id;
		
		// read element infos
		
		$result=$wpdb->get_row('SELECT * FROM '.$table_prefix.'mypictures,'.$table_prefix.'mygprelation,'.$table_prefix.'mygallery WHERE '.$table_prefix.'mypictures.id = '.$myrandomid.' AND '.$table_prefix.'mygprelation.pid = '.$myrandomid.' AND '.$table_prefix.'mygallery.id ='.$table_prefix.'mygprelation.gid' );
		
		$mypage_id=$wpdb->get_var('SELECT pageid FROM '.$table_prefix.'mygallery WHERE id ='.$result->gid);
		
		
		// return picture
		
		if ($mg_options[templatemode] AND (!$mypage_id)) $mypage_id=$mg_options[templatemodeid];
		
		if  ($mg_options[randombox]) {
			$myhyperlink_open='<a rel="lightbox" href="'.$myurl.$result->name.'/'.$result->picturepath.'">';
			$myhyperlink_close='</a>';
			
		}
		
		else if ($mypage_id) {
			
			$myreference=get_permalink($mypage_id);
			
			if ( $wp_rewrite->using_permalinks() ) {
				$myreference=$myreference.'?picture_id=';
			}
			else {
				$myreference=$myreference.'&amp;picture_id=';
			}
			
			
			$myhyperlink_open='<a href="'.$myreference.$result->pid.'">';
			$myhyperlink_close='</a>';
		}
		
		$thumb_size=getimagesize(ABSPATH.$mg_options[gallerybasepath].$result->name.'/tumbs/tmb_'.$result->picturepath);
		
		$mypicstring=$mypicstring.'<div class="myrandompic">'.$myhyperlink_open.'<img src="'.$myurl.$result->name.'/tumbs/tmb_'.$result->picturepath.'" alt="'.$result->alttitle.'" title="'.$result->alttitle.'" width="'.$thumb_size[0].'" height="'.$thumb_size[1].'" />'.$myhyperlink_close.'</div>';
	}
	
	
	echo $mypicstring;// maybe not the best way to show the result
	
}

//#################################################################

function myGalleryJScript() {
	
	global $mg_options;
	
	$myscript = '<script type="text/javascript">'."
	function changePicture(img, galid,myalt,mywidth,myheight,myftid) {
		document.getElementById(galid).setAttribute('src',img);
		document.getElementById(galid).setAttribute('alt',myalt);
		document.getElementById(galid).setAttribute('title',myalt);";
		if ($mg_options[igsafewh]) {
			$myscript =$myscript."document.getElementById(galid).setAttribute('width',mywidth);
			document.getElementById(galid).setAttribute('height',myheight);";
		}
		$myscript =$myscript."bigid='b'+galid;
		document.getElementById(bigid).setAttribute('href',img);
		var footerid=galid+'ft';
		document.getElementById(footerid).firstChild.nodeValue=document.getElementById(myftid).firstChild.nodeValue;
	}
	</script>";
	echo $myscript;
	
}

//#################################################################

function myGalleryStyle() {
	
	global $mg_options;
	
	$mystyle="\n".'<style type="text/css" media="screen">'."\n".'@import url('.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/css/'.$mg_options[stylefile].');'."\n".'</style>';
	echo $mystyle;
}
//#################################################################

function includeLightboxJS() {
	
	global $mg_options;
	
	if ($mg_options[lightboxversion]==2) {
		@include_once(myGalleryPath. 'myfunctions/mylbx2_inc.php');
	}
	
	else {
		@include_once(myGalleryPath. 'myfunctions/mylbx_inc.php');
	}
	
	echo $myscript;
}
//#################################################################
function my_button_init() {
	
	// used to insert button in wordpress 2.x editor
	
	$button_image_url = buttonsnap_dirname(__FILE__) . '/images/mgbrowser.gif';
	
	buttonsnap_jsbutton($button_image_url, 'myGallery Browser', 'window.open("'.myGalleryURL.'myfunctions/mygallerybrowser.php?myPath='.ABSPATH.'", "myGalleryBrowser",  "width=780,height=600,scrollbars=yes");');
}

//#################################################################

// init mygallery tables in wp-database if plugin is activated

if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
	
	require_once(ABSPATH . 'wp-content/plugins/mygallery/myfunctions/mygalleryinstall.php');
	add_action('init', 'mygallery_install');
}

// adding functions to wordpress

add_filter('the_content', 'mygallery',9); 

//add_filter('the_content', 'nasty_p_filter'); 

add_action('admin_menu', 'mygallery_menu');

// add JavaScript to header

add_action('wp_head', 'myGalleryJScript');

// add Stlye to header

$mg_options=get_option('mygalleryoptions');

if ($mg_options[includestyle]) add_action('wp_head', 'myGalleryStyle');

// add Lightbox JS to header

if (($mg_options[lightboxjs]) OR ($mg_options[gallerybox]) OR ($mg_options[randombox])) add_action('wp_head', 'includeLightboxJS');

// add gallerybrowser for posts

@include_once(myGalleryPath.'myfunctions/buttonsnap.php');	
add_action('init', 'my_button_init');

?>