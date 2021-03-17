var someComponent = require('../vue_components/component.vue').default;
someVueComponent = Vue.component('SomeComponent', someComponent);
app.value('SomeComponent', someVueComponent);
