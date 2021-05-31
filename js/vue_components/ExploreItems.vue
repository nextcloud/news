<template>
  <div class="grid">
    <div v-for="entry in explore.feeds" class="explore-feed grid-item">
      <h2 vue-if="entry.favicon" class="explore-title" v-bind:style="{ backgroundImage: 'url(' + entry.favicon + ')' }">
        <a target="_blank" rel="noreferrer" v-bind:href="entry.url">{{ entry.title }}</a>
      </h2>
    </div>
  </div>
</template>

<script>
export default {
  props: ['explore'],
}
</script>
