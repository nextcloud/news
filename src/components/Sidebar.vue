<template>
	<NcAppNavigation>
		<AddFeed v-if="showAddFeed" @close="closeAddFeed()" />
		<MoveFeed v-if="showMoveFeed" :feed="feedToMove" @close="closeMoveFeed()" />
		<FeedInfoTable v-if="showFeedSettings" @close="closeFeedSettings()" />
		<AppSettingsDialog v-if="showSettings" @close="closeSettings()" />
		<NcAppNavigationNew
			:text="t('news', 'Subscribe')"
			button-id="new-feed-button"
			button-class="icon-add"
			@click="addFeed()">
			<template #icon>
				<PlusIcon />
			</template>
		</NcAppNavigationNew>
		<div class="new-folder-container">
			<NcAppNavigationNewItem
				:name="t('news', 'New folder')"
				@new-item="newFolder">
				<template #icon>
					<FolderPlusIcon />
				</template>
			</NcAppNavigationNewItem>
		</div>
		<template #list>
			<NcAppNavigationItem :name="t('news', 'Unread articles')" :to="{ name: ROUTES.UNREAD }">
				<template #actions>
					<NcActionButton ref="triggerButton" icon="icon-checkmark" @click="markAllRead()">
						{{ t('news', 'Mark read') }}
					</NcActionButton>
				</template>
				<template #icon>
					<EyeIcon />
				</template>
				<template #counter>
					<NcCounterBubble v-show="items.unreadCount > 0" :count="items.unreadCount" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('news', 'All articles')"
				:to="{ name: ROUTES.ALL }">
				<template #icon>
					<RssIcon />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('news', 'Recently viewed')"
				:to="{ name: ROUTES.RECENT }">
				<template #icon>
					<HistoryIcon />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:name="t('news', 'Starred')"
				icon="icon-starred"
				:to="{ name: ROUTES.STARRED }"
				:allow-collapse="true"
				:force-menu="true"
				:open="isStarredOpen"
				@update:open="toggleStarredOpenState">
				<NcAppNavigationItem
					v-for="group in GroupedStars"
					:key="group.id"
					:ref="'starred-' + group.id"
					:name="group.title"
					:to="{ name: ROUTES.STARRED, params: { starredFeedId: group.id } }">
					<template #icon>
						<RssIcon v-if="!group.faviconLink" />
						<span v-else style="width: 16px; height: 16px; background-size: contain;" :style="{ backgroundImage: 'url(' + group.faviconLink + ')' }" />
					</template>
					<template #counter>
						<NcCounterBubble :count="group.starredCount" />
					</template>
				</NcAppNavigationItem>
				<template #counter>
					<NcCounterBubble :count="items.starredCount" />
				</template>
			</NcAppNavigationItem>

			<template v-if="loading">
				<NcAppNavigationItem name="Loading Feeds" :loading="true" />
			</template>
			<template v-else>
				<NcAppNavigationItem
					v-for="topLevelItem in topLevelNav"
					v-show="showItem(topLevelItem)"
					:key="topLevelItem.name || topLevelItem.title"
					:ref="isFolder(topLevelItem) ? 'folder-' + topLevelItem.id : 'feed-' + topLevelItem.id"
					:name="topLevelItem.name || topLevelItem.title"
					:open="topLevelItem.opened"
					:to="isFolder(topLevelItem) ? { name: ROUTES.FOLDER, params: { folderId: topLevelItem.id.toString() } } : { name: ROUTES.FEED, params: { feedId: topLevelItem.id.toString() } }"
					:allow-collapse="isFolder(topLevelItem)"
					:force-menu="true"
					@update:open="toggleFolderState(topLevelItem)">
					<NcAppNavigationItem
						v-for="feed in sortedFolderFeeds(topLevelItem)"
						v-show="showItem(feed)"
						:key="feed.name"
						:ref="'feed-' + feed.id"
						:name="feed.title"
						:to="{ name: ROUTES.FEED, params: { feedId: feed.id.toString() } }">
						<template #icon>
							<span style="width: 16px; height: 16px; background-size: contain;" :style="{ backgroundImage: 'url(' + feedIcon(feed) + ')' }" />
						</template>
						<template #counter>
							<NcCounterBubble
								v-show="feed.updateErrorCount > 8"
								:title="feed.lastUpdateError"
								type="highlighted"
								style="background-color: red"
								:count="feed.updateErrorCount" />
							<NcCounterBubble v-show="feed.unreadCount > 0" :count="feed.unreadCount" />
						</template>

						<template #actions>
							<SidebarFeedLinkActions :feed-id="feed.id" @open-move-dialog="openMoveFeed(feed)" />
						</template>
					</NcAppNavigationItem>
					<template #icon>
						<FolderAlertIcon v-if="isFolder(topLevelItem) && topLevelItem.updateErrorCount > 8" :title="t('news', 'Has feeds with errors!')" style="width: 22px; color: red" />
						<FolderIcon v-if="isFolder(topLevelItem) && topLevelItem.updateErrorCount <= 8" style="width:22px" />
						<span v-if="!isFolder(topLevelItem)" style="height: 16px; width: 16px; background-size: contain;" :style="{ backgroundImage: 'url(' + feedIcon(topLevelItem) + ')' }" />
					</template>
					<template #counter>
						<NcCounterBubble
							v-if="!isFolder(topLevelItem) && topLevelItem.updateErrorCount > 8"
							:title="topLevelItem.lastUpdateError"
							type="highlighted"
							style="background-color: red"
							:count="topLevelItem.updateErrorCount" />
						<NcCounterBubble v-show="topLevelItem.feedCount > 0" :count="topLevelItem.feedCount ? topLevelItem.feedCount : 0" />
						<NcCounterBubble v-show="topLevelItem.unreadCount > 0" :count="topLevelItem.unreadCount ? topLevelItem.unreadCount : 0" />
					</template>
					<template #actions>
						<SidebarFeedLinkActions
							v-if="topLevelItem.name === undefined && !topLevelItem.url.includes('news/sharedwithme')"
							:feed-id="topLevelItem.id"
							@open-move-dialog="openMoveFeed(topLevelItem)" />
						<NcActionButton
							v-if="topLevelItem.name !== undefined"
							icon="icon-checkmark"
							:close-after-click="true"
							@click="markFolderRead(topLevelItem)">
							{{ t("news", "Mark read") }}
						</NcActionButton>
						<NcActionButton
							v-if="topLevelItem.name !== undefined"
							icon="icon-rename"
							:close-after-click="true"
							@click="renameFolder(topLevelItem)">
							{{ t("news", "Rename") }}
						</NcActionButton>
						<NcActionButton
							v-if="topLevelItem.name !== undefined"
							icon="icon-delete"
							:close-after-click="true"
							@click="deleteFolder(topLevelItem)">
							{{ t("news", "Delete") }}
						</NcActionButton>
					</template>
				</NcAppNavigationItem>
			</template>
		</template>
		<template #footer>
			<ul class="footer-container">
				<NcAppNavigationItem
					:name="t('news', 'Explore')"
					:to="{ name: ROUTES.EXPLORE }">
					<template #icon>
						<EarthIcon />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem
					:name="t('news', 'Feed settings')"
					@click.prevent.stop="openFeedSettings">
					<template #icon>
						<ListStatusIcon :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem
					:name="t('news', 'News settings')"
					@click.prevent.stop="openSettings">
					<template #icon>
						<CogIcon :size="20" />
					</template>
				</NcAppNavigationItem>
			</ul>
		</template>
	</NcAppNavigation>
