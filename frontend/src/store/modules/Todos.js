import moment from "moment"

const LS_TODOS_UNCONFIRMED_ITEMS = 'ls_todos_unconfirmed_items'
const LS_TODOS_ITEMS_CACHE = 'ls_todos_items_cache'

const todos = {
    namespaced: true,
    state: {
        initialized: false,
        items: [],
        // Задача, открытая на отдельной странице
        focusId: null,
        // У какой задачи показывать кнопки управления
        moreId: null,
        logs: ['Init'],
    },
    getters: {
        initialized(state){
            return state.initialized
        },
        loading(state){
            return state.loading
        },
        all(state){
            return state.items
        },
        focusId(state){
            return state.focusId
        },
        moreId(state){
            return state.moreId
        },
        children: (state, getters) => id => {
            return state.items.filter(item => ((id === null && item.parentId === undefined) || item.parentId === id)).sort((a,b) => a.index - b.index)
        },
        firstLevel: state => {
            return state.items.filter(item => !item.parentId)
        },
        getById: state => id => {
            return state.items.find(item => item.id === id)
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
        /**
         * Получает изменения по всем заметкам
         */
        getChanges: (state, getters) => {
            return getters.unconfirmed.map(task => {
                if (task.isNew) {
                    return task
                }

                return {...task.updated, id: task.id}
            })
        },
        /**
         * Получает изменения по конкретной заметке
         */
        getTaskChanges: (state, getters) => id => {
            let task = getters.all.find(task => task.id === id)

            if (!task) return null

            if (task.isNew) {
                return task
            }

            if (task.updated) {
                return {...task.updated, id: task.id}
            }

            return null
        },
        prepareUnconfirmedForServer: (state) => {
            return state.items.filter(task => task.updated || task.isNew).map(task => {
                let formedTask = {...task, ...task.updated}

                delete formedTask.updated

                return formedTask
            })
        },
        groupedByDates: (state) => {
            let result = {}

            if (!state.items.length) {
                return result
            }

            state.items.forEach(item => {
                if (!item.date) {
                    return
                }

                let date = moment(item.date)
                let dateIndex = `${date.year()}.${date.month()}.${date.date()}`

                if (!result[dateIndex]) {
                    result[dateIndex] = []
                }

                result[dateIndex].push(item)
            })

            return result
        },
    },
    mutations: {
        log(state, message) {
            state.log.append(message)
        },
        setInitialized(state, initialized) {
            state.initialized = initialized
        },
        setLoading (state, value) {
            state.loading = value
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
            let newData = {...task, ...task.updated, ...payload}

            if (newData.date && newData.time) {
                let splittedDate = newData.date.split('-')
                let splittedTime = newData.time.split(':')
                let dateTime = new Date(splittedDate[0], splittedDate[1], splittedDate[2], splittedTime[0], splittedTime[1])
                let utcDate = dateTime.getUTCDate() < 10 ? '0'+dateTime.getUTCDate() : dateTime.getUTCDate()
                let utcMonth = dateTime.getUTCMonth() < 10 ? '0'+dateTime.getUTCMonth() : dateTime.getUTCMonth()
                let utcHours = dateTime.getUTCHours() < 10 ? '0'+dateTime.getUTCHours() : dateTime.getUTCHours()
                let utcMinutes = dateTime.getUTCMinutes() < 10 ? '0'+dateTime.getUTCMinutes() : dateTime.getUTCMinutes()

                payload.date_utc = `${dateTime.getUTCFullYear()}-${utcMonth}-${utcDate}`
                payload.time_utc = `${utcHours}:${utcMinutes}`
            } else if (newData.date) {
                // console.log(newData.date.split('-'), `date`)

                payload.date_utc = newData.date
                payload.time_utc = null
            } else if (newData.time) {
                let splittedTime = newData.time.split(':')
                let dateTime = new Date()

                dateTime.setHours(splittedTime[0])
                dateTime.setMinutes(splittedTime[1])

                let utcHours = dateTime.getUTCHours() < 10 ? '0'+dateTime.getUTCHours() : dateTime.getUTCHours()
                let utcMinutes = dateTime.getUTCMinutes() < 10 ? '0'+dateTime.getUTCMinutes() : dateTime.getUTCMinutes()

                payload.date_utc = null
                payload.time_utc = `${utcHours}:${utcMinutes}`
            }

            // Обновляем, чтобы vue реактивно обновил бы компонент, отображающий таску(если обновлять свойства, то реактивности не будет)
            if (task.isNew || instant) {
                // Со свойством instant сразу вносим изменения в таску, без свойства updated
                state.items.splice(index, 1, {...task, ...payload})
            } else {
                // Сохраняем изменения в отдельное свойство, чтобы все изменения можно было бы сбросить
                state.items.splice(index, 1, {...task, updated: {...task.updated, ...payload}})
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
        },
        // Кнопки управления
        setMoreId (state, id) {
            state.moreId = id
        },
    },
    actions: {
        async load ({state, commit, dispatch, getters}, payload) {
            // TODO: СОхранение в кеш браузера для offline работы
            if (getters.initialized && !payload.force) {
                return new Promise((resolve, reject) => {
                    resolve(getters.all)
                })
            } 

            commit('setItems', [])
            commit('setLoading', true)

            return new Promise(async (resolve, reject) => {
                this.axios.get('api/nodes/get', {params: {
                    clientId: payload.clientId,
                    collectionId: payload.collectionId,
                }})
                    .then(({data}) => {
                        if (data.error) {
                            console.error(data, `Ошибка при загрузке тасок`)

                            return
                        }

                        commit('setItems', data)
                        // window.localStorage.setItem(LS_TODOS_ITEMS_CACHE, JSON.stringify(data))
                    })
                    .catch((response) => {
                        dispatch('popupNotices/addError', {text: response.response.data.errors.join('<br>')}, { root: true })
                        console.error(response, `error on Tasks Load`)

                        if (window.localStorage.getItem(LS_TODOS_ITEMS_CACHE)) {
                            resolve(JSON.parse(window.indexedDB.getItem(LS_TODOS_ITEMS_CACHE)))
                        } else {
                            resolve([])
                        }
                    })
                    .finally( async () => {
                        await dispatch('loadFromStorage')

                        resolve(getters.all)
                    })
            })
        },
        async loadFromStorage ({getters, commit, dispatch}) {
            console.log(`loadFromStorage fafwer`)

            return new Promise(async (resolve, reject) => {
                commit('setLoading', true)

                let itemsInLS = await dispatch('getItemsFromLS')

                if (itemsInLS) {
                    for (let task of itemsInLS) {
                        if (task.isNew && !getters.getById(task.id)) {
                            await commit('createItem', task)
                        } else {
                            await commit('updateItem', {
                                id: task.id,
                                payload: task,
                            })
                        }
                    }
                }

                commit('setLoading', false)

                resolve(getters.all)
            })
        },
        getItemsFromLS ({state, commit}) {
            if (window.localStorage.getItem(LS_TODOS_UNCONFIRMED_ITEMS)) {
                return JSON.parse(window.localStorage.getItem(LS_TODOS_UNCONFIRMED_ITEMS))
            }

            return []
        },
        createItem ({commit, state, getters}, payload) {
            return new Promise((resolve, reject) => {
                // Временный id, настоящий придёт с сервера
                let tempId = (String(Date.now() + Math.random())).toString()
                let maxIndex = Math.max(getters.all.filter(item => item.parentId === payload.parentId).map(item => item.index)) || -1

                let newTaskData = {
                    id: tempId,
                    parentId: null,
                    index: maxIndex + 1,
                    date: null,
                    time: null,
                    labels: [],
                    children: [],
                    isNew: true,
                    message: '',
                    ...payload
                }

                commit('createItem', newTaskData)

                window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

                commit('setFocusId', null)

                resolve(newTaskData)
            })
        },
        resetInitialized({commit}) {
            return commit('setInitialized', false)
        },
        /**
         * Action updateChildren
         * Переиндексирует таски у одного родителя
         * @param commit
         * @param parentId
         * @param children
         */
        updateChildren ({commit, getters}, {id, oldParentId, newParentId, oldIndex, newIndex}) {
            let i = 0

            let item = getters.all.find((item) => item.id === id)

            commit('updateItem', {
                id: id,
                instant: true,
                payload: {parentId: newParentId, index: newIndex}
            })

            console.log(`changing ${item.message} from ${oldParentId}:${oldIndex} to ${newParentId}:${newIndex}`)

            if (newParentId !== oldParentId) {
                // Изменился родитель
                // Актуализируем index у старого родителя
                getters.all
                    .filter(item => item.parentId === oldParentId && item.id !== id && item.index > oldIndex)
                    .forEach(child => {
                        commit('updateItem', {id: child.id, payload: {index: child.index-1}, instant: true})
                    })

                // Актуализируем index у нового родителя
                getters.all
                    .filter(item => item.parentId === newParentId && item.id !== id && item.index >= newParentId)
                    .forEach(child => {
                        commit('updateItem', {id: child.id, payload: {index: child.index+1}, instant: true})
                    })
            } else {
                // Родитель не менялся
                if (newIndex < oldIndex) {
                    // Перенесли выше по списку
                    getters.all
                        .filter(item => item.parentId === newParentId && item.id !== id && item.index >= newIndex && item.index < oldIndex)
                        .forEach(child => {
                            console.log(`++ to ${child.message}`)
                            commit('updateItem', {id: child.id, payload: {index: child.index+1}, instant: true})
                        })
                } else {
                    // Перенесли ниже по списку
                    getters.all
                        .filter(item => item.parentId === newParentId && item.id !== id && item.index <= newIndex && item.index > oldIndex)
                        .forEach(child => {
                            console.log(`-- to ${child.message}`)
                            commit('updateItem', {id: child.id, payload: {index: child.index-1}, instant: true})
                        })
                }
            }
        },
        /**
         * Action updateItem
         * @param commit
         * @param state
         * @param getters
         * @param id
         * @param payload
         */
        async updateItem ({commit, state, getters}, {id, payload}) {
            if (!id) {
                console.warn(payload, `В action updateItem не передан id`)
                return
            }

            return new Promise((resolve, reject) => {
                commit('updateItem', {
                    id: id,
                    payload: payload,
                })

                window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

                resolve()
            })
        },
        /**
         * Action dragItem
         * Вызывается для отправки запроса при перетаскивании таски
         *
         * @param commit
         * @param state
         * @param getters
         * @param dispatch
         * @param payload
         */
        dragItem({commit, state, getters, dispatch}, payload) {
            this.axios.post('api/nodes/update', {
                collectionId: null,
                items: payload,
            })
                .then(async ({data}) => {
                    if (!data.id) {
                        console.error(data, `error in Drag Task response`)
                        return
                    }

                    return data
                })
                .catch((response) => {
                    dispatch('popupNotices/addError', {text: response.response.data.error}, { root: true })
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
            if (getters.getById(id).isNew) {
                commit('deleteItem', id)

                window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

                return
            }

            this.axios.post('api/nodes/delete', {
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

                    window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

                    return data
                })
                .catch((response) => {
                    dispatch('popupNotices/addError', {text: response.response.data.error}, { root: true })
                    console.error(response, `error on delete Task`)
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
         * Action сортировки лейблов (перетащили лейбл)
         */
        changeLabelsOrder ({state, commit, getters}, {id, labels}) {
            let task = getters.getById(id)

            commit('updateItem', {
                id: id,
                payload: {labels: labels},
            })

            window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))
        },
        /**
         * Action deleteLabel
         * @param dispatch
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
        /**
         * Action resetAllChanges -
         * @param commit
         * @param state
         * @param getters
         */
        resetAllChanges ({commit, state, getters}) {
            getters.getChanges.forEach(task => {
                if (task.isNew) {
                    commit('deleteItem', task.id)
                } else {
                    commit('updateItem', {
                        id: task.id,
                        payload: {...task, updated: null},
                        instant: true, // Обновляем task, а не свойство updated внутри
                    })
                }
            })

            window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))
        },
        /**
         * Action save - сохранение всех изменений на сервере
         * @param commit
         * @param state
         * @param getters
         * @param dispatch
         */
        save({commit, state, getters, dispatch}) {
            if (!getters.unconfirmed.length) {
                return
            }

            return new Promise((resolve, reject) => {
                this.axios.post('api/nodes/mass/update', {
                    collectionId: null,
                    nodes: getters.prepareUnconfirmedForServer
                })
                    .then(({data}) => {
                        // commit('updateItems', data.tasks)

                        for (let item of data.nodes) {
                            console.log(item, `updating ${item.id}`)

                            commit('updateItem', {
                                id: item.oldId || item.id,
                                payload: {...item, updated: null, isNew: false,},
                                instant: true,
                            })
                        }

                        window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

                        dispatch('popupNotices/addSuccess', {
                            text: `Записи сохранены (${data.nodes.length})`,
                            duration: 2000
                        }, { root: true })

                        resolve(data.nodes)
                    })
                    .catch((response) => {
                        dispatch('popupNotices/addError', {text: response.response.data.errors.join('<br>')}, { root: true })
                        console.error(response, `error on Update Task`)

                        reject(response.response.data)
                    })
            })
        },
        /**
         * Action save - сохранение одной таски
         * @param commit
         * @param state
         * @param getters
         * @param dispatch
         * @param task
         */
        saveOneTask({commit, state, getters, dispatch}, node) {
            this.axios.post('api/nodes/update', {
                collectionId: null,
                node: {...node, ...node.updated},
            })
                .then(({data}) => {
                    // commit('updateItems', data.tasks)

                    commit('updateItem', {
                        id: data.oldId || data.id,
                        payload: {...data, updated: null, isNew: false,},
                        instant: true,
                    })

                    window.localStorage.setItem(LS_TODOS_UNCONFIRMED_ITEMS, JSON.stringify(getters.getChanges))

                    dispatch('popupNotices/addSuccess', {
                        text: `Запись сохранена`,
                        duration: 2000
                    }, { root: true })

                    return data
                })
                .catch((response) => {
                    console.error(response, `error on Update Task`)
                    dispatch('popupNotices/addError', {text: response.response.data.error}, { root: true })
                })
        },
        resetFocus({commit}) {
            commit('setFocusId', null)
        },
        setMoreId({commit, state, getters, dispatch}, moreId) {
            commit('setMoreId', moreId)
        },
    }
};

export default todos
