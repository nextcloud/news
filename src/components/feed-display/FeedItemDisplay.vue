<template>
	<div
		class="feed-item-display"
		:class="{ screenreader: screenReaderMode }"
		:style="screenReaderItemHeight"
		v-bind="screenReaderMode ? { 'aria-setsize': itemCount, 'aria-posinset': itemIndex } : {}"
		@focusin="selectItemOnFocus">
		<ShareItem v-if="showShareMenu" :itemId="item.id" @close="closeShareMenu()" />
		<NcActions
			v-if="splitModeOff && !screenReaderMode"
			class="nav-icons"
			:inline="2">
			<NcActionButton
				class="nav-button left"
				:disabled="itemIndex <= 1"
				:title="t('news', 'Previous Item')"
				@click="prevItem">
				<template #icon>
					<ChevronLeftIcon :size="32" />
				</template>
			</NcActionButton>
			<NcActionButton
				class="nav-button right"
				:disabled="itemIndex >= itemCount"
				:title="t('news', 'Next Item')"
				@click="nextItem">
				<template #icon>
					<ChevronRightIcon :size="32" />
				</template>
			</NcActionButton>
		</NcActions>
		<div class="action-bar">
			<NcActions :inline="4">
				<NcActionButton
					:title="t('news', 'Share within Instance')"
					@click="showShareMenu = true">
					{{ t('news', 'Share within Instance') }}
					<template #icon>
						<ShareVariant />
					</template>
				</NcActionButton>
				<NcActionButton
					:title="t('news', 'Toggle star article')"
					@click="toggleStarred">
					{{ t('news', 'Toggle star article') }}
					<template #icon>
						<StarIcon :class="{ starred: item.starred }" :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="item.unread"
					:title="t('news', 'Mark read')"
					@click="toggleRead">
					{{ t('news', 'Mark read') }}
					<template #icon>
						<EyeIcon :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="!item.unread"
					:title="t('news', 'Mark unread')"
					@click="toggleRead">
					{{ t('news', 'Mark unread') }}
					<template #icon>
						<EyeCheckIcon :size="24" />
					</template>
				</NcActionButton>
				<NcActionButton
					v-if="!screenReaderMode"
					:title="t('news', 'Close details')"
					@click="splitModeOff ? closeDetails() : clearSelected()">
					{{ t('news', 'Close details') }}
					<template #icon>
						<CloseIcon :size="24" />
					</template>
				</NcActionButton>
			</NcActions>
		</div>
		<div class="article">
			<div class="heading">
				<h1 :dir="item.rtl && 'rtl'">
					<a
						ref="titleLink"
						target="_blank"
						rel="noreferrer"
						:href="item.url"
						:title="item.title">
						{{ item.title }}
					</a>
				</h1>
				<time class="date" :title="formatDate(item.pubDate)" :datetime="formatDateISO(item.pubDate)">
					{{ formatDate(item.pubDate) }}
				</time>
			</div>

			<div class="subtitle" :dir="item.rtl && 'rtl'">
				<span v-if="!item.sharedBy" class="source">
					<a :href="feedUrl">
						{{ feed.title }}
						<img
							:src="feedIcon"
							alt="favicon"
							style="width: 16px">
					</a>
				</span>
				<span v-if="item.sharedBy">
					<span v-if="item.author">-</span>
					{{ t('news', 'shared by') }}
					{{ item.sharedByDisplayName }}
				</span>
				<span v-show="item.author !== undefined && item.author !== null && item.author.trim() !== ''" class="author">
					{{ t('news', 'by') }} {{ item.author }}
				</span>
			</div>

			<div v-if="getMediaType(item.enclosureMime) == 'audio'" class="enclosure audio">
				<button @click="playAudio(item)">
					{{ t('news', 'Play audio') }}
				</button>
				<a
					class="button"
					style="text-decoration: none;"
					:href="item.enclosureLink"
					target="_blank"
					rel="noreferrer">
					{{ t('news', 'Download audio') }}
				</a>
			</div>

			<div v-if="getMediaType(item.enclosureMime) == 'video'" class="enclosure video">
				<video
					controls
					preload="none"
					:src="item.enclosureLink"
					:type="item.enclosureMime"
					:poster="enclosureVideoThumbnail"
					@play="stopAudio()" />
				<div class="download">
					<a
						class="button"
						style="text-decoration: none;"
						:href="item.enclosureLink"
						target="_blank"
						rel="noreferrer">
						{{ t('news', 'Download video') }}
					</a>
				</div>
			</div>

			<div v-if="enclosureMediaImage && getMediaType(item.enclosureMime) == 'image' && !feed.fullTextEnabled" class="enclosure image">
				<div v-if="!showEnclosureImage" class="consent-banner">
					<button class="consent-button" @click="allowImage()">
						<span class="consent-title">{{ t('news', 'Show external media') + ' (image)' }}</span>
						<span class="consent-src" :title="enclosureMediaImage">{{ t('news', 'from') + ' ' + getDomainName(enclosureMediaImage) }}</span>
					</button>
				</div>
				<div v-else>
					<img
						:src="enclosureMediaImage"
						width="100%"
						height="auto">
				</div>
			</div>

			<div v-else-if="enclosureMediaThumbnail && getMediaType(item.enclosureMime) !== 'video'" class="enclosure thumbnail">
				<div v-if="!showEnclosureThumbnail" class="consent-banner">
					<button class="consent-button" @click="allowThumbnail()">
						<span class="consent-title">{{ t('news', 'Show external media') + ' (thumbnail)' }}</span>
						<span class="consent-src" :title="item.enclosureLink">{{ t('news', 'from') + ' ' + getDomainName(enclosureMediaThumbnail) }}</span>
					</button>
				</div>
				<div v-else>
					<a v-if="item.enclosureLink" :href="item.enclosureLink"><img :src="enclosureMediaThumbnail" alt=""></a>
					<img v-else :src="enclosureMediaThumbnail" alt="">
				</div>
			</div>

			<!-- eslint-disable vue/no-v-html -->
			<div v-if="item.mediaDescription" class="enclosure description" v-html="item.mediaDescription" />

			<div
				class="body"
				:dir="item.rtl && 'rtl'"
				@click="onConsentClick"
				v-html="sanitizedBody" />
			<!--eslint-enable-->

			<div v-if="item.categories?.length > 0" class="feed-item-categories">
				<NcChip
					v-for="category in item.categories"
					:key="category"
					:text="category"
					noClose
					variant="secondary" />
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import type { Feed } from '../../types/Feed.ts'
import type { FeedItem } from '../../types/FeedItem.ts'

