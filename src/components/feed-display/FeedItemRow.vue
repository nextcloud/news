<template>
	<li
		class="feed-item-row"
		:class="{ compact: compactMode }"
		:aria-label="item.title"
		:aria-setsize="itemCount"
		:aria-posinset="itemIndex"
		role="button"
		tabindex="0"
		@keydown.enter="select()"
		@keydown.space.prevent="select()"
		@click="select()">
		<ShareItem v-if="showShareMenu" :item-id="shareItem" @close="closeShareMenu()" />
		<div class="link-container">
			<a
				class="external"
				target="_blank"
				rel="noreferrer"
				:href="item.url"
				:title="t('news', 'Open website')"
				:aria-label="`${t('news', 'Open website')} ${item.url}`"
				@click.middle="markRead(item); $event.stopPropagation();"
				@click="markRead(item); $event.stopPropagation();">
				<span
					v-if="getFeed(item.feedId).faviconLink"
					class="favicon"
					:style="{ backgroundImage: 'url(' + getFeed(item.feedId).faviconLink + ')' }" />
				<RssIcon v-else />
			</a>
		</div>

		<div class="main-container" :class="{ compact: compactMode }">
			<h1
				class="title-container"
				:class="{ compact: compactMode && !verticalSplit, unread: item.unread }"
				:dir="item.rtl && 'rtl'">
				{{ item.title }}
			</h1>

			<div class="intro-container" :class="{ compact: compactMode }">
				<!-- eslint-disable vue/no-v-html -->
				<span v-if="!compactMode || !verticalSplit" class="intro" v-html="item.intro" />
				<!--eslint-enable-->
			</div>

			<div v-if="!compactMode || !verticalSplit" class="date-container" :class="{ compact: compactMode }">
				<time class="date" :title="formatDate(item.pubDate)" :datetime="formatDateISO(item.pubDate)">
					{{ formatDateRelative(item.pubDate) }}
				</time>
			</div>
		</div>

		<div class="button-container" @click="$event.stopPropagation()">
			<NcActions :inline="3">
				<NcActionButton
					:title="t('news', 'Toggle star article')"
					@click="toggleStarred(item)">
					{{ t('news', 'Toggle star article') }}
					<template #icon>
						<StarIcon :class="{ starred: item.starred }" :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="item.unread && !item.keepUnread"
					:title="t('news', 'Keep article unread')"
					@click="toggleKeepUnread(item)">
					{{ t('news', 'Keep article unread') }}
					<template #icon>
						<EyeIcon :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="!item.unread && !item.keepUnread"
					:title="t('news', 'Toggle keep current article unread')"
					@click="toggleKeepUnread(item)">
					{{ t('news', 'Toggle keep current article unread') }}
					<template #icon>
						<EyeCheckIcon :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="item.keepUnread"
					:title="t('news', 'Remove keep article unread')"
					@click="toggleKeepUnread(item)">
					{{ t('news', 'Remove keep article unread') }}
					<template #icon>
						<EyeLockIcon :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton :title="t('news', 'Share within Instance')" @click="shareItem = item.id; showShareMenu = true">
					{{ t('news', 'Share within Instance') }}
					<template #icon>
						<ShareVariant />
					</template>
				</NcActionButton>
			</NcActions>
		</div>
	</li>
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeCheckIcon from 'vue-material-design-icons/EyeCheck.vue'
import EyeLockIcon from 'vue-material-design-icons/EyeLock.vue'
import RssIcon from 'vue-material-design-icons/Rss.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import ShareItem from '../ShareItem.vue'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { formatDate, formatDateISO, formatDateRelative } from '../../utils/dateUtils.ts'

