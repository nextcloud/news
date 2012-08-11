<?php 

if(isset($_['feedid'])){
    $feedmapper = new OCA\News\FeedMapper();
    $feed = $feedmapper->findById($_['feedid']);
    $feedTitle = $feed->getTitle();
} else {
    $feedTitle = '';
}

?>

<div class="feed_controls">
    <div class="feed_title">
        <h1>» <?php echo $feedTitle; ?></h1>
    </div>
    <input type="button" value="<?php echo $l->t('Mark all as read'); ?>" id="mark_all_as_read" />
    <select id="feed_filter">
        <option value="all"><?php echo $l->t('Show all articles'); ?></option>
        <option value="newest"><?php echo $l->t('Show newest articles'); ?></option>
    </select>
</div>