export class OC {

	generateUrl(url: any) {
		return ''
	}

	imagePath(app: any, img: any) {
		return ''
	}

	linkToRemote(app: any) {
		return ''
	}

	getLanguage() {
		return 'en-US'
	}

	getLocale() {
		return 'en_US'
	}

	isUserAdmin() {
		return false
	}

	L10N = {
		translate(app: any, text: any) {
			return text
		},

		translatePlural(app: any, text: any) {
			return text
		},
	}

	config = {}
}
