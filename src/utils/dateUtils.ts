import { formatRelativeTime } from '@nextcloud/l10n'
import moment from '@nextcloud/moment'

/**
 * Returns locale formatted date string
 *
 * @param epoch date value in epoch format
 * @return locale formatted date string
 */
export function formatDate(epoch: number) {
	return moment.unix(epoch).format('L, LTS') // e.g. "04/20/2025 18:12:21"
}

/**
 * Returns locale relative date string
 *
 * @param epoch date value in epoch format
 * @return locale relative date string
 */
export function formatDateRelative(epoch: number) {
	return epoch ? formatRelativeTime(epoch * 1000) : '' // e.g. "one hour ago"
}

/**
 * Returns ISO date string
 *
 * @param epoch date value in epoch format
 * @return ISO date string
 */
export function formatDateISO(epoch: number) {
	return moment.unix(epoch).toISOString() // ISO 8601 e.g. "2025-04-20T18:12:21Z"
}
