import { FEED_ORDER, FEED_UPDATE_MODE } from '../dataservices/feed.service'

export type Feed = {
  folderId?: number;
  unreadCount: number;
  url: string;
  title?: string;
  autoDiscover?: boolean;
  faviconLink?: string;
  id?: number;
  pinned: boolean;
  ordering: FEED_ORDER;
  fullTextEnabled: boolean;
  updateMode: FEED_UPDATE_MODE
}
