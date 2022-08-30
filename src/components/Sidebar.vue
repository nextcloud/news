<template>
	<NcAppNavigation>
		<AddFeed v-if="showAddFeed" @close="closeShowAddFeed()" />
		<NcAppNavigationNew :text="t('news', 'Subscribe')"
			button-id="new-feed-button"
			button-class="icon-add"
			@click="showShowAddFeed()" />

		<div class="news-navigation">
			<NcAppNavigationNewItem :title="t('news', 'New folder')" @new-item="newFolder">
				<template #icon>
					<PlusCircle :size="16" />
				</template>
			</NcAppNavigationNewItem>

			<NcAppNavigationItem :title="t('news', 'Unread articles')" icon="icon-rss">
				<template #actions>
					<ActionButton icon="icon-checkmark" @click="alert('Edit')">
						t('news','Mark read')
					</ActionButton>
				</template>
				<template #counter>
					<NcCounterBubble>5</NcCounterBubble>
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :title="t('news', 'All articles')" icon="icon-rss">
				<template #actions>
					<NcActionButton icon="icon-checkmark" @click="alert('Edit')">
						t('news','Mark read')
					</NcActionButton>
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :title="t('news', 'Starred')" icon="icon-starred">
				<template #counter>
					<NcCounterBubble>35</NcCounterBubble>
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem v-for="folder in folders"
				:key="folder.name"
				:title="folder.name"
				icon="icon-folder"
				:allow-collapse="true">
				<template #default>
					<NcAppNavigationItem v-for="feed in folder.feeds"
						:key="feed.name"
						:title="feed.title">
						<template #icon>
							<img v-if="feed.faviconLink"
								:src="feed.faviconLink"
								alt="feedIcon">
							<div v-if="!feed.faviconLink" class="icon-rss" />
						</template>
						<template #actions>
							<NcActionButton icon="icon-checkmark" @click="alert('Mark read')">
								{{ t("news", "Mark read") }}
							</NcActionButton>
							<NcActionButton icon="icon-pinned" @click="alert('Rename')">
								{{ t("news", "Unpin from top") }}
							</NcActionButton>
							<NcActionButton icon="icon-caret-dark"
								@click="deleteFolder(folder)">
								{{ t("news", "Newest first") }}
							</NcActionButton>
							<NcActionButton icon="icon-caret-dark"
								@click="deleteFolder(folder)">
								{{ t("news", "Oldest first") }}
							</NcActionButton>
							<NcActionButton icon="icon-caret-dark"
								@click="deleteFolder(folder)">
								{{ t("news", "Default order") }}
							</NcActionButton>
							<NcActionButton icon="icon-full-text-disabled"
								@click="deleteFolder(folder)">
								{{ t("news", "Enable full text") }}
							</NcActionButton>
							<NcActionButton icon="icon-full-text-enabled"
								@click="deleteFolder(folder)">
								{{ t("news", "Disable full text") }}
							</NcActionButton>
							<NcActionButton icon="icon-updatemode-default"
								@click="deleteFolder(folder)">
								{{ t("news", "Unread updated") }}
							</NcActionButton>
							<NcActionButton icon="icon-updatemode-unread"
								@click="deleteFolder(folder)">
								{{ t("news", "Ignore updated") }}
							</NcActionButton>
							<NcActionButton icon="icon-icon-rss" @click="deleteFolder(folder)">
								{{ t("news", "Open feed URL") }}
							</NcActionButton>
							<NcActionButton icon="icon-icon-rename"
								@click="deleteFolder(folder)">
								{{ t("news", "Rename") }}
							</NcActionButton>
							<NcActionButton icon="icon-delete" @click="deleteFolder(folder)">
								{{ t("news", "Delete") }}
							</NcActionButton>
						</template>
					</NcAppNavigationItem>
				</template>
				<template v-if="folder.feedCount > 0" #counter>
					<NcCounterBubble>{{ folder.feedCount }}</NcCounterBubble>
				</template>
				<template #actions>
					<NcActionButton icon="icon-checkmark" @click="alert('Mark read')">
						{{ t("news", "Mark read") }}
					</NcActionButton>
					<NcActionButton icon="icon-rename" @click="alert('Rename')">
						{{ t("news", "Rename") }}
					</NcActionButton>
					<NcActionButton icon="icon-delete" @click="deleteFolder(folder)">
						{{ t("news", "Delete") }}
					</NcActionButton>
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem :title="t('news', 'Explore')"
				icon="icon-link"
				:to="{ name: 'explore' }">
				<template #counter>
					<NcCounterBubble>35</NcCounterBubble>
				</template>
			</NcAppNavigationItem>
		</div>
	</NcAppNavigation>
</template>

<script>

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationNew from '@nextcloud/vue/dist/Components/NcAppNavigationNew.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import NcAppNavigationNewItem from '@nextcloud/vue/dist/Components/NcAppNavigationNewItem.js'
// import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import NcCounterBubble from '@nextcloud/vue/dist/Components/NcCounterBubble.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
import AddFeed from './AddFeed.vue'
import PlusCircle from 'vue-material-design-icons/PlusCircle.vue'

export default {
	components: {
		NcAppNavigation,
		NcAppNavigationNew,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		// AppNavigationCounter,
		NcCounterBubble,
		NcActionButton,
		AddFeed,
		PlusCircle,
	},
	data: () => {
		return {
			showAddFeed: false,
		}
	},
	computed: {
		folders() {
			return this.$store.state.folders
		},
	},
	created() {
		// TODO?
	},
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
		alert(msg) {
			window.alert(msg)
		},
	},
}
</script>

<style lang="scss" scoped>
.news-navigation {
	display: flex;
	flex-direction: column;
	padding-left: 10px;
	padding-right: 10px;
	gap: 10px;

	& ::v-deep .app-navigation-input-confirm {
		form {
			gap: 4px;
			align-items: center;
		}

		button.button-vue--icon-only {
			min-height: 36px !important;
			width: 36px !important;
			height: 36px !important;
			min-width: 36px !important;
		}
	}
}
</style>
