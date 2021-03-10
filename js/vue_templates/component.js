/* jshint undef: false */
var someComponent = Vue.component('SomeComponent', {
  props: {
    oldestFirst: Boolean
  },
  render (h) {
    'use strict';
    return h('p', ' show newest first is ' + this.oldestFirst.toString());
  }
});

app.value('SomeComponent', someComponent);
