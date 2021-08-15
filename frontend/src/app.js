import { createApp } from 'vue'
import App from './App'
import axios from 'axios'
import VueAxios from "vue-axios";
import Item from "./components/List/Item"
import Nested from './components/List/Nested'
import moment from 'moment';

// Vue.config.productionTip = false;

import router from './router';
import store from './store';

// Vue.directive('wheel', {
//   // Когда привязанный элемент вставлен в DOM...
//   inserted: function (el, binding) {
//     el.addEventListener('wheel', function (evt) {
//       evt.preventDefault()
//
//       if (binding.value(evt, el)) {
//         el.removeEventListener('wheel', this)
//       }
//     })
//
//     var startPosition, endPosition;
//
//     el.addEventListener('touchstart', function (evt) {
//       evt.preventDefault()
//
//       startPosition = evt.changedTouches[0].pageY
//     })
//     el.addEventListener('touchend', function (evt) {
//       evt.preventDefault()
//
//       endPosition = evt.changedTouches[0].pageY
//       evt.deltaY = endPosition - startPosition
//
//       if (binding.value(evt, el)) {
//         el.removeEventListener('touchend', this)
//       }
//     })
//   }
// })

const app = createApp(App)

app.component('item', Item)
app.component('nested', Nested)

app.use(store)
app.use(router)

app.use(VueAxios, axios)

app.config.globalProperties.$moment=moment

// effectively adding the router to every component instance
// app.config.globalProperties.$router = router

app.mount('#app')