import { generateUrl } from '@nextcloud/router'
import { useHotKey } from '@nextcloud/vue/composables/useHotKey'
import { useIsMobile } from '@nextcloud/vue/composables/useIsMobile'
import { defineComponent } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import NcChip from '@nextcloud/vue/components/NcChip'
import ChevronLeftIcon from 'vue-material-design-icons/ChevronLeft.vue'
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import CloseIcon from 'vue-material-design-icons/Close.vue'
import EyeIcon from 'vue-material-design-icons/Eye.vue'
import EyeCheckIcon from 'vue-material-design-icons/EyeCheck.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import ShareItem from '../ShareItem.vue'
import { DISPLAY_MODE, ITEM_HEIGHT, MEDIA_TYPE, SHOW_MEDIA, SPLIT_MODE } from '../../enums/index.ts'
import { ACTIONS, MUTATIONS } from '../../store/index.ts'
import { API_ROUTES } from '../../types/ApiRoutes.ts'
import { formatDate, formatDateISO } from '../../utils/dateUtils.ts'

export default defineComponent({
	name: 'FeedItemDisplay',
	components: {
		EyeCheckIcon,
		EyeIcon,
		CloseIcon,
		StarIcon,
		ShareVariant,
		NcActions,
		NcActionButton,
		NcChip,
		ShareItem,
		ChevronLeftIcon,
		ChevronRightIcon,
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
			required: false,
			default: null,
		},

		/**
		 * The index of the item in the current list
		 */
		itemIndex: {
			type: Number,
			required: false,
			default: null,
		},

		/**
		 * The name of the view e.g. all, unread, feed-10
		 */
		fetchKey: {
			type: String,
			required: true,
		},
	},

	emits: {
		selectItem: () => true,
		showDetails: () => true,
		prevItem: () => true,
		nextItem: () => true,
	},

	data: () => {
		return {
			isMobile: useIsMobile(),
			keepUnread: false,
			showShareMenu: false,
			allowEnclosureImage: false,
			allowEnclosureThumbnail: false,
		}
	},

	computed: {
		feed(): Feed {
			return this.$store.getters.feeds.find((feed: Feed) => feed.id === this.item.feedId) || {}
		},

		feedIcon() {
			return API_ROUTES.FAVICON + '/' + this.feed.urlHash
		},

		feedUrl() {
			return generateUrl('/apps/news/feed/' + this.feed.id)
		},

		screenReaderMode() {
			return this.$store.getters.displaymode === DISPLAY_MODE.SCREENREADER
		},

		splitModeOff() {
			return (this.$store.getters.splitmode === SPLIT_MODE.OFF || this.isMobile)
		},

		isSelected() {
			return this.$store.getters.selected === this.item
		},

		screenReaderItemHeight() {
			return this.screenReaderMode ? { height: ITEM_HEIGHT.DEFAULT + 'px' } : undefined
		},

		enclosureVideoThumbnail() {
			return this.mediaOptions[MEDIA_TYPE.THUMBNAILS] === SHOW_MEDIA.ALWAYS ? this.item.mediaThumbnail : null
		},

		showEnclosureThumbnail() {
			return this.mediaOptions[MEDIA_TYPE.THUMBNAILS] === SHOW_MEDIA.ALWAYS || this.allowEnclosureThumbnail
		},

		enclosureMediaThumbnail() {
			return this.mediaOptions[MEDIA_TYPE.THUMBNAILS] !== SHOW_MEDIA.NEVER && this.item.mediaThumbnail
		},

		showEnclosureImage() {
			return this.mediaOptions[MEDIA_TYPE.IMAGES] === SHOW_MEDIA.ALWAYS || this.allowEnclosureImage
		},

		enclosureMediaImage() {
			return this.mediaOptions[MEDIA_TYPE.IMAGES] !== SHOW_MEDIA.NEVER && this.item.enclosureLink
		},

		mediaOptions() {
			return this.$store.getters.mediaOptions
		},

		sanitizedBody() {
			if (!this.item.body) {
				return
			}

			const parser = new DOMParser()
			const doc = parser.parseFromString(this.item.body, 'text/html')

			doc.querySelectorAll('video').forEach((video) => {
				video.setAttribute('preload', 'none')
				if (this.mediaOptions[MEDIA_TYPE.THUMBNAILS] !== SHOW_MEDIA.ALWAYS) {
					video.removeAttribute('poster')
				}
			})

			doc.querySelectorAll('audio').forEach((audio) => {
				audio.setAttribute('preload', 'none')
			})

			if (this.mediaOptions[MEDIA_TYPE.IMAGES_BODY] !== SHOW_MEDIA.ALWAYS) {
				doc.querySelectorAll('picture').forEach((picture) => {
					if (this.mediaOptions[MEDIA_TYPE.IMAGES_BODY] === SHOW_MEDIA.NEVER) {
						picture.remove()
						return
					}
					picture.hidden = true
					const img = picture.querySelector('img')
					const source = picture.querySelector('source')
					const title = img?.getAttribute('alt') ?? img?.getAttribute('title')
					const url = img?.getAttribute('src') ?? source?.getAttribute('srcset') ?? 'unknown'

					this.modifyNode(picture, 'img')
					this.modifyNode(picture, 'source')

					this.createConsentButton(picture, url, title)
				})
			}

			if (this.mediaOptions[MEDIA_TYPE.IMAGES_BODY] !== SHOW_MEDIA.ALWAYS) {
				this.modifyNode(doc, 'img', this.mediaOptions[MEDIA_TYPE.IMAGES_BODY])
			}

			if (this.mediaOptions[MEDIA_TYPE.IFRAMES_BODY] !== SHOW_MEDIA.ALWAYS) {
				this.modifyNode(doc, 'iframe', this.mediaOptions[MEDIA_TYPE.IFRAMES_BODY])
			}

			return doc.body.innerHTML
		},

	},

	watch: {
		// Focus title link in article to emulate structural heading navigation
		// with screen readers
		async isSelected(newSelected) {
			if (newSelected && this.screenReaderMode) {
				await this.$nextTick()
				this.$refs.titleLink.focus()
			}
		},
	},

	created() {
		// create shortcuts
		if (this.splitModeOff && !this.screenReaderMode) {
			useHotKey('Escape', this.closeDetails)
		}
	},

	methods: {
		formatDate,
		formatDateISO,
		/**
		 * Sends message to state to clear the selectedId number
		 */
		clearSelected(): void {
			this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: undefined })
		},

		/**
		 * Select item when focused needed by screen reader navigation
		 */
		selectItemOnFocus(): void {
			if (this.screenReaderMode && !this.isSelected) {
				this.$store.commit(MUTATIONS.SET_SELECTED_ITEM, { id: this.item.id, key: this.fetchKey })
			}
		},

		/**
		 * Sends message to change the items starred property to the opposite value
		 */
		toggleStarred(): void {
			this.$store.dispatch(this.item.starred ? ACTIONS.UNSTAR_ITEM : ACTIONS.STAR_ITEM, { item: this.item })
		},

		toggleRead(): void {
			if (!this.item.keepUnread && this.item.unread) {
				this.$store.dispatch(ACTIONS.MARK_READ, { item: this.item })
			} else {
				this.$store.dispatch(ACTIONS.MARK_UNREAD, { item: this.item })
			}
		},

		closeShareMenu() {
			this.showShareMenu = false
		},

		getMediaType(mime: string): 'audio' | 'video' | 'image' | false {
			if (mime && mime.indexOf('audio') === 0) {
				return 'audio'
			} else if (mime && mime.indexOf('video') === 0) {
				return 'video'
			} else if (mime && mime.indexOf('image') === 0) {
				return 'image'
			}
			return false
		},

		playAudio(item: FeedItem) {
			this.$store.commit(MUTATIONS.SET_PLAYING_ITEM, item)
		},

		stopAudio() {
			const audioElements = document.getElementsByTagName('audio')

			for (let i = 0; i < audioElements.length; i++) {
				audioElements[i].pause()
			}
		},

		closeDetails() {
			this.$emit('showDetails')
		},

		prevItem() {
			this.$emit('prevItem')
		},

		nextItem() {
			this.$emit('nextItem')
		},

		allowThumbnail() {
			this.allowEnclosureThumbnail = true
		},

		allowImage() {
			this.allowEnclosureImage = true
		},

		getDomainName(url) {
			try {
				return new URL(url).hostname
			} catch {
				return 'invalid domain'
			}
		},

		modifyNode(doc, tagName, mode?) {
			doc.querySelectorAll(tagName).forEach((element) => {
				if (mode === SHOW_MEDIA.NEVER) {
					element.remove()
					return
				}
				const srcset = element.getAttribute('srcset')
				if (srcset) {
					element.dataset.srcset = srcset
					element.removeAttribute('srcset')
				}
				const src = element.getAttribute('src')
				if (src) {
					element.dataset.src = src
					element.removeAttribute('src')
				}
				element.hidden = true
				const url = src ?? srcset ?? 'unknown'
				const title = element.getAttribute('alt') ?? element.getAttribute('title')

				const parentNode = element.closest('picture')
				if (!parentNode) {
					this.createConsentButton(element, url, title)
				}
			})
		},

		onConsentClick(event) {
			const banner = event.target.closest('.consent-banner')
			if (!banner) {
				return
			}

			event.preventDefault()
			event.stopPropagation()

			const picture = banner.querySelector('picture')
			if (picture) {
				picture.hidden = false
				picture.querySelectorAll('source').forEach((source) => {
					if (source.dataset.srcset) {
						source.srcset = source.dataset.srcset
					}
				})
			}

			const img = banner.querySelector('img')
			if (img?.dataset.src) {
				img.src = img.dataset.src
				img.loading = 'lazy'
				img.decoding = 'async'
				img.hidden = false
			}
			if (img?.dataset.srcset) {
				img.srcset = img.dataset.srcset
			}

			const iframe = banner.querySelector('iframe')
			if (iframe?.dataset.src) {
				iframe.src = iframe.dataset.src
				iframe.hidden = false
			}

			const button = banner.querySelector('button')
			if (button) {
				button.remove()
			}
		},

		createConsentButton(element, src, description?) {
			const button = document.createElement('button')
			button.type = 'button'
			button.className = 'consent-button'

			const titleElement = document.createElement('span')
			titleElement.className = 'consent-title'
			titleElement.textContent = t('news', 'Show external media') + ' (' + element.localName + ')'
			button.appendChild(titleElement)

			const domain = this.getDomainName(src)
			const srcElement = document.createElement('span')
			srcElement.className = 'consent-src'
			srcElement.textContent = t('news', 'from') + ' ' + domain
			srcElement.ariaLabel = t('news', 'External media loaded from') + ' ' + domain
			try {
				srcElement.title = new URL(src).href
			} catch {
				srcElement.title = 'invalid url'
			}

			button.appendChild(srcElement)

			if (description) {
				const descElement = document.createElement('span')
				descElement.className = 'consent-desc'
				descElement.textContent = description
				button.appendChild(descElement)
			}

			const banner = document.createElement('div')
			banner.className = 'consent-banner'
			banner.appendChild(button)
			element.before(banner)
			banner.append(button, element)
			const parentLink = element.closest('a')
			if (parentLink) {
				parentLink.style.textDecoration = 'none'
			}
		},

	},
})

