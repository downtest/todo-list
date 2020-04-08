<template>
    <div class="task">
        <h3>Well, hello tasky #{{$route.params.id}}!</h3>
        <div>#{{localData.id}}</div>
        <div>
            <input type="text" v-model="localData.name">
        </div>
        <div>
            <textarea v-model="localData.message" cols="30" rows="10"></textarea>
        </div>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter (to, from, next) {
            // обрабатываем изменение параметров маршрута...
            // не забываем вызвать next()

            if (!to.params.data) {
                let id = parseInt(to.params.id);

                if (localStorage.focusedTask) {
                    to.params.data = JSON.parse(localStorage.focusedTask);
                } else {
                    console.log({id}, 'TODO: get From API');
                    to.params.data = {id};
                }
            }

            // Обновляем локальный кещ
            localStorage.focusedTask = JSON.stringify(to.params.data);

            next()
        },
        created() {
            // this.localData = this.data;
            this.name = this.data.name;
            this.message = this.data.message;
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
        data () {
            return {
                localData: {},
                name: '',
                message: '',
            }
        },
        watch: {
            name(value) {
                this.localData.name = value;
                this.$store.dispatch('todos/updateItem', this.localData)
            },
            message(value) {
                this.localData.message = value;
                this.$store.dispatch('todos/updateItem', this.localData)
            },
        },
        methods: {

        },
    }
</script>

<style lang="scss">

</style>
