<template>
    <div>
        <h3>Password reset</h3>

        <div v-if="currentUser.id">
            Вы уже авторизованы
        </div>

        <div v-else-if="$route.query.hash">
            <div>Установка нового пароля</div>

            <ul class="errors" v-if="validationErrors"><li v-for="error in validationErrors">{{error.join('\n')}}</li></ul>

            <div class="input">
                Email: <input :disabled="true" v-model="email" type="text" name="email">
            </div>

            <div :class="{input: true, 'error': validationErrors.password}">
                password: <input :class="{'error-input': validationErrors.password}" v-model="password" :type="showPasswords ? 'text' : 'password'" name="password">
            </div>

            <span class="btn-icon" @click="showPasswords = !showPasswords">
                <img class="icon" :src="showPasswords ? require('/assets/icons/eye_crossed.svg') : require('/assets/icons/eye_opened.svg')" alt="">
            </span>

            <div :class="{input: true, 'error': validationErrors.email}">
                password repeat: <input :class="{'error-input': validationErrors.passwordRepeat}" v-model="passwordRepeat" :type="showPasswords ? 'text' : 'password'" name="password-repeat">
            </div>

            <p>Пароль должен состоять минимум из 5 символов</p>

            <button @click="restore" :disabled="!couldBeRestored">Change password</button>
        </div>

    </div>
</template>

<script>
export default {
    name: 'user',
    data() {
        return {
            showSuccessMsg: false,
            email: this.$store.getters['user/current']['email'],
            showPasswords: false,
            password: null,
            passwordRepeat: null,
            validationErrors: {},
        }
    },
    computed: {
        currentUser() {
            return this.$store.state.user.current
        },
        couldBeRestored() {
            return !!this.password && this.password.length > 4 && this.password === this.passwordRepeat
        },
    },
    methods: {
        restore() {
            this.validationErrors = {}

            if (!this.couldBeRestored) {
                return
            }

            this.$store.dispatch('user/passwordReset', {
                email: this.email,
                hash: this.$route.query.hash,
                password: this.password,
            })
                .then((data) => {
                    this.$router.push({name: 'profile'})

                    if (data.status) {
                        this.showSuccessMsg = true
                    } else if (data.errors) {
                        this.validationErrors = data.errors || {}
                    }
                })
                .catch(({response}) => {
                    if (response && response.data && response.data.errors) {
                        this.validationErrors = response.data.errors
                    }
                })
        },
    },
    mounted() {
        if (!this.$route.query.hash) {
            return
        }

        this.$store.dispatch('user/getEmailByHash', this.$route.query.hash)
            .then(data => {
                if (data && data.status && data.email) {
                    this.email = data.email
                }
            })
            .catch(({response}) => {
                if (response && response.data && response.data.errors) {
                    this.validationErrors = response.data.errors
                }
            })
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

.btn-icon .icon {
    width: 30px;
}
</style>
