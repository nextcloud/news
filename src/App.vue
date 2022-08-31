<template>
	<NcContent app-name="news">
		<Sidebar />
		<NcAppContent>
			<router-view />
		</NcAppContent>
	</NcContent>
</template>

<script>
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import Sidebar from './components/Sidebar.vue'

export default {
	components: {
		NcContent,
		NcAppContent,
		Sidebar,
	},
	created() {
		this.$store.dispatch('loadFolder')
	},
}
</script>
