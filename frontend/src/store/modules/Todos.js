const todos = {
    namespaced: true,
    state: {
        items: [],
        logs: ['Init'],
    },
    getters: {
        firstLevel: state => {
            return state.items.filter(item => !item.parent_id)
        },
        getById: state => id => {
            return state.items.find(item => item.id === id)
        },
        children: (state, getters) => id => {
            return state.items.filter(item => item.parent_id === id)
        },
        parents: (state, getters) => id =>  {
            let result = []
            let task = getters.getById(id)

            if (task && task['parent_id']) {
                result.push(...getters.parents(task['parent_id']))
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
            state.items.splice(index, 1, {...task, tasks: children})
        },
        // addChild (state, {parentId, childId}) {
        //     // TODO: Переписать все мутации на индексы, а не id в ключе
        //     state.items[parentId]['tasks'].push(childId)
        // },
        // removeChild (state, {parentId, childId}) {
        //     // TODO: Переписать все мутации на индексы, а не id в ключе
        //     // Удаляем ребёнка у родителя
        //     let index = state.items[parentId]['tasks'].indexOf(childId)
        //     state.items[parentId]['tasks'].splice(index, 1)
        // },
        updateItem(state, {id, payload}) {
            let task = state.items.find(item => item.id === id)
            let index = state.items.findIndex(item => item.id === id)

            // Обновляем, чтобы vue реактивно обновил бы компонент, отображающий таску(если обновлять свойства, то реактивности не будет)
            state.items.splice(index, 1, {...task, ...payload})
        },
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
    },
    actions: {
        async load ({state, commit}, {clientId}) {
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    commit('setItems', [
                        {
                            id: 1,
                            index: 0,
                            message: "Поставщики",
                            parent_id: 0,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [2, 6], // to save the order of children
                        },
                        {
                            id: 2,
                            index: 1,
                            message: "Арсений\nСын маминой подруги",
                            parent_id: 1,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [3],
                        },
                        {
                            id: 3,
                            index: 2,
                            message: "Согласовать график на майские праздники",
                            parent_id: 2,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [5, 4],
                        },
                        {
                            id: 4,
                            index: 3,
                            message: "Заказать овощи",
                            parent_id: 3,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [],
                        },
                        {
                            id: 5,
                            index: 4,
                            message: "Обеспечить молочные изделия",
                            parent_id: 3,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [7],
                        },
                        {
                            id: 6,
                            index: 5,
                            message: "Виталик\nШкольный друг",
                            parent_id: 1,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [],
                        },
                        {
                            id: 7,
                            index: 6,
                            message: "Занести заказ в систему",
                            parent_id: 5,
                            datetime: null,
                            confirmed: true,
                            labels: ['Срочно', 'Важно'],
                            tasks: [8],
                        },
                        {
                            id: 8,
                            index: 7,
                            message: "Комп барахлит, вызвать мастера",
                            parent_id: 7,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [],
                        },
                        {
                            id: 9,
                            index: 8,
                            message: "Купить в магазине",
                            parent_id: 0,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [10, 11, 12, 13],
                        },
                        {
                            id: 10,
                            index: 9,
                            message: "Молоко",
                            parent_id: 9,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [],
                        },
                        {
                            id: 11,
                            index: 10,
                            message: "Сыр",
                            parent_id: 9,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [],
                        },
                        {
                            id: 12,
                            index: 11,
                            message: "Помидоры",
                            parent_id: 9,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [],
                        },
                        {
                            id: 13,
                            index: 12,
                            message: "Яйца",
                            parent_id: 9,
                            datetime: null,
                            confirmed: true,
                            labels: [],
                            tasks: [],
                        },
                    ])

                    return resolve(state.items)
                }, 800)
            })
        },
        async createItem ({commit, state}, payload) {
            payload.id = Math.max( ...Object.keys(state.items)) + 2
            payload.datetime = null
            payload.labels = []
            payload.tasks = []
            // Этот флаг говорит о том, что на фронте item создан, а на бэке ещё нет
            payload.confirmed = false

            commit('createItem', payload)

            setTimeout(() => {
                payload.confirmed = true

                commit('updateItem', {
                    id: payload.id,
                    payload: payload,
                })
            }, 3000)

            return payload
        },
        updateChildren ({commit}, {parentId, children}) {
            let i = 0

            children.forEach(child => {
                commit('updateItem', {id: child.id, payload: {index: i++}})
            })

            commit('updateChildren', {
                parentId: parentId,
                children: children.map(child => child.id),
            })
        },
        updateItem ({commit}, {id, payload}) {
            if (!id) {
                console.warn(payload, `В action updateItem не передан id`)
                return
            }

            commit('updateItem', {
                id: id,
                payload: payload,
            })
        },
        deleteItem ({commit, dispatch, state, getters}, id) {
            // Рекурсивно удаляем детей
            console.log(id, `deleting from items`)
            console.log(getters.children(id), `all children`)
            // TODO: Рекурсивно удалить детей

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
