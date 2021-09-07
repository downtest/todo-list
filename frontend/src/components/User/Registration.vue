<template>
    <div>
        <h3>Registration</h3>

        <ul class="errors">
            <li v-for="error in validationErrors.common">{{error}}</li>
        </ul>

        <div v-if="currentUser.id">
            Вы уже зарегистрированы
            <pre>{{currentUser}}</pre>
        </div>
        <div v-else>
            <div :class="{input: true, 'error': validationErrors.email}">
                Email: <input :class="{'error-input': validationErrors.email}" v-model="email" type="text" name="email">
                <div v-if="validationErrors.email" class="error-notification">{{validationErrors.email.join('\n')}}</div>
            </div>

            <div :class="{input: true, 'error': validationErrors.phone}">
                Phone: <input :class="{'error-input': validationErrors.phone}" v-model="phone" type="text" name="phone">
                <div v-if="validationErrors.phone" class="error-notification">{{validationErrors.phone.join('\n')}}</div>
            </div>

            <div :class="{input: true, 'error': validationErrors.password}">
                Password: <input :class="{'error-input': validationErrors.password}" v-model="password" type="text" name="password">
                <div v-if="validationErrors.password" class="error-notification">{{validationErrors.password.join('\n')}}</div>
            </div>

            <button @click="register">Register</button>
        </div>

    </div>
</template>

<script>
export default {
    name: 'user',
    data() {
        return {
            email: null,
            phone: null,
            password: null,
            validationErrors: {},
        }
    },
    computed: {
        currentUser() {
            return this.$store.state.user.current
        },
    },
    methods: {
        register() {
            this.validationErrors = {}

            this.$store.dispatch('user/register', {
                phone: this.phone,
                email: this.email,
                password: this.password,
                name: this.name,
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
.errors {
    color: #ae2a2a;
}

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
