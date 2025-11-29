<template>
	<NcAppNavigation>
		<AddFeed v-if="showAddFeed" @close="closeAddFeed()" />
		<MoveFeed v-if="showMoveFeed" :feed="feedToMove" @close="closeMoveFeed()" />
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
				:open="isStarredCollapsed"
				@update:open="isStarredCollapsed"
				:force-menu="true">
				<NcAppNavigationItem
					v-for="group in GroupedStars"
					:key="group.id"
					:ref="'starred-' + group.id"
					:name="group.title"
					:to="{ name: ROUTES.STARRED, params: { feedId: group.id } }">
					<template #icon>
						<RssIcon v-if="!group.faviconLink" />
						<span v-if="group.faviconLink" style="width: 16px; height: 16px; background-size: contain;" :style="{ backgroundImage: 'url(' + group.faviconLink + ')' }" />
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

			<NcAppNavigationItem
				:name="t('news', 'Explore')"
				:to="{ name: ROUTES.EXPLORE }">
				<template #icon>
					<EarthIcon />
				</template>
			</NcAppNavigationItem>
		</template>
		<template #footer>
			<NcAppNavigationSettings :name="t('news', 'Settings')">
				<div class="settings-button">
					<NcButton wide="true" target="_blank" href="https://nextcloud.github.io/news/">
						{{ t('news', 'Documentation') }}
					</NcButton>
				</div>
				<div class="settings-button">
					<NcButton wide="true" @click="showHelp = true">
						{{ t('news', 'Keyboard shortcuts') }}
					</NcButton>
				</div>
				<HelpModal v-if="showHelp" @close="showHelp = false" />
				<div class="settings-button">
					<NcButton wide="true" @click="showFeedInfoTable = true">
						{{ t('news', 'Article feed information') }}
					</NcButton>
				</div>
				<FeedInfoTable v-if="showFeedInfoTable" @close="showFeedInfoTable = false" />
				<div>
					<div class="select-container">
						<label>
							{{ t('news', 'Display mode') }}
						</label>
						<select
							id="select-displaymode"
							v-model="displaymode"
							:value="displaymode">
							<option
								v-for="displayMode in displayModeOptions"
								:key="displayMode.id"
								:value="displayMode.id">
								{{ displayMode.name }}
							</option>
						</select>
					</div>
					<div class="select-container">
						<label>
							{{ t('news', 'Split mode') }}
						</label>
						<select
							id="select-splitmode"
							v-model="splitmode"
							:disabled="displaymode === DISPLAY_MODE.SCREENREADER"
							:value="splitmode">
							<option
								v-for="splitMode in splitModeOptions"
								:key="splitMode.id"
								:value="splitMode.id">
								{{ splitMode.name }}
							</option>
						</select>
					</div>
					<div>
						<input
							id="toggle-preventreadonscroll"
							v-model="preventReadOnScroll"
							type="checkbox"
							class="checkbox">
						<label for="toggle-preventreadonscroll">
							{{ t('news', 'Disable mark read through scrolling') }}
						</label>
					</div>
					<div>
						<input
							id="toggle-showall"
							v-model="showAll"
							type="checkbox"
							class="checkbox">
						<label for="toggle-showall">
							{{ t('news', 'Show all articles') }}
						</label>
					</div>
					<div>
						<input
							id="toggle-oldestfirst"
							v-model="oldestFirst"
							type="checkbox"
							class="checkbox">
						<label for="toggle-oldestfirst">
							{{ t('news', 'Reverse ordering (oldest on top)') }}
						</label>
					</div>
					<div>
						<input
							id="toggle-disableRefresh"
							v-model="disableRefresh"
							type="checkbox"
							class="checkbox">
						<label for="toggle-disableRefresh">
							{{ t('news', 'Disable automatic refresh') }}
						</label>
					</div>
					<h3>{{ t('news', 'Abonnements (OPML)') }}</h3>
					<div class="button-container">
						<NcButton
							aria-label="UploadOpml"
							:disabled="loading"
							@click="$refs.fileSelect.click()">
							<template #icon>
								<UploadIcon :size="20" />
							</template>
						</NcButton>
						<input
							ref="fileSelect"
							type="file"
							class="hidden"
							aria-hidden="true"
							accept=".opml"
							@change="importOpml">
						<NcButton
							aria-label="DownloadOpml"
							:disabled="loading"
							@click="exportOpml">
							<template #icon>
								<DownloadIcon :size="20" />
							</template>
						</NcButton>
					</div>
				</div>
			</NcAppNavigationSettings>
		</template>
	</NcAppNavigation>
