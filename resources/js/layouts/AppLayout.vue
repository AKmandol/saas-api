<template>

    <div class="d-flex min-vh-100">

        <Sidebar />

        <div class="flex-grow-1 main-content">

            <Navbar :title="title" />

            <main class="p-4">

                <slot />

            </main>

        </div>

    </div>

</template>

<script>
import Sidebar from '../components/Sidebar.vue';
import Navbar from '../components/Navbar.vue';

export default {

    name: 'AppLayout',

    components: {
        Sidebar,
        Navbar,
    },

    props: {
        title: {
            type: String,
            default: 'Dashboard',
        },
    },
};
</script>
