<template>
    <div class="view-project">
        <header>
            <div class="level is-mobile margin-bottom-3">
                <router-link to="/projects" class="level-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-arrow-left-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 8 8 12 12 16"></polyline>
                        <line x1="16" y1="12" x2="8" y2="12"></line>
                    </svg>
                    <span class="margin-left-2">All projects</span>
                </router-link>

                <router-link :to="'/projects/' + $route.params.project_id + '/edit'"
                             class="level-right button is-info is-small">
                    Edit project
                </router-link>
            </div>
        </header>

        <article v-if="project" class="columns">
            <div class="column">
                <label class="is-size-7 has-text-weight-bold">Project</label>
                <h1 class="is-size-4">{{project.attributes.title}}</h1>

                <label class="is-size-7 has-text-weight-bold">System size</label>
                <p v-show="project.attributes.system_size">{{project.attributes.system_size}}kW</p>
                <p v-show="!project.attributes.system_size" class="has-text-grey">none</p>

                <label class="is-size-7 has-text-weight-bold">System details</label>
                <p>
                    {{project.attributes.system_details}}
                </p>
            </div>

            <div class="column">
                <contact-card v-for="contact in contacts"
                              :key="contact.id"
                              :contact="contact"
                              :isEditable="isContactsEditable"
                              class="view-project__contact"/>
            </div>

            <loading :active.sync="isLoading"></loading>
        </article>
    </div>
</template>

<style>
    .view-project {
        padding: 1rem;
    }

    .view-project__contact {
        margin: 1rem 0;
    }

    .view-project__contact:first-child {
        margin-top: 0;
    }

    .view-project__contact .level:not(:last-child) {
        margin-bottom: 0.75rem;
    }
</style>

<script>
    import {http} from './api.js';

    import ContactCard from './ContactCard.vue';

    //a loading overlay (vue-loading-overlay) is shown to the user for better UX while contacts are loading
    // it is currently installed in package.json using "yarn add vue-loading-overlay"
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components: {
            ContactCard,
            Loading
        },

        data() {
            return {
                project: null,
                contacts: [],
                isContactsEditable: false,
                isLoading: false
            };
        },

        mounted() {
            this.isLoading = true;//show spinner //todo: find a better solution for turn on\off spinner when dealing with async functions
            this.fetchProjectDetails();
            this.fetchProjectContacts();

        },

        methods: {
            async fetchProjectDetails() {
                let response = await http.get('/solar_projects/' + this.$route.params.project_id);
                this.project = response.data.data;

            },

            async fetchProjectContacts() {
                this.contacts = [];
                let response = await http.get('/solar_projects/' + this.$route.params.project_id + '/contacts');
                response.data.data.forEach(async contact => {
                    let response = await http.get('/contacts/' + contact.id);
                    this.contacts.push(response.data.data);
                });
                this.isLoading = false;//hide spinner
            },
        },
    }
</script>
