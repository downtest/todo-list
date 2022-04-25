const popupNotices = {
    namespaced: true,

    state: {
        all: [],
    },

    getters: {
        all(state) {
            return state.all
        },
        errors(state) {
            return state.all.filter(notice => notice.type === 'error')
        },
        warnings(state) {
            return state.all.filter(notice => notice.type === 'warning')
        },
        success(state) {
            return state.all.filter(notice => notice.type === 'success')
        },
    },

    mutations: {
        add(state, payload) {
            state.all.push(payload)
        },
        remove(state, id) {
            state.all.splice(state.all.findIndex(notice => notice.id === id), 1)
        },
    },

    actions: {
        addError({commit}, payload) {
            commit('add', {
                id: Date.now() + Math.random(),
                text: payload,
                type: 'error',
            })
        },
        addWarning({commit}, payload) {
            commit('add', {
                id: Date.now() + Math.random(),
                text: payload,
                type: 'warning',
            })
        },
        addSuccess({commit}, payload) {
            commit('add', {
                id: Date.now() + Math.random(),
                text: payload,
                type: 'success',
            })
        },
        remove({commit}, id) {
            commit('remove', id)
        },
    },
};

export default popupNotices
