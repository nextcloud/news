<template>
	<NcContent app-name="news">
		<Sidebar />
		<NcAppContent>
			<RouterView />
		</NcAppContent>
	</NcContent>
</template>

<script lang="ts">

import Vue from 'vue'
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import Sidebar from './components/Sidebar.vue'
import { ACTIONS } from './store'

export default Vue.extend({
	components: {
		NcContent,
		Sidebar,
		NcAppContent,
	},
	async created() {
		await this.$store.dispatch(ACTIONS.FETCH_FOLDERS)
		await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
	},
})
</script>

<style>
	.material-design-icon {
		color: var(--color-text-lighter)
	}
</style>
