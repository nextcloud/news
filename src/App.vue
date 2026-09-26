<template>
	<NcContent appName="news">
		<div v-if="app.error" id="warning-box">
			<div>
				{{ app.error }}

				<ul v-for="link of app.error.links" :key="link.url">
					<li>
						<a
							:href="link.url"
							target="_blank"
							rel="noreferrer">
							{{ link.text }}
						</a>
					</li>
				</ul>
			</div>
			<div>
				<span style="cursor: pointer;padding: 10px;font-weight: bold;" @click="removeError()">X</span>
			</div>
		</div>
		<div id="news-app">
			<div id="content-display" :class="{ playing: playingItem }">
				<Sidebar />
				<RouterView />
			</div>
			<div v-if="playingItem" class="podcast">
				<audio
					controls
					autoplay
					:src="playingItem.enclosureLink"
					@play="stopVideo()" />
				<a
					class="button podcast-download"
					:title="t('news', 'Download')"
					:href="playingItem.enclosureLink"
					target="_blank"
					rel="noreferrer">{{ t('news', 'Download') }}</a>
				<button
					class="podcast-close"
					:title="t('news', 'Close')"
					@click="stopPlaying()">
					{{ t('news', 'Close') }}
				</button>
			</div>
		</div>
	</NcContent>
</template>

<script lang="ts">

import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import NcContent from '@nextcloud/vue/components/NcContent'
import Sidebar from './components/Sidebar.vue'
import { ACTIONS, MUTATIONS } from './store/index.ts'

const TOKEN_EXPIRED_RELOAD_KEY = 'news-token-expired-reload-timestamp'
const TOKEN_EXPIRED_RELOAD_COOLDOWN_MS = 10000

export default defineComponent({
	components: {
		NcContent,
		Sidebar,
	},

	computed: {
		playingItem() {
			return this.$store.state.items.playingItem
		},

		...mapState(['app']),
	},

	watch: {
		'app.error': {
			handler(error) {
				this.reloadPageIfNeeded(error)
			},
			immediate: true,
		},
	},

	async created() {
		// fetch folders and feeds to build side bar
		await this.$store.dispatch(ACTIONS.FETCH_FOLDERS)
		await this.$store.dispatch(ACTIONS.FETCH_FEEDS)

		this.$store.commit(MUTATIONS.SET_LOADING, { value: false })
	},

	methods: {
		stopPlaying() {
			this.$store.commit(MUTATIONS.SET_PLAYING_ITEM, undefined)
		},

		stopVideo() {
			const videoElements = document.getElementsByTagName('video')
			for (let i = 0; i < videoElements.length; i++) {
				videoElements[i].pause()
			}
		},

		removeError() {
			this.$store.commit(MUTATIONS.SET_ERROR, undefined)
		},

		reloadPageIfNeeded(error) {
			const message = typeof error === 'string'
				? error
				: error?.message ?? error?.response?.data?.message ?? String(error ?? '')

			if (!message || !message.toLowerCase().includes('token expired') && !message.toLowerCase().includes('app not enabled')) {
				return false
			}

			try {
				const now = Date.now()
				const lastReload = Number(window.sessionStorage.getItem(TOKEN_EXPIRED_RELOAD_KEY) ?? '0')

				if (Number.isFinite(lastReload) && now - lastReload < TOKEN_EXPIRED_RELOAD_COOLDOWN_MS) {
					return false
				}

				window.sessionStorage.setItem(TOKEN_EXPIRED_RELOAD_KEY, String(now))
				this.reloadPage()
				return true
			} catch (e) {
				console.warn('Failed to reload due to token expiration:', e)
				return false
			}
		},

		reloadPage() {
			window.location.reload()
		},
	},
})
</script>

<style>
	#news-app {
		display: flex;
		flex-direction: column;
		width: 100%;
	}

	#warning-box {
		position: absolute;
		inset-inline-end: 35px;
		top: 15px;
		z-index: 5000;
		padding: 5px 10px;
		background-color: var(--color-main-background);
		color: var(--color-main-text);
		box-shadow: 0 0 6px 0 var(--color-box-shadow);
		border-radius: var(--border-radius);
		display: flex;
	}

	#warning-box a {
		color: #3a84e4;
		text-decoration: underline;
		font-size: small;
	}

	#content-display {
		display: flex;
		flex-direction: row;
		flex: 1;
		min-height: 0;
	}

	.podcast {
		height: 40px;
		display: flex;
		background-color: #474747;
		width: 100%;
	}

	.podcast audio {
		flex-grow: 1;
		background-color: rgba(0,0,0,0);
		height: 40px;
	}

	.podcast .podcast-download {
		padding: 4px 10px;
		margin: 2px 6px;
	}

	.podcast .podcast-close {
		margin: 2px 6px;
	}
</style>
