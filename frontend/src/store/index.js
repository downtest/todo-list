import { createStore } from 'vuex'
import counter from './modules/Counter'
import user from './modules/User'
import todos from './modules/Todos'

import icons from './modules/Icons'

import range from './tools/range'

import axios from "axios";

const debug = process.env.NODE_ENV !== 'production'

const store = createStore({
    modules: {
        counter,
        user,
        todos,
        icons,
        range,
    },
    strict: debug,
    plugins: [],
})

store.axios = axios.create({
    baseURL: process.env.VUE_APP_BACKEND_HOST,
    withCredentials: true,
    headers: {
        'X-User-Token': window.localStorage.getItem('ls_todos_user_token') || store.getters['user/token'],
    },
})

export default store
