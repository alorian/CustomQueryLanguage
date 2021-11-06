<template>
  <div class="py-3">
    <h2 class="mb-5">Projects list ({{ projectsList.length }}):</h2>
    <template v-if="projectsList.length > 0">
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Description</th>
            <th scope="col">Sort</th>
            <th scope="col">Source</th>
            <th scope="col">Epic</th>
            <th scope="col">Assignee</th>
            <th scope="col">Owner</th>
            <th scope="col">Responsible</th>
            <th scope="col">Created</th>
            <th scope="col">Updated</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="project in projectsList">
            <th scope="row">{{ project.id }}</th>
            <td>{{ project.name }}</td>
            <td>{{ project.description }}</td>
            <td>{{ project.sort }}</td>
            <td>{{ project.source }}</td>
            <td>{{ project.epic }}</td>
            <td>{{ project.assignee }}</td>
            <td>{{ project.owner }}</td>
            <td>{{ project.responsible }}</td>
            <td>{{ project.created_at }}</td>
            <td>{{ project.updated_at }}</td>
          </tr>
        </tbody>
      </table>

    </template>
    <template v-else>
      <div>No projects found</div>
    </template>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from "vue-property-decorator";
import Project from "../interfaces/Project"

@Component({})
export default class ProjectsList extends Vue {
  @Prop({default: []}) readonly projectsList!: Project[]
}
</script>
