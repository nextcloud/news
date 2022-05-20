<template>
    <AppNavigation>
        <AddFeed v-if="showAddFeed" @close="closeShowAddFeed()"></AddFeed>
        <AppNavigationNew
            :text="t('news','Subscribe')"
            button-id="new-feed-button"
            button-class="icon-add"
            @click="showShowAddFeed()"/>

            <AppNavigationNewItem :title="t('news','New folder')"
                                  icon="icon-add-folder"
                                  @new-item="newFolder">
            </AppNavigationNewItem>

            <AppNavigationItem :title="t('news','Unread articles')" icon="icon-rss">
                <template #actions>
                    <ActionButton icon="icon-checkmark" @click="alert('Edit')">
                        t('news','Mark read')
                    </ActionButton>
                </template>
                <template #counter>
                    <CounterBubble>5</CounterBubble>
                </template>
            </AppNavigationItem>
            <AppNavigationItem :title="t('news','All articles')" icon="icon-rss">
                <template #actions>
                    <ActionButton icon="icon-checkmark" @click="alert('Edit')">
                        t('news','Mark read')
                    </ActionButton>
                </template>
            </AppNavigationItem>
            <AppNavigationItem :title="t('news','Starred')" icon="icon-starred">
                <template #counter>
                    <CounterBubble>35</CounterBubble>
                </template>
            </AppNavigationItem>

            <AppNavigationItem  v-for="item in topLevelNav" :key="(item.title !== undefined ? 'feed' : 'folder' )+item.id"
                                :icon="item.title !== undefined ? 'icon-rss' : 'icon-folder'"
                                :allowCollapse="item.title === undefined"
                                :title="item.title || item.name">
                <template #default>
                    <AppNavigationItem v-for="feed in item.feeds" :title="feed.title" :key="feed.id">
                        <template #icon>
                            <img :src="feed.faviconLink" v-if="feed.faviconLink" alt="feedIcon">
                            <div class="icon-rss"  v-if="!feed.faviconLink"></div>
                        </template>
                        <template #actions>
                            <ActionButton icon="icon-checkmark" @click="alert('Mark read')">
                                {{ t('news', 'Mark read') }}
                            </ActionButton>
                            <ActionButton icon="icon-pinned" @click="alert('Rename')">
                                {{ t('news', 'Unpin from top') }}
                            </ActionButton>
                            <ActionButton icon="icon-caret-dark" @click="deleteFolder(item)">
                                {{ t('news', 'Newest first') }}
                            </ActionButton>
                            <ActionButton icon="icon-caret-dark" @click="deleteFolder(item)">
                                {{ t('news', 'Oldest first') }}
                            </ActionButton>
                            <ActionButton icon="icon-caret-dark" @click="deleteFolder(item)">
                                {{ t('news', 'Default order') }}
                            </ActionButton>
                            <ActionButton icon="icon-full-text-disabled" @click="deleteFolder(item)">
                                {{ t('news', 'Enable full text') }}
                            </ActionButton>
                            <ActionButton icon="icon-full-text-enabled" @click="deleteFolder(item)">
                                {{ t('news', 'Disable full text') }}
                            </ActionButton>
                            <ActionButton icon="icon-updatemode-default" @click="deleteFolder(item)">
                                {{ t('news', 'Unread updated') }}
                            </ActionButton>
                            <ActionButton icon="icon-updatemode-unread" @click="deleteFolder(item)">
                                {{ t('news', 'Ignore updated') }}
                            </ActionButton>
                            <ActionButton icon="icon-icon-rss" @click="deleteFolder(item)">
                                {{ t('news', 'Open feed URL') }}
                            </ActionButton>
                            <ActionButton icon="icon-icon-rename" @click="deleteFolder(item)">
                                {{ t('news', 'Rename') }}
                            </ActionButton>
                            <ActionButton icon="icon-delete" @click="deleteFolder(item)">
                                {{ t('news', 'Delete') }}
                            </ActionButton>
                        </template>
                    </AppNavigationItem>
                </template>
                <template #counter v-if="item.feedCount > 0">
                    <CounterBubble>{{ item.feedCount }}</CounterBubble>
                </template>
                <template #actions>
                    <ActionButton icon="icon-checkmark" @click="alert('Mark read')">
                        {{ t('news', 'Mark read') }}
                    </ActionButton>
                    <ActionButton icon="icon-rename" @click="alert('Rename')">
                        {{ t('news', 'Rename') }}
                    </ActionButton>
                    <ActionButton icon="icon-delete" @click="deleteFolder(item)">
                        {{ t('news', 'Delete') }}
                    </ActionButton>
                </template>
            </AppNavigationItem>

            <AppNavigationItem :title="t('news','Explore')"
                               icon="icon-link"
                               :to="{ name: 'explore' }">
                <template #counter>
                    <CounterBubble>35</CounterBubble>
                </template>
            </AppNavigationItem>
	</AppNavigation>
</template>

<script>
import Vuex  from 'vuex'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNewItem from '@nextcloud/vue/dist/Components/AppNavigationNewItem'
import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import CounterBubble from '@nextcloud/vue/dist/Components/CounterBubble'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AddFeed from "./AddFeed";

// import { ROUTES } from '../routes.js'
import { ACTIONS } from '../store/index.js'


export default {
    components: {
        AppNavigation,
        AppNavigationNew,
        AppNavigationItem,
        AppNavigationNewItem,
        AppNavigationCounter,
        CounterBubble,
        ActionButton,
        AddFeed
    },
    data() {
        return {
            // ROUTES,
        }
    },
    props: {
        showAddFeed: false,
    },
    computed: {
        ... Vuex.mapState(['feeds', 'folders']),
        topLevelNav (state) {
            return state.feeds.filter((feed) => {
                return feed.folderId === undefined || feed.folderId === null;
            }).concat(state.folders)
        }
    },
    methods: {
        newFolder(value) {
            const folderName = value.trim();
            const folder = {name: folderName};
            this.$store.dispatch('addFolder', {folder})
        },
        deleteFolder(folder) {
            this.$store.dispatch('deleteFolder', {folder})
            window.location.reload(true);
        },
        showShowAddFeed() {
            this.showAddFeed = true;
        },
        closeShowAddFeed() {
            this.showAddFeed = false;
        }
    },
    async created() {
        await this.$store.dispatch(ACTIONS.FETCH_FOLDERS)
        await this.$store.dispatch(ACTIONS.FETCH_FEEDS)
    },
}
</script>
