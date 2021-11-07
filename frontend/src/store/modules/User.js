const defaultUser = {
    id: null,
    phone: '',
    name: '',
    email: '',
    permissions: [],
}

const user = {
    namespaced: true,

    state: {
        current: defaultUser,
    },
    mutations: {
        update(state, payload) {
            // `state` указывает на локальное состояние модуля
            state.current = payload;
        },
        updateName(state, payload) {
            state.current.name = payload;
        },
    },

    getters: {
        current(state) {
            return state.current;
        }
    },

    actions: {
        register({commit}, payload) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/register', payload)
                    .then(({data}) => {
                        commit('update', data.user)

                        resolve(data.user)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        login({commit, dispatch}, payload) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/login', payload)
                    .then(({data}) => {
                        commit('update', data.user)

                        this.dispatch('todos/resetInitialized')
                        resolve(data.user)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        logout({commit, dispatch}) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/logout')
                    .then(({data}) => {
                        commit('update', defaultUser)

                        this.dispatch('todos/resetInitialized')
                        resolve(defaultUser)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        update({commit}, payload) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/update', payload)
                    .then(({data}) => {
                        commit('update', data.user)

                        resolve(data.user)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        current({commit, getters}) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/current')
                    .then(({data}) => {
                        commit('update', {...data.user, permissions: data.permissions})

                        resolve(getters.current)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        /**
         * Action Запрос сброса пароля (письмо на email для сброса пароля)
         * @returns {Promise<unknown>}
         */
        passwordForget({commit}, email) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/password/request-change', {email})
                    .then(({data}) => {
                        resolve(data)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        getEmailByHash({commit}, hash) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/password/get-email-by-hash', {hash})
                    .then(({data}) => {
                        resolve(data)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        /**
         * Action Установка нового пароля взамен забытого
         * @returns {Promise<unknown>}
         */
        passwordReset({commit}, payload) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/password/reset', payload)
                    .then(({data}) => {
                        commit('update', {...data.user, permissions: data.permissions})

                        resolve(data)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
    },
};

export default user;
