<template>
	<Content app-name="news">
		<Sidebar />
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import Sidebar from './components/Sidebar.vue'

export default {
	components: {
		Content,
		Sidebar,
	},
}
</script>