</template>

<script lang="ts">

import type { Feed } from '../types/Feed.ts'
import type { Folder } from '../types/Folder.ts'

import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { subscribe } from '@nextcloud/event-bus'
import { generateOcsUrl, generateUrl } from '@nextcloud/router'
import { useHotKey } from '@nextcloud/vue/composables/useHotKey'
import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcAppNavigation from '@nextcloud/vue/components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/components/NcAppNavigationItem'
import NcAppNavigationNew from '@nextcloud/vue/components/NcAppNavigationNew'
import NcAppNavigationNewItem from '@nextcloud/vue/components/NcAppNavigationNewItem'
import NcAppNavigationSettings from '@nextcloud/vue/components/NcAppNavigationSettings'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import DownloadIcon from 'vue-material-design-icons/Download.vue'
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import FolderAlertIcon from 'vue-material-design-icons/FolderAlert.vue'
import FolderPlusIcon from 'vue-material-design-icons/FolderPlus.vue'
import HistoryIcon from 'vue-material-design-icons/History.vue'
import PlusIcon from 'vue-material-design-icons/Plus.vue'
import RssIcon from 'vue-material-design-icons/Rss.vue'
import UploadIcon from 'vue-material-design-icons/Upload.vue'
import AddFeed from './AddFeed.vue'
import FeedInfoTable from './modals/FeedInfoTable.vue'
import HelpModal from './modals/HelpModal.vue'
import MoveFeed from './MoveFeed.vue'
import SidebarFeedLinkActions from './SidebarFeedLinkActions.vue'
import { DISPLAY_MODE } from '../enums/index.ts'
import { ROUTES } from '../routes/index.ts'
import { ACTIONS, MUTATIONS } from '../store/index.ts'
import { API_ROUTES } from '../types/ApiRoutes.ts'

