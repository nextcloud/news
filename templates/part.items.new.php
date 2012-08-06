<?php
$title = isset($_['title']) ? $_['title'] : '';

echo	'<div class="rightcontentmsg" id="feedadded">' .
		'You have subscribed to <b>"' . $title . '"</b>' .
	'</div>';
