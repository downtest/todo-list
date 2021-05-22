const user = {
    namespaced: true,

    state: {
        current: {
            id: null,
            phone: '',
            name: '',
            email: '',
            permissions: [],
        },
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
        login({commit}, payload) {
            this.axios.post('http://localhost:82/api/user/login', payload)
                .then(({data}) => {
                    commit('update', data.user)
                })

            // commit('update')
        },
        current({commit}) {
            this.axios.post('http://localhost:82/api/user/current')
                .then(({data}) => {
                    commit('update', {...data.user, permissions: data.permissions})
                })

            // commit('update')
        },
    },
};

export default user;
