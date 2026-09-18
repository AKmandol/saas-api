<template>

    <AppLayout title="Customers">

        <!-- Header -->

        <div
            class="d-flex justify-content-between align-items-center mb-4"
        >

            <h2 class="mb-0">
                Customers
            </h2>

            <button
                class="btn btn-primary"
                @click="openCreateModal"
            >
                + Add Customer
            </button>

        </div>

        <!-- Alert -->

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

        <!-- Filters -->

        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            v-model="filters.search"
                            type="text"
                            class="form-control"
                            placeholder="Name, email or phone"
                            @keyup.enter="fetchCustomers(1)"
                        >

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            v-model="filters.status"
                            class="form-select"
                            @change="fetchCustomers(1)"
                        >

                            <option value="">
                                All
                            </option>

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>

                    </div>

                    <div class="col-md-3 d-flex align-items-end">

                        <button
                            class="btn btn-secondary w-100"
                            @click="resetFilters"
                        >
                            Reset
                        </button>

                    </div>

                </div>

            </div>

        </div>

        <!-- Loading -->

        <div
            v-if="loading"
            class="text-center py-5"
        >
            <div class="spinner-border"></div>
        </div>

        <!-- Table -->

        <div
            v-else
            class="card shadow-sm"
        >

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                v-for="customer in customers"
                                :key="customer.id"
                            >

                                <td>
                                    {{ customer.id }}
                                </td>

                                <td>
                                    {{ customer.name }}
                                </td>

                                <td>
                                    {{ customer.email }}
                                </td>

                                <td>
                                    {{ customer.phone ?? '-' }}
                                </td>

                                <td>

                                    <span
                                        class="badge"
                                        :class="
                                            customer.status === 'active'
                                                ? 'bg-success'
                                                : 'bg-secondary'
                                        "
                                    >
                                        {{ customer.status }}
                                    </span>

                                </td>

                                <td>

                                    <button
                                        class="btn btn-sm btn-outline-primary me-2"
                                        @click="openEditModal(customer)"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        class="btn btn-sm btn-outline-danger"
                                        @click="deleteCustomer(customer)"
                                    >
                                        Delete
                                    </button>

                                </td>

                            </tr>

                            <tr v-if="customers.length === 0">

                                <td
                                    colspan="6"
                                    class="text-center py-4"
                                >
                                    No customers found.
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <!-- Pagination -->

        <div class="mt-4">

            <Pagination
                :meta="pagination"
                @change="fetchCustomers"
            />

        </div>

        <!-- Modal -->

        <div
            class="modal fade"
            :class="{ show: showModal }"
            :style="{
                display: showModal ? 'block' : 'none'
            }"
            tabindex="-1"
        >

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">

                            {{
                                editing
                                    ? 'Edit Customer'
                                    : 'Add Customer'
                            }}

                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            @click="closeModal"
                        ></button>

                    </div>

                    <form @submit.prevent="saveCustomer">

                        <div class="modal-body">

                            <!-- Name -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Name
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                >

                                <div
                                    v-if="formErrors.name"
                                    class="text-danger small"
                                >
                                    {{ formErrors.name[0] }}
                                </div>

                            </div>

                            <!-- Email -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                >

                                <div
                                    v-if="formErrors.email"
                                    class="text-danger small"
                                >
                                    {{ formErrors.email[0] }}
                                </div>

                            </div>

                            <!-- Phone -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Phone
                                </label>

                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="form-control"
                                >

                                <div
                                    v-if="formErrors.phone"
                                    class="text-danger small"
                                >
                                    {{ formErrors.phone[0] }}
                                </div>

                            </div>

                            <!-- Status -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Status
                                </label>

                                <select
                                    v-model="form.status"
                                    class="form-select"
                                >

                                    <option value="active">
                                        Active
                                    </option>

                                    <option value="inactive">
                                        Inactive
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button
                                type="button"
                                class="btn btn-secondary"
                                @click="closeModal"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="saving"
                            >
                                {{
                                    saving
                                        ? 'Saving...'
                                        : 'Save'
                                }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        <div
            v-if="showModal"
            class="modal-backdrop fade show"
        ></div>

    </AppLayout>

</template>

<script>
import api from '../services/api';
import AppLayout from '../layouts/AppLayout.vue';
import Pagination from '../components/Pagination.vue';

export default {

    name: 'Customers',

    components: {
        AppLayout,
        Pagination,
    },

    data() {

        return {

            customers: [],

            pagination: null,

            loading: false,

            saving: false,

            error: null,

            success: null,

            showModal: false,

            editing: false,

            editingId: null,

            filters: {
                search: '',
                status: '',
            },

            form: {
                name: '',
                email: '',
                phone: '',
                status: 'active',
            },

            formErrors: {},
        };

    },

    mounted() {

        this.fetchCustomers();

    },

    methods: {

        async fetchCustomers(page = 1) {

            this.loading = true;
            this.error = null;

            try {

                const params = new URLSearchParams();

                params.append('page', page);
                params.append('per_page', 10);

                if (this.filters.search) {
                    params.append(
                        'search',
                        this.filters.search
                    );
                }

                if (this.filters.status) {
                    params.append(
                        'status',
                        this.filters.status
                    );
                }

                const response =
                    await api.get(
                        `/customers?${params.toString()}`
                    );

                this.customers =
                    response.data;

                this.pagination =
                    response.meta;

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to load customers.';

            } finally {

                this.loading = false;

            }
        },

        openCreateModal() {

            this.editing = false;
            this.editingId = null;

            this.form = {
                name: '',
                email: '',
                phone: '',
                status: 'active',
            };

            this.formErrors = {};
            this.error = null;

            this.showModal = true;

        },

        openEditModal(customer) {

            this.editing = true;
            this.editingId = customer.id;

            this.form = {
                name: customer.name,
                email: customer.email,
                phone: customer.phone ?? '',
                status: customer.status,
            };

            this.formErrors = {};
            this.error = null;

            this.showModal = true;

        },

        closeModal() {

            this.showModal = false;
            this.formErrors = {};

        },

        async saveCustomer() {

            this.saving = true;
            this.formErrors = {};
            this.error = null;

            try {

                if (this.editing) {

                    await api.put(
                        `/customers/${this.editingId}`,
                        this.form
                    );

                    this.success =
                        'Customer updated successfully.';

                } else {

                    await api.post(
                        '/customers',
                        this.form
                    );

                    this.success =
                        'Customer created successfully.';
                }

                this.closeModal();

                await this.fetchCustomers(
                    this.pagination?.current_page ?? 1
                );

            } catch (error) {

                if (error.status === 422) {

                    this.formErrors =
                        error.data?.errors ?? {};

                    this.error =
                        error.data?.message ??
                        'Validation failed.';

                } else {

                    this.error =
                        error.data?.message ??
                        'Operation failed.';

                }

            } finally {

                this.saving = false;

            }
        },

        async deleteCustomer(customer) {

            const confirmed = window.confirm(
                `Delete ${customer.name}?`
            );

            if (!confirmed) {
                return;
            }

            try {

                await api.delete(
                    `/customers/${customer.id}`
                );

                this.success =
                    'Customer deleted successfully.';

                await this.fetchCustomers(
                    this.pagination?.current_page ?? 1
                );

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to delete customer.';

            }
        },

        resetFilters() {

            this.filters = {
                search: '',
                status: '',
            };

            this.fetchCustomers(1);

        },
    },
};
</script>
