<template>
    <div>
        <h3>Changind User {{currentUser.phone}}!</h3>

        <div v-if="currentUser.id">
            <pre>{{currentUser}}</pre>

            <div>Id: <input v-model="id" disabled="disabled" type="text"></div>
            <div :class="{input: true, 'error': validationErrors.name}">
                Name: <input :class="{'error-input': validationErrors.name}" v-model="name" type="text" name="name">
                <div v-if="validationErrors.name" class="error-notification">{{validationErrors.name.join('\n')}}</div>
            </div>
            <div :class="{input: true, 'error': validationErrors.email}">
                Email: <input :class="{'error-input': validationErrors.email}" v-model="email" type="text" name="email">
                <div v-if="validationErrors.email" class="error-notification">{{validationErrors.email.join('\n')}}</div>
            </div>
            <div :class="{input: true, 'error': validationErrors.phone}">
                Phone: <input :class="{'error-input': validationErrors.phone}" v-model="phone" type="text" name="phone">
                <div v-if="validationErrors.phone" class="error-notification">{{validationErrors.phone.join('\n')}}</div>
            </div>

            <button @click="changeUser">Change</button>
            <button @click="logout">Logout</button>
        </div>

        <div v-else>
            <div>#{{id}}</div>

            <div :class="{input: true, 'error': validationErrors.email}">
                Email: <input :class="{'error-input': validationErrors.email}" v-model="email" type="text" name="email">
                <div v-if="validationErrors.email" class="error-notification">{{validationErrors.email.join('\n')}}</div>
            </div>

            <div :class="{input: true, 'error': validationErrors.password}">
                Password: <input :class="{'error-input': validationErrors.password}" v-model="password" type="text" name="password">
                <div v-if="validationErrors.password" class="error-notification">{{validationErrors.password.join('\n')}}</div>
            </div>

            <button @click="login">Login</button>
            <a href="/password-forget">Restore password</a>
        </div>

    </div>
</template>

<script>
export default {
    name: 'user',
    data() {
        return {
            id: this.$store.getters['user/current']['id'],
            email: this.$store.getters['user/current']['email'],
            password: this.$store.getters['user/current']['password'],
            name: this.$store.getters['user/current']['name'],
            phone: this.$store.getters['user/current']['phone'],
            validationErrors: {},
        }
    },
    computed: {
        currentUser() {
            return this.$store.state.user.current
        },
    },
    watch: {
        currentUser(value) {
            this.id = value.id
            this.email = value.email
            this.password = value.password
            this.name = value.name
            this.phone = value.phone
        },
    },
    methods: {
        login() {
            this.validationErrors = {}

            this.$store.dispatch('user/login', {
                email: this.email,
                password: this.password,
            })
                .catch(({response}) => {
                    if (response && response.data && response.data.errors) {
                        this.validationErrors = response.data.errors
                    }
                })
        },
        logout() {
            this.validationErrors = {}

            this.$store.dispatch('user/logout')
                .then((data) => {
                    this.id = data.id
                    this.email = data.email
                    this.password = data.password
                    this.name = data.name
                    this.phone = data.phone
                })
        },
        changeUser() {
            this.validationErrors = {}

            this.$store.dispatch('user/update', {
                id: this.id,
                email: this.email,
                password: this.password,
                name: this.name,
                phone: this.phone,
            })
            .then((data) => {
                this.id = data.id
                this.email = data.email
                this.password = data.password
                this.name = data.name
                this.phone = data.phone
            })
            .catch(({response}) => {
                if (response && response.data && response.data.errors) {
                    this.validationErrors = response.data.errors
                }
            })
        },
    },
}
</script>

<style scoped lang="scss">
.input {
    width: 100px;

    &.error {

        .error-input {
            border-color: #ae2a2a;
            font-size: .7em;
        }

        .error-notification {
            color: #ae2a2a;
            font-size: .7em;
        }
    }
}
</style>
