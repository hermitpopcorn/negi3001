<template>
    <section class="section">
        <h2>Page Not Found</h2>
        <p>That URL unfortunately doesn't link to anywhere. You can <router-link :to="'/overview'" exact>click here</router-link> to go back to the overview dashboard.</p>
    </section>
</template>

<script>
export default {
    name: 'errorNotFound'
}
</script>
