<?php

/*Mini-Wraper to include Lighbox 1.0 */

$myscript = '<script type="text/javascript">'.'
var loadingImage = \''.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-old/loading.gif\';'."\n".'var closeButton = \''.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-old/close.png\';
</script>'."\n".'<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-old/lightbox.js"></script>';
$myscript=$myscript."\n".'<style type="text/css" media="screen">'."\n".'@import url('.get_bloginfo('wpurl').'/wp-content/plugins/mygallery/lightbox-old/lightbox.css);'."\n".'</style>';


?>
