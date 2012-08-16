<?php 

if(isset($_['feedid'])){
    $feedMapper = new OCA\News\FeedMapper();
    $feed = $feedMapper->findById($_['feedid']);
    $feedTitle = $feed->getTitle();
    
    $itemMapper = new OCA\News\ItemMapper();
    $unreadItemsCount = $itemMapper->countAllStatus($_['feedid'], OCA\News\StatusFlag::UNREAD);
    if($unreadItemsCount > 0){
        $readClass = '';
    } else {
        $readClass = 'all_read';
    }
} else {
    $feedTitle = '';
    $unreadItemsCount = 0;
}

// FIXME: get this setting from the database
$showOnlyUnread = true;

?>

<div class="feed_controls">
   <span title="<?php echo $l->t('Unread items'); ?>" class="unreaditemcounter <?php echo $readClass; ?>"><?php echo $unreadItemsCount; ?></span>
    <div class="feed_title">
        <h1 title="<?php echo $feedTitle; ?>"><?php echo $feedTitle; ?></h1>
    </div>
    <div class="controls">
        <input type="button" value="<?php echo $l->t('Mark all read'); ?>" id="mark_all_as_read" />
        <select id="feed_filter">
            <option value="unread" <?php if($showOnlyUnread){ echo 'selected="selected"'; }; ?>><?php echo $l->t('Show only unread articles'); ?></option>
            <option value="all" <?php if(!$showOnlyUnread){ echo 'selected="selected"'; }; ?>><?php echo $l->t('Show read/unread articles'); ?></option>
        </select>
    </div>
</div>