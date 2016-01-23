<li ng-class="{active: Navigation.isExploreActive()}" class="explore-feed">
    <a class="icon-link" ng-href="#/explore/?lang={{Navigation.getLanguageCode()}}">
       <?php p($l->t('Explore')) ?>
    </a>
</li>
