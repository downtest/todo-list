import { createApp } from 'vue'
import Demo from './Demo'
import List from './components/Demo/List'
import axios from 'axios'
import VueAxios from "vue-axios";

import store from './store';
import Item from "./components/List/Demo/Item";
import Nested from "./components/List/Demo/Nested";

const app = createApp(List)

app.component('item', Item)
app.component('nested', Nested)

app.use(store)
app.use(VueAxios, axios)


// effectively adding the router to every component instance
// app.config.globalProperties.$router = router

app.mount('#app')
