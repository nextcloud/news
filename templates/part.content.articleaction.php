<ul class="article-action-plugins">
    <li ng-repeat="plugin in ::plugins" class="util"
        id="article-plugin-{{ plugin.id }}"
        ng-click="pluginClick(plugin.id, $event, article)"
        news-stop-propagation>
        <button title="{{ plugin.title }}"></button>
    </li>
</ul>