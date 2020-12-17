<template>
	<AppNavigation>
		<AppNavigationNew
			:text="t('news','Subscribe')"
			button-id="new-feed-button"
			button-class="icon-add" />
		<ul id="locations" class="with-icon">
			<AppNavigationItem :title="t('news','Unread articles')" icon="icon-rss">
				<AppNavigationCounter slot="counter" :highlighted="true">
					5
				</AppNavigationCounter>
			</AppNavigationItem>
			<AppNavigationNewItem :title="t('news','New folder')"
				icon="icon-add"
				@new-item="newFolder" />
		</ul>
	</AppNavigation>
</template>

<script>
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNewItem from '@nextcloud/vue/dist/Components/AppNavigationNewItem'
import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'

export default {
	components: {
		AppNavigation,
		AppNavigationNew,
		AppNavigationItem,
		AppNavigationNewItem,
		AppNavigationCounter,
	},
	methods: {
		newFolder(value) {
			alert(value)
		},
	},
}
</script>
