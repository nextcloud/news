import { FEED_ORDER } from '../enums/index.ts'

/**
 * get sorting for the actual route including individual feed order
 *
 * @param store - The vuex store instance containing application state
 * @param fetchKey - A key used for the selected route
 * @return Sort order oldestFirst
 */
export function getOldestFirst(store, fetchKey: string): boolean {
	const feedOrdering = store.state.feeds?.ordering?.[fetchKey]
	if (!fetchKey.startsWith('feed-') || feedOrdering === FEED_ORDER.DEFAULT) {
		return store.getters.oldestFirst
	}
	return feedOrdering === FEED_ORDER.OLDEST
}

/**
 * filter out items that are already loaded but not in view range
 *
 * @param store - The vuex store instance containing application state
 * @param items - An array of feed items to be filtered
 * @param fetchKey - A key used for the selected route
 * @return The filtered array of feed items.
 */
export function outOfScopeFilter(store, items: FeedItem[], fetchKey: string): FeedItem[] {
	const lastItemLoaded = store.state.items.lastItemLoaded?.[fetchKey]
	const oldestFirst = getOldestFirst(store, fetchKey)

	if (!lastItemLoaded) {
		return items
	}
	return items.filter((item) => {
		return (oldestFirst ? lastItemLoaded >= item.id : lastItemLoaded <= item.id)
	})
}

/**
 * sort array of feed items
 *
 * @param items - An array of feed items to be sorted
 * @param oldestFirst - Direction of sorting
 * @return The sorted array of feed items
 */
export function sortedFeedItems(items: feedItem[], oldestFirst: boolean): FeedItem[] {
	return [...items].sort((a, b) => {
		return oldestFirst ? a.id - b.id : b.id - a.id
	})
}
