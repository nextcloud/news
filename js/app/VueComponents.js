var someComponent = require('../compiled_vue_components/component');
someVueComponent = Vue.component('SomeComponent', someComponent);
app.value('SomeComponent', someVueComponent);
