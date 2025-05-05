import moment from '@nextcloud/moment'

/**
 * Returns locale formatted date string
 *
 * @param {number} epoch date value in epoch format
 * @return {string} locale formatted date string
 */
export function formatDate(epoch: number) {
	return moment.unix(epoch).format('l, LTS') // e.g. "04/20/2025 18:12:21"
}

/**
 * Returns locale relative date string
 *
 * @param {number} epoch date value in epoch format
 * @return {string} locale relative date string
 */
export function formatDateRelative(epoch: number) {
	return moment.unix(epoch).fromNow() // e.g. "one hour ago"
}

/**
 * Returns ISO date string
 *
 * @param {number} epoch date value in epoch format
 * @return {string} ISO date string
 */
export function formatDateISO(epoch: number) {
	return moment.unix(epoch).toISOString() // ISO 8601 e.g. "2025-04-20T18:12:21Z"
}
