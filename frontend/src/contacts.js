import {http} from "./api";

/*
 This contact fetching mechanism is put in a separate file as EditProject.vue (project contacts assignment page) also needs to lookup
  existing contacts besides its main listing purpose in ListContact.vue
 */
export async function getAllContacts(params, contacts, parentComponent) {
    let response = await http.get('/contacts', {params});

    if (parentComponent != null) {
        parentComponent.pagination = response.data.meta;
    }
    let thisPageTotal = response.data.data.length;


    //each call for fetching individual contacts are asynchronous to each other (will be done in parallel) due to
    // below async keyword which is good for sake of performance
    response.data.data.forEach(async contact => {
        try {
            let response = await http.get('/contacts/' + contact.id);

            contacts.push(response.data.data);

            //only flag page load as completed if accumulating contacts length get to total expected for this page
            if (contacts.length == thisPageTotal) {
                if (parentComponent != null) {
                    parentComponent.pageContactsLoaded = true;
                }
            }

            console.log(contact)

        } catch (error) {
            console.error(error) // Failure on fetching each contact!
            if (parentComponent != null) {
                this.showError()
            }
        }

    });


}
