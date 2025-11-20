/**
 * updates unread cache
 *
 * @param newItems latest unread items from store
 * @param unreadCache cache array to update
 */
export function updateUnreadCache(newItems, unreadCache) {
	const cachedItemIds = new Set(unreadCache.map((item) => item.id))

	for (const item of newItems) {
		if (!cachedItemIds.has(item.id)) {
			unreadCache.push(item)
		}
	}
}
