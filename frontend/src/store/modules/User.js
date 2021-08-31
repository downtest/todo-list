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
        login({commit}, payload) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/login', payload)
                    .then(({data}) => {
                        commit('update', data.user)

                        resolve(data.user)
                    })
                    .catch((response) => {
                        reject(response)
                    })
            })
        },
        logout({commit}) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/logout')
                    .then(({data}) => {
                        commit('update', defaultUser)

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
    },
};

export default user;
