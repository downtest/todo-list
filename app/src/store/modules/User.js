const user = {
    namespaced: true,

    state: {
        id: 0,
        name: '',
    },
    mutations: {
        update(state, payload) {
            // `state` указывает на локальное состояние модуля
            state = payload;
        },
        updateName(state, payload) {
            state.name = payload;
        },
    },

    getters: {
        get(state) {
            return state;
        }
    }
};

export default user;
