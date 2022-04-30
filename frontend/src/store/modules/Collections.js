const collections = {
    namespaced: true,

    state: {
        collections: [],
        currentCollection: null,
        loadedForUser: null,
    },
    mutations: {
        setCollections(state, payload) {
            state.collections = payload;
        },
        setCurrentCollection(state, payload) {
            state.currentCollection = payload;
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
            return state.collections.find(collection => collection.id === state.currentCollection);
        },
    },

    actions: {
        load({getters, commit, state}, userId) {
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
                        commit('setCurrentCollection', data.currentCollection)

                        resolve(data)
                    })
            })

            // commit('update')
        },
    },
};

export default collections;
