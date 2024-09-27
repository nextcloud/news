<template>
	<NcContent app-name="news">
		<div v-if="app.error" id="warning-box">
			<div>
				{{ app.error }}

				<ul v-for="link of app.error.links" :key="link.url">
					<li>
						<a :href="link.url"
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
				<audio controls
					autoplay
					:src="playingItem.enclosureLink"
					@play="stopVideo()" />
				<a class="button podcast-download"
					:title="t('news', 'Download')"
					:href="playingItem.enclosureLink"
					target="_blank"
					rel="noreferrer">{{ t('news', 'Download') }}</a>
				<button class="podcast-close"
					:title="t('news', 'Close')"
					@click="stopPlaying()">
					{{ t('news', 'Close') }}
				</button>
			</div>
		</div>
	</NcContent>
</template>

<script lang="ts">

import Vue from 'vue'
import { mapState } from 'vuex'
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'
import Sidebar from './components/Sidebar.vue'
import { ACTIONS, MUTATIONS } from './store'

export default Vue.extend({
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
	async created() {
		// fetch folders and feeds to build side bar
		await this.$store.dispatch(ACTIONS.FETCH_FOLDERS)
		await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
		// fetch starred to get starred count
		await this.$store.dispatch(ACTIONS.FETCH_STARRED)
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
    right: 35px;
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
