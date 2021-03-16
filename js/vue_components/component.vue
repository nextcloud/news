<template>
  <p>asdf newest first is {{this.oldestFirst.toString()}}</p>
</template>

<script>
module.exports = {
  props: {
    oldestFirst: Boolean
  }
};
</script>

