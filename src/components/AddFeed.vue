<template>
    <Modal @close="$emit('close')">
        <div id="new-feed" news-add-feed="Navigation.feed">
            <form ng-submit="Navigation.createFeed(Navigation.feed)"
                ng-init="Navigation.feed.autoDiscover=true"
                name="feedform">
                <fieldset ng-disabled="Navigation.addingFeed" style="padding: 16px">
                    <input type="text"
                        :value="feed"
                        ng-model="Navigation.feed.url"
                        ng-class="{'ng-invalid':
                        !Navigation.addingFeed &&
                        Navigation.feedUrlExists(Navigation.feed.url)
                    }"
                        :placeholder="t('news', 'Web address')"
                        name="address"
                        pattern="[^\s]+"
                        required
                        autofocus />

                    <p class="error"
                        ng-show="!Navigation.addingFeed &&
                        Navigation.feedUrlExists(Navigation.feed.url)">
                        {{ t("news", "Feed exists already!") }}
                    </p>

                    <!-- select a folder -->
                    <CheckboxRadioSwitch :checked.sync="createNewFolder" type="switch">
                        {{ t("news", "New folder") }}?
                    </CheckboxRadioSwitch>

                    <Multiselect v-if="!createNewFolder"
                        v-model="folder"
                        :options="folders"
                        track-by="id"
                        label="name" />

                    <!-- add a folder -->
                    <input v-if="createNewFolder"
                        type="text"
                        ng-model="Navigation.feed.newFolder"
                        ng-class="{'ng-invalid':
                            !Navigation.addingFeed &&
                            !Navigation.addingFeed &&
                            Navigation.showNewFolder &&
                            Navigation.folderNameExists(
                                Navigation.feed.newFolder
                            )
                        }"
                        :placeholder="t('news', 'Folder name')"
                        name="folderName"
                        style="width: 90%"
                        required />

                    <p class="error"
                        ng-show="!Navigation.addingFeed &&
                    Navigation.folderNameExists(Navigation.feed.newFolder)">
                        {{ t("news", "Folder exists already!") }}
                    </p>

                    <!-- basic auth -->

                    <CheckboxRadioSwitch :checked.sync="withBasicAuth" type="switch">
                        {{ t("news", "Credentials") }}?
                    </CheckboxRadioSwitch>

                    <div v-if="withBasicAuth" class="add-feed-basicauth">
                        <p class="warning">
                            {{
                                t(
                                    "news",
                                    "HTTP Basic Auth credentials must be stored unencrypted! Everyone with access to the server or database will be able to access them!"
                                )
                            }}>
                        </p>
                        <input type="text"
                            ng-model="Navigation.feed.user"
                            :placeholder="t('news', 'Username')"
                            name="user"
                            autofocus />

                        <input type="password"
                            ng-model="Navigation.feed.password"
                            :placeholder="t('news', 'Password')"
                            name="password"
                            autocomplete="new-password" />
                    </div>

                    <CheckboxRadioSwitch :checked.sync="autoDiscover" type="switch">
                        {{ t("news", "Auto discover Feed") }}?
                    </CheckboxRadioSwitch>

                    <Button :wide="true"
                        type="primary"
                        ng-disabled="
                        Navigation.feedUrlExists(Navigation.feed.url) ||
                                (
                                    Navigation.showNewFolder &&
                                    Navigation.folderNameExists(folder.name)
                                )"
                        @click="addFeed()">
                        {{ t("news", "Subscribe") }}
                    </Button>
                </fieldset>
            </form>
        </div>
    </Modal>
</template>

<script>
/* eslint-disable vue/require-prop-types */
/* eslint-disable vue/require-prop-type-constructor */
/* eslint-disable vue/require-default-prop */
/* eslint-disable vue/no-mutating-props */

import Modal from '@nextcloud/vue/dist/Components/Modal'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import Button from '@nextcloud/vue/dist/Components/Button'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'

export default {
    components: {
        Modal,
        CheckboxRadioSwitch,
        Button,
        Multiselect,
    },
    props: {
        feed: '',
    },
    emits: ['close'],
    data() {
        return {
            folder: {},
            autoDiscover: true,
            createNewFolder: false,
            withBasicAuth: false,
        }
    },
    computed: {
        folders() {
            return this.$store.state.folders
        },
    },
    methods: {
        newFolder() {
            this.createNewFolder = true
        },
        abortNewFolder() {
            this.createNewFolder = false
        },
        addFeed() {
            this.$store.dispatch('addFeed', {
                feedReq: {
                    url: this.feed,
                    folder: this.folder,
                    autoDiscover: true,
                },
            })
        },
    },
}
</script>

<style scoped>
input {
    width: 100%
}

.multiselect {
    width: 100%
}
</style>
