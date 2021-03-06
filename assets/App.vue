<template>
  <div id="app">

    <div class="py-4">
      <form action="" v-on:submit.prevent="fetchProjects">
        <div class="row">
          <div class="col-11">
            <v-select
                v-on:search="onQueryInput"
                v-on:input="onSuggestionSelect"
                :value="''"
                :components="{OpenIndicator}"
                :dropdownShouldOpen="shouldOpen"
                :clearSearchOnBlur="() => false"
                :clearSearchOnSelect="false"
                :filterable="false"
                :clearable="false"
                :options="queryState.suggestionsList"
                :transition="''"
                :select-on-key-codes="[]"
                :map-keydown="keydownHandlers"
            >

              <template v-slot:no-options="{ search, searching }"></template>

              <template v-slot:option="suggestion">
                <span v-html="highlightSuggestion(suggestion)"></span>
              </template>

              <template v-slot:search="search">
                <span class="query-status icon"
                      :class="{'icon-ok': queryState.valid, 'icon-error': !queryState.valid}"
                ></span>

                <input
                    class="vs__search"
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

    <div v-if="showErrors && queryState.errorsList" class="text-danger">
      <div v-for="error in queryState.errorsList">
        {{ error }}
      </div>
    </div>

    <h2 class="mt-3 mb-4">Projects list ({{ projectsCountAnimated }}):</h2>

    <transition name="fade">
      <ProjectsList :projects-list="projectsList" v-if="!projectsLoading"></ProjectsList>
    </transition>

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
import gsap from "gsap";

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

  projectsLoading = true

  projectsCountPrev = 0;

  get projectsCountAnimated() {
    return this.projectsCountPrev.toFixed(0);
  }

  showErrors = false

  queryInput = debounce(this.validateQuery, 200)

  created() {
    this.fetchProjects()
  }

  shouldOpen(VueSelect: any): boolean {
    return this.queryState.suggestionsList.length > 0 && VueSelect.open;
  }

  onQueryInput(query: string) {
    this.showErrors = false
    this.queryState.query = query

    if (this.$refs.search && this.$refs.search instanceof HTMLInputElement && this.$refs.search.selectionStart) {
      this.queryState.caretPos = this.$refs.search.selectionStart
      this.$refs.search.focus()
    } else {
      this.queryState.caretPos = 0
    }

    this.queryInput()
  }

  onSuggestionSelect(suggestion: { label: string, value: string }) {
    let newQuery = this.queryState.query;
    newQuery = newQuery.substring(0, this.queryState.caretPos) + suggestion.value + newQuery.substring(this.queryState.caretPos);

    const newCaretPos = this.queryState.caretPos + suggestion.value.length

    this.queryState.suggestionsList = []

    if (this.$refs.search && this.$refs.search instanceof HTMLInputElement) {
      this.$refs.search.value = newQuery
      this.$refs.search.selectionStart = newCaretPos

      this.$refs.search.dispatchEvent(new Event('input', {
        bubbles: true,
        cancelable: true,
      }))
    }
  }

  async validateQuery() {
    try {
      const validationResponse = await Api.validateQuery(this.queryState.query, this.queryState.caretPos)
      this.queryState.valid = validationResponse.data.valid
      this.queryState.suggestionsList = validationResponse.data.suggestionsList
      this.queryState.errorsList = validationResponse.data.errorsList
    } catch (e: any) {
      this.queryState.valid = false;
      this.queryState.errorsList = [e.message]
    }
  }

  async fetchProjects() {
    this.queryState.errorsList = []
    this.showErrors = true
    this.projectsLoading = true
    try {
      const fetchResponse = await Api.fetchProjects(this.queryState.query, this.queryState.caretPos)
      this.queryState.valid = fetchResponse.data.queryState.valid
      this.queryState.suggestionsList = fetchResponse.data.queryState.suggestionsList
      this.queryState.errorsList = fetchResponse.data.queryState.errorsList
      this.projectsList = fetchResponse.data.projectsList

      // animation projects number
      gsap.to(this.$data, { duration: 0.5, projectsCountPrev: this.projectsList.length });
    } catch (e: any) {
      this.queryState.valid = false;
      this.queryState.errorsList = [e.message]
    }
    setTimeout(() => {
      this.projectsLoading = false
    }, 50)
  }

  highlightSuggestion(suggestion: { label: string, value: string }) {
    const pos = suggestion.label.length - suggestion.value.length
    return '<strong>' + suggestion.label.substring(0, pos) + '</strong>' + suggestion.label.substring(pos)
  }

  keydownHandlers(map: { [key: number]: Function }, VueSelect: any) {
    return {
      ...map,
      13: (e: KeyboardEvent) => {
        // select suggestion on Enter if dropdown is open
        // fetch request on Enter if dropdown is closed
        if (this.shouldOpen(VueSelect)) {
          e.preventDefault()
        }
        VueSelect.typeAheadSelect()
      }
    }
  }

}
</script>
