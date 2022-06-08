<?php

/*Mini-Wraper to include Lighbox 2.0 */

$myscript = '<script type="text/javascript">'."\n".'var fileLoadingImage = \''.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-plugin/images/loading.gif\';'."\n".'var fileBottomNavCloseImage = \''.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-plugin/images/closelabel.gif\';
</script>'."\n";	
$myscript=$myscript.'<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-plugin/js/prototype.js"></script>';
$myscript=$myscript.'<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-plugin/js/scriptaculous.js?load=effects"></script>';
$myscript=$myscript.'<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-plugin/js/lightbox.js"></script>';
$myscript=$myscript."\n".'<style type="text/css" media="screen">'."\n".'@import url('.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-plugin/css/lightbox.css);'."\n".'</style>';


?>
