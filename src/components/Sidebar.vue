<template>
	<AppNavigation>
		<AddFeed v-if="showAddFeed" @close="closeShowAddFeed()" />
		<AppNavigationNew
			:text="t('news', 'Subscribe')"
			button-id="new-feed-button"
			button-class="icon-add"
			@click="showShowAddFeed()" />

		<ul id="locations" class="with-icon">
			<AppNavigationNewItem
				:title="t('news', 'New folder')"
				icon="icon-add-folder"
				@new-item="newFolder" />

			<AppNavigationItem
				:title="t('news', 'Unread articles')"
				icon="icon-rss">
				<template #actions>
					<ActionButton icon="icon-checkmark" @click="alert('Edit')">
						t('news','Mark read')
					</ActionButton>
				</template>
				<template #counter>
					<CounterBubble>5</CounterBubble>
				</template>
			</AppNavigationItem>
			<AppNavigationItem
				:title="t('news', 'All articles')"
				icon="icon-rss">
				<template #actions>
					<ActionButton icon="icon-checkmark" @click="alert('Edit')">
						t('news','Mark read')
					</ActionButton>
				</template>
			</AppNavigationItem>
			<AppNavigationItem
				:title="t('news', 'Starred')"
				icon="icon-starred">
				<template #counter>
					<CounterBubble>35</CounterBubble>
				</template>
			</AppNavigationItem>

			<AppNavigationItem
				v-for="folder in folders"
				:key="folder.name"
				:title="folder.name"
				icon="icon-folder"
				:allow-collapse="true">
				<template #default>
					<AppNavigationItem
						v-for="feed in folder.feeds"
						:key="feed.name"
						:title="feed.title">
						<template #icon>
							<img
								v-if="feed.faviconLink"
								:src="feed.faviconLink"
								alt="feedIcon">
							<div v-if="!feed.faviconLink" class="icon-rss" />
						</template>
						<template #actions>
							<ActionButton
								icon="icon-checkmark"
								@click="alert('Mark read')">
								{{ t("news", "Mark read") }}
							</ActionButton>
							<ActionButton
								icon="icon-pinned"
								@click="alert('Rename')">
								{{ t("news", "Unpin from top") }}
							</ActionButton>
							<ActionButton
								icon="icon-caret-dark"
								@click="deleteFolder(folder)">
								{{ t("news", "Newest first") }}
							</ActionButton>
							<ActionButton
								icon="icon-caret-dark"
								@click="deleteFolder(folder)">
								{{ t("news", "Oldest first") }}
							</ActionButton>
							<ActionButton
								icon="icon-caret-dark"
								@click="deleteFolder(folder)">
								{{ t("news", "Default order") }}
							</ActionButton>
							<ActionButton
								icon="icon-full-text-disabled"
								@click="deleteFolder(folder)">
								{{ t("news", "Enable full text") }}
							</ActionButton>
							<ActionButton
								icon="icon-full-text-enabled"
								@click="deleteFolder(folder)">
								{{ t("news", "Disable full text") }}
							</ActionButton>
							<ActionButton
								icon="icon-updatemode-default"
								@click="deleteFolder(folder)">
								{{ t("news", "Unread updated") }}
							</ActionButton>
							<ActionButton
								icon="icon-updatemode-unread"
								@click="deleteFolder(folder)">
								{{ t("news", "Ignore updated") }}
							</ActionButton>
							<ActionButton
								icon="icon-icon-rss"
								@click="deleteFolder(folder)">
								{{ t("news", "Open feed URL") }}
							</ActionButton>
							<ActionButton
								icon="icon-icon-rename"
								@click="deleteFolder(folder)">
								{{ t("news", "Rename") }}
							</ActionButton>
							<ActionButton
								icon="icon-delete"
								@click="deleteFolder(folder)">
								{{ t("news", "Delete") }}
							</ActionButton>
						</template>
					</AppNavigationItem>
				</template>
				<template v-if="folder.feedCount > 0" #counter>
					<CounterBubble>{{ folder.feedCount }}</CounterBubble>
				</template>
				<template #actions>
					<ActionButton
						icon="icon-checkmark"
						@click="alert('Mark read')">
						{{ t("news", "Mark read") }}
					</ActionButton>
					<ActionButton icon="icon-rename" @click="alert('Rename')">
						{{ t("news", "Rename") }}
					</ActionButton>
					<ActionButton
						icon="icon-delete"
						@click="deleteFolder(folder)">
						{{ t("news", "Delete") }}
					</ActionButton>
				</template>
			</AppNavigationItem>

			<AppNavigationItem
				:title="t('news', 'Explore')"
				icon="icon-link"
				:to="{ name: 'explore' }">
				<template #counter>
					<CounterBubble>35</CounterBubble>
				</template>
			</AppNavigationItem>
		</ul>
	</AppNavigation>
</template>

<script>
/* eslint-disable vue/require-prop-type-constructor */
/* eslint-disable vue/require-default-prop */

import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNewItem from '@nextcloud/vue/dist/Components/AppNavigationNewItem'
// import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import CounterBubble from '@nextcloud/vue/dist/Components/CounterBubble'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AddFeed from './AddFeed'

export default {
	components: {
		AppNavigation,
		AppNavigationNew,
		AppNavigationItem,
		AppNavigationNewItem,
		// AppNavigationCounter,
		CounterBubble,
		ActionButton,
		AddFeed,
	},
	props: {
		showAddFeed: false,
	},
	computed: {
		folders() {
			return this.$store.state.folders
		},
	},
	created() {},
	methods: {
		newFolder(value) {
			const folderName = value.trim()
			const folder = { name: folderName }
			this.$store.dispatch('addFolder', { folder })
		},
		deleteFolder(folder) {
			this.$store.dispatch('deleteFolder', { folder })
			window.location.reload(true)
		},
		showShowAddFeed() {
			this.showAddFeed = true
		},
		closeShowAddFeed() {
			this.showAddFeed = false
		},
	},
}
</script>
