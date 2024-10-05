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
			<NcAppNavigationItem v-if="showAll"
				:name="t('news', 'All articles')"
				icon="icon-rss"
				:to="{ name: ROUTES.ALL }">
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
		<template #footer>
			<NcAppNavigationSettings :name="t('news', 'Settings')">
				<NcButton @click="showHelp = true">
					{{ t('news', 'Keyboard shortcuts') }}
				</NcButton>
				<HelpModal v-if="showHelp" @close="showHelp=false" />
				<div>
					<div>
						<input id="toggle-preventreadonscroll"
							v-model="preventReadOnScroll"
							type="checkbox"
							class="checkbox">
						<label for="toggle-preventreadonscroll">
							{{ t('news', 'Disable mark read through scrolling') }}
						</label>
					</div>
					<!---
					<div>
						<input id="toggle-compact"
							v-model="compact"
							type="checkbox"
							class="checkbox">
						<label for="toggle-compact">
							{{ t('news', 'Compact view') }}
						</label>
					</div>
					<div>
						<input id="toggle-compactexpand"
							v-model="compactExpand"
							type="checkbox"
							class="checkbox">
						<label for="toggle-compactexpand">
							{{ t('news', 'Expand articles on key navigation') }}
						</label>
					</div>
					--->
					<div>
						<input id="toggle-showall"
							v-model="showAll"
							type="checkbox"
							class="checkbox">
						<label for="toggle-showall">
							{{ t('news', 'Show all articles') }}
						</label>
					</div>
					<div>
						<input id="toggle-oldestfirst"
							v-model="oldestFirst"
							type="checkbox"
							class="checkbox">
						<label for="toggle-oldestfirst">
							{{ t('news', 'Reverse ordering (oldest on top)') }}
						</label>
					</div>
				</div>
			</NcAppNavigationSettings>
		</template>
	</NcAppNavigation>
</template>

<script lang="ts">

import { mapState } from 'vuex'
import Vue from 'vue'
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { subscribe } from '@nextcloud/event-bus'

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import NcAppNavigationNewItem from '@nextcloud/vue/dist/Components/NcAppNavigationNewItem.js'
import NcAppNavigationSettings from '@nextcloud/vue/dist/Components/NcAppNavigationSettings.js'
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'

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

import HelpModal from './modals/HelpModal.vue'
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
		NcAppNavigationSettings,
		NcCounterBubble,
		NcActionButton,
		NcButton,
		AddFeed,
		RssIcon,
		FolderIcon,
		EyeIcon,
		EarthIcon,
		FolderPlusIcon,
		PlusIcon,
		SidebarFeedLinkActions,
		HelpModal,
	},
	data: () => {
		return {
			showAddFeed: false,
			ROUTES,
			showHelp: false,
		}
	},
	computed: {
		...mapState(['feeds', 'folders', 'items']),
		...mapState(SideBarState),
		compact: {
			get() {
				return this.$store.getters.compact
			},
			set(newValue) {
				this.saveSetting('compact', newValue)
			},
		},
		compactExpand: {
			get() {
				return this.$store.getters.compactExpand
			},
			set(newValue) {
				this.saveSetting('compactExpand', newValue)
			},
		},
		oldestFirst: {
			get() {
				return this.$store.getters.oldestFirst

			},
			set(newValue) {
				this.saveSetting('oldestFirst', newValue)
			},
		},
		preventReadOnScroll: {
			get() {
				return this.$store.getters.preventReadOnScroll

			},
			set(newValue) {
				this.saveSetting('preventReadOnScroll', newValue)
			},
		},
		showAll: {
			get() {
				return this.$store.getters.showAll

			},
			set(newValue) {
				this.saveSetting('showAll', newValue)
			},
		},
	},
	created() {
		if (this.$route.query.subscribe_to) {
			this.showAddFeed = true
		}
	},
	mounted() {
		subscribe('news:global:toggle-help-dialog', () => {
			this.showHelp = !this.showHelp
		})
	},
	methods: {
		async saveSetting(key, value) {
			this.$store.commit(key, { value })
			const url = generateOcsUrl(
				'/apps/provisioning_api/api/v1/config/users/{appId}/{key}',
				{
					appId: 'news',
					key,
				},
			)
			value = value ? '1' : '0'
			try {
				const { data } = await axios.post(url, {
					configValue: value,
				})
				this.handleResponse({
					status: data.ocs?.meta?.status,
				})
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Unable to update news config'),
					error: e,
				})
			}
		},
		handleResponse({ status, errorMessage, error }) {
			if (status !== 'ok') {
				showError(errorMessage)
				console.error(errorMessage, error)
			} else {
				showSuccess(t('news', 'Successfully updated news configuration'))
			}
		},
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
