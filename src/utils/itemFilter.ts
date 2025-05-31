import { FEED_ORDER } from '../enums/index.ts'
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
	const feedOrdering = store.state.feeds?.ordering?.[fetchKey]
	let oldestFirst = false

	if (!lastItemLoaded) {
		return items
	}

	/*
	 * feeds can have different sorting
	 */
	if (!fetchKey.startsWith('feed-') || feedOrdering === FEED_ORDER.DEFAULT) {
		oldestFirst = store.getters.oldestFirst
	} else if (feedOrdering === FEED_ORDER.OLDEST) {
		oldestFirst = true
	} else if (feedOrdering === FEED_ORDER.NEWEST) {
		oldestFirst = false
	}

	return items.filter((item) => {
		return (oldestFirst ? lastItemLoaded >= item.id : lastItemLoaded <= item.id)
	})
}
