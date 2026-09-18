<template>

    <AppLayout title="Users">

        <div
            class="d-flex justify-content-between align-items-center mb-4"
        >

            <h2 class="mb-0">
                Users
            </h2>

            <button
                class="btn btn-primary"
                @click="openCreateModal"
            >
                + Add User
            </button>

        </div>

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

        <!-- Search -->

        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <div class="row">

                    <div class="col-md-8">

                        <input
                            v-model="search"
                            type="text"
                            class="form-control"
                            placeholder="Search by name or email"
                            @keyup.enter="fetchUsers(1)"
                        >

                    </div>

                    <div class="col-md-4">

                        <button
                            class="btn btn-secondary w-100"
                            @click="resetSearch"
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
                                <th>Role</th>
                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                v-for="user in users"
                                :key="user.id"
                            >

                                <td>
                                    {{ user.id }}
                                </td>

                                <td>
                                    {{ user.name }}
                                </td>

                                <td>
                                    {{ user.email }}
                                </td>

                                <td>

                                    <span
                                        class="badge bg-primary"
                                    >
                                        {{ user.role }}
                                    </span>

                                </td>

                                <td>

                                    <button
                                        class="btn btn-sm btn-outline-primary me-2"
                                        @click="openEditModal(user)"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        class="btn btn-sm btn-outline-danger"
                                        @click="deleteUser(user)"
                                    >
                                        Delete
                                    </button>

                                </td>

                            </tr>

                            <tr v-if="users.length === 0">

                                <td
                                    colspan="5"
                                    class="text-center py-4"
                                >
                                    No users found.
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="mt-4">

            <Pagination
                :meta="pagination"
                @change="fetchUsers"
            />

        </div>

        <!-- Modal -->

        <div
            class="modal fade"
            :class="{ show: showModal }"
            :style="{
                display: showModal ? 'block' : 'none'
            }"
        >

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">

                            {{
                                editing
                                    ? 'Edit User'
                                    : 'Add User'
                            }}

                        </h5>

                        <button
                            class="btn-close"
                            @click="closeModal"
                        ></button>

                    </div>

                    <form @submit.prevent="saveUser">

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

                            <!-- Password -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    :placeholder="
                                        editing
                                            ? 'Leave blank to keep current password'
                                            : ''
                                    "
                                >

                                <div
                                    v-if="formErrors.password"
                                    class="text-danger small"
                                >
                                    {{ formErrors.password[0] }}
                                </div>

                            </div>

                            <!-- Confirm Password -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Confirm Password
                                </label>

                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="form-control"
                                >

                            </div>

                            <!-- Role -->

                            <div class="mb-3">

                                <label class="form-label">
                                    Role
                                </label>

                                <select
                                    v-model="form.role"
                                    class="form-select"
                                >

                                    <option value="admin">
                                        Admin
                                    </option>

                                    <option value="manager">
                                        Manager
                                    </option>

                                    <option value="user">
                                        User
                                    </option>

                                </select>

                                <div
                                    v-if="formErrors.role"
                                    class="text-danger small"
                                >
                                    {{ formErrors.role[0] }}
                                </div>

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

    name: 'Users',

    components: {
        AppLayout,
        Pagination,
    },

    data() {

        return {

            users: [],

            pagination: null,

            search: '',

            loading: false,

            saving: false,

            error: null,

            success: null,

            showModal: false,

            editing: false,

            editingId: null,

            form: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                role: 'user',
            },

            formErrors: {},

        };

    },

    mounted() {

        this.fetchUsers();

    },

    methods: {

        async fetchUsers(page = 1) {

            this.loading = true;
            this.error = null;

            try {

                const params = new URLSearchParams();

                params.append('page', page);
                params.append('per_page', 10);

                if (this.search) {

                    params.append(
                        'search',
                        this.search
                    );

                }

                const response =
                    await api.get(
                        `/users?${params.toString()}`
                    );

                this.users =
                    response.data;

                this.pagination =
                    response.meta;

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to load users.';

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
                password: '',
                password_confirmation: '',
                role: 'user',
            };

            this.formErrors = {};
            this.showModal = true;

        },

        openEditModal(user) {

            this.editing = true;
            this.editingId = user.id;

            this.form = {
                name: user.name,
                email: user.email,
                password: '',
                password_confirmation: '',
                role: user.role,
            };

            this.formErrors = {};
            this.showModal = true;

        },

        closeModal() {

            this.showModal = false;
            this.formErrors = {};

        },

        async saveUser() {

            this.saving = true;
            this.formErrors = {};
            this.error = null;

            try {

                const payload = {
                    name: this.form.name,
                    email: this.form.email,
                    role: this.form.role,
                };

                if (this.form.password) {

                    payload.password =
                        this.form.password;

                    payload.password_confirmation =
                        this.form.password_confirmation;
                }

                if (this.editing) {

                    await api.put(
                        `/users/${this.editingId}`,
                        payload
                    );

                    this.success =
                        'User updated successfully.';

                } else {

                    payload.password =
                        this.form.password;

                    payload.password_confirmation =
                        this.form.password_confirmation;

                    await api.post(
                        '/users',
                        payload
                    );

                    this.success =
                        'User created successfully.';
                }

                this.closeModal();

                await this.fetchUsers(
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

        async deleteUser(user) {

            if (
                !window.confirm(
                    `Delete ${user.name}?`
                )
            ) {
                return;
            }

            try {

                await api.delete(
                    `/users/${user.id}`
                );

                this.success =
                    'User deleted successfully.';

                await this.fetchUsers(
                    this.pagination?.current_page ?? 1
                );

            } catch (error) {

                this.error =
                    error.data?.message ??
                    'Failed to delete user.';

            }
        },

        resetSearch() {

            this.search = '';

            this.fetchUsers(1);

        },
    },
};
</script>
