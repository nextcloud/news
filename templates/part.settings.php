<div id="app-settings-header">
    <button name="app settings"
            class="settings-button"
            data-apps-slide-toggle="#app-settings-content">
        <?php p($l->t('Settings')); ?>
    </button>
</div>

<div id="app-settings-content">
    <h3><?php p($l->t('Settings')); ?></h3>

    <p>
        <label>
            <input type="checkbox"
                   ng-click="Settings.toggleSetting('preventReadOnScroll')"
                   ng-checked="Settings.getSetting('preventReadOnScroll')"
                   name="preventReadOnScroll">
            <?php p($l->t('Disable mark read through scrolling')); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox"
                   ng-click="Settings.toggleSetting('compact')"
                   ng-checked="Settings.getSetting('compact')"
                   name="compact">
            <?php p($l->t('Compact view')); ?>
        </label>
    </p>

    <p ng-show="Settings.getSetting('compact')">
        <label>
            <input type="checkbox"
                   ng-click="Settings.toggleSetting('compactExpand')"
                   ng-checked="Settings.getSetting('compactExpand')"
                   name="compactExpand">
            <?php p($l->t('Expand articles on key navigation')); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox"
                   ng-click="Settings.toggleSetting('showAll')"
                   ng-checked="Settings.getSetting('showAll')"
                   name="showAll">
            <?php p($l->t('Show all articles')); ?>
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox"
                   ng-click="Settings.toggleSetting('oldestFirst')"
                   ng-checked="Settings.getSetting('oldestFirst')"
                   name="oldestFirst">
            <?php p($l->t('Reverse ordering (oldest on top)')); ?>
        </label>
    </p>

    <div class="import-export">
        <h3><?php p($l->t('Subscriptions (OPML)')); ?></h3>

        <input type="file"
               id="opml-upload"
               name="import"
               news-read-file="Settings.importOPML($fileContent)"/>

        <button title="<?php p($l->t('Import')); ?>"
                class="icon-upload svg button-icon-label"
                news-trigger-click="#opml-upload"
                ng-class="{'entry-loading': Settings.isOPMLImporting}"
                ng-disabled=
                "Settings.isOPMLImporting || Settings.isArticlesImporting">
        </button>

        <a title="<?php p($l->t('Export')); ?>"
           class="button icon-download svg button-icon-label"
           href="<?php p($_['url_generator']->linkToRoute('news.export.opml')); ?>"
           target="_blank"
           rel="noreferrer"
           ng-hide="App.isFirstRun()">
        </a>

        <button
            class="icon-download svg button-icon-label"
            title="<?php p($l->t('Export')); ?>"
            ng-show="App.isFirstRun()"
            disabled>
        </button>

        <p class="error" ng-show="Settings.opmlImportError">
            <?php p(
                $l->t('Error when importing: file does not contain valid OPML')
            ); ?>
        </p>
        <p class="error" ng-show="Settings.opmlImportEmptyError">
            <?php p(
                $l->t('Error when importing: OPML is does neither contain ' .
                    'feeds nor folders')
            ); ?>
        </p>

        <h3><?php p($l->t('Unread/Starred Articles')); ?></h3>

        <input
            type="file"
            id="article-upload"
            name="importarticle"
            news-read-file="Settings.importArticles($fileContent)"/>

        <button title="<?php p($l->t('Import')); ?>"
                class="icon-upload svg button-icon-label"
                ng-class="{'entry-loading': Settings.isArticlesImporting}"
                ng-disabled="Settings.isOPMLImporting || Settings.isArticlesImporting"
                news-trigger-click="#article-upload">
        </button>

        <a title="<?php p($l->t('Export')); ?>"
           class="button icon-download svg button-icon-label"
           href="<?php p($_['url_generator']->linkToRoute('news.export.articles')); ?>"
           target="_blank"
           rel="noreferrer"
           ng-hide="App.isFirstRun()">
        </a>
        <button
            class="icon-download svg button-icon-label"
            title="<?php p($l->t('Export')); ?>"
            ng-show="App.isFirstRun()"
            disabled>
        </button>

        <p class="error" ng-show="Settings.articleImportError">
            <?php p(
                $l->t('Error when importing: file does not contain valid JSON')
            ); ?>
        </p>

    </div>

    <h3><?php p($l->t('Help')); ?></h3>

    <p>
        <a href="#/shortcuts/"><?php p($l->t('Keyboard shortcuts')); ?></a>
    </p>

    <p>
        <a target="_blank"
           rel="noreferrer"
           href="https://github.com/nextcloud/news/tree/master/docs"><?php p($l->t('Documentation')); ?></a>
    </p>
    <p>
        <a target="_blank"
           rel="noreferrer"
           href="https://github.com/nextcloud/news/issues/new"><?php p($l->t('Report a bug')); ?></a>
    </p>

</div>
