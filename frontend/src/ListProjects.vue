<template>
    <div class="view-projects">
        <pagination v-if="pagination !== null" :data="pagination" v-model="page" @input="fetchListOfProjects"/>

        <div v-if="projects !== null">
            <ul>
                <li v-for="project in projects" :key="project.id">
                    <view-project-item :project="project" v-if="project.attributes"/>
                    <div v-else class="view-projects__placeholder" :style="{'animation-delay': 1 * 50 + 'ms'}"/>
                </li>
            </ul>
        </div>
    </div>
</template>

<style>
    .view-projects {
        padding: 1rem;
    }

    .view-projects .pagination {
        padding: 0 1rem;
    }

    .view-projects .view-project-item {
        margin: 1rem 0;
    }

    .view-projects .view-project-item:hover {
        background: #fafafa;
    }

    .view-projects__placeholder {
        background: grey;
        height: 5.125rem;
        margin: 1rem 0;

        /* See https://codepen.io/ErEllison/pen/ajVYqb */
        animation-duration: 1s;
        animation-fill-mode: forwards;
        animation-iteration-count: infinite;
        animation-name: placeHolderShimmer;
        animation-timing-function: linear;
        background: #f6f7f8;
        background: linear-gradient(to right, #fafafa 8%, #f4f4f4 38%, #fafafa 54%);
        background-size: 1000px 640px;
        position: relative;
    }

    @keyframes placeHolderShimmer {
        0% {
            background-position: -468px 0
        }
        100% {
            background-position: 468px 0
        }
    }
</style>

<script>
    import {http} from './api.js';

    import Pagination from './Pagination.vue';
    import ViewProjectItem from './ViewProjectItem.vue';

    export default {
        components: {
            Pagination,
            ViewProjectItem,
        },

        data() {
            return {
                page: 1,
                pagination: null,
                projects: null,
            };
        },

        mounted() {
            this.fetchListOfProjects();
        },

        methods: {
            async fetchListOfProjects() {
                let params = {page: this.page};
                let response = await http.get('/solar_projects', {params});
                this.projects = response.data.data;
                this.pagination = response.data.meta;
                this.fetchAllProjectDetails();
            },

            fetchAllProjectDetails() {
                this.projects.forEach(async (project, i) => {
                    let id = project.id;
                    let response = await http.get('/solar_projects/' + id);
                    if (this.projects[i] && this.projects[i].id === id) {
                        this.projects.splice(i, 1, response.data.data);
                    }
                });
            },
        },
    }
</script>
