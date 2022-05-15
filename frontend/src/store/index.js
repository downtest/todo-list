import { createStore } from 'vuex'
import collections from './modules/Collections'
import counter from './modules/Counter'
import user from './modules/User'
import todos from './modules/Todos'

import icons from './modules/Icons'
import popupNotices from './modules/PopupNotices'

import range from './tools/range'

import axios from "axios";

const debug = process.env.NODE_ENV !== 'production'

const store = createStore({
    modules: {
        collections,
        counter,
        user,
        todos,
        icons,
        popupNotices,
        range,
    },
    strict: debug,
    plugins: [],
})

store.axios = axios.create({
    baseURL: process.env.VUE_APP_BACKEND_HOST,
    withCredentials: false,
    headers: {
        'X-User-Token': window.localStorage.getItem('ls_todos_user_token') || store.getters['user/token'],
    },
})

export default store
