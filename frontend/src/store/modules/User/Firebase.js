const firebase = {
    namespaced: true,

    state: {
        loadedForUser: null,
        tokens: [],
    },
    mutations: {
        setLoadedForUser(state, payload) {
            state.loadedForUser = payload;
        },
        setTokens(state, payload) {
            state.tokens = payload;
        },
        deleteTokenById(state, id) {
            state.tokens.splice(state.tokens.findIndex(tokenObj => tokenObj.id === id), 1)
        },
        addToken(state, token) {
            state.tokens.push(token)
        },
    },

    getters: {
        all(state) {
            return state.tokens;
        },
        // Токен для текущего устройства
        current(state) {
            return state.tokens.find(token => token.device === window.navigator.userAgent);
        },
    },

    actions: {
        load({dispatch, getters, commit, state}, userId) {
            if (state.loadedForUser === userId) {
                return new Promise((resolve) => {
                    resolve(getters.all)
                })
            }

            return new Promise((resolve, reject) => {
                this.axios.get(`/api/user/firebase/get`, {userId})
                    .then(({data}) => {
                        commit('setLoadedForUser', userId)
                        commit('setTokens', data.tokens)
 
                        resolve(data.tokens)
                    })
                    .catch((response) => {
                        dispatch('popupNotices/addError', {text: response.response.data.error}, { root: true })
                    })
            })
        },
        store({dispatch, getters, commit, state}, payload) {
            // if (state.loadedForUser === userId) {
            //     return new Promise((resolve) => {
            //         resolve()
            //     })
            // }

            if (!payload.userId) {
                console.error(payload, 'Должен быть указан параметр userId')
                return
            }

            if (!payload.firebaseToken) {
                console.error(payload, 'Должен быть указан параметр firebaseToken')
                return
            }

            return new Promise((resolve, reject) => {
                this.axios.post(`/api/user/firebase/store`, payload)
                    .then(({data}) => {
                        if (data.error) {
                            dispatch('popupNotices/addError', {text: data.error}, { root: true })
                            reject()

                            return
                        }

                        commit('addToken', data.firebaseToken[0])

                        resolve(data)
                    })
                    .catch((response) => {
                        dispatch('popupNotices/addError', {text: response.response.data.error}, { root: true })
                    })
            })
        },
        // Action: Удаляем токен, чтобы юзер не получал бы уведомлений
        delete({dispatch, getters, commit, state}, payload) {
            if (!getters.current) {
                console.info(`У пользователя нет текущего токена`)
                return
            }

            return new Promise((resolve, reject) => {
                this.axios.post(`/api/user/firebase/delete`, payload)
                    .then(({data}) => {
                        if (data.error) {
                            dispatch('popupNotices/addError', {text: data.error}, { root: true })
                            reject()

                            return
                        }

                        commit('deleteTokenById', payload.tokenId)
                    })
                    .catch((response) => {
                        dispatch('popupNotices/addError', {text: response.response.data.error}, { root: true })
                    })
            })
        },
        send({dispatch, getters, commit, state}, payload) {
            // if (state.loadedForUser === userId) {
            //     return new Promise((resolve) => {
            //         resolve()
            //     })
            // }

            return new Promise((resolve, reject) => {
                this.axios.post(`/api/user/firebase/send`, payload)
                    .then(({data}) => {
                        // commit('setLoadedForUser', userId)
                        // commit('setContacts', data.contacts)
                        if (data.error) {
                            dispatch('popupNotices/addError', {text: data.error}, { root: true })
                            reject()

                            return
                        }

                        resolve(data)
                    })
                    .catch((response) => {
                        dispatch('popupNotices/addError', {text: response.response.data.error}, { root: true })
                    })
            })

            // commit('update')
        },
    },
};

export default firebase;
