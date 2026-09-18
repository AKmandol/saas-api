<template>

    <header class="bg-white border-bottom p-3">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                {{ title }}
            </h5>

            <div class="d-flex align-items-center">

                <span class="me-3">
                    {{ user?.name }}
                </span>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    @click="logout"
                >
                    Logout
                </button>

            </div>

        </div>

    </header>

</template>

<script>
import api from '../services/api';

export default {

    name: 'Navbar',

    props: {
        title: {
            type: String,
            default: 'Dashboard',
        },
    },

    data() {
        return {
            user: null,
        };
    },

    mounted() {
        this.loadUser();
    },

    methods: {

        async loadUser() {
    try {
        const response = await api.get('/auth/me');

        this.user = response.data.user;
    } catch (error) {
        if (error.status === 401) {
            this.$router.push('/login');
        }
    }
},

        async logout() {

            try {

                await api.post('/auth/logout');

            } catch (error) {

                console.error(error);

            } finally {

                localStorage.removeItem('token');
                localStorage.removeItem('user');

                this.$router.push('/login');

            }
        },
    },
};
</script>
