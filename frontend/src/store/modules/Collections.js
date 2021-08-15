const user = {
    namespaced: true,

    state: {
        collections: [],
        currentUserId: null,
    },
    mutations: {
        setCollections(state, payload) {
            state.collections = payload;
        },
        setCurrentUserId(state, payload) {
            state.currentUserId = payload;
        },
    },

    getters: {
        all(state) {
            return state.collections;
        },
        currentUserId(state) {
            return state.currentUserId;
        },
    },

    actions: {
        load({getters, commit}, userId) {
            if (getters.cu)

            this.axios.post(`http://localhost:82/api/user/login`,)
                .then(({data}) => {
                    commit('setCollections', data)
                    commit('setCurrentUserId', userId)
                })

            // commit('update')
        },
    },
};

export default user;
