const widgetTemplate = `
<div>
    <style>
	    .icon-news {
	        background-image: url("/apps/news/img/app-dark.svg");
	    }

	    .icon-news-white {
	        background-image: url("/apps/news/img/app.svg");
	    }

        .widget-news-article {
            margin-bottom: 0.3rem;
            padding: 0.5rem;
        }

        .article-info {
            font-size: x-small;
        }

        .date {
            display: inline;
        }

        .feed-title {
            display: inline;
            float: right;
            max-width: 11rem;
	    	overflow: hidden;
	    	text-overflow: ellipsis;
	    	white-space: nowrap;
        }

        .title {
	    	overflow: hidden;
	    	text-overflow: ellipsis;
	    	white-space: nowrap;
	    	display: block;
	    }

        .widget-news-article:hover {
            background-color: var(--color-background-hover);
            border-radius: var(--border-radius-large);
        }
    </style>
    <ul>
        <li ng-repeat="item in Widget.getItems() |
                orderBy:'id':Widget.sortIds track by item.id"
            data-id="{{ ::item.id }}">
            <a ng-href="{{ ::item.url }}"
                target="_blank"
                rel="noreferrer">
                <div class="widget-news-article">
                    <div class="title"
                       title="{{ ::item.title }}">
                        {{ ::item.title }}
                    </div>
                    <div class="article-info">
                        <time class="date"
                                  title="{{ item.pubDate*1000 |
                                    date:'yyyy-MM-dd HH:mm:ss' }}"
                                  datetime="{{ item.pubDate*1000 |
                                    date:'yyyy-MM-ddTHH:mm:ssZ' }}">
                                {{ item.pubDate*1000 | relativeTimestamp }}
                        </time>
                        <div class="feed-title">from {{ Widget.getFeed(item.feedId).title }}</div>
                    </div>
                </div>
            </a>
        </li>
    </ul>
</div>
`;

app.component('widgetComponent', {
    template: widgetTemplate,
    controller: 'WidgetController',
    controllerAs: 'Widget',
    bindings: {}
});

