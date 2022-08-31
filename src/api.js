// SPDX-FileCopyrightText: 2022 Carl Schwan <carl@carlschwan.eu>
// SPDX-Licence-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
    folder: {
        async create(name) {
            const { data } = await axios.post(generateUrl('/apps/news/api/v2/folders'), {
                name,
            })
            return data.folders
        },
        async update(folderId, name) {
            await axios.patch(generateUrl('/apps/news/api/v2/folders/{folderId}', {
                folderId,
                name,
            }))
        },
        async delete(folderId) {
            await axios.post(generateUrl('/apps/news/api/v2/folders/{folderId}', {
                folderId,
            }))
        },
        async index() {
            const { data } = await axios.post(generateUrl('/apps/news/api/v1-3/folders'))
            return data.folders
        },
        async read(folderId, newestItemId = 0) {
            const { data } = await axios.post(generateUrl('/apps/news/api/v1-3/folders/{folderId}/read', {
                folderId,
            }, {
                newestItemId,
            }))
        },
    },
    feed: {
        async get() {
            const { data } = await axios.get(generateUrl('/apps/news/feeds'))
            return data.feeds
        }
        async add({ feedUrl, folderId }) {
			let url = feedUrl.trim()
			if (!url.startsWith('http')) {
				url = 'https://' + url
			}
            const { data } = await axios.post(generateUrl('/apps/news/feeds'), {
                folderId,
                url,
            })
        },
    },
}
