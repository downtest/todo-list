<template>
    <div>
        <h3>Forget Password</h3>

        <div v-if="currentUser.id">
            Вы уже авторизованы
        </div>

        <div v-else-if="showSuccessMsg">
            На вашу почту {{email}} отправлено письмо с инструкциями для сброса пароля
        </div>

        <form @submit.prevent="restore" v-else>
            <ul class="errors" v-if="validationErrors.common"><li v-for="error in validationErrors.common">{{error}}</li></ul>

            <div :class="{input: true, 'error': validationErrors.email}">
                Email: <input :class="{'error-input': validationErrors.email}" v-model="email" type="text" name="email">
                <div v-if="validationErrors.email" class="error-notification">{{validationErrors.email.join('\n')}}</div>
            </div>

            <button>Restore</button>
        </form>

    </div>
</template>

<script>
export default {
    name: 'user',
    data() {
        return {
            email: '',
            validationErrors: {},
            loading: false,
            showSuccessMsg: false,
        }
    },
    computed: {
        currentUser() {
            return this.$store.state.user.current
        },
    },
    methods: {
        restore() {
            this.loading = true
            this.validationErrors = {}

            this.$store.dispatch('user/passwordForget', this.email)
                .then((data) => {
                    if (data.status) {
                        this.showSuccessMsg = true
                    } else {
                        this.validationErrors = data.errors || {}
                    }
                })
                .catch(({response}) => {
                    if (response && response.data && response.data.errors) {
                        this.validationErrors = response.data.errors
                    }
                })
                .finally(() => {this.loading = false})
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
