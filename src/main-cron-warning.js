// Send error to Vuex State for displaying in Vue Application
window.store.commit('SET_ERROR', {
	toString: () => t('news', 'Ajax or webcron mode detected! Your feeds will not be updated!'),
	links: [{
		url: 'https://docs.nextcloud.org/server/latest/admin_manual/configuration_server/background_jobs_configuration.html#cron',
		text: t('news', 'How to set up the operating system cron'),
	}, {
		url: 'https://github.com/nextcloud/news-updater',
		text: t('news', 'Install and set up a faster parallel updater that uses the News app\'s update API'),
	}],
})
