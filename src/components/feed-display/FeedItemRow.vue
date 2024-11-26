<template>
	<div class="feed-item-row" :class="{ 'compact': compactMode }" @click="select()">
		<ShareItem v-if="showShareMenu" :item-id="shareItem" @close="closeShareMenu()" />
		<div class="link-container">
			<a class="external"
				target="_blank"
				rel="noreferrer"
				:href="item.url"
				:title="t('news', 'Open website')"
				@click.middle="markRead(item); $event.stopPropagation();"
				@click="markRead(item); $event.stopPropagation();">
				<span v-if="getFeed(item.feedId).faviconLink"
					class="favicon"
					:style="{ 'backgroundImage': 'url(' + getFeed(item.feedId).faviconLink + ')' }" />
				<RssIcon v-else />
			</a>
		</div>

		<div class="main-container" :class="{ 'compact': compactMode }">
			<div class="title-container" :class="{ 'compact': compactMode, 'unread': item.unread }">
				<h1 :dir="item.rtl && 'rtl'">
					{{ item.title }}
				</h1>
			</div>

			<div class="intro-container" :class="{ 'compact': compactMode }">
				<!-- eslint-disable vue/no-v-html -->
				<span class="intro" v-html="item.intro" />
				<!--eslint-enable-->
			</div>

			<div class="date-container" :class="{ 'compact': compactMode }">
				<time class="date" :title="formatDate(item.pubDate*1000, 'yyyy-MM-dd HH:mm:ss')" :datetime="formatDatetime(item.pubDate*1000, 'yyyy-MM-ddTHH:mm:ssZ')">
					{{ getRelativeTimestamp(item.pubDate*1000) }}
				</time>
			</div>
		</div>

		<div class="button-container" @click="$event.stopPropagation()">
			<StarIcon :class="{'starred': item.starred }" @click="toggleStarred(item)" />
			<EyeIcon v-if="item.unread && !item.keepUnread" @click="toggleKeepUnread(item)" />
			<EyeCheckIcon v-if="!item.unread && !item.keepUnread" @click="toggleKeepUnread(item)" />
			<EyeLockIcon v-if="item.keepUnread" class="keep-unread" @click="toggleKeepUnread(item)" />
			<NcActions>
				<NcActionButton :title="t('news', 'Share within Instance')" @click="shareItem = item.id; showShareMenu = true">
					{{ t('news', 'Share') }}
					<template #icon>
						<ShareVariant />
					</template>
				</NcActionButton>
			</NcActions>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue'
import { mapState } from 'vuex'

import StarIcon from 'vue-material-design-icons/Star.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeCheckIcon from 'vue-material-design-icons/EyeCheck.vue'
import EyeLockIcon from 'vue-material-design-icons/EyeLock.vue'
import RssIcon from 'vue-material-design-icons/Rss.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'

import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

import ShareItem from '../ShareItem.vue'

import { Feed } from '../../types/Feed'
import { FeedItem } from '../../types/FeedItem'
import { ACTIONS, MUTATIONS } from '../../store'

export default Vue.extend({
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
		item: {
			type: Object,
			required: true,
		},
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
			return this.$store.getters.compact
		},
	},
	methods: {
		select(): void {
			this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: this.item.id })
			this.markRead(this.item)
			this.$emit('show-details')
		},
		formatDate(epoch: number): string {
			return new Date(epoch).toLocaleString()
		},
		formatDatetime(epoch: number): string {
			return new Date(epoch).toISOString()
		},
		getRelativeTimestamp(previous: number): string {
			const current = Date.now()

			const msPerMinute = 60 * 1000
			const msPerHour = msPerMinute * 60
			const msPerDay = msPerHour * 24
			const msPerMonth = msPerDay * 30
			const msPerYear = msPerDay * 365

			const elapsed = current - previous

			if (elapsed < msPerMinute) {
				return t('news', '{num} seconds', { num: Math.round(elapsed / 1000) })
			} else if (elapsed < msPerHour) {
				return t('news', '{num} minutes ago', { num: Math.round(elapsed / msPerMinute) })
			} else if (elapsed < msPerDay) {
				return t('news', '{num} hours ago', { num: Math.round(elapsed / msPerHour) })
			} else if (elapsed < msPerMonth) {
				return t('news', '{num} days ago', { num: Math.round(elapsed / msPerDay) })
			} else if (elapsed < msPerYear) {
				return t('news', '{num} months ago', { num: Math.round(elapsed / msPerMonth) })
			} else {
				return t('news', '{num} years ago', { num: Math.round(elapsed / msPerYear) })
			}
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
			this.$set(item, 'keepUnread', !item.keepUnread)
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
		flex-grow: 1;
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
		overflow: unset;
	}

	.feed-item-row .intro-container {
		line-height: initial;
		height: 32pt;
		overflow: hidden;
	}

	.feed-item-row .intro-container.compact {
		flex: 1 1 auto;
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
	}

	.feed-item-row .button-container {
		display: flex;
		flex-direction: row;
		align-self: center;
	}

	.feed-item-row .button-container .button-vue, .feed-item-row .button-container .button-vue .button-vue__wrapper, .feed-item-row .button-container .material-design-icon {
		width: 30px !important;
		min-width: 30px;
		min-height: 30px;
		height: 30px;
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
