<template>
  <div id="app">

    <div class="py-4">
      <form action="" v-on:submit.prevent="fetchProjects">
        <div class="row">
          <div class="col-11">
            <v-select
                v-on:search="onQueryInput"
                :components="{OpenIndicator}"
                :dropdownShouldOpen="shouldOpen"
                :clearSearchOnBlur="() => false"
                :clearSearchOnSelect="false"
                :filterable="false"
            >
              <template v-slot:no-options="{ search, searching }">
                <div class="text-left">
                  Syntax
                </div>
              </template>

              <template v-slot:search="search">
                <span class="query-status icon"
                      :class="{'icon-ok': queryState.valid, 'icon-error': !queryState.valid}"
                ></span>

                <input
                    class="vs__search"
                    ref="input"
                    v-bind="search.attributes"
                    v-on="search.events"
                />

                <a target="_blank"
                   href="https://github.com/alorian/jql_transpiler"
                   class="help-link text-decoration-none"
                >
                  <span class="icon icon-question"></span>
                </a>
              </template>
            </v-select>
          </div>
          <div class="col-1">
            <button class="btn btn-primary w-100" type="submit">Search</button>
          </div>
        </div>
      </form>
    </div>

    <div v-if="queryState.errorsList" class="text-danger">
      <div v-for="error in queryState.errorsList">
        {{ error }}
      </div>
    </div>

    <ProjectsList :projects-list="projectsList"></ProjectsList>

  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import ProjectsList from "./components/ProjectsList.vue";
import OpenIndicator from "./components/OpenIndicator.vue";
import vSelect from 'vue-select';
import Project from "./interfaces/Project";
import QueryState from "./interfaces/QueryState"
import { debounce } from "lodash";
import Api from "./Api"

@Component({
  components: {
    ProjectsList,
    vSelect
  }
})
export default class App extends Vue {
  OpenIndicator: any = OpenIndicator

  projectsList: Project[] = []

  queryState: QueryState = {
    valid: true,
    query: '',
    caretPos: 0,
    suggestionsList: [],
    errorsList: []
  }

  queryInput = debounce(this.validateQuery, 350)

  created() {
    this.fetchProjects()
  }

  shouldOpen(): boolean {
    return false;
    //return this.queryState.suggestionsList.length > 0;
  }

  onQueryInput(query: string) {
    this.queryState.query = query

    if (this.$refs.input && this.$refs.input instanceof HTMLInputElement && this.$refs.input.selectionStart) {
      this.queryState.caretPos = this.$refs.input.selectionStart
    } else {
      this.queryState.caretPos = 0
    }

    this.queryInput()
  }

  async validateQuery() {
    try {
      const validationResponse = await Api.validateQuery(this.queryState.query, this.queryState.caretPos)
      this.queryState = validationResponse.data
    } catch (e: any) {
      this.queryState.valid = false;
      this.queryState.errorsList = [e.message]
    }
  }

  async fetchProjects() {
    try {
      const fetchResponse = await Api.fetchProjects(this.queryState.query, this.queryState.caretPos)
      this.queryState = fetchResponse.data.queryState
      if (this.queryState.valid) {
        this.projectsList = fetchResponse.data.projectsList
      }
    } catch (e: any) {
      this.queryState.valid = false;
      this.queryState.errorsList = [e.message]
    }
  }

}
</script>
