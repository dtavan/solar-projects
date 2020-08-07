/*
todo: add validation for email box (currently save fails from server side if email is not well formatted)
*/
<template>
    <div class="box">
        <div class="level is-mobile">
            <div class="level-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-user">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span class="margin-left-2" v-if="!isEditable">{{contact.attributes.first_name}} {{contact.attributes.last_name}}</span>
                <span class="margin-left-2" v-if="isEditable">
          first name:<input type="text" v-model="contact.attributes.first_name" class="name-input ml-5 mb-3 is-size-6"
                            @keyup="onEditChanged"><br>
          last name:<input type="text" v-model="contact.attributes.last_name" class="name-input ml-7 is-size-6"
                           @keyup="onEditChanged">
        </span>
            </div>
        </div>

        <div class="level is-mobile">
            <div class="level-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-mail">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <span class="margin-left-2" v-if="!isEditable">{{contact.attributes.email}}</span>
                <span class="margin-left-2" v-if="isEditable">
          email:<input type="text" v-model="contact.attributes.email" class="email-input" @keyup="onEditChanged">
        </span>
            </div>
        </div>

        <div style="width:100%" v-if="showSavedContactsSelect">
            <div style="width: 70%;"><span style="float:left">Fill from existing contact: </span>
                <div class="contacts-lookup">
                    <Select2 v-model="selectedContactName" :options="allContactNamesOnly" :settings="{width: '220px'}"
                             @select="onContactSelected($event)"/>
                </div>
            </div>
        </div>
        <div v-if="isEditable " class="float-right">
            <button class="svg-icon mr-5" @click="onUseExistingContacts">
                <svg-icon width="20" height="20" :iconType="'history'" iconColor="white"/>
            </button>
            <button v-if="shouldShowPlusButton" class="svg-icon mr-5" @click="onAddNewContact">
                <svg-icon width="20" height="20" :iconType="'add'" iconColor="white"/>
            </button>
            <button class="svg-icon" @click="onRemoveClicked">
                <svg-icon width="20" height="20" :iconType="'remove'" iconColor="white"/>
            </button>
        </div>

        <div class="clear-float"></div>

        <loading :active.sync="isDeleting"></loading>

    </div>
</template>
<style scoped>

    .ml-5 {
        margin-left: 5px;
    }

    .mb-3 {
        margin-bottom: 3px;
    }

    .ml-7 {
        margin-left: 7px;
    }

    .mr-5 {
        margin-right: 5px;
    }

    .name-input {
        width: 300px
    }

    .email-input {
        margin-left: 37px;
        width: 300px
    }

    .contacts-lookup {
        margin-left: 190px;
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

    //customizable svg icon component (used for +- thumbnail button)
    import SvgIcon from "@/components/SvgIcon.vue";

    import {http} from "./api";

    //a loading overlay (vue-loading-overlay) is shown to the user for better UX while contacts are loading
    // it is currently installed in package.json using "yarn add vue-loading-overlay"
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components: {
            SvgIcon,
            Loading
        },
        props: {
            contact: {
                required: true,
            },
            isEditable: {
                type: Boolean
            },
            shouldShowPlusButton: {
                type: Boolean,
                default: false
            },
            allContactNames: {
                type: Array
            }
        },
        data() {
            return {
                isDeleting: false,
                selectedContactName: "",
                //allContactNames:["a1","a2","a3"],
                showSavedContactsSelect: false

            }
        },
        computed: {
            allContactNamesOnly: {
                get: function () {
                    if (this.allContactNames != null) {
                        return this.allContactNames.map(contactName => {
                            return contactName.full_name;
                        })

                    } else {
                        return []
                    }
                }
            }
        },
        methods: {
            onEditChanged: function () {
                if (this.contact.attributes.first_name.length > 0) {
                    this.$emit("onContactChanged")

                }
            },
            onRemoveClicked: function () {
                //console.log(this.contact)
                this.removeContact();
            },
            async removeContact() {
                try {
                    this.isDeleting = true;
                    if (!this.contact.id.startsWith("browser_")) { //if id is a uuid it refers a db existing contact and therefore need to delete it in db
                        await http.delete('/contacts/' + this.contact.id, {});
                        this.showDeleteSuccess();
                    }

                    //also delete it from memory (parent component contacts array)
                    this.$emit("onContactRemoved", this.contact.id)
                    this.isDeleting = false;
                } catch (error) {
                    this.isDeleting = false;
                    console.error('error deleting contact!')
                    this.showDeleteError()
                }
            },
            showDeleteSuccess: function () {
                this.$toast.open({
                    message: "Contact successfully deleted from database.",
                    position: "top-left",
                    type: "success",
                    duration: 3000,
                    dismissible: true
                });
            },
            showDeleteError: function () {
                this.$toast.open({
                    message: "Error deleting contact from database!",
                    position: "top-left",
                    type: "error",
                    duration: 3000,
                    dismissible: true
                });
            },
            onAddNewContact: function () {
                this.$emit('onAddNewContact')
            },
            onUseExistingContacts: function () {
                this.showSavedContactsSelect = !this.showSavedContactsSelect;
                this.$emit('onUseExistingContacts')
            },
            onContactSelected: function ({id, text}) {
                //todo: make select2 accept uuids as ids

                //workaround to find the uuid
                let index = this.allContactNamesOnly.findIndex(full_name => full_name == text);
                let contactId = this.allContactNames[index].id;
                console.log(id) //for temp bypassing lint not used error
                //todo: write more validation logic to prevent repeated selection of same contacts for single project
                this.fillEditsFromExistingContact(contactId)

                //notify parent to show save button
                this.$emit("onContactChanged")

                /*todo: for more consideration on adding new contacts:
                       1- if + button clicked and fresh contact is filled in, when saving it, first check the combination of firstname lastname and
                          email address already exists or not, if it exists, reuse existing contact (reuse its uuid)
                    */
            },
            fillEditsFromExistingContact(id) {
                let index = this.allContactNames.findIndex(contact => contact.id == id);
                let contact = this.allContactNames[index]
                //dont forget to keep id
                this.contact.id = id
                this.contact.attributes.first_name = contact.first_name
                this.contact.attributes.last_name = contact.last_name
                this.contact.attributes.email = contact.email
            }
        }
    }
</script>
