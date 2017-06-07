<?php print_unescaped($this->inc('part.content.warnings')) ?>

<div news-auto-focus="#app-content"
    id="articles"
     ng-class="{
        compact: Content.isCompactView(),
        'feed-view': Content.isFeed()
    }"
    news-compact-expand="{{ Content.isCompactExpand() }}">
    <div ng-show="Content.getItems().length == 0" class="no-feeds-available">
        <p ng-show="Content.isShowAll()"><?php p($l->t('No articles available')) ?></p>
        <p ng-show="!Content.isShowAll()"><?php p($l->t('No unread articles available')) ?></p>
    </div>
    <ul>
        <li class="item {{ ::Content.getFeed(item.feedId).cssClass }}"
            ng-repeat="item in Content.getItems() |
                orderBy:[Content.orderBy()] track by item.id"
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
                    title="{{ (Content.showHeadlineAsTitle() ? item.title : '') }}"
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
                        <h1 ng-attr-dir="{{item.rtl && 'rtl'}}"><a>{{ ::item.title }} <span class="intro">{{ ::item.intro }}</span></a></h1>
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
                    <li class="util more" news-stop-propagation ng-hide="noPlugins">
                        <button class="icon-more" news-toggle-show="#actions-{{item.id}}"></button>
                        <div class="article-actions" id="actions-{{item.id}}">
                            <ul news-article-actions="item" no-plugins="noPlugins"><ul>
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
                    <span class="source"><?php p($l->t('from')) ?>
                        <a ng-href="#/items/feeds/{{ ::item.feedId }}/">
                            {{ ::Content.getFeed(item.feedId).title }}
                            <img ng-if="Content.getFeed(item.feedId).faviconLink && !Content.isCompactView()" src="{{ ::Content.getFeed(item.feedId).faviconLink }}" alt="favicon">
                        </a>
                    </span>
                </div>

                <div class="enclosure" ng-if="item.enclosureLink">
                    <video controls preload="none" ng-if="Content.getMediaType(item.enclosureMime) =='video'" news-play-one ng-src="{{ item.enclosureLink|trustUrl }}" type="{{ item.enclosureMime }}">
                    </video>
                    <button ng-if="Content.getMediaType(item.enclosureMime) == 'audio'" ng-click="App.play(item)"><?php p($l->t('Play audio')) ?></button>
                    <a ng-show="Content.getMediaType(item.enclosureMime) =='video'" class="button" ng-href="{{ item.enclosureLink|trustUrl }}" target="_blank" rel="noreferrer">
                        <?php p($l->t('Download video')) ?>
                    </a>
                    <a ng-show="Content.getMediaType(item.enclosureMime) =='audio'" class="button" ng-href="{{ item.enclosureLink|trustUrl }}" target="_blank" rel="noreferrer">
                        <?php p($l->t('Download audio')) ?>
                    </a>
                </div>


                <div class="body" news-bind-html-unsafe="item.body" ng-attr-dir="{{item.rtl && 'rtl'}}"></div>

            </div>
        </li>
    </ul>
</div>
