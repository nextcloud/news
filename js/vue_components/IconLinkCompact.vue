<template>
  <li class="util only-in-compact">
    <a class="external icon-link"
      v-on:click="ctrl.markRead(item.id)"
      target="_blank"
      rel="noreferrer"
      v-bind:href="item.url"
      v-bind:title="t('news', 'Open website')"
      news-stop-propagation>
    </a>
  </li>
</template>

<script>

export default {
  props: ['ctrl', 'item', 't'],
};
</script>


