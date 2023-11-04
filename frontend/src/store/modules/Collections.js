const collections = {
    namespaced: true,

    state: {
        collections: [],
        currentCollectionId: null,
        loadedForUser: null,
    },
    mutations: {
        setCollections(state, payload) {
            state.collections = payload;
        },
        setCurrentCollectionId(state, payload) {
            state.currentCollectionId = payload;
        },
        setLoadedForUser(state, payload) {
            state.loadedForUser = payload;
        },
    },

    getters: {
        all(state) {
            return state.collections;
        },
        current(state) {
            if (state.collections.length <= 0) {
                return null
            }

            return state.collections.find(collection => collection.id === state.currentCollectionId);
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
                this.axios.post(`/api/collections/get`)
                    .then(({data}) => {
                        commit('setLoadedForUser', userId)
                        commit('setCollections', data.collections)
                        commit('setCurrentCollectionId', data.currentCollection)

                        resolve(data)
                    })
                    .catch((response) => {
                        dispatch('popupNotices/addError', {text: response.response.data.errors.join('<br>')}, { root: true })
                    })
            })

            // commit('update')
        },
    },
};

export default collections;
