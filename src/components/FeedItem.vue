<template>
	<div class="feed-item-container" @click="expand()">
		<div class="feed-item-row" style="display: flex; padding: 5px 10px;">
			<div style="padding: 0px 5px;">
				<EarthIcon />
			</div>
			<div style="flex-grow: 1; overflow: hidden; text-overflow: ellipsis;">
				<span style="text-overflow: ellipsis;" :style="{ 'white-space': !isExpanded ? 'nowrap' : 'normal' }">
					{{ item.title }}
				</span>
			</div>
			<div class="button-container" style="display: flex; flex-direction: row; align-self: start;">
				<StarIcon :class="{'starred': item.starred }" />
				<Eye />
				<ShareVariant />
			</div>
		</div>

		<div v-if="isExpanded" style="padding: 5px 10px;">
			<div class="feed-item-author" v-if="item.author != undefined" v-html="item.author" />
			<div v-html="item.body" />
		</div>
	</div>
</template>

<script>
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'

export default {
	name: 'FeedItem',
	components: {
		EarthIcon,
		StarIcon,
		Eye,
		ShareVariant,
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
	},
}

</script>

<style>
	.feed-item-container, .feed-item-container * {
		cursor: pointer;
	}

	.feed-item-container {
		border-bottom: 1px solid #222;
	}

	.feed-item-row:hover {
		background-color: #222;
	}

	.material-design-icon {
		color: #555555;
	}

	.material-design-icon:hover {
		color: var(--color-main-text);
	}

	.material-design-icon.starred {
		color: rgb(255, 204, 0);
	}

	.material-design-icon.starred:hover {
		color: #555555;
	}
</style>
