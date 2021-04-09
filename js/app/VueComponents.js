var someComponent = require('../vue_components/component.vue').default;
someVueComponent = Vue.component('SomeComponent', someComponent);
app.value('SomeComponent', someVueComponent);

var IconLinkCompact = require('../vue_components/IconLinkCompact.vue').default;
var iconLinkCompactComponent = Vue.component('IconLinkCompact', IconLinkCompact);
app.value('IconLinkCompact', iconLinkCompactComponent);
