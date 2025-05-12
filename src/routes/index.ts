import VueRouter from 'vue-router'
import { generateUrl } from '@nextcloud/router'

import ExplorePanel from '../components/routes/Explore.vue'
import StarredPanel from '../components/routes/Starred.vue'
import UnreadPanel from '../components/routes/Unread.vue'
import FeedPanel from '../components/routes/Feed.vue'
import FolderPanel from '../components/routes/Folder.vue'
import AllPanel from '../components/routes/All.vue'

import store from './../store/app'

export const ROUTES = {
	EXPLORE: 'explore',
	STARRED: 'starred',
	STARREDFEED: 'starredfeed',
	UNREAD: 'unread',
	FEED: 'feed',
	FOLDER: 'folder',
	ALL: 'all',
}

const getInitialRoute = function() {
	const params: { feedId?: string; folderId?: string } = {}

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
		if (store.state.lastViewedFeedId) {
			params.feedId = store.state.lastViewedFeedId
			return {
				name: ROUTES.STARREDFEED,
				params,
			}
		}
		return {
			name: ROUTES.STARRED,
		}
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
		path: '/starred',
		component: StarredPanel,
		redirect: { name: 'starredItems' },
		props: true,
		children: [
			{
				name: 'starredItems',
				path: '',
			},
			{
				name: ROUTES.STARREDFEED,
				path: ':feedId',
			},
		],
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
		name: ROUTES.ALL,
		path: '/all',
		component: AllPanel,
		props: true,
	},
]

export default new VueRouter({
	mode: 'history',
	base: generateUrl('/apps/news'),
	linkActiveClass: 'active',
	routes,
})
