import {
    createRouter,
    createWebHistory,
} from 'vue-router';

import Login from '../pages/Login.vue';
import Dashboard from '../pages/Dashboard.vue';
import Customers from '../pages/Customers.vue';
import Users from '../pages/Users.vue';
import Company from '../pages/Company.vue';
import Subscription from '../pages/Subscription.vue';

const routes = [

    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: {
            guest: true,
        },
    },

    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            requiresAuth: true,
        },
    },

    {
        path: '/customers',
        name: 'customers',
        component: Customers,
        meta: {
            requiresAuth: true,
        },
    },

    {
        path: '/users',
        name: 'users',
        component: Users,
        meta: {
            requiresAuth: true,
        },
    },

    {
        path: '/company',
        name: 'company',
        component: Company,
        meta: {
            requiresAuth: true,
        },
    },

    {
        path: '/subscription',
        name: 'subscription',
        component: Subscription,
        meta: {
            requiresAuth: true,
        },
    },

    {
        path: '/',
        redirect: '/dashboard',
    },

    {
        path: '/:pathMatch(.*)*',
        redirect: '/dashboard',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {

    const token = localStorage.getItem('token');

    if (to.meta.requiresAuth && !token) {
        return {
            name: 'login',
        };
    }

    if (to.meta.guest && token) {
        return {
            name: 'dashboard',
        };
    }

    return true;
});

export default router;
