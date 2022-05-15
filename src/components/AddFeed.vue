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
                           :placeholder="t('news','Web address')"
                           name="address"
                           pattern="[^\s]+"
                           required
                           autofocus>

                    <p class="error"
                       ng-show="!Navigation.addingFeed &&
                        Navigation.feedUrlExists(Navigation.feed.url)">
                        {{ t('news', 'Feed exists already!') }}
                    </p>

                    <!-- select a folder -->
                    <select name="folder"
                            :title="t('news','Folder')"
                            v-if="!createNewFolder"
                            ng-model="Navigation.feed.existingFolder"
                            ng-options="folder.name for folder in
                        Navigation.getFolders() track by folder.name">
                        <option value=""
                        >-- {{ t('news', 'No folder') }} --
                        </option>
                    </select>
                    <button type="button"
                            class="icon-add add-new-folder-primary"
                            v-if="!createNewFolder"
                            :title="t('news','New folder')"
                            @click="newFolder()"
                            news-focus="#new-feed [name='folderName']"></button>

                    <!-- add a folder -->
                    <input type="text"
                           ng-model="Navigation.feed.newFolder"
                           ng-class="{'ng-invalid':
                            !Navigation.addingFeed &&
                            !Navigation.addingFeed &&
                            Navigation.showNewFolder &&
                            Navigation.folderNameExists(
                                Navigation.feed.newFolder
                            )
                        }"
                           :placeholder="t('news','Folder name')"
                           name="folderName"
                           v-if="createNewFolder"
                           style="width: 90%"
                           required>
                    <button type="button"
                            v-if="createNewFolder"
                            class="icon-close add-new-folder-primary"
                            :title="t('news','Go back')"
                            @click="abortNewFolder()"
                            ng-click="Navigation.showNewFolder=false;
                                  Navigation.feed.newFolder=''">
                    </button>


                    <p class="error" ng-show="!Navigation.addingFeed &&
                    Navigation.folderNameExists(Navigation.feed.newFolder)">
                        {{ t('news', 'Folder exists already!') }}
                    </p>

                    <!-- basic auth -->

                    <CheckboxRadioSwitch :checked.sync="withBasicAuth" type="switch">
                        {{ t('news', 'Credentials') }}?
                    </CheckboxRadioSwitch>

                    <div v-if="withBasicAuth" class="add-feed-basicauth">
                        <p class="warning">{{
                                t('news',
                                    'HTTP Basic Auth credentials must be stored unencrypted! Everyone with access to the server or database will be able to access them!')
                            }}></p>
                        <input type="text"
                               ng-model="Navigation.feed.user"
                               :placeholder="t('news','Username')"
                               name="user"
                               autofocus>

                        <input type="password"
                               ng-model="Navigation.feed.password"
                               :placeholder="t('news','Password')"
                               name="password" autocomplete="new-password">
                    </div>

                    <CheckboxRadioSwitch :checked.sync="autoDiscover" type="switch">
                        {{ t('news', 'Auto discover Feed') }}?
                    </CheckboxRadioSwitch>

                    <Button type="primary" ng-disabled="
                        Navigation.feedUrlExists(Navigation.feed.url) ||
                                (
                                    Navigation.showNewFolder &&
                                    Navigation.folderNameExists(folder.name)
                                )">
                        {{t('news','Subscribe')}}
                    </Button>
                </fieldset>
            </form>
        </div>
    </Modal>
</template>

<script>
import Modal from '@nextcloud/vue/dist/Components/Modal'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import Button from '@nextcloud/vue/dist/Components/Button'

export default {
    components: {
        Modal,
        CheckboxRadioSwitch,
        Button
    },
    methods: {
        newFolder() {
            this.createNewFolder = true;
        },
        abortNewFolder() {
            this.createNewFolder = false;
        }
    },
    props: {
        feed: '',
        autoDiscover: true,
        withBasicAuth: false,
        createNewFolder: false
    },
    emits: ['close']
}
</script>

<style scoped>

    .button-vue {
        width: 100%;
    }
    input {
        width: 100%;
    }
    select {
        width: 90%;
    }

</style>
