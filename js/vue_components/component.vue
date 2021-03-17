<template>
  <div>
    <p>asdf newest first is {{this.oldestFirst.toString()}}</p>
    <Actions>
      <ActionButton icon="icon-delete">Delete something</ActionButton>
      <ActionButton icon="icon-add">Add Something</ActionButton>
    </Actions>
  </div>
</template>

<script>
import Actions from '@nextcloud/vue/dist/Components/Actions';
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton';

export default {
  props: {
    oldestFirst: Boolean
  },
  components: {
    Actions,
    ActionButton,
  }
};
</script>

