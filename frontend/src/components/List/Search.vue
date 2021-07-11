<template>
<div>
    <input class="search-input" v-model="phrase" type="text">
    <span @click="erase">-</span>

    <div class="search-items" v-show="showSearchResults">
        <div class="search-items__item" v-for="task in foundTasks" :key="task.id">
            <a class="search-items__link" @click="$router.push({name: 'task-list', params: {parentId: task.id}})">{{task.message.split('\n')[0]}}</a>
        </div>
    </div>
</div>
</template>

<script>
export default {
    name: "Search",
    data() {
        return {
            phrase: null,
            foundTasks: [],
            showSearchResults: false,
        }
    },
    computed: {
        tasks() {
            return this.$store.getters['todos/all']
        },
    },
    watch: {
        phrase(value) {
            if (!value || value.length < 2) {
                this.foundTasks = []
                this.showSearchResults = false

                return
            }

            this.showSearchResults = true
            let regExp = new RegExp(value, 'i')
            this.foundTasks = this.tasks.filter(task => regExp.test(task.message))
        }
    },
    methods: {
        erase() {
            this.phrase = null
        },
    },
    mounted() {
        window.addEventListener('click', (event) => {
            if (event.target.classList.contains('search-input')) {
                // Клик по инпуту
                this.showSearchResults = true
            } else if (event.target.classList.contains('search-items__link')) {
                // Клик по ссылке
                this.showSearchResults = false
            } else {
                // focus out
                this.showSearchResults = false
            }
        })
    },
}
</script>

<style scoped>
.search-items {
    width: 60%;
    left: 20%;
    position: absolute;
    background-color: #bcbcbc;
    text-align: left;
}

.search-items__item {
    border: 1px solid red;
    padding: 5px 10px;
}
.search-items__item:hover {
    background-color: rgba(33, 193, 33, 0.5);
    color: #ededed;
}
.search-items__link {
    cursor: pointer;
}
</style>
