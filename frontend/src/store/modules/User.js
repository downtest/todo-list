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
        token: null,
    },
    mutations: {
        update(state, payload) {
            // `state` указывает на локальное состояние модуля
            state.current = payload;
        },
        updateName(state, payload) {
            state.current.name = payload;
        },
        setToken(state, token) {
            state.token = token;

            if (token) {
                // Устанавливаем токен в заголовок на все дальнейшие запросы
                this.axios.defaults.headers['X-User-Token'] = token
                // Сохраняем токен в LocalStorage
                window.localStorage.setItem('ls_todos_user_token', token)
            } else {
                delete this.axios.defaults.headers['X-User-Token']
                window.localStorage.removeItem('ls_todos_user_token')
            }
        },
    },

    getters: {
        current(state) {
            return state.current;
        },
        token(state) {
            return state.token;
        },
    },

    actions: {
        register({commit}, payload) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/register', payload)
                    .then(({data}) => {
                        commit('update', data.user)
                        commit('setToken', data.token)

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
                        commit('setToken', data.token)

                        // Устанавливаем коллекции
                        if (data.collections) {
                            commit('collections/setCollections', data.collections, {root: true})
                        }

                        if (data.currentCollection) {
                            commit('collections/setCurrentCollectionId', data.currentCollection.id, {root: true})
                        }

                        if (data.user) {
                            dispatch('todos/load', {
                                clientId: data.user['id'],
                                collectionId: data.currentCollection ? data.currentCollection.id : null,
                            }, {root: true})
                        }

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
                commit('update', defaultUser)
                commit('setToken', null)

                // Сбрасываем таски
                dispatch('todos/loadFromStorage', null, {root: true})

                // Сбрасываем firebase ключи
                commit('firebase/setLoadedForUser', null, {root: true})
                commit('firebase/setTokens', [], {root: true})

                // Сбрасываем коллекции
                commit('collections/setLoadedForUser', null, {root: true})
                commit('collections/setCollections', [], {root: true})
                commit('collections/setCurrentCollectionId', null, {root: true})

                this.dispatch('todos/resetInitialized')

                resolve(defaultUser)
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
        current({dispatch, commit, getters}) {
            return new Promise((resolve, reject) => {
                this.axios.post('/api/user/current')
                    .then(({data}) => {
                        commit('update', {...data.user, permissions: data.permissions})

                        // Устанавливаем контакты
                        commit('contacts/setContacts', data.contacts, {root: true})

                        // Устанавливаем коллекции
                        commit('collections/setCollections', data.collections, {root: true})

                        if (data.currentCollection) {
                            commit('collections/setCurrentCollectionId', data.currentCollection.id, {root: true})
                        }

                        if (data.user) {
                            dispatch('todos/load', {
                                clientId: data.user['id'],
                                collectionId: data.currentCollection ? data.currentCollection.id : null,
                            }, {root: true})
                        } else {
                            dispatch('todos/loadFromStorage', null, {root: true})
                        }

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
