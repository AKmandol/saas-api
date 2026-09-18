<template>
    <div class="container">

        <div class="row justify-content-center mt-5">

            <div class="col-md-5 col-lg-4">

                <div class="card shadow-sm">

                    <div class="card-body p-4">

                        <h3 class="text-center mb-4">
                            SaaS Platform
                        </h3>

                        <div
                            v-if="error"
                            class="alert alert-danger"
                        >
                            {{ error }}
                        </div>

                        <form @submit.prevent="login">

                            <div class="mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    placeholder="Enter email"
                                    required
                                >

                                <div
                                    v-if="errors.email"
                                    class="text-danger small mt-1"
                                >
                                    {{ errors.email[0] }}
                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    placeholder="Enter password"
                                    required
                                >

                                <div
                                    v-if="errors.password"
                                    class="text-danger small mt-1"
                                >
                                    {{ errors.password[0] }}
                                </div>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                                :disabled="loading"
                            >
                                {{
                                    loading
                                        ? 'Logging in...'
                                        : 'Login'
                                }}
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
</template>

<script>
import api from '../services/api';

export default {

    name: 'Login',

    data() {
        return {
            form: {
                email: 'owner@acme.test',
                password: '',
            },

            errors: {},

            error: null,

            loading: false,
        };
    },

    methods: {

        async login() {
    this.loading = true;
    this.error = null;
    this.errors = {};

    try {
        const response = await api.post('/auth/login', this.form);

        const authData = response.data;

        localStorage.setItem('token', authData.token);

        if (authData.user) {
            localStorage.setItem(
                'user',
                JSON.stringify(authData.user)
            );
        }

        localStorage.setItem(
            'roles',
            JSON.stringify(authData.roles || [])
        );

        localStorage.setItem(
            'permissions',
            JSON.stringify(authData.permissions || [])
        );

        this.$router.push('/dashboard');
    } catch (error) {
        if (error.status === 422) {
            this.errors = error.data?.errors || {};
        } else {
            this.error =
                error.data?.message ||
                'Login failed. Please check your credentials.';
        }
    } finally {
        this.loading = false;
    }
}
    },
};
</script>
