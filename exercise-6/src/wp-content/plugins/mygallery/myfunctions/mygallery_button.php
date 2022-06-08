<?php

// Insert a quicktag button  - based on the Edit Button Framework by Owen Winkler

//#################################################################
function mygallery_picturebrowser()

{
	if(strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php'))
	{
?>
<script language="JavaScript" type="text/javascript"><!--
var toolbar = document.getElementById("ed_toolbar");
<?php

		edit_insert_button("gallerybrowser", "mygallerybrowser", "gallerybrowser");
	
?>

function mygallerybrowser()
{
	window.open("<?php echo myGalleryURL ?>myfunctions/mygallerybrowser.php?myPath=<?php echo ABSPATH ?>", "myGalleryBrowser",  "width=700,height=600,scrollbars=yes");
}

//--></script>
<?php
	}
}
//#################################################################

if(!function_exists('edit_insert_button'))
{
	function edit_insert_button($caption, $js_onclick, $title = '')
	{
	?>
	if(toolbar)
	{
		var theButton = document.createElement('input');
		theButton.type = 'button';
		theButton.value = '<?php echo $caption; ?>';
		theButton.onclick = <?php echo $js_onclick; ?>;
		theButton.className = 'ed_button';
		theButton.title = "<?php echo $title; ?>";
		theButton.id = "<?php echo "ed_{$caption}"; ?>";
		toolbar.appendChild(theButton);
	}
	<?php

	}
}
//#################################################################
?>