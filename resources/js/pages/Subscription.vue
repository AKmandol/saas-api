<template>

    <AppLayout title="Subscription">

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
            class="text-center py-5"
        >
            <div class="spinner-border"></div>
        </div>

        <template v-else>

            <!-- Current subscription -->

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h4 class="mb-3">
                        Current Subscription
                    </h4>

                    <div v-if="subscription">

                        <div class="row">

                            <div class="col-md-3">

                                <strong>
                                    Plan
                                </strong>

                                <div>
                                    {{ subscription.plan.name }}
                                </div>

                            </div>

                            <div class="col-md-3">

                                <strong>
                                    Status
                                </strong>

                                <div>

                                    <span
                                        class="badge bg-success"
                                    >
                                        {{ subscription.status }}
                                    </span>

                                </div>

                            </div>

                            <div class="col-md-3">

                                <strong>
                                    Starts
                                </strong>

                                <div>
                                    {{ formatDate(
                                        subscription.starts_at
                                    ) }}
                                </div>

                            </div>

                            <div class="col-md-3">

                                <strong>
                                    Ends
                                </strong>

                                <div>
                                    {{ formatDate(
                                        subscription.ends_at
                                    ) }}
                                </div>

                            </div>

                        </div>

                    </div>

                    <div v-else>

                        No active subscription.

                    </div>

                </div>

            </div>

            <!-- Plans -->

            <h4 class="mb-3">
                Available Plans
            </h4>

            <div class="row g-4">

                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="col-md-4"
                >

                    <div
                        class="card shadow-sm h-100"
                        :class="{
                            'border border-primary':
                                subscription?.plan?.id === plan.id
                        }"
                    >

                        <div class="card-body">

                            <h4>
                                {{ plan.name }}
                            </h4>

                            <p class="text-muted">
                                {{ plan.slug }}
                            </p>

                            <hr>

                            <h6>
                                Features
                            </h6>

                            <ul class="list-group mb-4">

                                <li
                                    v-for="
                                        feature
                                        in plan.features
                                    "
                                    :key="feature.id"
                                    class="list-group-item d-flex justify-content-between"
                                >

                                    <span>
                                        {{ feature.feature }}
                                    </span>

                                    <strong>
                                        {{ feature.limit }}
                                    </strong>

                                </li>

                            </ul>

                            <button
                                class="btn w-100"
                                :class="
                                    subscription?.plan?.id === plan.id
                                        ? 'btn-secondary'
                                        : 'btn-primary'
                                "
                                :disabled="
                                    subscription?.plan?.id === plan.id
                                    || changing
                                "
                                @click="changePlan(plan)"
                            >

                                {{
                                    subscription?.plan?.id === plan.id
                                        ? 'Current Plan'
                                        : changing
                                            ? 'Updating...'
                                            : 'Choose Plan'
                                }}

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </template>

    </AppLayout>

</template>

<script>
import api from '../services/api';
import AppLayout from '../layouts/AppLayout.vue';

export default {

    name: 'Subscription',

    components: {
        AppLayout,
    },

    data() {

        return {

            subscription: null,

            plans: [],

            loading: false,

            changing: false,

            error: null,

            success: null,

        };

    },

    mounted() {

        this.loadData();

    },

    methods: {

        async loadData() {

            this.loading = true;
            this.error = null;

            try {

                const [
                    subscriptionResponse,
                    plansResponse,
                ] = await Promise.all([
                    api.get('/subscription'),
                    api.get('/plans'),
                ]);

                this.subscription =
                    subscriptionResponse.data;

                this.plans =
                    plansResponse.data;

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to load subscription.';

            } finally {

                this.loading = false;

            }
        },

        async changePlan(plan) {

            if (
                !window.confirm(
                    `Change subscription to ${plan.name}?`
                )
            ) {
                return;
            }

            this.changing = true;
            this.error = null;
            this.success = null;

            try {

                const response =
                    await api.put(
                        '/subscription',
                        {
                            plan_id: plan.id,
                        }
                    );

                this.subscription =
                    response.data;

                this.success =
                    'Subscription updated successfully.';

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to update subscription.';

            } finally {

                this.changing = false;

            }
        },

        formatDate(date) {

            if (!date) {
                return '-';
            }

            return new Date(date)
                .toLocaleDateString();

        },
    },
};
</script>
