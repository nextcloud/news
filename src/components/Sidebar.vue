<template>
	<NcAppNavigation>
		<AddFeed v-if="showAddFeed" @close="closeShowAddFeed()" />
		<NcAppNavigationNew :text="t('news', 'Subscribe')"
			button-id="new-feed-button"
			button-class="icon-add"
			@click="showShowAddFeed()" />
		<template #list>
			<NcAppNavigationNewItem :title="t('news', 'New folder')"
				icon="icon-add-folder"
				@new-item="newFolder" />

			<NcAppNavigationItem :title="t('news', 'Unread articles')" icon="icon-rss">
				<template #actions>
					<NcActionButton icon="icon-checkmark" @click="alert('TODO: Mark Read')">
						t('news','Mark read')
					</NcActionButton>
				</template>
				<template #counter>
					<NcCounterBubble>5</NcCounterBubble>
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :title="t('news', 'All articles')" icon="icon-rss">
				<template #actions>
					<ActionButton icon="icon-checkmark" @click="alert('TODO: Edit')">
						t('news','Mark read')
					</ActionButton>
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :title="t('news', 'Starred')" icon="icon-starred" :to="{ name: ROUTES.STARRED }">
				<template #counter>
					<NcCounterBubble>{{ items.starredCount }}</NcCounterBubble>
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem v-for="topLevelItem in topLevelNav"
				:key="topLevelItem.name || topLevelItem.title"
				:title="topLevelItem.name || topLevelItem.title"
				:icon="topLevelItem.name !== undefined ? 'icon-folder': ''"
				:allow-collapse="true">
				<template #default>
					<NcAppNavigationItem v-for="feed in topLevelItem.feeds"
						:key="feed.name"
						:title="feed.title">
						<template #icon>
							<img v-if="feed.faviconLink"
								:src="feed.faviconLink"
								alt="feedIcon">
							<div v-if="!feed.faviconLink" class="icon-rss" />
						</template>
						<template #actions>
							<NcActionButton icon="icon-checkmark"
								@click="alert('TODO: Mark read')">
								{{ t("news", "Mark read") }}
							</NcActionButton>
							<NcActionButton icon="icon-pinned"
								@click="alert('TODO: Unpin from top')">
								{{ t("news", "Unpin from top") }}
							</NcActionButton>
							<NcActionButton icon="icon-caret-dark"
								@click="alert('TODO: Newest First')">
								{{ t("news", "Newest first") }}
							</NcActionButton>
							<NcActionButton icon="icon-caret-dark"
								@click="alert('TODO: Oldest first')">
								{{ t("news", "Oldest first") }}
							</NcActionButton>
							<NcActionButton icon="icon-caret-dark"
								@click="alert('TODO: Default Order')">
								{{ t("news", "Default order") }}
							</NcActionButton>
							<NcActionButton icon="icon-full-text-disabled"
								@click="alert('TODO: Enable Full Text')">
								{{ t("news", "Enable full text") }}
							</NcActionButton>
							<NcActionButton icon="icon-full-text-enabled"
								@click="alert('TODO: DIsable Full Text')">
								{{ t("news", "Disable full text") }}
							</NcActionButton>
							<NcActionButton icon="icon-updatemode-default"
								@click="alert('TODO: Unread Updated')">
								{{ t("news", "Unread updated") }}
							</NcActionButton>
							<NcActionButton icon="icon-updatemode-unread"
								@click="alert('TOODO: Ignore UPdated')">
								{{ t("news", "Ignore updated") }}
							</NcActionButton>
							<NcActionButton icon="icon-icon-rss"
								@click="alert('TODO: Open Feed URL')">
								{{ t("news", "Open feed URL") }}
							</NcActionButton>
							<NcActionButton icon="icon-icon-rename"
								@click="alert('TODO: Rename')">
								{{ t("news", "Rename") }}
							</NcActionButton>
							<NcActionButton icon="icon-delete"
								@click="alert('TODO: Delete Feed')">
								{{ t("news", "Delete") }}
							</NcActionButton>
						</template>
					</NcAppNavigationItem>
				</template>
				<template v-if="topLevelItem.feedCount > 0" #counter>
					<NcCounterBubble>{{ topLevelItem.feedCount }}</NcCounterBubble>
				</template>
				<template #actions>
					<NcActionButton icon="icon-checkmark" @click="alert('TODO: Mark read')">
						{{ t("news", "Mark read") }}
					</NcActionButton>
					<NcActionButton icon="icon-rename" @click="alert('TODO: Rename')">
						{{ t("news", "Rename") }}
					</NcActionButton>
					<NcActionButton icon="icon-delete" @click="deleteFolder(topLevelItem)">
						{{ t("news", "Delete") }}
					</NcActionButton>
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem :title="t('news', 'Explore')"
				icon="icon-link"
				:to="{ name: ROUTES.EXPLORE }">
				<template #counter>
					<NcCounterBubble>35</NcCounterBubble>
				</template>
			</NcAppNavigationItem>
		</template>
	</NcAppNavigation>
</template>

<script lang="ts">

import { mapState } from 'vuex'
import Vue from 'vue'

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import NcAppNavigationNewItem from '@nextcloud/vue/dist/Components/NcAppNavigationNewItem.js'
// import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

import { ROUTES } from '../routes'
import { ACTIONS, AppState } from '../store'

import AddFeed from './AddFeed.vue'

import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'

const SideBarState = {
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	topLevelNav(localState: any, state: AppState): (Feed | Folder)[] {
		let navItems: (Feed | Folder)[] = state.feeds.filter((feed: Feed) => {
			return feed.folderId === undefined || feed.folderId === null
		})
		navItems = navItems.concat(state.folders)

		return navItems
	},
}

export default Vue.extend({
	components: {
		NcAppNavigation,
		NcAppNavigationNew,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		// AppNavigationCounter,
		NcCounterBubble,
		NcActionButton,
		AddFeed,
	},
	data: () => {
		return {
			showAddFeed: false,
			ROUTES,
		}
	},
	computed: {
		...mapState(['feeds', 'folders', 'items']),
		...mapState(SideBarState),
	},
	created() {
		// TODO: init?
	},
	methods: {
		newFolder(value: string) {
			const folderName = value.trim()
			const folder = { name: folderName }
			this.$store.dispatch(ACTIONS.ADD_FOLDERS, { folder })
		},
		deleteFolder(folder: Folder) {
			this.$store.dispatch(ACTIONS.DELETE_FOLDER, { folder })
		},
		showShowAddFeed() {
			this.showAddFeed = true
		},
		closeShowAddFeed() {
			this.showAddFeed = false
		},
		alert(msg: string) {
			window.alert(msg)
		},
	},
})

</script>
