<?php
use OCA\News\Plugin\Client\Plugin;

script('news', 'nextcloud-news-main');

// load plugin scripts and styles
foreach (Plugin::getStyles() as $appName => $fileName) {
  style($appName, $fileName);
}
foreach (Plugin::getScripts() as $appName => $fileName) {
  script($appName, $fileName);
}

print_unescaped($this->inc('part.content.warnings'))

?>


<div id="q-app"></div>