</template>

<script lang="ts">

import type { Feed } from '../types/Feed.ts'
import type { Folder } from '../types/Folder.ts'

import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import { generateOcsUrl } from '@nextcloud/router'
import { useHotKey } from '@nextcloud/vue/composables/useHotKey'
import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcAppNavigation from '@nextcloud/vue/components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/components/NcAppNavigationNew'
import NcAppNavigationNewItem from '@nextcloud/vue/components/NcAppNavigationNewItem'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import CogIcon from 'vue-material-design-icons/CogOutline.vue'
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import FolderAlertIcon from 'vue-material-design-icons/FolderAlert.vue'
import FolderPlusIcon from 'vue-material-design-icons/FolderPlus.vue'
import HistoryIcon from 'vue-material-design-icons/History.vue'
import ListStatusIcon from 'vue-material-design-icons/ListStatus.vue'
import PlusIcon from 'vue-material-design-icons/Plus.vue'
import RssIcon from 'vue-material-design-icons/Rss.vue'
import AddFeed from './AddFeed.vue'
import AppSettingsDialog from './modals/AppSettingsDialog.vue'
import FeedInfoTable from './modals/FeedInfoTable.vue'
import MoveFeed from './MoveFeed.vue'
import SidebarFeedLinkActions from './SidebarFeedLinkActions.vue'
import { ROUTES } from '../routes/index.ts'
import { ACTIONS } from '../store/index.ts'
import { API_ROUTES } from '../types/ApiRoutes.ts'

