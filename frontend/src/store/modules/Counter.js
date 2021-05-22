const counter = {
    namespaced: true,

    state: { count: 777 },
    mutations: {
        increment(state) {
            // `state` указывает на локальное состояние модуля
            state.count++;
        },
        decrement(state) {
            // `state` указывает на локальное состояние модуля
            state.count--;
        },
    },

    getters: {
        doubleCount(state) {
            return state.count * 2;
        }
    }
};

export default counter;
