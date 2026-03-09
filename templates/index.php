<?php
use OCA\News\Plugin\Client\Plugin;

script('news', 'news-main');

// load plugin scripts and styles
foreach (Plugin::getStyles() as $appName => $fileName) {
  style($appName, $fileName);
}
foreach (Plugin::getScripts() as $appName => $fileName) {
  script($appName, $fileName);
}
?>

<div id="q-app"></div>
