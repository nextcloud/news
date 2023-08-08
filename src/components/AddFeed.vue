<template>
  <NcModal @close="$emit('close')">
    <div id="new-feed">
      <form name="feedform">
        <fieldset style="padding: 16px">
          <input type="text"
            v-model="feedUrl"
            :placeholder="t('news', 'Web address')"
            :class="{ 'invalid': feedUrlExists() }"
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
              :options="folders"
              :placeholder="'-- ' + t('news', 'No folder') + ' --'"
              track-by="id"
              label="name"
              style="flex-grow: 1;" />

            <!-- add a folder -->
            <input v-if="createNewFolder"
              type="text"
              v-model="newFolderName"
              :class="{ 'invalid': folderNameExists() }"
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
                v-model="feedUser"
                :placeholder="t('news', 'Username')"
                name="user"
                autofocus
                style="flex-grow: 1">

              <input type="password"
                v-model="feedPassword"
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
import { ACTIONS } from '../store'

type AddFeedState = {
  folder?: Folder;
  newFolderName: String;

  autoDiscover: boolean;
  createNewFolder: boolean;
  withBasicAuth: boolean;

  feedUrl?: String;
  feedUser?: String;
  feedPassword?: String;
};

export default Vue.extend({
  components: {
    NcModal,
    NcCheckboxRadioSwitch,
    NcButton,
    NcMultiselect,
    NcSelect
  },
  data: (): AddFeedState => {
    return {
      folder: undefined,
      newFolderName: '',

      autoDiscover: true,
      createNewFolder: false,
      withBasicAuth: false,

      feedUrl: '',
      feedUser: '',
      feedPassword: ''
    }
  },
  computed: {
    folders(): Folder[] {
      return this.$store.state.folders.folders
    },
    disableAddFeed(): boolean {
        return this.feed === "" || this.feedUrlExists() || (this.createNewFolder && this.newFolderName === "" || this.folderNameExists())
    }
  },
  methods: {
    /**
     * Adds a New Feed via the Vuex Store
     */
    async addFeed() {
      this.$store.dispatch(ACTIONS.ADD_FEED, {
        feedReq: {
          url: this.feedUrl,
          folder: this.createNewFolder ? { name: this.newFolderName } : this.folder,
          autoDiscover: this.autoDiscover,
          user: this.feedUser === '' ? undefined : this.feedUser,
          password: this.feedPassword === '' ? undefined : this.feedPassword
        },
      });

      this.$emit('close');
    },
    /**
     * Checks if Feed Url exists in Vuex Store Feeds
     */
    feedUrlExists(): boolean {
      for (let feed of this.$store.state.feeds.feeds) {
        if (feed.url === this.feedUrl) {
          return true;
        }
      }

      return false;
    },
    /**
     * Check if Folder Name exists in Vuex Store Folders
     */
    folderNameExists(): boolean {
      if (this.createNewFolder) {
        for (let folder of this.$store.state.folders.folders) {
          if (folder.name === this.newFolderName) {
            return true;
          }
        }
      }
      return false;
    }
  },
})

</script>

<style scoped>
.invalid {
  border: 1px solid rgb(251, 72, 72) !important;
}
</style>