</script>

<style lang="scss">
	.feed-item-display {
		display: flex;
		flex-direction: column;
	}

	.feed-item-display.screenreader {
		overflow: hidden;
	}

	.article {
		padding: 0 50px 50px 50px;
		width: 100%;
		height: 100%;
		max-width: 1024px;
		margin-inline: auto;
		margin-inline-end: auto;
	}

	.article video {
		width: 100%;
		background-size: cover;
	}

	.article .enclosure.video {
		display: flex;
		flex-direction: column;
	}

	.article .enclosure.video .download {
		justify-content: center;
		display: flex;
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

	.article .body ul {
		margin: 7px 0;
		padding-inline-start: 14px;
		list-style-type: disc;
	}

	.article .body ul li {
		cursor: default;
		line-height: 21px;
	}

	.article .body p {
		line-height: 1.5;
		margin: 7px 0 14px 0;
	}

	.article .body blockquote {
		border-inline-start: 2px solid var(--color-border-dark);
		padding-inline-start: 10px;
		font-style: italic;
		margin: 0;
	}

	.article .body code {
		background: var(--color-background-hover);
		font-family: monospace;
		padding: 0.2em 0.4em;
		border-radius: 4px;
	}

	.article .body pre {
		background: var(--color-background-hover);
		padding: 1em;
		border-radius: 4px;
		max-width: 100%;
		overflow-x: auto;
	}

	.article .body pre code {
		font-family: monospace;
		color: var(--color-text-lighter);
	}

	.article .subtitle {
		color: var(--color-text-lighter);
		display: flex;
		font-size: 15px;
		gap: 15px;
		padding: 25px 0;
	}

	.article .author {
		color: var(--color-text-lighter);
		font-size: 15px;
	}

	.article img {
		max-width: 100%;
		height: auto;
	}

	.article h1 {
		font-weight: bold;
		font-size: 17px;
	}

	.article table {
		white-space: unset;
	}

	.action-bar {
		position: sticky;
		top: 0;
		z-index: 10;
		background: var(--color-main-background);
		padding: 10px 20px 0px 20px;
		display: flex;
		justify-content: right;
	}

	.action-bar-nav {
		flex-grow: 1;
	}

	.nav-icons .nav-button {
		position: absolute;
		top: 50%;
	}

	.nav-icons .nav-button.left {
		inset-inline-start: 1rem;
	}

	.nav-icons .nav-button.right {
		inset-inline-end: 1rem;
	}

	.feed-item-display .action-bar .button-vue,
	.feed-item-display .action-bar .button-vue .button-vue__wrapper
	{
		width: 30px !important;
		min-width: 30px;
		min-height: 30px;
		height: 30px;
	}

	.feed-item-categories {
		display: flex;
		flex-wrap: wrap;
		gap: var(--default-grid-baseline);
		margin-top: var(--default-grid-baseline);
	}

	.consent-button {
		align-items: flex-start;
		display: flex;
		flex-direction: column;
		gap: 4px;
		text-align: start;
		width: 100% !important;
	}

	.consent-title {
		font-size: 1rem;
		font-weight: 700;
		white-space: nowrap;
	}

	.consent-src,
	.consent-desc {
		color: var(--color-text-lighter);
		font-size: 0.85rem;
	}

	.consent-desc {
		border-top: 1px solid var(--color-border-dark);
		padding-top: 6px;
		margin-top: 4px;
		width: 100%;
	}

</style>
