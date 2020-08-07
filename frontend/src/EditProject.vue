<template>
    <div class="edit-project">
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

                <router-link :to="'/projects/' + $route.params.project_id + ''"
                             class="level-right button is-info is-small">
                    View project
                </router-link>
            </div>
        </header>

        <article v-if="project" class="columns">
            <div class="column">
                <label class="is-size-7 has-text-weight-bold">Project</label>
                <br>
                <input type="text" v-model="project.attributes.title" class="input-box is-size-4"><br>

                <label class="is-size-7 has-text-weight-bold">System size</label>
                <p><select v-model="systemSizeSelection" class="has-text-grey">
                    <option value="None">None</option>
                    <option value="Specified">Specified</option>
                </select></p>
                <p v-show="project.attributes.system_size" class="is-size-7">Please specify:<input type="number" min="1"
                                                                                                   v-model.number="systemSize"
                                                                                                   class="ml-5 mr-5 system-size-input">kW
                </p>

                <label class="is-size-7 has-text-weight-bold">System details</label>
                <br>
                <textarea v-model="project.attributes.system_details" class="system-details-box input-box" rows="10">
        </textarea>
                <button v-if="shouldSaveProject || shouldSaveContacts" class="level-right button is-info is-small"
                        @click="onSave">
                    Save
                </button>

            </div>

            <div class="column">
                <div class="float-right" v-if="contacts.length==0">
                    No Contacts
                    <button class="svg-icon mr-5" @click="onAddNewContact">
                        <svg-icon width="20" height="20" :iconType="'add'" iconColor="white"/>
                    </button>
                </div>

                <contact-card v-for="(contact, index) in contacts"
                              :key="contact.id"
                              :contact="contact"
                              :shouldShowPlusButton="index+1==contacts.length"
                              :isEditable="isContactsEditable"
                              :allContactNames="allContactNames"
                              @onContactChanged="onContactChanged"
                              @onContactRemoved="onContactRemoved"
                              @onAddNewContact="onAddNewContact"
                              @onUseExistingContacts="onGetAllContacts"
                              class="edit-project__contact"/>
            </div>

            <loading :active.sync="isLoading"></loading>
            <loading :active.sync="isSaving"></loading>
        </article>
    </div>
</template>

<style scoped>

    .edit-project {
        padding: 1rem;
    }

    .edit-project__contact {
        margin: 1rem 0;
    }

    .edit-project__contact:first-child {
        margin-top: 0;
    }

    .edit-project__contact .level:not(:last-child) {
        margin-bottom: 0.75rem;
    }

    .system-size-input {
        width: 80px
    }

    .ml-5 {
        margin-left: 5px;
    }

    .mr-5 {
        margin-right: 5px;
    }

    .system-details-box {
        font-size: 1em;
    }

    .input-box {
        width: 400px
    }

    .svg-icon {
        border-color: #3298dc;
        color: #fff;
        box-shadow: 0 0 40px 40px #3498db inset, 0 0 0 0 #3298dc;
        padding: 2px;
        border-radius: 0px;
        cursor: pointer;
        font-size: larger;
        width: 30px;
        height: 30px;
    }

    .icon-container {
        justify-content: center;
        margin: 0 auto;
    }

    .float-right {
        float: right
    }

    .clear-float {
        clear: both
    }

</style>

