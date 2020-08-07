<template>
    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        <a @click="prev" :disabled="value <= 1" class="pagination-previous">Previous page</a>
        <a @click="next" :disabled="value >= data.last_page" class="pagination-next">Next page</a>
        <label class="pagination-list">
            {{data.current_page}} of {{data.last_page}}
        </label>
    </nav>
</template>

<script>
    export default {
        props: {
            data: {
                type: Object,
                required: true,
            },
            value: {
                type: Number,
            },
        },

        methods: {
            prev() {
                this.$emit('input', this.value - 1);
            },

            next() {
                this.$emit('input', this.value + 1);
            },
        },
    }
</script>
