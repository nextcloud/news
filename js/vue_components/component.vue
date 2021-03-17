<template>
  <div>
    <p>asdf newest first is {{this.oldestFirst.toString()}}</p>
  </div>
</template>

<script>
// import { ActionButton } from '@nextcloud/vue/dist/Components';

module.exports = {
  props: {
    oldestFirst: Boolean
  }
};
</script>