<script>
    //todo: better to merge edit and view functionality on single file (instead of have EditProject.vue and ViewProject) although this
    //separation makes code more readable, it is less maintainable (less DRY)
    import {http} from './api.js';

    import ContactCard from './ContactCard.vue';

    //customizable svg icon component (used for + thumbnail button)
    import SvgIcon from "@/components/SvgIcon.vue";

    //a loading overlay (vue-loading-overlay) is shown to the user for better UX while contacts are loading
    // it is currently installed in package.json using "yarn add vue-loading-overlay"
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    //fetching contacts from server
    import {getAllContacts} from './contacts'

    export default {
        components: {
            ContactCard,
            SvgIcon,
            Loading
        },

        data() {
            return {
                project: null,
                shouldSaveProject: false,
                shouldSaveContacts: false,
                changeCount: 0,
                contacts: [],
                isContactsEditable: true,
                isLoading: false,
                isSavingContacts: false,
                isSavingProject: false,
                playing: false,
                //todo: rename below flag to a better name which explain the purpose consistent with ListContacts usage of it
                //flag to notify this component when fetching all contacts (used for contact lookup) is finished
                pageContactsLoaded: false,
                allContacts: [] //array to keep all existing contact used for lookup filling of contact details if history button (in ContactCard) clicked
            };
        },

        mounted() {
            this.isLoading = true;//show spinner
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

            onSave: function () {

                //save contact information
                if (this.shouldSaveContacts) {
                    if (this.isAnyContactFirstNameBlank()) { //some firstnames are blank, so reject saving and warn user
                        this.isSavingContacts = false;//hide spinner
                        this.showError("all contact should have at least their first name set!")
                    } else { // no firstname is left blank, so we can save contacts
                        let count = 0;
                        let totalCount = this.contacts.length;
                        if (totalCount > 0) {
                            this.isSavingContacts = true;//show spinner
                        }
                        //iterate though all contacts and either update or create them
                        this.contacts.forEach(async contact => {
                            try {
                                //if contact was existing try updating it, otherwise issue a create request
                                if (!contact.id.startsWith("browser_")) { // an id of integer is assigned if user has recently created this contact (prior to saving it)
                                    //update case
                                    await http.put('/contacts/' + contact.id, contact.attributes);
                                } else {
                                    //create case
                                    let response = await http.post('/contacts', contact.attributes);
                                    contact.id = response.data.data.id;
                                }

                                count++;
                                //if last contact reached, also show success message for UX user feedback
                                if (count == totalCount) {

                                    //will clear below flag after finish saving project contacts relation instead
                                    //this.shouldSaveContacts = false

                                    //save project contacts mappings
                                    this.saveProjectContactsRelation()

                                }

                            } catch (error) {

                                this.isSavingContacts = false;//hide spinner
                                console.error(error) // Failure on fetching each contact!
                                this.showError()
                            }
                        });
                    }
                }

                //save project information
                if (this.shouldSaveProject) {
                    const saveProject = async () => {
                        try {

                            this.isSavingProject = true;
                            await http.put('/solar_projects/' + this.$route.params.project_id, this.project.attributes);
                            this.isSavingProject = false;
                            this.shouldSaveProject = false
                            this.showProjectSaveSuccess();
                        } catch (error) {
                            this.isSavingProject = false;
                            console.error('error saving project!')
                            this.showError()
                        }
                    }
                    saveProject();
                }
            },
            async saveProjectContactsRelation() {
                try {
                    this.isSavingContacts = true;
                    let projectContactsMapping = this.getProjectContactsMapping()
                    await http.put('/solar_projects/' + this.$route.params.project_id + "/contacts", {data: projectContactsMapping});
                    this.isSavingContacts = false;
                    this.shouldSaveContacts = false;
                    this.showContactsSaveSuccess();

                } catch (error) {
                    console.error('error saving project contact mapping!')
                    this.showError()
                }
            },
            getProjectContactsMapping() {
                let projectContactsMapping = [];

                for (var ind in this.contacts) {
                    let contact = this.contacts[ind]
                    projectContactsMapping.push({
                        type: 'contacts',
                        id: contact.id
                    })
                }
                return projectContactsMapping;
            },
            updateShouldSave: function () {
                if (this.changeCount > 2) {
                    this.shouldSaveProject = true
                }
                this.changeCount++;
            },

            onContactChanged: function () {
                this.shouldSaveContacts = true
            },
            onContactRemoved: function (id) {
                this.removeContactById(id);
                if (!id.startsWith("browser_")) { //if we were an existing db contact, also refresh the project contacts relation
                    this.saveProjectContactsRelation()
                }
            },
            removeContactById: function (id) {
                let index = this.contacts.findIndex(contact => contact.id == id)
                this.contacts.splice(index, 1)
            },
            onAddNewContact: function () {
                this.contacts.push(
                    {
                        /*
                          assigning a browser side uuid with a "browser_" suffix let us keep track of contacts prior to obtaining
                          a uuid from server, for example for deleting unsaved newly created contacts
                         */
                        'id': "browser_" + this.$uuid.v1(),
                        'attributes': {
                            'first_name': '',
                            'last_name': '',
                            'email': '',
                        }
                    })
            },
            showError: function (message) {
                this.$toast.open({
                    message: message == null ? "An error occurred! please try later or contact our customer service." : message,
                    position: "top-left",
                    type: "error",
                    duration: 5000,
                    dismissible: true
                });
            },
            showProjectSaveSuccess: function () {
                this.$toast.open({
                    message: "Project successfully saved into database.",
                    position: "top-left",
                    type: "success",
                    duration: 3000,
                    dismissible: true
                });
            },
            showContactsSaveSuccess: function () {
                this.$toast.open({
                    message: "Contacts successfully saved into database.",
                    position: "top-left",
                    type: "success",
                    duration: 3000,
                    dismissible: true
                });
            },
            showAllContactsLoading: function () {
                this.$toast.open({
                    message: "Loading all contacts for the first time.Please wait...",
                    position: "top-left",
                    type: "info",
                    duration: 10000,
                    dismissible: true
                });
            },
            isAnyContactFirstNameBlank: function () {
                let oneBlankFirstNameFound = false;
                for (let ind in this.contacts) {
                    if (this.contacts[ind].attributes.first_name.trim() == "") {
                        oneBlankFirstNameFound = true;
                        break;
                    }
                }
                return oneBlankFirstNameFound;
            },
            onGetAllContacts: function () {

                /*
                  todo: 1- make faster lookup mechanism which searches on serverside and return fewer relevant results
                        2-devise a better passing of parameters for below function (preferably not "this" variable)
                        feth contacts from server on ContactCard press of fill from history (contact lookup) button

                 */
                if (this.allContacts.length == 0) {
                    this.isLoading = true
                    this.showAllContactsLoading()
                    getAllContacts(null, this.allContacts, this)
                }

            }

        },
        computed: {
            projectTitle: {
                get: function () {
                    //care for ajax delay for project assignment
                    if (this.project) {
                        return this.project.attributes.title
                    } else {
                        return null
                    }
                }
            },
            projectDetails: {
                get: function () {
                    //care for ajax delay for project assignment
                    if (this.project) {
                        return this.project.attributes.system_details
                    } else {
                        return null
                    }
                }
            },
            systemSizeSelection: {
                get: function () {

                    if (this.project) {
                        if (this.project.attributes.system_size == null) {
                            return "None"
                        } else {
                            return "Specified"
                        }
                    } else {
                        return "None"
                    }
                },
                //reset system size to null if None selected
                set: function (newvalue) {
                    if (newvalue == "None") {
                        this.project.attributes.system_size = null
                    } else {
                        this.project.attributes.system_size = 1
                    }
                    this.shouldSaveProject = true
                }
            },
            systemSize: {
                get: function () {
                    if (this.project) {
                        if (this.project.attributes.system_size) {
                            return this.project.attributes.system_size
                        } else {
                            return null
                        }
                    } else {
                        return 1
                    }
                },
                set: function (newvalue) {

                    //fall back to integer 1 if letter e is inputted in edit box
                    if (parseInt(newvalue) == null) {
                        newvalue = 1;
                    } else if (newvalue > 300 || newvalue < 1) {
                        newvalue = 1
                    }
                    this.project.attributes.system_size = newvalue
                    this.shouldSaveProject = true
                }
            },
            isSaving: {
                get: function () {
                    if (this.isSavingContacts || this.isSavingProject) {
                        return true
                    } else {
                        return false
                    }
                },
                set: function () {
                }
            },

            allContactNames: {
                set: function () {
                },
                get: function () {
                    if (this.pageContactsLoaded) {
                        let extractedContacts = [];
                        for (let ind in this.allContacts) {
                            let contact = this.allContacts[ind]
                            let contactFullName = contact.attributes.first_name + ' ' + contact.attributes.last_name;
                            extractedContacts.push({
                                id: contact.id,
                                full_name: contactFullName,
                                first_name: contact.attributes.first_name,
                                last_name: contact.attributes.last_name,
                                email: contact.attributes.email
                            })
                        }
                        return extractedContacts;
                    } else {
                        return null;
                    }
                }
            }

        },
        watch: {
            projectTitle: function (newvalue) {
                //only show save button if user input changes
                if (newvalue != null) {

                    this.updateShouldSave();
                }
            },
            projectDetails: function (newvalue) {
                //only show save button if user input changes
                if (newvalue != null) {
                    this.updateShouldSave();
                }
            },
            pageContactsLoaded: function (newvalue) {
                if (newvalue == true) {
                    this.isLoading = false
                }
            }
        },
        //prevent leave without saving if required
        beforeRouteLeave(to, from, next) {
            if (this.shouldSaveContacts || this.shouldSaveProject) {
                this.$dialog.confirm('You have unsaved changes.Do you want to continue without saving?')
                    .then(function () {
                        next();
                    })
                    .catch(function () {
                        next(false);
                    });
            } else {
                next();
            }
        }
    }
</script>
