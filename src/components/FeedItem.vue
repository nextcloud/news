<template>
	<div class="feed-item-container">
		<div class="feed-item-row" @click="expand()">
			<div style="padding-right: 5px; display: flex; flex-direction: row; align-self: start;">
				<a class="external"
					target="_blank"
					rel="noreferrer"
					:href="item.url"
					:title="t('news', 'Open website')"
					@click="markRead(item.id); $event.stopPropagation();">
					<EarthIcon />
				</a>
				<RssIcon v-if="!getFeed(item.feedId).faviconLink" />
				<span v-if="getFeed(item.feedId).faviconLink" :style="{ 'backgroundImage': 'url(' + Content.getFeed(item.feedId).faviconLink + ')' }" />
			</div>
			<div class="title-container" :class="{ 'unread': item.unread }">
				<span :style="{ 'white-space': !isExpanded ? 'nowrap' : 'normal' }" :dir="item.rtl && 'rtl'">
					{{ item.title }}
					<span v-if="!isExpanded" class="intro" v-html="item.intro" />
				</span>
			</div>
			<div class="date-container">
				<time class="date" :title="formatDate(item.pubDate*1000, 'yyyy-MM-dd HH:mm:ss')" :datetime="formatDate(item.pubDate*1000, 'yyyy-MM-ddTHH:mm:ssZ')">
					{{ getRelativeTimestamp(item.pubDate*1000) }}
				</time>
			</div>
			<div class="button-container" @click="$event.stopPropagation()">
				<StarIcon :class="{'starred': item.starred }" @click="toggleStarred()" />
				<Eye :class="{ 'keep-unread': item.keepUnread }" @click="toggleKeepUnread()" />
				<NcActions :force-menu="true">
					<template #icon>
						<ShareVariant />
					</template>
					<NcActionButton>
						<template #default>
							<!-- TODO: --> TODO
						</template>
						<template #icon>
							<ShareVariant />
						</template>
					</NcActionButton>
				</NcActions>
			</div>
		</div>

		<div v-if="isExpanded" style="padding: 5px 10px;">
			<div class="article">
				<!--div class="heading only-in-expanded">
					<time class="date" :title="formatDate(item.pubDate*1000, 'yyyy-MM-dd HH:mm:ss')" :datetime="formatDate(item.pubDate*1000, 'yyyy-MM-ddTHH:mm:ssZ')">
						{{ getRelativeTimestamp(item.pubDate*1000) }}
					</time>
					<h1 :dir="item.rtl && 'rtl'">
						<a class="external"
							target="_blank"
							rel="noreferrer"
							:href="item.url"
							:title="item.title">
							{{ item.title }}
						</a>
					</h1>
				</div-->

				<div class="subtitle" :dir="item.rtl && 'rtl'">
					<span v-show="item.author !== undefined" class="author">
						{{ t('news', 'by') }} {{ item.author }}
					</span>
					<span v-if="!item.sharedBy" class="source">{{ t('news', 'from') }}
						<!-- TODO: Fix this -->
						<a :href="`#/items/feeds/${item.feedId}/`">
							{{ getFeed(item.feedId).title }}
							<img v-if="getFeed(item.feedId).faviconLink && isCompactView()" :src="getFeed(item.feedId).faviconLink" alt="favicon">
						</a>
					</span>
					<span v-if="item.sharedBy">
						<span v-if="item.author">-</span>
						{{ t('news', 'shared by') }}
						{{ item.sharedByDisplayName }}
					</span>
				</div>

				<div v-if="getMediaType(item.enclosureMime) == 'audio'" class="enclosure">
					<button @click="play(item)">
						{{ t('news', 'Play audio') }}
						<!--?php p($l->t('Play audio')) ?-->
					</button>
					<a class="button"
						:href="item.enclosureLink"
						target="_blank"
						rel="noreferrer">
						{{ t('news', 'Download audio') }}
						<!--?php p($l->t('Download audio')) ? -->
					</a>
				</div>
				<div v-if="getMediaType(item.enclosureMime) == 'video'" class="enclosure">
					<video controls
						preload="none"
						news-play-one
						:src="item.enclosureLink"
						:type="item.enclosureMime" />
					<a class="button"
						:href="item.enclosureLink"
						target="_blank"
						rel="noreferrer">
						{{ t('news', 'Download video') }}
					</a>
				</div>

				<div v-if="item.mediaThumbnail" class="enclosure thumbnail">
					<a :href="item.enclosureLink"><img :src="item.mediaThumbnail" alt=""></a>
				</div>

				<div v-if="item.mediaDescription" class="enclosure description" v-html="item.mediaDescription" />

				<div class="body" :dir="item.rtl && 'rtl'" v-html="item.body" />
			</div>
		</div>
	</div>
</template>

<script>
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import RssIcon from 'vue-material-design-icons/Rss.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'

export default {
	name: 'FeedItem',
	components: {
		EarthIcon,
		StarIcon,
		Eye,
		ShareVariant,
		RssIcon,
		NcActions,
		NcActionButton,
	},
	props: {
		item: {
			type: Object,
			required: true,
		},
	},
	data: () => {
		return {
			expanded: false,
		}
	},
	computed: {
		isExpanded() {
			return this.expanded
		},
	},
	methods: {
		expand() {
			this.expanded = !this.expanded
		},
		formatDate() {
			return 'test'
		},
		getRelativeTimestamp() {
			return 'yesterday'
		},
		getFeed(id) {
			return {}
		},
		getMediaType(mime) {
			return false
		},
		play(item) {
			// TODO: implement this
		},
		markRead() {
			// TODO: implement this
		},
		toggleKeepUnread() {
			// TODO: implement this
		},
		toggleStarred() {
			// TODO: implement this
		},
	},
}

</script>

<style>

	.feed-item-container {
		border-bottom: 1px solid #222;
	}

	.feed-item-row {
		display: flex; padding: 5px 10px;
	}

	.feed-item-row:hover {
		background-color: #222;
	}

	.feed-item-row, .feed-item-row * {
		cursor: pointer;
	}

	.feed-item-row .title-container {
		color: var(--color-text-lighter);

		flex-grow: 1;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.feed-item-row .title-container.unread {
		color: var(--color-main-text);
    font-weight: bold;
	}

	.feed-item-row .intro {
		color: var(--color-text-lighter);
    font-size: 10pt;
    font-weight: normal;
    margin-left: 20px;
	}

	.feed-item-row .date-container {
		padding-left: 4px;
	}

	.feed-item-row .button-container {
		display: flex;
		flex-direction: row;
		align-self: start;
	}

	.button-container .action-item .button-vue, .button-container .material-design-icon {
		width: 30px !important;
    min-width: 30px;
    min-height: 30px;
    height: 30px;
	}

	.material-design-icon {
		color: #555555;
	}

	.material-design-icon:hover {
		color: var(--color-main-text);
	}

	.material-design-icon.rss-icon:hover {
		color: #555555;
	}

	.material-design-icon.starred {
		color: rgb(255, 204, 0);
	}

	.material-design-icon.keep-unread {
		color: var(--color-main-text);
	}

	.material-design-icon.starred:hover {
		color: #555555;
	}

	.article {
		padding: 0 50px 50px 50px;
	}

	.article .body {
		color: var(--color-main-text);
    font-size: 15px;
	}

	.article a {
		text-decoration: underline;
	}

	.article .body a {
		color: #3a84e4
	}

	.article .subtitle {
		color: var(--color-text-lighter);
    font-size: 15px;
    padding: 25px 0;
	}

	.article .author {
		color: var(--color-text-lighter);
    font-size: 15px;
	}
</style>
