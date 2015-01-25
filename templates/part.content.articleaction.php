<ul class="article-action-plugins" news-stop-propagation>
    <li ng-repeat="plugin in ::plugins"
        class="util article-plugin-{{ plugin.id }}">
        <button title="{{ plugin.title }}"></button>
    </li>
</ul>