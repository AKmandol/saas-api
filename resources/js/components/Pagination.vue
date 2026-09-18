<template>

    <nav v-if="meta && meta.last_page > 1">

        <ul class="pagination">

            <li
                class="page-item"
                :class="{ disabled: meta.current_page === 1 }"
            >

                <button
                    class="page-link"
                    @click="$emit('change', meta.current_page - 1)"
                    :disabled="meta.current_page === 1"
                >
                    Previous
                </button>

            </li>

            <li
                v-for="page in pages"
                :key="page"
                class="page-item"
                :class="{
                    active: page === meta.current_page
                }"
            >

                <button
                    class="page-link"
                    @click="$emit('change', page)"
                >
                    {{ page }}
                </button>

            </li>

            <li
                class="page-item"
                :class="{
                    disabled:
                        meta.current_page === meta.last_page
                }"
            >

                <button
                    class="page-link"
                    @click="$emit(
                        'change',
                        meta.current_page + 1
                    )"
                    :disabled="
                        meta.current_page === meta.last_page
                    "
                >
                    Next
                </button>

            </li>

        </ul>

    </nav>

</template>

<script>
export default {

    name: 'Pagination',

    props: {
        meta: {
            type: Object,
            default: null,
        },
    },

    emits: ['change'],

    computed: {

        pages() {

            if (!this.meta) {
                return [];
            }

            const pages = [];

            for (
                let i = 1;
                i <= this.meta.last_page;
                i++
            ) {
                pages.push(i);
            }

            return pages;
        },
    },
};
</script>
