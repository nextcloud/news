import type { FEED_ORDER, FEED_UPDATE_MODE } from '../enums/index.ts'

export type Feed = {
	folderId?: number
	unreadCount: number
	starredCount: number
	url: string
	title?: string
	autoDiscover?: boolean
	faviconLink?: string
	id?: number
	pinned: boolean
	preventUpdate: boolean
	ordering: FEED_ORDER
	fullTextEnabled: boolean
	updateMode: FEED_UPDATE_MODE
	updateErrorCount: number
	lastUpdateError: string
	location: string
}
