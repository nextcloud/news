<template>
	<Content app-name="news">
		<Sidebar />
		<AppContent>
			<router-view />
		</AppContent>
	</Content>
</template>

<script lang="ts">

import Vue from 'vue'
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Sidebar from './components/Sidebar.vue'

export default Vue.extend({
	components: {
		Content,
		Sidebar,
		AppContent,
	},
	created() {
		this.$store.dispatch('loadFolder')
	},
})
</script>
