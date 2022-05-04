<template>
    <div>
        <h3>Регистрация</h3>

        <ul class="errors">
            <li v-for="error in validationErrors.common">{{error}}</li>
        </ul>

        <div v-if="currentUser.id">
            Вы уже зарегистрированы
            <pre>{{currentUser}}</pre>
        </div>
        <div v-else>
            <div :class="{input: true, 'error': validationErrors.email}">
              <div class="input--title">Email*:</div>
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

            <div :class="{input: true, 'error': validationErrors.password}">
              <div class="input--title">Password*:</div>
              <div class="input--input">
                <input :class="{'error-input': validationErrors.password}" v-model="password" type="text" name="password">
                <div v-if="validationErrors.password" class="error-notification">{{validationErrors.password.join('\n')}}</div>
              </div>
            </div>

            <button @click="register">Зарегистрироваться</button>
        </div>

        <br>
        <router-link :to="{name: 'profile'}">
            На страницу авторизации
        </router-link>

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
