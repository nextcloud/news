<!--
  - Copyright (c) 2020. The Nextcloud Bookmarks contributors.
  -
  - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
  -->

<template>
	<div class="item-skeleton">
		<div class="item__labels" />
		<div class="item__actions" />
	</div>
</template>

<script>
import Vue from 'vue'

export default Vue.extend({
	name: 'ItemSkeleton',
	components: {
	},
	computed: {

	},
})
</script>

<style>
.item-skeleton {
	background: 'var(--color-placeholder-dark)';
	height: '45px';
}
</style>
