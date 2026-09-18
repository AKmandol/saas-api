<template>

    <AppLayout title="Company">

        <div class="row justify-content-center">

            <div class="col-md-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h4 class="mb-4">
                            Company Settings
                        </h4>

                        <div
                            v-if="success"
                            class="alert alert-success"
                        >
                            {{ success }}
                        </div>

                        <div
                            v-if="error"
                            class="alert alert-danger"
                        >
                            {{ error }}
                        </div>

                        <div
                            v-if="loading"
                            class="text-center py-4"
                        >
                            <div class="spinner-border"></div>
                        </div>

                        <form
                            v-else
                            @submit.prevent="updateCompany"
                        >

                            <div class="mb-3">

                                <label class="form-label">
                                    Company Name
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                >

                                <div
                                    v-if="errors.name"
                                    class="text-danger small"
                                >
                                    {{ errors.name[0] }}
                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Slug
                                </label>

                                <input
                                    v-model="form.slug"
                                    type="text"
                                    class="form-control"
                                >

                                <div
                                    v-if="errors.slug"
                                    class="text-danger small"
                                >
                                    {{ errors.slug[0] }}
                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Status
                                </label>

                                <input
                                    :value="company?.status"
                                    type="text"
                                    class="form-control"
                                    disabled
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="saving"
                            >

                                {{
                                    saving
                                        ? 'Updating...'
                                        : 'Update Company'
                                }}

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </AppLayout>

</template>

<script>
import api from '../services/api';
import AppLayout from '../layouts/AppLayout.vue';

export default {

    name: 'Company',

    components: {
        AppLayout,
    },

    data() {

        return {

            company: null,

            form: {
                name: '',
                slug: '',
            },

            loading: false,

            saving: false,

            error: null,

            success: null,

            errors: {},

        };

    },

    mounted() {

        this.fetchCompany();

    },

    methods: {

        async fetchCompany() {

            this.loading = true;
            this.error = null;

            try {

                const response =
                    await api.get('/company');

                this.company =
                    response.data;

                this.form = {
                    name: this.company.name,
                    slug: this.company.slug,
                };

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to load company.';

            } finally {

                this.loading = false;

            }
        },

        async updateCompany() {

            this.saving = true;
            this.errors = {};
            this.error = null;
            this.success = null;

            try {

                const response =
                    await api.put(
                        '/company',
                        this.form
                    );

                this.company =
                    response.data;

                this.form = {
                    name: this.company.name,
                    slug: this.company.slug,
                };

                this.success =
                    'Company updated successfully.';

            } catch (error) {

                if (error.status === 422) {

                    this.errors =
                        error.data?.errors ?? {};

                    this.error =
                        error.data?.message ??
                        'Validation failed.';

                } else {

                    this.error =
                        error.data?.message ??
                        'Failed to update company.';

                }

            } finally {

                this.saving = false;

            }
        },
    },
};
</script>
