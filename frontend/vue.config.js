process.env.VUE_APP_BACKEND_HOST = process.env.BACKEND_HOST
process.env.VUE_APP_EXTERNAL_OAUTH_FULL_URL = process.env.EXTERNAL_OAUTH_FULL_URL

module.exports = {
    runtimeCompiler: true,
    pages: {
        index: {
            entry: 'src/app.js',
            template: 'public/index.html',
            filename: 'index.html',
        },
        landing1: {
            entry: 'src/main.js',
            template: 'public/landing1.html',
            filename: 'landing1.html',
        },
    },
}
