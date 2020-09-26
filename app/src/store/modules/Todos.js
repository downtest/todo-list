const todos = {
    namespaced: true,
    state: {
        items: {
            0: {
                tasks: [1,9],
            },
            1: {
                id: 1,
                message: "Поставщики",
                parent_id: 0,
                datetime: null,
                labels: [],
                tasks: [2, 6], // to save the order of children
            },
            2: {
                id: 2,
                message: "Арсений\nСын маминой подруги",
                parent_id: 1,
                datetime: null,
                labels: [],
                tasks: [3],
            },
            3: {
                id: 3,
                message: "Согласовать график на майские праздники",
                parent_id: 2,
                datetime: null,
                labels: [],
                tasks: [5, 4],
            },
            4: {
                id: 4,
                message: "Заказать овощи",
                parent_id: 3,
                datetime: null,
                labels: [],
                tasks: [],
            },
            5: {
                id: 5,
                message: "Обеспечить молочные изделия",
                parent_id: 3,
                datetime: null,
                labels: [],
                tasks: [7],
            },
            6: {
                id: 6,
                message: "Виталик\nШкольный друг",
                parent_id: 1,
                datetime: null,
                labels: [],
                tasks: [],
            },
            7: {
                id: 7,
                message: "Занести заказ в систему",
                parent_id: 5,
                datetime: null,
                labels: ['Срочно', 'Важно'],
                tasks: [8],
            },
            8: {
                id: 8,
                message: "Комп барахлит, вызвать мастера",
                parent_id: 7,
                datetime: null,
                labels: [],
                tasks: [],
            },
            9: {
                id: 9,
                message: "Купить в магазине",
                parent_id: 0,
                datetime: null,
                labels: [],
                tasks: [10, 11, 12, 13],
            },
            10: {
                id: 10,
                message: "Молоко",
                parent_id: 9,
                datetime: null,
                labels: [],
                tasks: [],
            },
            11: {
                id: 11,
                message: "Сыр",
                parent_id: 9,
                datetime: null,
                labels: [],
                tasks: [],
            },
            12: {
                id: 12,
                message: "Помидоры",
                parent_id: 9,
                datetime: null,
                labels: [],
                tasks: [],
            },
            13: {
                id: 13,
                message: "Яйца",
                parent_id: 9,
                datetime: null,
                labels: [],
                tasks: [],
            },
        },
        logs: ['Init'],
    },
    getters: {
        firstLevel: state => {
            let result = []

            for (let id of state.items[0]['tasks']) {
                if (state.items[id] && state.items[id]['parent_id'] === 0) {
                    result.push(state.items[id])
                }
            }

            return result
        },
        getById: state => id => {
            return state.items[id]
        },
        children: state => id => {
            let result = []

            if (!state.items[id] || !state.items[id].tasks) {
                return result
            }

            let children = state.items[id].tasks

            for (let childId of children) {
                if (state.items[childId]) {
                    result.push(state.items[childId])
                }
            }

            return result
        },
        parents: (state, getters) => id =>  {
            let result = [null]

            if (state.items[id]['parent_id']) {
                result = getters.parents(state.items[id]['parent_id'])
            }

            result.push(getters.getById(id))

            return result
        },
    },
    mutations: {
        log(state, message) {
            state.log.append(message)
        },
        createItem (state, {payload}) {
            this._vm.$set(state.items, payload.id, payload)
        },
        updateChildren (state, {parentId, children}) {
            state.items[parentId]['tasks'] = children
        },
        addChild (state, {parentId, childId}) {
            state.items[parentId]['tasks'].push(childId)
        },
        removeChild (state, {parentId, childId}) {
            // Удаляем ребёнка у родителя
            let index = state.items[parentId]['tasks'].indexOf(childId)
            state.items[parentId]['tasks'].splice(index, 1)
        },
        updateParent (state, {id, parentId}) {
            state.items[id]['parent_id'] = parentId
        },
        updateItem(state, {id, payload}) {
            for (let prop in payload) {
                state.items[id][prop] = payload[prop]
            }
        },
        deleteItem(state, id) {
            // Реактивно удаляем элемент из массива (чтобы vue реактивно удалил бы его)
            this._vm.$delete(state.items, id)
        },
        addLabel (state, {id, label}) {
            state.items[id]['labels'].push(label)
        },
        deleteLabel (state, {id, index}) {
            state.items[id]['labels'].splice(index, 1)
        },
    },
    actions: {
        async createItem ({commit, state}, {parentId, payload}) {
            payload.id = Math.max( ...Object.keys(state.items)) + 1
            payload.parent_id = parentId
            payload.datetime = null
            payload.labels = []
            payload.tasks = []

            commit('addChild', {
                parentId,
                childId: payload.id
            })

            commit('createItem', {parentId, payload})

            return payload.id
        },
        updateParent ({commit}, {id, parentId}) {
            commit('updateParent', {id, parentId})
        },
        updateChildren ({commit}, {parentId, children}) {
            commit('updateChildren', {parentId, children})
        },
        updateItem ({commit}, {id, payload}) {
            commit('updateItem', {id, payload})
        },
        deleteItem ({commit, dispatch, state}, id) {
            // Рекурсивно удаляем детей
            if (state.items[id].tasks) {
                for (let childId of state.items[id].tasks) {
                    // Вызываем себя же
                    dispatch('deleteItem', childId)
                }
            }

            // Вызываем мутатор
            commit('deleteItem', id)
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
