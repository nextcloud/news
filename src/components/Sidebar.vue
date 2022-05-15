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

            <!-- Ordner... -->

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
    methods: {
        newFolder(value) {
            alert(value)
        },
        showShowAddFeed() {
            this.showAddFeed = true;
        },
        closeShowAddFeed() {
            this.showAddFeed = false;
        }
    },
}
</script>
