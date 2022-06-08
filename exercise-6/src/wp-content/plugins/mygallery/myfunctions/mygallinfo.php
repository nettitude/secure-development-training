<?php
//#################################################################

// check if user has the rights to use the function 

get_currentuserinfo();
if ($user_level < 6)
{
	return;
}
//#################################################################


?>
<div class="wrap">
<h2><?php _e('General Info', 'myGallery') ;?></h2>
<p>&#8222;Non si vive di solo pane.&#8221; &#8211; &#8222;Der Mensch lebt nicht vom Brot allein.&#8221;</p>
<p>Ich freue mich immer &uuml;ber Anregungen, Erw&auml;hnung in anderen Blogs und eMails (gerne auch echte Postkarten aus aller Welt). <br>
Auch steht es jedem frei, eine kleine Spende via PayPal zu t&auml;tigen oder mir etwas von meiner <a href="http://www.amazon.de/gp/registry/registry.html/303-8835025-5324264?%5Fencoding=UTF8&type=wishlist&id=3LK2132IQ9NH6">amazon-Wunschliste</a> zu bestellen.</p>
<p>I would be happy if you mention me in your blog, post a comment, write me an email (or send me a postcard). You may also make a donation via Paypal or buy me something from my <a href="http://www.amazon.de/gp/registry/registry.html/303-8835025-5324264?%5Fencoding=UTF8&type=wishlist&id=3LK2132IQ9NH6">amazon wish-list</a>.</p>

<p>Die jeweils neuste Version gibt es hier/ Newest version can be found here: <a href="http://www.wildbits.de/mygallery/">www.wildbits.de/mygallery/</p>
<p></a> Deutschsprachiges Hilfe-Forum rund um WordPress / german WordPress support-forum: <a href="http://forum.wordpress.de/">forum.wordpress.de</a></p>

<p><br><form id="payform" action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"/><input type="image" src="https://www.paypal.com/de_DE/i/btn/x-click-but04.gif"  border="0" alt="Spenden via PayPal" style="border: 0px; padding:0px;" /><input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" /></form></p>
</div>
<?php

?>
