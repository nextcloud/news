<div id="app-settings-header">
<button name="app settings"
  class="settings-button"
  data-apps-slide-up-area="#app-settings-content"></button>
</div>

<div id="app-settings-content">
  <h3><?php p($l->t('Settings')); ?></h3>

  <p ng-click="Settings.toggleSetting('compact')">
    <input type="checkbox" ng-checked="Settings.getSetting('compact')">
    <?php p($l->t('Use compact view')); ?>
  </p>

  <p ng-click="Settings.toggleSetting('showAll')">
    <input type="checkbox" ng-checked="Settings.getSetting('showAll')">
    <?php p($l->t('Show unread articles')); ?>
  </p>

  <p ng-click="Settings.toggleSetting('oldestFirst')">
    <input type="checkbox" ng-checked="Settings.getSetting('oldestFirst')">
    <?php p($l->t('Order by oldest first')); ?>
  </p>

  <p ng-click="Settings.toggleSetting('preventReadOnScroll')">
    <input type="checkbox" ng-checked="Settings.getSetting('preventReadOnScroll')">
    <?php p($l->t('Do not as mark read when scrolling')); ?>
  </p>


  <h3><?php p($l->t('Subscriptions (OPML)')); ?></h3>

  <input type="file"
         id="opml-upload"
         name="import"
         accept="text/x-opml, text/xml"
         news-read-file="Settings.importOpml($fileContent)"/>

  <button title="<?php p($l->t('Import')); ?>"
          class="icon-upload svg button-icon-label"
          news-trigger-click="#opml-upload">
    <?php p($l->t('Import')); ?>
  </button>


  <a title="<?php p($l->t('Export')); ?>"
    class="button icon-download svg button-icon-label"
    href="<?php p(\OCP\Util::linkToRoute('news.export.opml')); ?>"
    target="_blank"
    ng-show="feedSize() > 0">
    <?php p($l->t('Export')); ?>
  </a>

  <button
    class="icon-download svg button-icon-label"
    title="<?php p($l->t('Export')); ?>"
    ng-hide="feedSize() > 0"
    disabled>
    <?php p($l->t('Export')); ?>
  </button>

  <p class="error" ng-show="Settings.opmlImportError">
    <?php p($l->t('Error when importing: file does not contain valid OPML')); ?>
  </p>


  <h3><?php p($l->t('Unread/Starred Articles')); ?></h3>

  <input
    type="file"
    id="article-upload"
    name="importarticle"
    accept="application/json"
    news-read-file="Settings.importArticles($fileContent)"/>

  <button title="<?php p($l->t('Import')); ?>"
    class="icon-upload svg button-icon-label"
    ng-class="{'icon-loading-small': Settings.importing}"
    ng-disabled="importing"
    news-trigger-click="#article-upload">
    <?php p($l->t('Import')); ?>
  </button>

  <a title="<?php p($l->t('Export')); ?>" class="button icon-download svg button-icon-label"
    href="<?php p(\OCP\Util::linkToRoute('news.export.articles')); ?>"
    target="_blank"
    ng-show="feedSize() > 0">
    <?php p($l->t('Export')); ?>
  </a>
  <button
    class="icon-download svg button-icon-label"
    title="<?php p($l->t('Export')); ?>"
    ng-hide="feedSize() > 0"
    disabled>
    <?php p($l->t('Export')); ?>
  </button>

  <p class="error" ng-show="Settings.articleImportError">
    <?php p($l->t('Error when importing: file does not contain valid JSON')); ?>
  </p>

</div>