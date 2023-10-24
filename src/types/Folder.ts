import { Feed } from './Feed'

export type Folder = {
  feeds: Feed[];
  feedCount: number;
  name: string;
  id: number;
}
