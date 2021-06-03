import { createStore } from 'vuex'
import counter from './modules/Counter'
import user from './modules/User'
import todos from './modules/Todos'
import axios from "axios";

const debug = process.env.NODE_ENV !== 'production'

const store = createStore({
    modules: {
        counter,
        user,
        todos,
    },
    strict: debug,
    plugins: [],
})

store.axios = axios.create({
    baseURL: 'http://localhost:82',
    withCredentials: true,
    // headers: {'Origin': 'localhost:81'}, // Передаётся самим браузером автоматически, вроде
})

export default store
