<?php 

//#################################################################

function mygallery_install() {
	 
	global $table_prefix, $wpdb, $user_level;
	
	// test user level
	
	get_currentuserinfo();
	if ($user_level < 8)
	{
		return;
	}
	
	$mg_options=get_option('mygalleryoptions');
	
	if ($mg_options[mgversion]!='1.2') {
	
	// define myGallery exif Options
	
	$mg_exifoptions[showexif]=0;
	$mg_exifoptions[make]=0;
	$mg_exifoptions[model]=0;
	$mg_exifoptions[exposuretime]=0;
	$mg_exifoptions[afnumber]=0;
	$mg_exifoptions[focall]=0;
	$mg_exifoptions[iso]=0;
	$mg_exifoptions[flash]=0;
	$mg_exifoptions[datime]=0;
	$mg_exifoptions[expro]=0;
	$mg_exifoptions[exifskipempty]=1;
	
	update_option('mygexifoptions', $mg_exifoptions);
	
	// define and set myGallery Options
	
	$mg_options = array ();
	
	$mg_options[shrinkfit]=0;
	$mg_options[shrinkwidth]=400;
	$mg_options[scalethumb]=0;
	$mg_options[tumbwidth]=100;
	$mg_options[tumbheight]=75;
	$mg_options[tumbwidth_a]=100;
	$mg_options[tumbwidth_b]=100;
	$mg_options[preserve]=0;
	$mg_options[sortorder]=1;
	$mg_options[previewpic]=1;
	$mg_options[galdescrip]=1;
	$mg_options[longnames]=1;
	$mg_options[bigpicshowthumbs]=0;
	$mg_options[excludeoverview]=1;
	$mg_options[includestyle]=1;
	$mg_options[allowedfiletypes]=array ('jpg', 'png', 'gif');
	$mg_options[simplescale]=0;
	$mg_options[simplescalewidth]=400;
	$mg_options[sortascdes]=1;
	$mg_options[language]='';
	$mg_options[inlinepicdesc]=0;
	$mg_options[stylefile]='mygallery_default.css';
	$mg_options[lightboxjs]=0;
	$mg_options[galpagesize]=1;
	$mg_options[galpagebreak]=0;
	$mg_options[navigationup]='&circ;';
	$mg_options[navigationback]='&laquo;';
	$mg_options[navigationfor]='&raquo;';
	$mg_options[gallerybasepath]='wp-content/myfotos/';
	$mg_options[gal_sortascdes]=1;
	$mg_options[gal_sortorder]=1;
	$mg_options[igsafewh]=0;
	$mg_options[gallerybox]=0;
	$mg_options[igpicdes]=0;
	$mg_options[galbpicdes]=1;
	$mg_options[lightboxversion]=1;
	$mg_options[thumbstopages]=0;
	$mg_options[thumbsamount]=0;
	$mg_options[thumbscounterdisplay]=0;
	$mg_options[mypiccounter]=0;
	$mg_options[galcountdisplay]=0;
	$mg_options[picturesingallery]=0;
	$mg_options[sortorder]=1;
	$mg_options[thumbquality]=100;
	$mg_options[randombox]=0;
	$mg_options[templatemode]=0;
	$mg_options[templatemodeid]=0;
	}
	
	// set tablename
	
	$table_name = $table_prefix . "mygallery"; // main gallery
	$table_name2 = $table_prefix . "mypictures"; // single pictures
	$table_name3 = $table_prefix . "mygprelation"; // relation
	
	// if tables doesn«t exist, create them
	
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	
	if($wpdb->get_var("show tables like '$table_name'")!= $table_name){
		
		$sql = "CREATE TABLE ".$table_name." (id mediumint(9) NOT NULL AUTO_INCREMENT,name VARCHAR(255) NOT NULL , pageid  mediumint(9), previewpic VARCHAR(255), galdescrip TINYTEXT, longname VARCHAR(255), excludegal TINYINT, gallsortnr TINYINT, parentid TINYINT, PRIMARY KEY (id));";
		
		dbDelta($sql);
	}
	
	if($wpdb->get_var("show tables like '$table_name2'")!= $table_name2){
		
		$sql = "CREATE TABLE ".$table_name2." (id mediumint(9) NOT NULL AUTO_INCREMENT,picturepath VARCHAR(255) NOT NULL , description TINYTEXT, picsort mediumint(9), alttitle VARCHAR(255), picexclude TINYINT, PRIMARY KEY (id));";
		
		dbDelta($sql);
		
	}
	
	if($wpdb->get_var("show tables like '$table_name3'")!= $table_name3){
		
		$sql = "CREATE TABLE ".$table_name3." (gid mediumint(9) NOT NULL, pid mediumint(9) NOT NULL);";
		
		dbDelta($sql);
		
	}
	
	// auto patch function
	
	
	// update patch for version 1.0
	
	//if (($mg_options[mgversion]=='0.5.7') or ($mg_options[mgversion]=='0.5.8') or ($mg_options[mgversion]=='0.5.9') ) {
		
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name.' LIKE "gallsortnr"');
		if (!$result) $wpdb->query('ALTER TABLE '.$table_name.' ADD gallsortnr TINYINT');
		
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name.' LIKE "parentid"');
		if (!$result) $wpdb->query('ALTER TABLE '.$table_name.' ADD parentid TINYINT');
		
		$result=$wpdb->query('SHOW COLUMNS FROM '.$table_name2.' LIKE "picexclude"');
		if (!$result) $wpdb->query('ALTER TABLE '.$table_name2.' ADD picexclude TINYINT');
	//}
	
	$mg_options[mgversion]='1.2';

	update_option('mygalleryoptions', $mg_options);
}
//#################################################################
?>
