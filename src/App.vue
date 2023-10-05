<template>
	<NcContent app-name="news">
		<div id="news-app">
			<div id="content-display" :class="{ playing: playingItem }">
				<Sidebar />
				<NcAppContent>
					<RouterView />
				</NcAppContent>
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
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import Sidebar from './components/Sidebar.vue'
import { ACTIONS, MUTATIONS } from './store'

export default Vue.extend({
	components: {
		NcContent,
		Sidebar,
		NcAppContent,
	},
	computed: {
		playingItem() {
			return this.$store.state.items.playingItem
		},
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
	},
})
</script>

<style>
	.material-design-icon {
		color: var(--color-text-lighter)
	}

	#news-app {
		display: flex;
		flex-direction: column;
		width: 100%;
	}

	.route-container {
		height: 100%;
	}

	#content-display {
		display: flex;
		flex-direction: row;
		height: 100%;
	}

	#content-display.playing {
		height: calc(100vh - 98px)
	}

	.podcast {
		position: absolute;
		bottom: 0px;
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
