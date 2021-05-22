<template>
    <div>
        <h3>Changind User {{currentUser.phone}}!</h3>

        <div v-if="currentUser.id">
            <pre>{{currentUser}}</pre>
        </div>

        <div v-else>
            <div>#{{id}}</div>
            <div><input v-model="phone" type="text"></div>
            <div><input v-model="name" type="text"></div>

            <button @click="login">Login</button>
        </div>

    </div>
</template>

<script>
export default {
    name: 'user',
    data() {
        return {
            id: this.$store.state.user.current.id,
            name: null,
            phone: null,
        }
    },
    computed: {
        currentUser() {
            return this.$store.state.user.current
        },
    },
    methods: {
        login() {
            this.$store.dispatch('user/login', {
                name: this.name,
                phone: this.phone,
            })
        },
    },
}
</script>
