<template>
	<NcModal @close="$emit('close')">
		<div id="new-feed" news-add-feed="Navigation.feed">
			<form ng-submit="Navigation.createFeed(Navigation.feed)"
				ng-init="Navigation.feed.autoDiscover=true"
				name="feedform">
				<fieldset ng-disabled="Navigation.addingFeed" style="padding: 16px">
					<input type="text"
						v-model="feedUrl"
						:placeholder="t('news', 'Web address')"
						name="address"
						pattern="[^\s]+"
						required
						autofocus
                        style="width: 90%;">

					<p class="error" v-if="feedUrlExists()">
						{{ t("news", "Feed exists already!") }}
					</p>
                    

					<!-- select a folder -->
                    <div style="display:flex;">
                        <NcSelect v-if="!createNewFolder && folders"
                            v-model="folder"
                            :options="folders.folders"
                            :placeholder="'-- ' + t('news', 'No folder') + ' --'"
                            track-by="id"
                            label="name"
                            style="flex-grow: 1;" />

                        <!-- add a folder -->
                        <input v-if="createNewFolder"
                            type="text"
                            v-model="newFolderName"
                            ng-class="{'ng-invalid':
                                                            !Navigation.addingFeed &&
                                                            !Navigation.addingFeed &&
                                                            Navigation.showNewFolder &&
                                                            Navigation.folderNameExists(Navigation.feed.newFolder)
                                                }"
                            :placeholder="t('news', 'Folder name')"
                            name="folderName"
                            style="flex-grow: 1; padding: 22px 12px; margin: 0px;"
                            required>

                        <NcCheckboxRadioSwitch :checked.sync="createNewFolder" type="switch">
                            {{ t("news", "New folder") }}?
                        </NcCheckboxRadioSwitch>
                    </div>

                    <p class="error"
                        v-if="folderNameExists()">
                        {{ t("news", "Folder exists already!") }}
                    </p>

					<!-- basic auth -->
                    <p v-if="withBasicAuth" class="warning">
                        {{
                            t(
                                "news",
                                "HTTP Basic Auth credentials must be stored unencrypted! Everyone with access to the server or database will be able to access them!"
                            )
                        }}
                    </p>

                    <div style="display: flex">
                        <NcCheckboxRadioSwitch :checked.sync="withBasicAuth" type="switch" style="flex-grow: 1;">
                            {{ t("news", "Credentials") }}?
                        </NcCheckboxRadioSwitch>

                        <div v-if="withBasicAuth" class="add-feed-basicauth" style="flex-grow: 1;  display: flex;">
                            <input type="text"
                                ng-model="Navigation.feed.user"
                                :placeholder="t('news', 'Username')"
                                name="user"
                                autofocus
                                style="flex-grow: 1">

                            <input type="password"
                                ng-model="Navigation.feed.password"
                                :placeholder="t('news', 'Password')"
                                name="password"
                                autocomplete="new-password"
                                style="flex-grow: 1">
                        </div>
                    </div>

					<NcCheckboxRadioSwitch :checked.sync="autoDiscover" type="switch">
						{{ t("news", "Auto discover Feed") }}?
					</NcCheckboxRadioSwitch>

					<NcButton :wide="true"
						type="primary"
						:disabled="disableAddFeed"
						@click="addFeed()">
						{{ t("news", "Subscribe") }}
					</NcButton>
				</fieldset>
			</form>
		</div>
	</NcModal>
</template>

<script lang="ts">

import Vue from 'vue'

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcMultiselect from '@nextcloud/vue/dist/Components/NcMultiselect.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'

import { Folder } from '../types/Folder'
import { Feed } from '../types/Feed'
import { ACTIONS } from '../store'
import { mapState } from 'vuex'
import axios from 'axios'

type AddFeedState = {
	folder?: Folder;
    newFolderName: String;

	autoDiscover: boolean;
	createNewFolder: boolean;
	withBasicAuth: boolean;
    // feedUrlExists: boolean;

	// from props
	feedUrl?: String;
};

export default Vue.extend({
	components: {
		NcModal,
		NcCheckboxRadioSwitch,
		NcButton,
		NcMultiselect,
        NcSelect
	},
	// props: {
	// 	feed: {
	// 		type: String,
	// 		default: '',
	// 	},
	// },
	data: (): AddFeedState => {
		return {
			folder: undefined,
            newFolderName: '',

			autoDiscover: true,
			createNewFolder: false,
			withBasicAuth: false,

            feedUrl: ''
		}
	},
	computed: {
        // ...mapState(['folders']),
		folders(): Folder[] {
			return this.$store.state.folders
		},
        disableAddFeed(): boolean {
            return this.feed === "" || this.feedUrlExists() || (this.createNewFolder && this.newFolderName === "" || this.folderNameExists())
        }
	},
	methods: {
        created() {

        },

		async addFeed() {
            let url = this.feedUrl;

			this.$store.dispatch(ACTIONS.ADD_FEED, {
				feedReq: {
					url: this.feedUrl,
					folder: this.createNewFolder ? { name: this.newFolderName } : this.folder,
					autoDiscover: this.autoDiscover,
				},
			});
            this.$emit('close');
		},

        feedUrlExists(): boolean {
            // TODO: check feed url
            console.log(this.feedUrl);

            return false;
        },
        folderNameExists(): boolean {
            // TODO: check folder name
            console.log(this.newFolderName)
            return false;
        }
	},
})

</script>

<style scoped>
/* input {
    width: 100%
}

.multiselect {
    width: 100% 
}*/
</style>
