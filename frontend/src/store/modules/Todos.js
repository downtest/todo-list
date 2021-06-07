const todos = {
    namespaced: true,
    state: {
        items: [],
        focusId: null,
        logs: ['Init'],
    },
    getters: {
        firstLevel: state => {
            return state.items.filter(item => !item.parentId)
        },
        getById: state => id => {
            return state.items.find(item => item.id === id)
        },
        children: (state, getters) => id => {
            return state.items.filter(item => item.parentId === id)
        },
        parents: (state, getters) => id =>  {
            let result = []
            let task = getters.getById(id)

            if (task && task['parentId']) {
                result.push(...getters.parents(task['parentId']))
            }

            result.push(task)

            return result
        },
    },
    mutations: {
        log(state, message) {
            state.log.append(message)
        },
        setItems (state, items) {
            state.items = items
        },
        createItem (state, payload) {
            state.items.push(payload)
        },
        updateChildren (state, {parentId, children}) {
            let task = state.items.find(item => item.id === parentId)
            let index = state.items.findIndex(item => item.id === parentId)

            // Обновляем, чтобы vue реактивно обновил бы компонент, отображающий таску(если обновлять свойства, то реактивности не будет)
            state.items.splice(index, 1, {...task, children: children})
        },
        // addChild (state, {parentId, childId}) {
        //     // TODO: Переписать все мутации на индексы, а не id в ключе
        //     state.items[parentId]['children'].push(childId)
        // },
        // removeChild (state, {parentId, childId}) {
        //     // TODO: Переписать все мутации на индексы, а не id в ключе
        //     // Удаляем ребёнка у родителя
        //     let index = state.items[parentId]['children'].indexOf(childId)
        //     state.items[parentId]['children'].splice(index, 1)
        // },
        updateItem(state, {id, payload}) {
            let task = state.items.find(item => item.id === id)
            let index = state.items.findIndex(item => item.id === id)

            // Обновляем, чтобы vue реактивно обновил бы компонент, отображающий таску(если обновлять свойства, то реактивности не будет)
            state.items.splice(index, 1, {...task, ...payload})
        },
        /**
         * Mutation deleteItem
         * Удаляем элемент и его потомков
         * @param state
         * @param id
         */
        deleteItem(state, id) {
            let index = state.items.findIndex(item => item.id === id)

            state.items.splice(index, 1)
        },
        addLabel (state, {id, label}) {
            let task = state.items.find(item => item.id === id)

            task['labels'].push(label)
        },
        deleteLabel (state, {id, index}) {
            let task = state.items.find(item => item.id === id)

            task['labels'].splice(index, 1)
        },
        setFocusId (state, id) {
            state.focusId = id
            console.log(state.focusId, `current focus Id from ${id}`)
        },
    },
    actions: {
        async load ({state, commit}, {clientId}) {
            return new Promise((resolve, reject) => {
                this.axios.get('api/tasks/get', {params: {
                    collectionId: null,
                }})
                    .then(({data}) => {
                        commit('setItems', data)

                        return resolve(data)
                    })
                    .catch((response) => {
                        console.error(response, `error on Tasks Load`)
                    })
            })
        },
        async createItem ({commit, state}, payload) {
            payload.id = new String(Date.now() + Math.random()) // Временный id, настоящий придёт с сервера
            payload.datetime = null
            payload.labels = []
            payload.children = []
            // Этот флаг говорит о том, что на фронте item создан, а на бэке ещё нет
            payload.confirmed = false

            commit('createItem', payload)

            this.axios.post('api/tasks/insert', {
                    collectionId: null,
                    parentId: payload.parentId,
                    message: payload.message,
                })
                .then(async ({data}) => {
                    await commit('updateItem', {
                        id: payload.id,
                        payload: {confirmed: true, ...data},
                    })

                    commit('setFocusId', data.id)
                })
                .catch((response) => {
                    console.error(response, `error on Create Task`)
                })

            return payload
        },
        /**
         * Action updateChildren
         * Переиндексирует таски у одного родителя
         * @param commit
         * @param parentId
         * @param children
         */
        updateChildren ({commit}, {parentId, children}) {
            let i = 0

            children.forEach(child => {
                commit('updateItem', {id: child.id, payload: {index: i++}})
            })
        },
        /**
         * Action updateItem
         * @param commit
         * @param id
         * @param payload
         */
        updateItem ({commit}, {id, payload}) {
            if (!id) {
                console.warn(payload, `В action updateItem не передан id`)
                return
            }

            console.log(payload, `updating ${id}`)

            commit('updateItem', {
                id: id,
                payload: {confirmed: true, ...payload},
            })

            this.axios.post('api/tasks/update', {
                    collectionId: null,
                    taskId: id,
                    ...payload
                })
                .then(({data}) => {
                    console.log(data, `item updated`)

                    commit('updateItem', {
                        id: id,
                        payload: {confirmed: true},
                    })

                    return data
                })
                .catch((response) => {
                    console.error(response, `error on Update Task`)
                })
        },
        /**
         * Action deleteItem
         * Удаляет таску
         * @param commit
         * @param dispatch
         * @param state
         * @param getters
         * @param id
         */
        async deleteItem ({commit, dispatch, state, getters}, id) {
            this.axios.post('api/tasks/delete', {
                collectionId: null,
                taskId: id,
            })
                .then(async ({data}) => {
                    if (!data.data || !data.data.deletedTasks) {
                        console.error(data, `error in Delete Task response`)
                        return
                    }

                    for (let deletedId of data.data.deletedTasks) {
                        // Рекурсивно удаляем детей
                        commit('deleteItem', deletedId)
                    }

                    return data
                })
                .catch((response) => {
                    console.error(response, `error on Delete Task Response`)
                })
        },
        addLabel ({commit}, {id, label}) {
            commit('addLabel', {id, label})
        },
        deleteLabel ({commit}, {id, index}) {
            commit('deleteLabel', {id, index})
        },
    }
};

export default todos
