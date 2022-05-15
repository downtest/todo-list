<template>
    <div>
        <div v-if="currentUser.id">
            <h3>Добро пожаловать!</h3>

            <div class="input">
                <div class="input--title">ID:</div>
                <div class="input--input">
                    <input v-model="id" disabled="disabled" type="text" name="id">
                </div>
            </div>

            <div :class="{input: true, 'error': validationErrors.name}">
                <div class="input--title">Name:</div>
                <div class="input--input">
                    <input :class="{'error-input': validationErrors.name}" v-model="name" type="text" name="name">
                    <div v-if="validationErrors.name" class="error-notification">{{validationErrors.name.join('\n')}}</div>
                </div>
            </div>

            <div :class="{input: true, 'error': validationErrors.email}">
                <div class="input--title">Email:</div>
                <div class="input--input">
                    <input :class="{'error-input': validationErrors.email}" v-model="email" type="text" name="email">
                    <div v-if="validationErrors.email" class="error-notification">{{validationErrors.email.join('\n')}}</div>
                </div>
            </div>

            <div :class="{input: true, 'error': validationErrors.phone}">
                <div class="input--title">Phone:</div>
                <div class="input--input">
                    <input :class="{'error-input': validationErrors.phone}" v-model="phone" type="text" name="phone">
                    <div v-if="validationErrors.phone" class="error-notification">{{validationErrors.phone.join('\n')}}</div>
                </div>
            </div>

            <button @click="changeUser">Change</button>
            <button @click="logout">Logout</button>
        </div>

        <div v-else>
            <h3>Авторизация</h3>

            <div class="auth-buttons">
              <a class="auth-button" @click="openVkOAuth">
                <img :src="$store.getters['icons/Oauth']['vk']" alt="vk">
              </a>
            </div>

            <div :class="{input: true, 'error': validationErrors.email}">
              <div class="input--title">Email:</div>
              <div class="input--input">
                <input :class="{'error-input': validationErrors.email}" v-model="email" type="text" name="email">
                <div v-if="validationErrors.email" class="error-notification">{{validationErrors.email.join('\n')}}</div>
              </div>
            </div>

            <div :class="{input: true, 'error': validationErrors.password}">
              <div class="input--title">Password:</div>
              <div class="input--input">
                <input :class="{'error-input': validationErrors.password}" v-model="password" type="text" name="password">
                <div v-if="validationErrors.password" class="error-notification">{{validationErrors.password.join('\n')}}</div>
              </div>
            </div>

            <button @click="login">Login</button>

            <div>
                <br>
                <router-link :to="{name: 'password-forget'}">
                    Забыли пароль?
                </router-link>

                <br>
                <br>
                <router-link :to="{name: 'registration'}">
                    Зарегистрироваться
                </router-link>
            </div>
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

                    if (response && response.data && response.data.error) {
                        this.$store.dispatch('popupNotices/addError', {text: response.data.error})
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
        openVkOAuth() {
            let oauthUrl = process.env.VUE_APP_EXTERNAL_OAUTH_FULL_URL
            let vkUrl = this.$router.resolve({
              name: 'external-oauth',
              params: {service: 'vk'}
            }).fullPath

            window.open(
                `https://oauth.vk.com/authorize?client_id=8156687&redirect_uri=${oauthUrl}/vk&display=page&scope=4194304`,
                "_blank",
                "toolbar=1, scrollbars=1, resizable=1, width=" + 600 + ", height=" + 400
            )
        },
    },
}
</script>
