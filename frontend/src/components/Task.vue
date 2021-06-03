<template>
    <div class="task">
        <h3>Well, hello tasky #{{$route.params.id}}!</h3>
        <div>{{localData}}</div>
        <div>
            <input type="text" v-model="name">
        </div>
        <div>
            <textarea v-model="message" cols="30" rows="10"></textarea>
        </div>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter (to, from, next) {
            // обрабатываем изменение параметров маршрута...
            if (!to.params.data) {
                let id = to.params.id

                if (localStorage.focusedTask) {
                    to.params.data = JSON.parse(localStorage.focusedTask)
                    console.log(to.params.data, 'From LocalStorage')
                } else {
                    to.params.data = this.$store.getters['todos/getById'](id)
                    console.log(to.params.data, `From API by ${id}`)
                }
            }

            // Обновляем локальный кещ
            localStorage.focusedTask = JSON.stringify(to.params.data);

            next()
        },
        created() {
            // this.localData = this.data;
            // this.name = this.data.name;
            // this.message = this.data.message;
        },
        props: {
            id: {
                required: true,
            },
            data: {
                required: true,
                type: Object,
            },
        },
        computed: {
            name: {
                get() {return this.data.name},
                set(value) {
                    this.localData.name = value
                    this.localData.id = this.id

                    this.$store.dispatch('todos/updateItem', {
                        id: this.id,
                        payload: this.localData,
                    })
                },
            },
            message: {
                get() {return this.data.message},
                set(value) {
                    this.localData.message = value
                    this.localData.id = this.id
                    this.$store.dispatch('todos/updateItem', {
                        id: this.id,
                        payload: this.localData,
                    })
                },
            },
        },
        data () {
            return {
                localData: {},
            }
        },
        watch: {
            // name(value) {
            //     this.localData.name = value;
            //     this.$store.dispatch('todos/updateItem', this.localData)
            // },
            // message(value) {
            //     this.localData.message = value;
            //     this.$store.dispatch('todos/updateItem', this.localData)
            // },
        },
        methods: {

        },
    }
</script>

<style lang="scss">

</style>
