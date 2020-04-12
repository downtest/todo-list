const todos = {
    namespaced: true,
    state: {
        items_old: {
            0: [
                {
                    id: 1,
                    name: "task 1",
                    message: "",
                },
                {
                    id: 3,
                    name: "task 3",
                    message: "",
                },
                {
                    id: 5,
                    name: "task 5",
                    message: "",
                }
            ],
            1: [
                {
                    id: 2,
                    name: "task 2",
                    message: "",
                }
            ],
            3: [
                {
                    id: 4,
                    name: "task 4",
                    message: "",
                }
            ]
        },
        items: {
            0: {
                tasks: [1,3,5],
            },
            1: {
                id: 1,
                name: "task 1",
                message: "",
                parent_id: 0,
                tasks: [6, 2], // to save the order of children
            },
            2: {
                id: 2,
                name: "task 2",
                message: "",
                parent_id: 1,
                tasks: [],
            },
            3: {
                id: 3,
                name: "task 3",
                message: "",
                parent_id: 0,
                tasks: [4],
            },
            4: {
                id: 4,
                name: "task 4",
                message: "",
                parent_id: 3,
                tasks: [],
            },
            5: {
                id: 5,
                name: "task 5",
                message: "",
                parent_id: 0,
                tasks: [],
            },
            6: {
                id: 6,
                name: "task 6",
                message: "",
                parent_id: 1,
                tasks: [7, 8],
            },
            7: {
                id: 7,
                name: "task 7",
                message: "",
                parent_id: 6,
                tasks: [],
            },
            8: {
                id: 8,
                name: "task 8",
                message: "",
                parent_id: 6,
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
