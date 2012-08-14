<?php 

if(isset($_['feedid'])){
    $feedmapper = new OCA\News\FeedMapper();
    $feed = $feedmapper->findById($_['feedid']);
    $feedTitle = $feed->getTitle();
} else {
    $feedTitle = '';
}

// FIXME: get this setting from the database
$showOnlyUnread = true;

?>

<div class="feed_controls">
   
    <div class="feed_title">
        <h1><?php echo $feedTitle; ?></h1>
    </div>
    <div class="controls">
        <input type="button" value="<?php echo $l->t('Mark all as read'); ?>" id="mark_all_as_read" />
        <select id="feed_filter">
            <option value="unread" <?php if($showOnlyUnread){ echo 'selected="selected"'; }; ?>><?php echo $l->t('Show only unread articles'); ?></option>
            <option value="all" <?php if(!$showOnlyUnread){ echo 'selected="selected"'; }; ?>><?php echo $l->t('Show read/unread articles'); ?></option>
        </select>
    </div>
</div>