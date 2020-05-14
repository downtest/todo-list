import Vue from 'vue'
import App from './App'

// Vue.config.productionTip = false;

import router from './router';
import store from './store';

Vue.directive('wheel', {
  // Когда привязанный элемент вставлен в DOM...
  inserted: function (el, binding) {
    el.addEventListener('wheel', function (evt) {
      if (binding.value(evt, el)) {
        el.removeEventListener('wheel', this)
      }
    })

    var startPosition, endPosition;

    el.addEventListener('touchstart', function (evt) {
      startPosition = evt.changedTouches[0].pageY
    })
    el.addEventListener('touchend', function (evt) {
      endPosition = evt.changedTouches[0].pageY
      evt.deltaY = endPosition - startPosition

      if (binding.value(evt, el)) {
        el.removeEventListener('touchend', this)
      }
    })
  }
})

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app')
