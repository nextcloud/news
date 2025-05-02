import type { Feed } from './Feed.ts'

export type Folder = {
	feeds: Feed[]
	feedCount: number
	updateErrorCount: number
	name: string
	id: number
	opened: boolean
}
