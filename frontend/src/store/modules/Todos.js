const LS_TODOS_UNCONFIRMED_ITEMS = 'ls_todos_unconfirmed_items'

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
        unconfirmed: (state) => {
            return state.items.filter(task => task.updated || task.isNew)
        },
        getChanges: (state, getters) => {
            return getters.unconfirmed.map(task => {
                if (task.isNew) {
                    return task
                }

                return {...task.updated, id: task.id}
            })
        },
        prepareUnconfirmedForServer: (state) => {
            return state.items.filter(task => task.updated || task.isNew).map(task => {
                let formedTask = {...task, ...task.updated}

                delete formedTask.updated

                return formedTask
            })
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
        updateItems(state, items) {
            for (let task of items) {
                let currentTask = state.items.find(item => item.id === (task.oldId || task.id))
                let index = state.items.findIndex(item => item.id === (task.oldId || task.id))

                // Обновляем, чтобы vue реактивно обновил бы компонент, отображающий таску(если обновлять свойства, то реактивности не будет)
                state.items.splice(index, 1, {...currentTask, ...task})
            }
        },
        updateItem(state, {id, payload, instant}) {
            let task = state.items.find(item => item.id === id)
            let index = state.items.findIndex(item => item.id === id)

            // Обновляем, чтобы vue реактивно обновил бы компонент, отображающий таску(если обновлять свойства, то реактивности не будет)
            if (instant) {
                // Со свойством instant сразу вносим изменения в таску, без свойства updated
                state.items.splice(index, 1, {...task, ...payload})
            } else {
                // Сохраняем изменения в отдельное свойство, чтобы все изменения можно было бы сбросить
                state.items.splice(index, 1, {...task, updated: payload})
            }
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
        setFocusId (state, id) {
            state.focusId = id
            console.log(state.focusId, `current focus Id from ${id}`)
        },
    },
    actions: {
        async load ({state, commit, dispatch}, {clientId}) {
            return new Promise((resolve, reject) => {
                this.axios.get('api/tasks/get', {params: {
                    collectionId: null,
                }})
                    .then(({data}) => {
                        commit('setItems', data)

                        if (window.localStorage.getItem(LS_TODOS_UNCONFIRMED_ITEMS)) {
                            for (let task of JSON.parse(window.localStorage.getItem(LS_TODOS_UNCONFIRMED_ITEMS))) {
                                if (task.isNew) {
                                    dispatch('createItem', task)
                                } else {
                                    dispatch('updateItem', {
                                        id: task.id,
                                        payload: task,
                                    })
                                }
                            }
                        }

                        return resolve(data)
                    })
                    .catch((response) => {
                        console.error(response, `error on Tasks Load`)
                    })
            })
        },
        async createItem ({commit, state, getters}, payload) {
            // Временный id, настоящий придёт с сервера
            let tempId = (new String(Date.now() + Math.random())).toString()

            commit('createItem', {
                id: tempId,
                datetime: null,
                labels: [],
                children: [],
                isNew: true,
                ...payload
            })

            window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

            commit('setFocusId', tempId)

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
                commit('updateItem', {id: child.id, payload: {index: i++, parentId}, instant: true})
            })
        },
        /**
         * Action updateItem
         * @param commit
         * @param state
         * @param getters
         * @param id
         * @param payload
         */
        updateItem ({commit, state, getters}, {id, payload}) {
            if (!id) {
                console.warn(payload, `В action updateItem не передан id`)
                return
            }

            commit('updateItem', {
                id: id,
                payload: payload,
            })

            window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))
        },
        /**
         * Action dragItem
         * Вызывается для отправки запроса при перетаскивании таски
         *
         * @param commit
         * @param state
         * @param getters
         * @param payload
         */
        dragItem({commit, state, getters}, payload) {
            this.axios.post('api/tasks/update', {
                collectionId: null,
                id: payload.taskId,
                parentId: payload.parentId,
                index: payload.index,
            })
                .then(async ({data}) => {
                    if (!data.id) {
                        console.error(data, `error in Drag Task response`)
                        return
                    }

                    return data
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
                    if (!data.deletedTasks) {
                        console.error(data, `error in Delete Task response`)
                        return
                    }

                    for (let deletedId of data.deletedTasks) {
                        // Рекурсивно удаляем детей
                        commit('deleteItem', deletedId)
                    }

                    return data
                })
        },
        addLabel ({state, commit, getters}, {id, label}) {
            let task = getters.getById(id)
            let labels = task.updated ? task.updated.labels : task.labels

            commit('updateItem', {
                id: id,
                payload: {labels: [...labels, label]},
            })

            window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))
        },
        /**
         * Action deleteLabel
         * @param commit
         * @param getters
         * @param id
         * @param index
         */
        deleteLabel ({dispatch, commit, getters}, {id, index}) {
            // TODO: Тут ошибка, нельзя менять state не в мутации
            let task = getters.getById(id)
            let labels = task.updated ? task.updated.labels : task.labels
            let copiedLabels = [...labels]


            if (!labels) {
                copiedLabels = []
            }

            copiedLabels.splice(index, 1)

            commit('updateItem', {
                id: id,
                payload: {labels: copiedLabels},
            })

            window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))
        },
        resetChanges ({commit, state, getters}, id) {
            let task = getters.getById(id)

            commit('updateItem', {
                id: id,
                payload: {...task, updated: null},
                instant: true, // Обновляем task, а не свойство updated внутри
            })

            window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))
        },
        save({commit, state, getters}) {
            console.log(getters.unconfirmed, `getters.unconfirmed`)
            if (!getters.unconfirmed.length) {
                return
            }

            this.axios.post('api/tasks/mass/update', {
                collectionId: null,
                tasks: getters.prepareUnconfirmedForServer
            })
                .then(({data}) => {
                    // commit('updateItems', data.tasks)

                    for (let task of data.tasks) {
                        console.log(task, `updating ${task.id}`)

                        commit('updateItem', {
                            id: task.oldId || task.id,
                            payload: task,
                            instant: true,
                        })
                    }

                    window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

                    return data.tasks
                })
                .catch((response) => {
                    console.error(response, `error on Update Task`)
                })
        },
    }
};

export default todos
