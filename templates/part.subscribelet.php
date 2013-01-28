
<small>
	<?php p($l->t('Drag this to your browser bookmarks and click on it whenever you want to subscribe to a webpage quickly:')) ?>
</small>
<a class="button bookmarklet" 
   href="javascript:(function() {
   	var a=window,
   	    b=document,
   	    c=encodeURIComponent,
   	    d=a.open('<?php print_unescaped(OCP\Util::linkToAbsolute('news', 'subscribe.php'))?>?output=popup&url='+c(b.location),
   	    			'bkmk_popup','left='+((a.screenX||a.screenLeft)+10)+',
   	    			 top='+((a.screenY||a.screenTop)+10)+',
   	    			 height=150px,width=360px,resizable=1,alwaysRaised=1');
   	    a.setTimeout(function() {
   	    	d.focus()},300);
   	    })();">
	<?php p($l->t('Subscribe')) ?>
</a>

