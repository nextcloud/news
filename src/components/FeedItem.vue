<template>
	<div style="padding: 5px 10px; display: flex;" @click="expand()">
		<div style="padding: 0px 5px;">
			<EarthIcon />
		</div>
		<div style="flex-grow: 1; overflow: hidden; text-overflow: ellipsis;">
			<span style="text-overflow: ellipsis;" :style="{ 'white-space': !isExpanded ? 'nowrap' : 'normal' }">
				{{ item.title }}
			</span>
		</div>
		<div class="button-container" style="display: flex; flex-direction: row;">
			<StarIcon />
			<EyeOutline />
			<ShareVariant />
		</div>
	</div>
</template>

<script>
import EarthIcon from 'vue-material-design-icons/Earth.vue'
import StarIcon from 'vue-material-design-icons/Star.vue'
import EyeOutline from 'vue-material-design-icons/EyeOutline.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'

export default {
	name: 'FeedItem',
	components: {
		EarthIcon,
		StarIcon,
		EyeOutline,
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
