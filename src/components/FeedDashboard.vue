<template>
	<NcDashboardWidget :items="items">
		<template #default="{ item }">
			<NcDashboardWidgetItem
				:main-text="item.mainText"
				:sub-text="item.subText"
				:target-url="item.targetURL"
				:avatar-is-no-user="true"
				:avatar-url="item.favicon">

			<NcDashboardWidgetItem>
		</template>
	</NcDashboardWidget>
</template>

<script>
import NcDashboardWidget from '@nextcloud/vue/dist/Components/NcDashboardWidget.js'
import NcDashboardWidgetItem from '@nextcloud/vue/dist/Components/NcDashboardWidgetItem'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

const newsItems = loadState('news', 'dashboard-widget-feeds')

console.log(newsItems)

export default {
	name: 'NewsFeedWidget',
	components: {
		NcDashboardWidget,
		NcDashboardWidgetItem,
	},
	props: [],
	data() {
		return {
			newsItems: newsItems
		}
	},

	computed: {
		items() {
			return this.newsItems.map((g) => {
				return {
					id: g.id,
					mainText: g.title,
					subText: `Unread: ${g.unreadCount}`,
					targetURL: generateUrl(`/apps/news/#/items/feeds/${g.id}`),
					favicon: g.faviconLink,
				}
			})
		}
	}
}
</script>