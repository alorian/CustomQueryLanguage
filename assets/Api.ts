import axios from "axios"

class Api {

  public async validateQuery(query: string, caretPos: number = 0) {
    return axios.post('/validate', {query, caretPos})
  }

  public async fetchProjects(query: string = '', caretPos: number = 0) {
    return axios.post('/projects', {query, caretPos})
  }


}

export default new Api();