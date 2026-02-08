<template>
	<NcModal @close="$emit('close')">
		<div id="share-item">
			<form name="feedform">
				<fieldset>
					<input
						ref="nameInput"
						v-model="userName"
						type="text"
						:placeholder="t('news', 'User Name')"
						name="user"
						pattern="[^\s]+"
						required
						autofocus
						autocomplete="off"
						@keyup="debounceSearchUsers()">

					<div class="user-bubble-container">
						<NcLoadingIcon v-if="searching" />
						<NcUserBubble
							v-for="user in users"
							v-else-if="!searching"
							:key="user.shareName"
							:size="30"
							:displayName="user.displayName"
							:primary="selected.map((val) => { return val.shareName }).includes(user.shareName)"
							:user="user.shareName"
							@click="clickUser(user)" />
					</div>

					<NcButton
						:wide="true"
						variant="primary"
						:disabled="selected.length === 0"
						@click="share()">
						<template v-if="selected.length === 0">
							{{ t("news", "Share") }}
						</template>
						<template v-else-if="selected.length === 1">
							{{ t("news", "Share with") + ' ' + selected[0].displayName }}
						</template>
						<template v-else-if="selected.length > 1">
							{{ t("news", "Share with {num} users", { num: selected.length }) }}
						</template>
					</NcButton>
				</fieldset>
			</form>
		</div>
	</NcModal>
</template>

<script lang="ts">

import _ from 'lodash'
import { defineComponent } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcModal from '@nextcloud/vue/components/NcModal'
import NcUserBubble from '@nextcloud/vue/components/NcUserBubble'
import { ShareService } from '../dataservices/share.service'

type ShareUser = {
	shareName: string
	displayName: string
}

export default defineComponent({
	components: {
		NcModal,
		NcButton,
		NcUserBubble,
		NcLoadingIcon,
	},

	props: {
		/**
		 * The itemId from the item to share
		 */
		itemId: {
			type: Number,
			required: true,
		},
	},

	emits: {
		close: () => true,
	},

	data: () => {
		return {
			userName: '',
			users: [],
			selected: [],
			searching: false,
		} as {
			userName: string
			users: ShareUser[]
			selected: ShareUser[]
			searching: boolean
			debounceSearchUsers?: () => void
		}
	},

	created() {
		this.debounceSearchUsers = _.debounce(this.searchUsers, 800)
		this.$nextTick(() => this.$refs?.nameInput?.focus())
	},

	methods: {
		/**
		 * Adds or removes user to selected list
		 *
		 * @param user - User that was clicked
		 */
		clickUser(user: ShareUser) {
			const selectedUsers = this.selected.map((val: ShareUser) => {
				return val.shareName
			})
			if (selectedUsers.includes(user.shareName)) {
				this.selected.splice(selectedUsers.indexOf(user.shareName), 1)
			} else {
				this.selected.push(user)
			}
		},

		/**
		 * Searches for Users based on user input to display for sharing
		 */
		async searchUsers() {
			this.users = []
			this.searching = true
			const response = await ShareService.fetchUsers(this.userName)
			this.searching = false

			for (const user of response.data.ocs.data.users) {
				this.users.push({ displayName: user.label, shareName: user.value.shareWith })
			}
		},

		/**
		 * Shares an item with another use in the same nextcloud instance
		 */
		async share() {
			await ShareService.share(this.itemId, this.selected.map((val: ShareUser) => {
				return val.shareName
			}))

			this.$emit('close')
		},
	},
})

</script>

<style>
#share-item .user-bubble__content * {
	cursor: pointer;
}

#share-item fieldset {
	padding: 16px;
}

#share-item input {
	width: 90%;
}

#share-item .user-bubble-container {
	margin: 10px;
}

#share-item .user-bubble__wrapper {
	margin: 5px 10px;
}

</style>
