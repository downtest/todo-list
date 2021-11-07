import { createStore } from 'vuex'
import counter from './modules/Counter'
import user from './modules/User'
import todos from './modules/Todos'
import icons from './modules/Icons'
import axios from "axios";

const debug = process.env.NODE_ENV !== 'production'

const store = createStore({
    modules: {
        counter,
        user,
        todos,
        icons,
    },
    strict: debug,
    plugins: [],
})

store.axios = axios.create({
    baseURL: process.env.VUE_APP_BACKEND_HOST,
    withCredentials: true,
    // headers: {'Origin': 'localhost:81'}, // Передаётся самим браузером автоматически, вроде
})

export default store
