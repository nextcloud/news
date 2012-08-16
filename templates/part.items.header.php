<?php 

if(isset($_['feedid'])){
    $feedId = $_['feedid'];
    $itemMapper = new OCA\News\ItemMapper();
    switch ($feedId) {
        case -1:
            $feedTitle = $l->t('Starred');
            $unreadItemCount = $itemMapper->countAllStatus($feedId, OCA\News\StatusFlag::IMPORTANT);
            break;

        case -2:
            $feedTitle = $l->t('New articles');
            $unreadItemCount = $itemMapper->countEveryItemByStatus(OCA\News\StatusFlag::UNREAD);
            break;
        
        default:
            $feedMapper = new OCA\News\FeedMapper();
            $feed = $feedMapper->findById($feedId);
            $feedTitle = $feed->getTitle();
            $unreadItemCount = $itemMapper->countAllStatus($feedId, OCA\News\StatusFlag::UNREAD);
            break;
    }

    if($unreadItemCount > 0){
        $readClass = '';
    } else {
        $readClass = 'all_read';
    }
} else {
    $feedTitle = '';
    $unreadItemCount = 0;
}

$showAll = OCP\Config::getUserValue(OCP\USER::getUser(), 'news', 'showAll'); 

?>

<div class="feed_controls">
   <span title="<?php echo $l->t('Unread items'); ?>" class="unreaditemcounter <?php echo $readClass; ?>"><?php echo $unreadItemCount; ?></span>
    <div class="feed_title">
        <h1 title="<?php echo $feedTitle; ?>"><?php echo $feedTitle; ?></h1>
    </div>
    <div class="controls">
        <input type="button" value="<?php echo $l->t('Mark all read'); ?>" id="mark_all_as_read" />
        <select id="feed_filter">
            <option value="unread" <?php if(!$showAll){ echo 'selected="selected"'; }; ?>><?php echo $l->t('Show only unread articles'); ?></option>
            <option value="all" <?php if($showAll){ echo 'selected="selected"'; }; ?>><?php echo $l->t('Show read/unread articles'); ?></option>
        </select>
    </div>
</div>