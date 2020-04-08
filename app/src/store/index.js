import Vue from 'vue'
import Vuex from 'vuex'
import counter from './modules/Counter'
import user from './modules/User'
import todos from './modules/Todos'

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
    modules: {
        counter,
        user,
        todos,
    },
    strict: debug,
    plugins: []
})
