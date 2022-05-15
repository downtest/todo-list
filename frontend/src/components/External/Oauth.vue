<template>
  <div>
    <h1>Авторизация через {{service}}</h1>

    <div class="errors">
        <div class="error" v-for="error in errors">{{error}}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Oauth",
  data() {
    return {
      errors: [],
      response: null,
    }
  },
  computed: {
    service() {
      return this.$route.params.service
    },
  },
  mounted() {
    if (!this.service) {
      console.error(`Не задан сервис`, this.$route.params)
    }

    switch (this.service) {
        case 'vk':
            let code = this.$route.query.code

            this.axios.get(`${process.env.VUE_APP_BACKEND_HOST}/api/external/oauth/vk`, {params: {code: code}})
                .then((response) => {
                    this.response = response.data

                    if (response.data && response.data.status) {
                        if (!response.data.user || !response.data.token || !response.data.collections || !response.data.currentCollection) {
                            this.errors.push("Внутренняя ошибка авторизации, пожалуйста, используйте другой метод авторизации, либо обратитесь в нашу тех.поддержку");
                        }

                        window.opener.$store.commit('user/update', response.data.user)
                        window.opener.$store.commit('user/setToken', response.data.token)
                        window.opener.$store.commit('collections/setCollections', response.data.collections)
                        window.opener.$store.commit('collections/setCurrentCollectionId', response.data.currentCollection.id)

                        window.opener.$store.dispatch('todos/load', {
                            clientId: response.data.user.id,
                            collectionId: response.data.currentCollection ? response.data.currentCollection.id : null,
                            force: true,
                        })

                        // Redirect
                        // window.opener.$router.push({name: 'profile'})

                        window.close()
                    } else if (response.data && response.data.errors) {
                        this.errors = response.data.errors
                    } else {
                        this.errors.push('Непредвиденный ответ от сервера ' + this.service)
                    }
                })
                .catch((response) => {
                    if (response.response.data && response.response.data.errors) {
                        this.errors = response.response.data.errors
                    } else {
                        this.errors.push(response)
                    }
                })

            break
    }
  },
}
</script>