export default defineComponent({
	name: 'SidebarNavigation',
	components: {
		NcAppNavigation,
		NcAppNavigationNew,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		NcCounterBubble,
		NcActionButton,
		AddFeed,
		MoveFeed,
		CogIcon,
		RssIcon,
		FolderIcon,
		EyeIcon,
		EarthIcon,
		FolderAlertIcon,
		FolderPlusIcon,
		HistoryIcon,
		ListStatusIcon,
		PlusIcon,
		SidebarFeedLinkActions,
		FeedInfoTable,
		AppSettingsDialog,
	},

	data: () => {
		return {
			ROUTES,
			showAddFeed: false,
			showFeedSettings: false,
			showMoveFeed: false,
			showSettings: false,
			feedToMove: undefined,
			polling: null,
		}
	},

	computed: {
		...mapState(['items']),
		topLevelNav(): (Feed | Folder)[] {
			const feeds: { pinned: Feed[], ungrouped: Feed[] } = this.$store.getters.feeds.reduce((result, feed: Feed) => {
				if (feed.folderId === undefined || feed.folderId === null) {
					if (feed.pinned) {
						result.pinned.push(feed)
					} else {
						result.ungrouped.push(feed)
					}
				}
				return result
			}, { pinned: [], ungrouped: [] })

			const folders = this.$store.getters.folders

			const navItems: (Feed | Folder)[] = [
				...feeds.pinned,
				...feeds.ungrouped,
				...folders,
			]

			return navItems
		},

		GroupedStars(): Array<Feed> {
			return this.$store.getters.feeds.filter((item: Feed) => item.starredCount !== 0)
		},

		active() {
			return {
				feedId: Number(this.$route.params?.feedId),
				folderId: Number(this.$route.params?.folderId),
			}
		},

		loading() {
			return this.$store.getters.loading
		},

		disableRefresh() {
			return this.$store.getters.disableRefresh
		},

		showAll() {
			return this.$store.getters.showAll
		},

		navFolder() {
			return this.topLevelNav.filter((item) => item.name !== undefined && this.showItem(item))
		},

		navFeeds() {
			const topLevelFeeds = this.topLevelNav.filter((item) => item.title !== undefined && this.showItem(item))
			const folderFeeds = this.navFolder
				.filter((folder) => folder.opened)
				.reduce((result, folder) => {
					return result.concat(this.sortedFolderFeeds(folder))
				}, [])
				.filter((item) => this.showItem(item))
			return [
				...topLevelFeeds,
				...folderFeeds,
			]
		},

		isStarredOpen() {
			return this.$store.getters.starredOpenState
		},
	},

	watch: {
		'$route.query.subscribe_to': {
			handler() {
				if (this.$route.query.subscribe_to) {
					this.showAddFeed = true
				}
			},
		},
	},

	created() {
		if (!this.disableRefresh) {
			// refresh feeds every minute
			this.polling = setInterval(() => {
				this.$store.dispatch(ACTIONS.FETCH_FEEDS)
			}, 60000)
		}
		// create shortcuts for feed/folder navigation
		useHotKey('d', this.prevFeed)
		useHotKey('f', this.nextFeed)
		useHotKey('c', this.prevFolder)
		useHotKey('v', this.nextFolder)
	},

	beforeUnmount() {
		clearInterval(this.polling)
	},

	methods: {
		newFolder(value: string) {
			const folderName = value.trim()
			if (this.$store.getters.folders.some((f) => f.name === folderName)) {
				showError(t('news', 'Folder exists already!'))
			} else {
				const folder = { name: folderName }
				this.$store.dispatch(ACTIONS.ADD_FOLDERS, { folder })
			}
		},

		markAllRead() {
			this.$store.getters.feeds.forEach((feed: Feed) => {
				this.$store.dispatch(ACTIONS.FEED_MARK_READ, { feed })
			})
		},

		markFolderRead(folder: Folder) {
			const feeds = this.$store.getters.feeds.filter((feed: Feed) => {
				return feed.folderId === folder.id
			})
			feeds.forEach((feed: Feed) => {
				this.$store.dispatch(ACTIONS.FEED_MARK_READ, { feed })
			})
		},

		renameFolder(folder: Folder) {
			const name = window.prompt(t('news', 'Rename Folder'), folder.name)

			// null when user presses escape (do nothing)
			if (name !== null) {
				this.$store.dispatch(ACTIONS.FOLDER_SET_NAME, { folder, name })
			}
		},

		deleteFolder(folder: Folder) {
			const shouldDelete = window.confirm(t('news', 'Are you sure you want to delete?'))

			if (shouldDelete) {
				folder.feeds.forEach((feed) => {
					this.$store.dispatch(ACTIONS.FEED_DELETE, { feed })
				})
				this.$store.dispatch(ACTIONS.DELETE_FOLDER, { folder })
			}
		},

		addFeed() {
			this.showAddFeed = true
		},

		closeAddFeed() {
			this.showAddFeed = false
		},

		openMoveFeed(feed) {
			this.feedToMove = feed
			this.showMoveFeed = true
		},

		closeMoveFeed() {
			this.showMoveFeed = false
		},

		openFeedSettings() {
			this.showFeedSettings = true
		},

		closeFeedSettings() {
			this.showFeedSettings = false
		},

		openSettings() {
			this.showSettings = true
		},

		closeSettings() {
			this.showSettings = false
		},

		isFolder(item: Feed | Folder) {
			return (item as Folder).name !== undefined
		},

		toggleFolderState(folder: Folder) {
			folder.opened = !folder.opened
			this.$store.dispatch(ACTIONS.FOLDER_OPEN_STATE, { folder })
		},

		async toggleStarredOpenState() {
			const value = !this.$store.getters.starredOpenState
			this.$store.commit('starredOpenState', { value })
			const configValue = value ? '1' : '0'
			const url = generateOcsUrl('/apps/provisioning_api/api/v1/config/users/news/starredOpenState')
			try {
				await axios.post(url, {
					configValue,
				})
			} catch (e) {
				const errorMessage = t('news', 'Unable to save starred open state')
				showError(errorMessage)
				console.error(errorMessage, e)
			}
		},

		isActiveFeed(feed) {
			return feed.id === this.active.feedId
		},

		isActiveFolder(folder) {
			return folder.id === this.active.folderId
		},

		hasActiveFeeds(folder) {
			return folder.feeds.some((item) => this.isActiveFeed(item))
		},

		showItem(item: Feed | Folder) {
			if (this.showAll) {
				return true
			}
			if (this.isFolder(item)) {
				return item.feedCount > 0 || this.isActiveFolder(item) || this.hasActiveFeeds(item) || item.updateErrorCount > 8
			} else {
				return item.unreadCount > 0 || item.updateErrorCount > 8 || this.isActiveFeed(item)
			}
		},

		sortedFolderFeeds(item: Feed | Folder) {
			return this.isFolder(item) ? item.feeds.slice().sort((a, b) => (b.pinned === true) - (a.pinned === true)) : []
		},

		getFeedIndex(direction) {
			if (this.$route.name === 'feed') {
				const feedIndex = this.navFeeds.findIndex((it) => it.id === Number(this.$route.params.feedId))
				return direction === 'prev' ? feedIndex - 1 : feedIndex + 1
			} else {
				// get current folder index
				const folderIndex = this.getFolderIndex(direction)
				// search for the nearest feed
				if (direction === 'next') {
					return this.navFeeds.findIndex((feed) => {
						const feedFolderIndex = this.navFolder.findIndex((folder) => folder.id === feed.folderId)
						return feedFolderIndex >= folderIndex - 1
					})
				} else {
					return this.navFeeds.findLastIndex((feed) => {
						const feedFolderIndex = this.navFolder.findIndex((folder) => folder.id === feed.folderId)
						return feedFolderIndex <= folderIndex
					})
				}
			}
		},

		getFolderIndex(direction) {
			if (this.$route.name === 'feed') {
				// use folder id from feed when the active item is a feed
				const feed = this.navFeeds.find((feed: Feed) => this.isActiveFeed(feed))
				const folderIndex = feed ? this.navFolder.findIndex((it) => it.id === feed.folderId) : -1
				return direction === 'prev' ? folderIndex : folderIndex + 1
			} else {
				const folderIndex = this.navFolder.findIndex((it) => it.id === Number(this.$route.params.folderId))
				return direction === 'prev' ? folderIndex - 1 : folderIndex + 1
			}
		},

		switchFeed(direction) {
			const newIndex = this.getFeedIndex(direction)
			if (newIndex >= 0 && newIndex < this.navFeeds.length) {
				const feedId = this.navFeeds[newIndex].id.toString()
				this.$router.push({ name: 'feed', params: { feedId } })
				this.$refs['feed-' + feedId]?.[0]?.$el?.scrollIntoView?.({ behavior: 'auto', block: 'nearest' })
			}
		},

		prevFeed() {
			this.switchFeed('prev')
		},

		nextFeed() {
			this.switchFeed('next')
		},

		switchFolder(direction) {
			const newIndex = this.getFolderIndex(direction)
			if (newIndex >= 0 && newIndex < this.navFolder.length) {
				const folderId = this.navFolder[newIndex].id.toString()
				this.$router.push({ name: 'folder', params: { folderId } })
				this.$refs['folder-' + folderId]?.[0]?.$el?.scrollIntoView?.({ behavior: 'auto', block: 'nearest' })
			}
		},

		prevFolder() {
			this.switchFolder('prev')
		},

		nextFolder() {
			this.switchFolder('next')
		},

		feedIcon(feed: Feed) {
			return API_ROUTES.FAVICON + '/' + feed.urlHash
		},
	},
})

</script>

<style scoped>
.footer-container {
	padding: var(--app-navigation-padding);
}

.new-folder-container {
	padding: calc(var(--default-grid-baseline, 4px) * 2);
}

/*
 * workaround remove extra scroll bar in navigation body
 */
:deep(.app-navigation__body) {
	overflow-y: unset !important;
	flex: 1 0 auto;
}
</style>
