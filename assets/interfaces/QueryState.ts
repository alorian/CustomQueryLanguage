export default interface QueryState {

    valid: boolean

    query: string

    caretPos: number

    suggestionsList: Array<{ label: string, value: string }>

    errorsList: Array<string>

}