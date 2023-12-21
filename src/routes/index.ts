import VueRouter from 'vue-router'

import ExplorePanel from '../components/routes/Explore.vue'
import StarredPanel from '../components/routes/Starred.vue'
import UnreadPanel from '../components/routes/Unread.vue'
import FeedPanel from '../components/routes/Feed.vue'
import FolderPanel from '../components/routes/Folder.vue'
import AllPanel from '../components/routes/All.vue'

export const ROUTES = {
	EXPLORE: 'explore',
	STARRED: 'starred',
	UNREAD: 'unread',
	FEED: 'feed',
	FOLDER: 'folder',
	ALL: 'all',
}

const getInitialRoute = function() {
	// TODO: Fetch Recent route from Browser Session?
	return ROUTES.UNREAD
}

const routes = [
	// using
	// { path: '/collections/all', component: CollectionGeneral, alias: '/' },
	// instead of
	{ path: '/', redirect: getInitialRoute() },
	// would also be an option, but it currently does not work
	// reliably with router-link due to
	// https://github.com/vuejs/vue-router/issues/419
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
		name: ROUTES.ALL,
		path: '/all',
		component: AllPanel,
		props: true,
	},
]

export default new VueRouter({
	linkActiveClass: 'active',
	routes, // short for `routes: routes`
})
