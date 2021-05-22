<template>
    <div>
        <h1>{{ $route.meta.title }}</h1>

        <p>And some static text here!</p>
    </div>
</template>

<script>
    export default {
        props: ['title'],
    }
</script>

<style lang="scss">

</style>
