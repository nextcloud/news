<template>
	<NcContent app-name="news">
		<Sidebar />
		<NcAppContent>
			<router-view />
		</NcAppContent>
	</NcContent>
</template>

<script lang="ts">

import Vue from 'vue'
import NcContent from '@nextcloud/vue/dist/Components/NcContent'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import Sidebar from './components/Sidebar.vue'

export default Vue.extend({
	components: {
		NcContent,
		Sidebar,
		NcAppContent,
	},
	created() {
		this.$store.dispatch('loadFolder')
	},
})
</script>
