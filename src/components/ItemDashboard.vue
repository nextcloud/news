<template>
	<NcDashboardWidget :items="items">
		<template #default="{ item }">
			<NcDashboardWidgetItem
				:main-text="item.mainText"
				:sub-text="item.subText"
				:target-url="item.targetURL">

			<NcDashboardWidgetItem>
		</template>
	</NcDashboardWidget>
</template>

<script>
import NcDashboardWidget from '@nextcloud/vue/dist/Components/NcDashboardWidget.js'
import NcDashboardWidgetItem from '@nextcloud/vue/dist/Components/NcDashboardWidgetItem'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

const newsItems = loadState('news', 'dashboard-widget-items')

console.log(newsItems)

export default {
	name: 'NewsItemWidget',
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
					subText: g.intro,
					targetURL: generateUrl(`/apps/news/#/items/feeds/${g.feedId}`)
				}
			})
		}
	}
}
</script>