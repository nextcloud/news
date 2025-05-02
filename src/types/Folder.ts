import type { Feed } from './Feed'

export type Folder = {
	feeds: Feed[]
	feedCount: number
	updateErrorCount: number
	name: string
	id: number
	opened: boolean
}
