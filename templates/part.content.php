<?php print_unescaped($this->inc('part.content.warnings')) ?>

<div news-auto-focus="#app-content"
    id="articles"
     ng-class="{
        compact: Content.isCompactView(),
        'feed-view': Content.isFeed()
    }"
    news-compact-expand="{{ Content.isCompactExpand() }}"
    class="app-content-detail">
    <div ng-show="Content.getItems().length == 0" class="no-feeds-available">
        <p ng-show="Content.isShowAll()"><?php p($l->t('No articles available')) ?></p>
        <p ng-show="!Content.isShowAll()"><?php p($l->t('No unread articles available')) ?></p>
    </div>
    <button ng-controller="NavigationController as Navigation" id="mark-all-read-button" ng-click="Navigation.markCurrentRead()" class="hidden">
        <span title="Mark Read" class="icon-checkmark"></span>
    </button>

    <ul>
        <li class="item {{ ::Content.getFeed(item.feedId).cssClass }}"
            ng-repeat="item in Content.getItems() |
                orderBy:'id':Content.oldestFirst:Content.sortIds track by item.id"
            ng-mouseup="Content.markRead(item.id)"
            ng-click="Content.markRead(item.id); Content.setItemActive(item.id)"
            news-on-active="Content.setItemActive(item.id)"
            ng-class="{read: !item.unread, open: item.show, active: Content.isItemActive(item.id)}"
            data-id="{{ ::item.id }}">

            <div class="utils" ng-click="Content.toggleItem(item)">
                <ul>
                    <li class="util-spacer"></li>
                    <li class="util only-in-compact">
                        <a class="external icon-link"
                            ng-click="Content.markRead(item.id)"
                            target="_blank"
                            rel="noreferrer"
                            ng-href="{{ ::item.url }}"
                            title="<?php p($l->t('Open website')) ?>"
                            news-stop-propagation>
                        </a>
                    </li>
                    <li class="title only-in-compact"
                        ng-class="{
                            'icon-rss':
                                !Content.getFeed(item.feedId).faviconLink
                        }"
                        ng-style="{
                            backgroundImage:
                                'url('
                                    + Content.getFeed(item.feedId).faviconLink +
                                ')'
                            }">
                        <h1 ng-attr-dir="{{item.rtl && 'rtl'}}"><a>{{ ::item.title }} <span class="intro" news-bind-html-unsafe="::item.intro"></span></a></h1>
                    </li>
                    <li class="only-in-compact">
                        <time class="date"
                                title="{{ item.pubDate*1000 |
                                            date:'yyyy-MM-dd HH:mm:ss' }}"
                            datetime="{{ item.pubDate*1000 |
                                            date:'yyyy-MM-ddTHH:mm:ssZ' }}">
                                {{ Content.getRelativeDate(item.pubDate) }}
                        </time>
                    </li>
                    <li ng-click="Content.toggleStar(item.id)"
                        class="util"
                        news-stop-propagation>
                        <button class="star svg"
                                ng-hide="item.starred"
                                title="<?php p($l->t('Star article')); ?>">
                        </button>
                        <button class="starred svg"
                                ng-show="item.starred"
                                title="<?php p($l->t('Unstar article')); ?>">
                        </button>
                    </li>
                    <li ng-click="Content.toggleKeepUnread(item.id)"
                        class="util toggle-keep-unread"
                        news-stop-propagation>
                        <button class="icon-toggle"
                            ng-hide="item.keepUnread"
                            title="<?php p($l->t('Keep article unread')); ?>">
                        </button>
                        <button
                            class="icon-toggle keep-unread"
                            ng-show="item.keepUnread"
                            title="<?php
                                p($l->t('Remove keep article unread'));
                            ?>">
                        </button>
                    </li>
                    <!-- DROPDOWN SHARE -->
                    <li ng-controller="ShareController as Share"
                        class="util dropdown"
                        news-stop-propagation>
                        <button class="icon-share share dropbtn"
                            title="<?php p($l->t('Share')) ?>"
                            ng-click="Share.toggleDropdown()">
                        </button>

                        <div
                            ng-if="Share.showDropDown"
                            style="margin-top: 2.8em;"
                            class="dropdown-content">
                            <!-- Share with users -->
                            <p class="label-group"><?php p($l->t('Share with users')) ?></p>
                            <form ng-submit=""
                                name="contactForm"
                                autocomplete="off">
                                <fieldset class="contact-input">
                                    <input
                                        ng-model="nameQuery"
                                        ng-model-options="{debounce: 400}"
                                        ng-change="Share.searchUsers(nameQuery)"
                                        type="text"
                                        placeholder="<?php p($l->t('Username')) ?>"
                                        title="<?php p($l->t('Username')) ?>"
                                        name="contactName"
                                        required
                                        style="width: 200px">
                                        <div ng-if="App.loading.isLoading('user')"
                                            ng-class="{'icon-loading-small': App.loading.isLoading('user') }">
                                        </div>
                                </fieldset>
                            </form>

                            <div style="margin-left: 2em; font-size: 0.85em;"
                                ng-if="!(Share.userList.length > 0) && nameQuery && !App.loading.isLoading('user')">
                                <?php p($l->t('No users found')) ?>
                            </div>
                            <a
                                ng-repeat="user in Share.userList"
                                class="icon-category-installed pr-3"
                                ng-click="Share.shareItem(item.id, user.value.shareWith)">
                                {{ user.label }}
                                <span class="right" style="margin-top: 0.9em; margin-right: 1em"
                                    ng-class="{'icon-loading-small': Share.isLoading(user.value.shareWith), 'icon-checkmark': Share.isStatus(item.id, user.value.shareWith, true), 'icon-close': Share.isStatus(item.id, user.value.shareWith, false)}">
                                </span>
                            </a>

                            <p class="label-group"> <?php p($l->t('Share on social media')) ?> </p>
                            <div class="row">
                                <div class="col-4">
                                    <a target="_blank"
                                        class="icon-dropdown icon-facebook pr-5"
                                        ng-href="{{Share.getFacebookUrl(item.url)}}"></a>
                                </div>
                                <div class="col-4">
                                    <a target="_blank"
                                        class="icon-dropdown icon-twitter pr-5"
                                        ng-href="{{Share.getTwitterUrl(item.url)}}"></a>
                                </div>
                                <div class="col-4">
                                    <a class="icon-dropdown icon-mail pr-5"
                                        ng-href="mailto:?subject=<?php p($l->t('I wanted you to see this article')) ?>&amp;body=<?php p($l->t('Check out this article')) ?>{{ ::item.url }}"></a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!-- END DROPDOWN -->

                    <li class="util more" news-stop-propagation ng-hide="noPlugins">
                        <button class="icon-more" news-toggle-show="#actions-{{item.id}}"></button>
                        <div class="article-actions" id="actions-{{item.id}}">
                            <ul news-article-actions="item" no-plugins="noPlugins"></ul>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="article" ng-if="!Content.isCompactView() || item.show">

                <div class="heading only-in-expanded">
                    <time class="date"
                          title="{{ item.pubDate*1000 |
                            date:'yyyy-MM-dd HH:mm:ss' }}"
                          datetime="{{ item.pubDate*1000 |
                            date:'yyyy-MM-ddTHH:mm:ssZ' }}">
                        {{ Content.getRelativeDate(item.pubDate) }}
                    </time>
                    <h1 ng-attr-dir="{{item.rtl && 'rtl'}}">
                        <a class="external"
                            target="_blank"
                            rel="noreferrer"
                            ng-href="{{ ::item.url }}"
                            title="{{ ::item.title }}">
                            {{ ::item.title }}
                        </a>
                    </h1>
                </div>

                <div class="subtitle" ng-attr-dir="{{item.rtl && 'rtl'}}">
                    <span class="author" ng-show="item.author">
                        <?php p($l->t('by')) ?> {{ ::item.author }}
                    </span>
                    <span ng-if="!item.sharedBy" class="source"><?php p($l->t('from')) ?>
                        <a ng-href="#/items/feeds/{{ item.feedId }}/">
                            {{ ::Content.getFeed(item.feedId).title }}
                            <img ng-if="Content.getFeed(item.feedId).faviconLink && !Content.isCompactView()" src="{{ ::Content.getFeed(item.feedId).faviconLink }}" alt="favicon">
                        </a>
                    </span>
                    <span ng-if="item.sharedBy">
                        <span ng-if="item.author">-</span>
                        <?php p($l->t('shared by')) ?>
                        {{ ::item.sharedBy }}
                    </span>
                </div>

                <div class="enclosure" ng-if="Content.getMediaType(item.enclosureMime) == 'audio'">
                    <button ng-click="App.play(item)"><?php p($l->t('Play audio')) ?></button>
                    <a class="button" ng-href="{{ item.enclosureLink|trustUrl }}" target="_blank" rel="noreferrer">
                        <?php p($l->t('Download audio')) ?>
                    </a>
                </div>
                <div class="enclosure" ng-if="Content.getMediaType(item.enclosureMime) == 'video'">
                    <video controls preload="none" news-play-one ng-src="{{ item.enclosureLink|trustUrl }}" type="{{ item.enclosureMime }}">
                    </video>
                    <a class="button" ng-href="{{ item.enclosureLink|trustUrl }}" target="_blank" rel="noreferrer">
                        <?php p($l->t('Download video')) ?>
                    </a>
                </div>

                <div class="enclosure thumbnail" ng-if="item.mediaThumbnail">
                    <a ng-href="{{ ::item.enclosureLink }}"><img ng-src="{{ item.mediaThumbnail|trustUrl }}" alt="" /></a>
                </div>

                <div class="enclosure description" ng-if="item.mediaDescription" news-bind-html-unsafe="item.mediaDescription"></div>

                <div class="body" news-bind-html-unsafe="item.body" ng-attr-dir="{{item.rtl && 'rtl'}}"></div>

            </div>
        </li>
    </ul>
</div>