export default defineComponent({
	name: 'SidebarNavigation',
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
		MoveFeed,
		RssIcon,
		FolderIcon,
		EyeIcon,
		EarthIcon,
		FolderAlertIcon,
		FolderPlusIcon,
		HistoryIcon,
		PlusIcon,
		UploadIcon,
		DownloadIcon,
		SidebarFeedLinkActions,
		HelpModal,
		FeedInfoTable,
	},

	data: () => {
		return {
			showAddFeed: false,
			showMoveFeed: false,
			feedToMove: undefined,
			DISPLAY_MODE,
			ROUTES,
			showHelp: false,
			showFeedInfoTable: false,
			polling: null,
			uploadStatus: null,
			selectedFile: null,
			displayModeOptions: [
				{
					id: '0',
					name: t('news', 'Default'),
				},
				{
					id: '1',
					name: t('news', 'Compact'),
				},
				{
					id: '2',
					name: t('news', 'Screenreader'),
				},
			],

			splitModeOptions: [
				{
					id: '0',
					name: t('news', 'Vertical'),
				},
				{
					id: '1',
					name: t('news', 'Horizontal'),
				},
				{
					id: '2',
					name: t('news', 'Off'),
				},
			],
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

		loading: {
			get() {
				return this.$store.getters.loading
			},
		},

		displaymode: {
			get() {
				return this.$store.getters.displaymode
			},

			set(newValue) {
				this.saveSetting('displaymode', newValue)
			},
		},

		splitmode: {
			get() {
				return this.$store.getters.splitmode
			},

			set(newValue) {
				this.saveSetting('splitmode', newValue)
			},
		},

		oldestFirst: {
			get() {
				return this.$store.getters.oldestFirst
			},

			set(newValue) {
				this.$store.commit(MUTATIONS.RESET_ITEM_STATES)
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
				this.$store.commit(MUTATIONS.RESET_ITEM_STATES)
				this.saveSetting('showAll', newValue)
			},
		},

		disableRefresh: {
			get() {
				return this.$store.getters.disableRefresh
			},

			set(newValue) {
				if (!newValue) {
					// refresh feeds every minute
					this.polling = setInterval(() => {
						this.$store.dispatch(ACTIONS.FETCH_FEEDS)
					}, 60000)
				} else {
					clearInterval(this.polling)
				}
				this.saveSetting('disableRefresh', newValue)
			},
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

		/**
		 * Parent "Starred" is only collapsed when the route is STARRED
		 * and there is a feedId param (i.e. viewing a specific starred group)
		 */
		isStarredCollapsed(): boolean {
			// ROUTES is available via data() as this.ROUTES
			return this.$route.name === this.ROUTES.STARRED && (this.$route.params && this.$route.params.feedId)
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

	mounted() {
		subscribe('news:global:toggle-help-dialog', () => {
			this.showHelp = !this.showHelp
		})
		subscribe('news:global:toggle-feed-info', () => {
			this.showFeedInfoTable = !this.showFeedInfoTable
		})
	},

	beforeUnmount() {
		clearInterval(this.polling)
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
			if (typeof value === 'boolean') {
				value = value ? '1' : '0'
			}
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

		async importOpml(event) {
			const file = event.target.files[0]
			if (file) {
				this.selectedFile = file
			} else {
				showError(t('news', 'Please select a valid OPML file'))
				return
			}

			this.$store.commit(MUTATIONS.SET_LOADING, { value: true })
			this.showFeedInfoTable = true
			const formData = new FormData()
			formData.append('file', this.selectedFile)

			try {
				const response = await fetch(generateUrl('/apps/news/import/opml'), {
					method: 'POST',
					body: formData,
				})

				if (response.ok) {
					const data = await response.json()
					if (data.status === 'ok') {
						showSuccess(t('news', 'File successfully uploaded'))
					} else {
						showError(data.message, { timeout: -1 })
					}
				} else {
					showError(t('news', 'Error uploading the opml file'))
				}
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Error connecting to the server'),
					error: e,
				})
			}
			// refresh feeds and folders after import
			this.$store.dispatch(ACTIONS.FETCH_FOLDERS)
			this.$store.dispatch(ACTIONS.FETCH_FEEDS)
			this.$store.commit(MUTATIONS.SET_LOADING, { value: false })
		},

		async exportOpml() {
			try {
				const response = await fetch(generateUrl('/apps/news/export/opml'))
				if (response.ok) {
					const formattedDate = new Date().toISOString().split('T')[0]
					const blob = await response.blob()
					const link = document.createElement('a')
					link.href = URL.createObjectURL(blob)
					link.download = 'subscriptions-' + formattedDate + '.opml'
					link.click()
				} else {
					showError(t('news', 'Error retrieving the opml file'))
				}
			} catch (e) {
				this.handleResponse({
					errorMessage: t('news', 'Error connecting to the server'),
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

		isFolder(item: Feed | Folder) {
			return (item as Folder).name !== undefined
		},

		toggleFolderState(folder: Folder) {
			folder.opened = !folder.opened
			this.$store.dispatch(ACTIONS.FOLDER_OPEN_STATE, { folder })
		},

		isActiveFeed(feed) {
			return this.$route.name === 'feed' ? feed.id === Number(this.$route.params?.feedId) : false
		},

		isActiveFolder(folder) {
			return this.$route.name === 'folder' ? folder.id === Number(this.$route.params?.folderId) : false
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
				this.$refs['feed-' + feedId][0].$el.scrollIntoView({ behavior: 'auto', block: 'nearest' })
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
				this.$refs['folder-' + folderId][0].$el.scrollIntoView({ behavior: 'auto', block: 'nearest' })
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
.button-container {
	display: flex;
	width: 100%;
}

.button-container button {
	flex: 1;
}

.select-container {
	display: flex;
	align-items: center;
}

.select-container select {
	min-width: 140px;
	text-overflow: ellipsis;
	margin-inline-start: auto;
}

.settings-button {
	padding: 2px;
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
