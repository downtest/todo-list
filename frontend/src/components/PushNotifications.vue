<template>
    <div class="menu--icon-btn">
        <span class="icon" @click="toggleBellHandler">
            <img class="icon" :src="$store.getters['icons/Bell']" alt="Bell">
            <span v-if="!current" class="bell__cross"></span>
        </span>

        <!--        <button @click="testPushHandler">Test</button>-->
    </div>
</template>

<script>
if (location.protocol === 'https:') {
    import("../web-pushes/firebase")
    import("firebase/messaging").then(({getMessaging, onMessage}) => {
        const messaging = getMessaging()

        /**
         * Обработка push-уведомления
         */
        onMessage(messaging, (payload) => {
            console.log('Message received. ', payload);
            // ...

            navigator.serviceWorker.ready.then(function (serviceWorker) {
                serviceWorker.showNotification(payload.notification.title, payload.notification);
            });
        });
    })
}

export default {
    name: "PushNotifications",
    computed: {
        userId() {
            if (this.$store.getters['user/current']['id']) {
                this.$store.dispatch('firebase/load', this.$store.getters['user/current']['id'])
            }

            return this.$store.getters['user/current']['id']
        },
        tokens() {
            return this.$store.getters['firebase/all']
        },
        current() {
            return this.$store.getters['firebase/current']
        },
    },
    methods: {
        toggleBellHandler() {
            if (this.current) {
                if (window.confirm('Push-уведомления на этом устройстве будут отключены')) {
                    this.deleteTokenHandler()
                }
            } else {
                this.getTokenHandler()
            }
        },
        getTokenHandler(silent = false) {
            if (location.protocol !== 'https:') {
                if (!silent)
                    this.$store.dispatch('popupNotices/addWarning', {text: 'Для того, чтобы принимать push-уведомления, пожалуйста, перейдите на https://listodo.ru'})

                return;
            }

            if (!this.$store.getters['user/current']['id']) {
                if (!silent)
                    this.$store.dispatch('popupNotices/addWarning', {text: 'Для того, чтобы принимать push-уведомления, пожалуйста, авторизуйтесь'})

                // console.log(`Пользователь не авторизован, нельзя принимать push-уведомления`)
                return;
            }

            import("firebase/messaging").then(({getToken}) => {
                import('../web-pushes/firebase').then(firebase => {
                    getToken(firebase.default.messaging, {vapidKey: firebase.default.vapidKey}).then((currentToken) => {
                        if (currentToken) {
                            // Send the token to your server
                            this.$store.dispatch('firebase/store', {
                                userId: this.$store.getters['user/current']['id'],
                                firebaseToken: currentToken,
                            }).then(() => {
                                if (!silent)
                                    this.$store.dispatch('popupNotices/addSuccess', {text: 'Уведомления включены', duration: 3000})
                            })
                        } else {
                            // Show permission request UI
                            console.log('No registration token available. Request permission to generate one.');
                            // ...
                        }
                    }).catch((err) => {
                        console.log('An error occurred while retrieving token. ', err);
                        // ...
                    })
                })
            })
        },
        deleteTokenHandler() {
            if (!this.$store.getters['firebase/current']) {
                console.info('У пользователя нет current firebase token')
                return
            }

            this.$store.dispatch('firebase/delete', {
                userId: this.$store.getters['user/current']['id'],
                token: this.$store.getters['firebase/current']['token'],
                tokenId: this.$store.getters['firebase/current']['id'],
            })
        },
        testPushHandler() {
            this.$store.dispatch('firebase/send', {
                foo: 'bar123',
            }).then((response) => {
                console.log(response, `done`)
            })
        },
    },
    mounted() {
        this.$store.dispatch('firebase/load').then(tokens => {
            console.log(tokens, `tokens loaded`)

            if (!this.$store.getters['firebase/current']) {
                this.getTokenHandler(true)
            }
        })
    },
}
</script>
