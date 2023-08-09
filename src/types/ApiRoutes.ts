import { generateUrl } from '@nextcloud/router'

export const API_ROUTES = {
	FOLDER: generateUrl('/apps/news/folders'),
	FEED: generateUrl('/apps/news/feeds'),
}