export default defineComponent({
	name: 'FeedItemRow',
	components: {
		StarIcon,
		EyeIcon,
		EyeCheckIcon,
		EyeLockIcon,
		ShareVariant,
		RssIcon,
		NcActions,
		NcActionButton,
		ShareItem,
	},

	props: {
		/**
		 * The item to display
		 */
		item: {
			type: Object,
			required: true,
		},

		/**
		 * The number of items in the current list
		 */
		itemCount: {
			type: Number,
			required: true,
		},

		/**
		 * The index of the item in the current list
		 */
		itemIndex: {
			type: Number,
			required: true,
		},
	},

	emits: {
		'show-details': () => true,
	},

	data: () => {
		return {
			showShareMenu: false,
			shareItem: undefined,
		}
	},

	computed: {
		...mapState(['feeds']),
		compactMode() {
			return this.$store.getters.displaymode === '1'
		},

		verticalSplit() {
			return this.$store.getters.splitmode === '0'
		},
	},

	methods: {
		formatDate,
		formatDateRelative,
		formatDateISO,
		select(): void {
			this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: this.item.id })
			this.markRead(this.item)
			this.$emit('show-details')
		},

		getFeed(id: number): Feed {
			return this.$store.getters.feeds.find((feed: Feed) => feed.id === id) || {}
		},

		markRead(item: FeedItem): void {
			if (!item.keepUnread && item.unread) {
				this.$store.dispatch(ACTIONS.MARK_READ, { item })
			}
		},

		toggleKeepUnread(item: FeedItem): void {
			item.keepUnread = !item.keepUnread
			this.$store.dispatch(ACTIONS.MARK_UNREAD, { item })
		},

		toggleStarred(item: FeedItem): void {
			this.$store.dispatch(item.starred ? ACTIONS.UNSTAR_ITEM : ACTIONS.STAR_ITEM, { item })
		},

		closeShareMenu() {
			this.showShareMenu = false
		},
	},
})

</script>

<style>
	.feed-item-row {
		display: flex; padding: 10px 10px;
	}

	.feed-item-row.compact {
		display: flex; padding: 4px 4px !important;
		border-bottom: 1px solid var(--color-border);
	}

	.feed-item-row.compact a.external {
		line-height: 0;
	}

	.feed-item-row:hover {
		background-color: var(--color-background-hover);
	}

	.feed-item-row, .feed-item-row * {
		cursor: pointer;
	}

	.feed-item-row .link-container {
		padding-right: 12px;
		display: flex;
		flex-direction: row;
		align-self: center;
	}

	.favicon {
		height: 24px;
		width: 24px;
		display: inline-block;
		background-size: contain;
	}

	.feed-item-row .main-container {
		flex-grow: 1;
		min-width: 0;
	}

	.feed-item-row .main-container.compact {
		display: flex;
		align-items: center;
		gap: 1rem;
	}

	.feed-item-row .title-container {
		color: var(--color-text-lighter);
		flex-grow: 1;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}

	.feed-item-row .title-container.unread {
		color: var(--color-main-text);
		font-weight: bold;
	}

	.feed-item-row .title-container.compact {
		flex: 0 1 auto;
		overflow-y: unset;
		overflow-x: scroll;
		max-width: 100%;
		text-overflow: clip;
	}

	.feed-item-row .intro-container {
		line-height: initial;
		height: 32pt;
		overflow: hidden;
	}

	.feed-item-row .intro-container.compact {
		flex: 1 1;
		height: 26pt !important;
		align-content: center;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.feed-item-row .intro {
		color: var(--color-text-lighter);
		font-size: 10pt;
		font-weight: normal;
	}

	@media only screen and (min-width: 320px) {
		.feed-item-row .date-container {
			font-size: small;
		}
	}

	@media only screen and (min-width: 768px) {
		.feed-item-row .date-container {
			font-size: medium;
		}
	}

	.feed-item-row .date-container {
		color: var(--color-text-lighter);
		white-space: nowrap;
	}

	.feed-item-row .date-container.compact {
		flex: 0 0 auto;
		font-size: small;
		padding-right: 4px;
	}

	.feed-item-row .button-container {
		display: flex;
		flex-direction: row;
		align-self: center;
	}

	.feed-item-row .button-container .button-vue,
	.feed-item-row .button-container .button-vue .button-vue__wrapper,
	.feed-item-row .button-container .material-design-icon
	{
		width: 24px !important;
		min-width: 24px;
		min-height: 24px;
		height: 24px;
	}

	.feed-item-row .button-container .material-design-icon {
		color: var(--color-text-lighter)
	}

	.feed-item-row .button-container .material-design-icon:hover {
		color: var(--color-text-light);
	}

	.feed-item-row .button-container .material-design-icon.rss-icon:hover {
		color: #555555;
	}

	.material-design-icon.starred {
		color: rgb(255, 204, 0) !important;
	}

	.feed-item-row .button-container .material-design-icon.keep-unread {
		color: var(--color-main-text);
	}

	.material-design-icon.starred:hover {
		color: #555555;
	}

	.feed-item-row .button-container .eye-check-icon {
		color: var(--color-placeholder-dark);
	}

	.active, .active:hover {
		background-color: var(--color-background-darker);
	}
</style>
