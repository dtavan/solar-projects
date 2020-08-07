import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

//for notifications
import VueToast from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-default.css';

Vue.use(VueToast)
Vue.use(VuejsDialog)

//for dialog confirmations
import VuejsDialog from "vuejs-dialog"
import 'vuejs-dialog/dist/vuejs-dialog.min.css';

//required to keep track of new contacts before saving to database (before issuing uuid from server)
import UUID from "vue-uuid";

Vue.use(UUID);

//used for searching and reusing existing contacts from a dropdown
import Select2 from 'v-select2-component';

Vue.component('Select2', Select2);

import App from './src/App.vue';
import ListProjects from './src/ListProjects.vue';
import ViewProject from './src/ViewProject.vue';
import EditProject from './src/EditProject.vue';
import ListContacts from './src/ListContacts.vue';

import './vendor/bulma.min.css';
import './src/app.css';

new Vue({
    el: '#app',
    render: make => make(App),
    router: new VueRouter({
        routes: [
            {
                path: '',
                redirect: '/projects',
            },
            {
                path: '/projects',
                component: ListProjects,
            },
            {
                path: '/projects/:project_id',
                component: ViewProject,
            },
            {
                path: '/projects/:project_id/edit',
                component: EditProject,
            },
            {
                path: '/contacts',
                component: ListContacts,
            },
        ],
        mode: 'history',
    }),
});

document.title = 'Pylon Demo App';
