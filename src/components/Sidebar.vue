<template>
	<NcAppNavigation>
		<AddFeed v-if="showAddFeed" @close="closeShowAddFeed()" />
		<NcAppNavigationNew :text="t('news', 'Subscribe')"
			button-id="new-feed-button"
			button-class="icon-add"
			:icon="''"
			@click="showShowAddFeed()">
			<template #icon>
				<PlusIcon />
			</template>
		</NcAppNavigationNew>
		<template #list>
			<NcAppNavigationNewItem :title="t('news', 'New folder')"
				:icon="''"
				@new-item="newFolder">
				<template #icon>
					<FolderPlusIcon />
				</template>
			</NcAppNavigationNewItem>

			<NcAppNavigationItem :name="t('news', 'Unread articles')" icon="icon-rss" :to="{ name: ROUTES.UNREAD }">
				<template #actions>
					<NcActionButton icon="icon-checkmark" @click="markAllRead()">
						{{ t('news','Mark read') }}
					</NcActionButton>
				</template>
				<template #icon>
					<EyeIcon />
				</template>
				<template #counter>
					<NcCounterBubble v-if="items.unreadCount > 0">
						{{ items.unreadCount }}
					</NcCounterBubble>
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :name="t('news', 'All articles')" icon="icon-rss" :to="{ name: ROUTES.ALL }">
				<template #icon>
					<RssIcon />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :name="t('news', 'Starred')" icon="icon-starred" :to="{ name: ROUTES.STARRED }">
				<template #counter>
					<NcCounterBubble>{{ items.starredCount }}</NcCounterBubble>
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem v-for="topLevelItem in topLevelNav"
				:key="topLevelItem.name || topLevelItem.title"
				:name="topLevelItem.name || topLevelItem.title"
				:icon="''"
				:to="isFolder(topLevelItem) ? { name: ROUTES.FOLDER, params: { folderId: topLevelItem.id.toString() }} : { name: ROUTES.FEED, params: { feedId: topLevelItem.id.toString() } }"
				:allow-collapse="true"
				:force-menu="true">
				<template #default>
					<NcAppNavigationItem v-for="feed in topLevelItem.feeds"
						:key="feed.name"
						:name="feed.title"
						:icon="''"
						:to="{ name: ROUTES.FEED, params: { feedId: feed.id.toString() } }">
						<template #icon>
							<RssIcon v-if="!feed.faviconLink" />
							<span v-if="feed.faviconLink" style="width: 16px; height: 16px; background-size: contain;" :style="{ 'backgroundImage': 'url(' + feed.faviconLink + ')' }" />
						</template>
						<template #counter>
							<NcCounterBubble v-if="feed.unreadCount > 0">
								{{ feed.unreadCount }}
							</NcCounterBubble>
						</template>

						<template #actions>
							<SidebarFeedLinkActions :feed-id="feed.id" />
						</template>
					</NcAppNavigationItem>
				</template>
				<template #icon>
					<FolderIcon v-if="topLevelItem.feedCount !== undefined" style="width:22px" />
					<RssIcon v-if="topLevelItem.feedCount === undefined && !topLevelItem.faviconLink" />
					<span v-if="topLevelItem.feedCount === undefined && topLevelItem.faviconLink" style="height: 16px; width: 16px; background-size: contain;" :style="{ 'backgroundImage': 'url(' + topLevelItem.faviconLink + ')' }" />
				</template>
				<template #counter>
					<NcCounterBubble v-if="topLevelItem.feedCount > 0">
						{{ topLevelItem.feedCount }}
					</NcCounterBubble>
					<NcCounterBubble v-if="topLevelItem.unreadCount > 0">
						{{ topLevelItem.unreadCount }}
					</NcCounterBubble>
				</template>
				<template #actions>
					<SidebarFeedLinkActions v-if="topLevelItem.name === undefined && !topLevelItem.url.includes('news/sharedwithme')" :feed-id="topLevelItem.id" />
					<NcActionButton v-if="topLevelItem.name !== undefined" icon="icon-checkmark" @click="markFolderRead(topLevelItem)">
						{{ t("news", "Mark read") }}
					</NcActionButton>
					<NcActionButton v-if="topLevelItem.name !== undefined" icon="icon-rename" @click="renameFolder(topLevelItem)">
						{{ t("news", "Rename") }}
					</NcActionButton>
					<NcActionButton v-if="topLevelItem.name !== undefined" icon="icon-delete" @click="deleteFolder(topLevelItem)">
						{{ t("news", "Delete") }}
					</NcActionButton>
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem :name="t('news', 'Explore')"
				icon="true"
				:to="{ name: ROUTES.EXPLORE }">
				<template #counter>
					<NcCounterBubble>35</NcCounterBubble>
				</template>
				<template #icon>
					<EarthIcon />
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
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

import RssIcon from 'vue-material-design-icons/Rss.vue'
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import FolderPlusIcon from 'vue-material-design-icons/FolderPlus.vue'
import PlusIcon from 'vue-material-design-icons/Plus.vue'

import { ROUTES } from '../routes'
import { ACTIONS, AppState } from '../store'

import AddFeed from './AddFeed.vue'
import SidebarFeedLinkActions from './SidebarFeedLinkActions.vue'

import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'

const SideBarState = {
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	topLevelNav(localState: any, state: AppState): (Feed | Folder)[] {
		let navItems: (Feed | Folder)[] = state.feeds.filter((feed: Feed) => {
			return feed.folderId === undefined || feed.folderId === null
		})
		navItems = navItems.concat(state.folders)

		// bring pinned items to the top
		return navItems.sort((item, item2) => {
			if ((item as Feed).pinned && !(item2 as Feed).pinned) {
				return -1
			} else if ((item2 as Feed).pinned && !(item as Feed).pinned) {
				return 1
			}
			return 0
		})
	},
}

export default Vue.extend({
	components: {
		NcAppNavigation,
		NcAppNavigationNew,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		NcCounterBubble,
		NcActionButton,
		AddFeed,
		RssIcon,
		FolderIcon,
		EyeIcon,
		EarthIcon,
		FolderPlusIcon,
		PlusIcon,
		SidebarFeedLinkActions,
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
		if (this.$route.query.subscribe_to) {
			this.showAddFeed = true
		}
	},
	methods: {
		newFolder(value: string) {
			const folderName = value.trim()
			const folder = { name: folderName }
			this.$store.dispatch(ACTIONS.ADD_FOLDERS, { folder })
		},
		markAllRead() {
			const shouldMarkRead = window.confirm(t('news', 'Are you sure you want to mark all read?'))

			if (shouldMarkRead) {
				this.$store.getters.feeds.forEach((feed: Feed) => {
					this.$store.dispatch(ACTIONS.FEED_MARK_READ, { feed })
				})
			}
		},
		markFolderRead(folder: Folder) {
			const shouldMarkRead = window.confirm(t('news', 'Are you sure you want to mark all read?'))

			if (shouldMarkRead) {
				const feeds = this.$store.getters.feeds.filter((feed: Feed) => {
					return feed.folderId === folder.id
				})
				feeds.forEach((feed: Feed) => {
					this.$store.dispatch(ACTIONS.FEED_MARK_READ, { feed })
				})
			}
		},
		renameFolder(folder: Folder) {
			const name = window.prompt(t('news', 'Rename Folder'), folder.name)

			// null when user presses escape (do nothing)
			if (name !== null) {
				this.$store.dispatch(ACTIONS.FOLDER_SET_NAME, { folder, name })
			}
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
		isFolder(item: Feed | Folder) {
			return (item as Folder).name !== undefined
		},
	},
})

</script>
