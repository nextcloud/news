<template>
    <AppNavigation>
        <AddFeed v-if="showAddFeed" @close="closeShowAddFeed()"></AddFeed>
        <AppNavigationNew
            :text="t('news','Subscribe')"
            button-id="new-feed-button"
            button-class="icon-add"
            @click="showShowAddFeed()"/>

        <ul id="locations" class="with-icon">

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

            <AppNavigationItem v-for="folder in folders" :title="folder.name" icon="icon-folder" :allowCollapse="true">
                <template #default>
                    <AppNavigationItem v-for="feed in folder.feeds" :title="feed.title">
                        <ActionButton icon="icon-checkmark" @click="alert('Mark read')">
                            {{ t('news', 'Mark read') }}
                        </ActionButton>
                        <ActionButton icon="icon-pinned" @click="alert('Rename')">
                            {{ t('news', 'Unpin from top') }}
                        </ActionButton>
                        <ActionButton icon="icon-delete" @click="deleteFolder(folder)">
                            {{ t('news', 'Newest first') }}
                        </ActionButton>
                    </AppNavigationItem>
                </template>
                <template #counter v-if="folder.feedCount > 0">
                    <CounterBubble>{{ folder.feedCount }}</CounterBubble>
                </template>
                <template #actions>
                    <ActionButton icon="icon-checkmark" @click="alert('Mark read')">
                        {{ t('news', 'Mark read') }}
                    </ActionButton>
                    <ActionButton icon="icon-rename" @click="alert('Rename')">
                        {{ t('news', 'Rename') }}
                    </ActionButton>
                    <ActionButton icon="icon-delete" @click="deleteFolder(folder)">
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
        </ul>
    </AppNavigation>
</template>

<script>
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNewItem from '@nextcloud/vue/dist/Components/AppNavigationNewItem'
import AppNavigationCounter from '@nextcloud/vue/dist/Components/AppNavigationCounter'
import CounterBubble from '@nextcloud/vue/dist/Components/CounterBubble'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AddFeed from "./AddFeed";

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
    props: {
        showAddFeed: false,
    },
    computed: {
        folders() {
            return this.$store.state.folders
        },
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
    created() {
    }
}
</script>
