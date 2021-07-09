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
