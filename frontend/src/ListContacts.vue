<template>
    <div class="list-contacts">
        <pagination v-if="pagination !== null" :data="pagination" v-model="page" @input="fetchContacts"/>

        <div v-if="contacts !== null">

            <table v-if="pageContactsLoaded" class="table is-fullwidth">
                <tbody>
                <tr v-for="contact in contacts" :key="contact.id">
                    <td>{{contact.attributes.first_name}}</td>
                    <td>{{contact.attributes.last_name}}</td>
                    <td>{{contact.attributes.email}}</td>
                </tr>
                </tbody>
            </table>

            <loading :active.sync="isLoading"></loading>

        </div>
    </div>
</template>

<style>
    .list-contacts {
        padding: 1rem;
    }
</style>

<script>

    import Pagination from './Pagination.vue';

    //a loading overlay (vue-loading-overlay) is shown to the user for better UX while contacts are loading
    // it is currently installed in package.json using "yarn add vue-loading-overlay"
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    //fetching contacts from server
    import {getAllContacts} from './contacts'

    export default {
        components: {
            Pagination,
            Loading
        },

        data() {
            return {
                page: 1,
                pagination: null,
                contacts: null,
                pageContactsLoaded: false,
            };
        },

        mounted() {
            this.fetchContacts();
        },
        computed: {
            isLoading: {
                get: function () {
                    return !this.pageContactsLoaded
                },
                set: function () {
                }
            },
        },
        methods: {
            // TODO: this is bugged, contacts appear as they load up rather than all at once.
            async fetchContacts() {

                //this.showError(); simulating an error notification
                this.isLoading = true;
                this.contacts = null;
                this.pageContactsLoaded = false;
                let contacts = [];
                let params = {page: this.page};

                try {

                    getAllContacts(params, contacts, this)
                    this.contacts = contacts;

                    //Note:
                    //another method to solve this issue is to use for loop instead of foreach, because foreach is not promise aware
                    // and awaiting inside its body does not wait for its proceeding statement (this.contacts = contacts) but I have
                    // solved the flicker using pageContactsLoaded flag and a v-if instead

                } catch (error) {
                    console.error(error) // Failure on fetching contact list header!
                    this.showError()
                }
            },
            showError: function () {
                this.$toast.open({
                    message: "An error occurred! please try later or contact our customer service.",
                    position: "top-left",
                    type: "error",
                    duration: 5000,
                    dismissible: true
                });
            },
        },
    }
</script>
