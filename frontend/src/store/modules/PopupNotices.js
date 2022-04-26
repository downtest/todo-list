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
        update(state, payload) {
            let index = state.all.findIndex((notice) => notice.id === payload.id)
            let notice = state.all[index]

            state.all.splice(index, 1, {...payload, ...notice})
        },
        remove(state, id) {
            let index = state.all.findIndex(notice => notice.id === id)

            if (index >= 0) {
                state.all.splice(index, 1)
            }
        },
    },

    actions: {
        add({commit, dispatch}, payload) {
            if (!payload.id) {
                payload.id = Date.now() + Math.random()
            }

            if (!payload.type) {
                payload.type = 'error'
            }

            commit('add', payload)

            if (payload.duration) {
                setTimeout(() => {
                    dispatch('remove', payload.id)
                }, payload.duration)
            }
        },
        addError({dispatch}, payload) {
            dispatch('add', {...payload, type: 'error'})
        },
        addWarning({dispatch}, payload) {
            dispatch('add', {...payload, type: 'warning'})
        },
        addSuccess({dispatch}, payload) {
            dispatch('add', {...payload, type: 'success'})
        },
        remove({state, commit}, id) {
            let notice = state.all.find(notice => notice.id === id)

            if (notice.deprecated) {
                return
            }

            commit('update', {id: id, deprecated: true,})

            setTimeout(() => commit('remove', id), 300)
        },
    },
};

export default popupNotices
