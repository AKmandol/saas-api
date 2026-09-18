<template>

    <aside class="sidebar bg-dark text-white p-3">

        <h4 class="mb-4">
            SaaS Platform
        </h4>

        <nav class="nav flex-column gap-1">

            <router-link
                to="/dashboard"
                class="nav-link"
            >
                Dashboard
            </router-link>

            <router-link
                to="/customers"
                class="nav-link"
            >
                Customers
            </router-link>

            <router-link
                to="/users"
                class="nav-link"
            >
                Users
            </router-link>

            <router-link
                to="/company"
                class="nav-link"
            >
                Company
            </router-link>

            <router-link
                to="/subscription"
                class="nav-link"
            >
                Subscription
            </router-link>

        </nav>

    </aside>

</template>

<script>
export default {
    name: 'Sidebar',
};
</script>
