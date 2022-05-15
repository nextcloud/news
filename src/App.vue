<template>
	<Content app-name="news">
		<Sidebar />
        <AppContent>
            <router-view></router-view>
        </AppContent>
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Sidebar from './components/Sidebar.vue'
import {FeedService} from "./services/feedService";

export default {
	components: {
		Content,
		Sidebar,
        AppContent,
	},
    created() {
        const feedService = new FeedService();
        feedService.init();
    }

}
</script>
