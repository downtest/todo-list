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
                tasks: [2, 6], // to save the order of children
            },
            2: {
                id: 2,
                message: "Арсений\nСын маминой подруги",
                parent_id: 1,
                tasks: [3],
            },
            3: {
                id: 3,
                message: "Согласовать график на майские праздники",
                parent_id: 2,
                tasks: [5, 4],
            },
            4: {
                id: 4,
                message: "Заказать овощи",
                parent_id: 3,
                tasks: [],
            },
            5: {
                id: 5,
                message: "Обеспечить молочные изделия",
                parent_id: 2,
                tasks: [7],
            },
            6: {
                id: 6,
                message: "Виталик\nШкольный друг",
                parent_id: 1,
                tasks: [],
            },
            7: {
                id: 7,
                message: "Занести заказ в систему",
                parent_id: 5,
                tasks: [8],
            },
            8: {
                id: 8,
                message: "Комп барахлит, вызвать мастера",
                parent_id: 7,
                tasks: [],
            },
            9: {
                id: 9,
                message: "Купить в магазине",
                parent_id: 0,
                tasks: [10, 11, 12, 13],
            },
            10: {
                id: 10,
                message: "Молоко",
                parent_id: 9,
                tasks: [],
            },
            11: {
                id: 11,
                message: "Сыр",
                parent_id: 9,
                tasks: [],
            },
            12: {
                id: 12,
                message: "Помидоры",
                parent_id: 9,
                tasks: [],
            },
            13: {
                id: 13,
                message: "Яйца",
                parent_id: 9,
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
        }
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
            this._vm.$delete(state.items, id)
        },
    },
    actions: {
        createItem ({commit, state}, {parentId, payload}) {
            console.log(`Creating item for parent ${parentId}`)
            payload.id = Math.max( ...Object.keys(state.items)) + 1
            payload.parent_id = parentId
            payload.tasks = []

            commit('addChild', {
                parentId,
                childId: payload.id
            })
            commit('createItem', {parentId, payload})
        },
        updateParent ({commit}, {id, parentId, newIndex}) {
            console.log(`Acting updateParent on #${id} to ${parentId} to index ${newIndex}`)
            commit('updateParent', {id, parentId})
        },
        updateChildren ({commit}, {parentId, children}) {
            commit('updateChildren', {parentId, children})
        },
        updateItem ({commit}, {id, payload}) {
            console.log(payload, 'payload in action update')
            commit('updateItem', {id, payload})
        },
        deleteItem ({commit, dispatch, state}, id) {
            // Рекурсивно удаляем детей
            if (state.items[id].tasks) {
                for (let childId of state.items[id].tasks) {
                    dispatch('deleteItem', childId)
                }
            }

            commit('deleteItem', id)
        }
    }
};

export default todos
