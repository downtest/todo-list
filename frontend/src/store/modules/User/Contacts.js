const contacts = {
    namespaced: true,

    state: {
        contacts: [],
        loadedForUser: null,
    },
    mutations: {
        setContacts(state, payload) {
            state.contacts = payload;
        },
        setLoadedForUser(state, payload) {
            state.loadedForUser = payload;
        },
    },

    getters: {
        all(state) {
            return state.contacts;
        },
    },

    actions: {
        load({dispatch, getters, commit, state}, userId) {
            if (state.loadedForUser === userId) {
                return new Promise((resolve) => {
                    resolve()
                })
            }

            return new Promise((resolve, reject) => {
                this.axios.post(`/api/contacts/get`)
                    .then(({data}) => {
                        commit('setLoadedForUser', userId)
                        commit('setContacts', data.contacts)

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

export default contacts;
