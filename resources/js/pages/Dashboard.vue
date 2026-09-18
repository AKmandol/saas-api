<template>

    <AppLayout title="Dashboard">

        <div
            v-if="error"
            class="alert alert-danger"
        >
            {{ error }}
        </div>

        <div
            v-if="loading"
            class="text-center py-5"
        >
            <div
                class="spinner-border"
                role="status"
            ></div>
        </div>

        <template v-else-if="dashboard">

            <div class="row g-4 mb-4">

                <!-- Customers -->

                <div class="col-md-4">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h6 class="text-muted">
                                Total Customers
                            </h6>

                            <h2>
                                {{ dashboard.customers.total }}
                            </h2>

                            <div class="small text-muted">

                                Active:
                                {{ dashboard.customers.active }}

                                |

                                Inactive:
                                {{ dashboard.customers.inactive }}

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Users -->

                <div class="col-md-4">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h6 class="text-muted">
                                Total Users
                            </h6>

                            <h2>
                                {{ dashboard.users.total }}
                            </h2>

                        </div>

                    </div>

                </div>

                <!-- Subscription -->

                <div class="col-md-4">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h6 class="text-muted">
                                Current Plan
                            </h6>

                            <h2>
                                {{
                                    dashboard.subscription?.plan
                                    ?? 'N/A'
                                }}
                            </h2>

                            <span
                                class="badge bg-success"
                            >
                                {{
                                    dashboard.subscription?.status
                                    ?? 'N/A'
                                }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Company -->

            <div class="card shadow-sm">

                <div class="card-body">

                    <h5 class="mb-3">
                        Company Overview
                    </h5>

                    <p class="mb-1">
                        <strong>Company:</strong>
                        {{ company?.name }}
                    </p>

                    <p class="mb-0">
                        <strong>Slug:</strong>
                        {{ company?.slug }}
                    </p>

                </div>

            </div>

        </template>

    </AppLayout>

</template>

<script>
import api from '../services/api';
import AppLayout from '../layouts/AppLayout.vue';

export default {

    name: 'Dashboard',

    components: {
        AppLayout,
    },

    data() {

        return {
            dashboard: null,
            company: null,
            loading: false,
            error: null,
        };

    },

    mounted() {

        this.loadDashboard();

    },

    methods: {

        async loadDashboard() {

            this.loading = true;
            this.error = null;

            try {

                const [
                    dashboardResponse,
                    companyResponse,
                ] = await Promise.all([
                    api.get('/dashboard'),
                    api.get('/company'),
                ]);

                this.dashboard =
                    dashboardResponse.data;

                this.company =
                    companyResponse.data;

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to load dashboard.';

            } finally {

                this.loading = false;

            }
        },
    },
};
</script>
