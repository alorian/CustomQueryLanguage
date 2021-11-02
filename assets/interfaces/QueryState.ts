export default interface QueryState {

    valid: boolean

    query: string

    caretPos: number

    suggestionsList: Array<string>

    errorsList: Array<string>

}