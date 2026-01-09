import { generateUrl } from '@nextcloud/router'
import { createRouter, createWebHistory } from 'vue-router'
import AllPanel from '../components/routes/All.vue'
import ExplorePanel from '../components/routes/Explore.vue'
import FeedPanel from '../components/routes/Feed.vue'
import FolderPanel from '../components/routes/Folder.vue'
import ItemPanel from '../components/routes/Item.vue'
import RecentPanel from '../components/routes/Recent.vue'
import StarredPanel from '../components/routes/Starred.vue'
import UnreadPanel from '../components/routes/Unread.vue'
import store from './../store/app.ts'

const base = generateUrl('/apps/news')

export const ROUTES = {
	EXPLORE: 'explore',
	STARRED: 'starred',
	UNREAD: 'unread',
	FEED: 'feed',
	FOLDER: 'folder',
	ITEM: 'item',
	ALL: 'all',
	RECENT: 'recent',
}

/**
 *
 */
function getInitialRoute() {
	const params: { feedId?: string, folderId?: string } = {}

	switch (store.state.lastViewedFeedType) {
		case '0':
			params.feedId = store.state.lastViewedFeedId
			return {
				name: ROUTES.FEED,
				params,
			}
		case '1':
			params.folderId = store.state.lastViewedFeedId
			return {
				name: ROUTES.FOLDER,
				params,
			}
		case '2':
			return { name: ROUTES.STARRED }
		case '3':
			return { name: ROUTES.ALL }
		case '5':
			return { name: ROUTES.EXPLORE }
		default:
			return { name: ROUTES.UNREAD }
	}
}

const routes = [
	{ path: '/', redirect: getInitialRoute() },
	{
		name: ROUTES.EXPLORE,
		path: '/explore',
		component: ExplorePanel,
		props: true,
	},
	{
		name: ROUTES.STARRED,
		path: '/starred/:starredFeedId?',
		component: StarredPanel,
		props: true,
	},
	{
		name: ROUTES.UNREAD,
		path: '/unread',
		component: UnreadPanel,
		props: true,
	},
	{
		name: ROUTES.FEED,
		path: '/feed/:feedId',
		component: FeedPanel,
		props: true,
	},
	{
		name: ROUTES.FOLDER,
		path: '/folder/:folderId',
		component: FolderPanel,
		props: true,
	},
	{
		name: ROUTES.ITEM,
		path: '/item/:itemId',
		component: ItemPanel,
		props: true,
	},
	{
		name: ROUTES.ALL,
		path: '/all',
		component: AllPanel,
		props: true,
	},
	{
		name: ROUTES.RECENT,
		path: '/recent',
		component: RecentPanel,
		props: true,
	},
]

const router = createRouter({
	history: createWebHistory(base),
	linkActiveClass: 'active',
	routes,
})

export default router
