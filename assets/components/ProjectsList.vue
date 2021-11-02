<template>
  <div class="py-3">
    <h2>Projects list:</h2>
    <template v-if="projectsList.length > 0">
    <div v-for="project in projectsList" class="py-2">
      {{ project.name }}
    </div>
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
  @Prop({ default: [] }) readonly projectsList!: Project[]
}
</script>
