import Vue from 'vue'
import App from './App'

// Vue.config.productionTip = false;

import router from './router';
import store from './store';

Vue.directive('wheel', {
  // Когда привязанный элемент вставлен в DOM...
  inserted: function (el, binding) {
    let f = function (evt) {
      if (binding.value(evt, el)) {
        el.removeEventListener('wheel', f)
      }
    }
    el.addEventListener('wheel', f)
  }
})

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app')
