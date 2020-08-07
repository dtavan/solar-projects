<template>
    <div class="view-project-item">
        <div class="level is-mobile">
            <router-link :to="'/projects/' + project.id">
                <h2 class="is-size-5">{{project.attributes.title}}</h2>

                <div class="bottom has-text-grey">
                    <span v-show="project.attributes.system_size">System: {{project.attributes.system_size}}kW</span>
                    <span v-show="!project.attributes.system_size">No system design</span>
                </div>
            </router-link>

            <a :href="getProjectLocationUrl(project)"
               target="_blank"
               class="view-project-item__map-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-map-pin">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </a>
        </div>
    </div>
</template>

<style>
    .view-project-item {
        padding: .75rem 1.25rem;
        padding-right: 0.75rem; /* account for map-link padding */
    }

    .view-project-item h2 {
        margin-bottom: 0.25rem;
    }

    .view-project-item__map-link {
        position: relative;
        padding: .5rem;
    }
</style>

<script>
    export default {
        props: {
            project: {
                required: true,
            },
        },

        methods: {
            getProjectLocationUrl(project) {
                let lon = project.attributes.site_longitude;
                let lat = project.attributes.site_latitude;
                return `https://www.openstreetmap.org/#map=18/${lat}/${lon}`;
            },
        },
    }
</script>
