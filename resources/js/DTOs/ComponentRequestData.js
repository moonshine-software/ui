export class ComponentRequestData {
  constructor() {
    this._events = ''
    this._selector = ''

    this._beforeRequest = null
    this._customResponse = null

    this._beforeResponse = null
    this._afterResponse = null

    // TODO Error request refactoring
    this._errorCallback = null
    this._afterErrorCallback = null

    this._extraAttributes = {}
  }

  get events() {
    return this._events
  }

  withEvents(value) {
    this._events = value

    return this
  }

  get selector() {
    return this._selector
  }

  withSelector(value) {
    this._selector = value

    return this
  }

  get beforeRequest() {
    return this._beforeRequest
  }

  hasBeforeRequest() {
    return this._beforeRequest !== null && this._beforeRequest
  }

  withBeforeRequest(value) {
    this._beforeRequest = value

    return this
  }

  get beforeResponse() {
    return this._beforeResponse
  }

  hasBeforeResponse() {
    return this._beforeResponse !== null && typeof this._beforeResponse === 'function'
  }

  withBeforeResponse(value) {
    this._beforeResponse = value

    return this
  }

  get customResponse() {
    return this._customResponse
  }

  hasCustomResponse() {
    return this._customResponse !== null && this._customResponse
  }

  withCustomResponse(value) {
    this._customResponse = value

    return this
  }

  get afterResponse() {
    return this._afterResponse
  }

  hasAfterResponse() {
    return this._afterResponse !== null && typeof this._afterResponse === 'function'
  }

  withAfterResponse(value) {
    this._afterResponse = value

    return this
  }

  get errorCallback() {
    return this._errorCallback
  }

  hasErrorCallback() {
    return this._errorCallback !== null && typeof this._errorCallback === 'function'
  }

  withErrorCallback(value) {
    this._errorCallback = value

    return this
  }

  get afterErrorCallback() {
    return this._afterErrorCallback
  }

  hasAfterErrorCallback() {
    return this._afterErrorCallback !== null && typeof this._afterErrorCallback === 'function'
  }

  withAfterErrorCallback(value) {
    this._afterErrorCallback = value

    return this
  }

  get extraAttributes() {
    return this._extraAttributes
  }

  withExtraAttributes(value) {
    this._extraAttributes = value

    return this
  }

  fromDataset(dataset = {}) {
    return this.withEvents(dataset.asyncEvents ?? '')
      .withSelector(dataset.asyncSelector ?? '')
      .withCustomResponse(dataset.asyncCallback ?? null)
      .withBeforeRequest(dataset.asyncBeforeFunction ?? null)
  }

  fromObject(object = {}) {
    return this.withEvents(object.events ?? '')
      .withSelector(object.selector ?? '')
      .withCustomResponse(object.customResponse ?? null)
      .withBeforeRequest(object.beforeFunction ?? null)
      .withBeforeResponse(object.beforeResponse ?? null)
      .withAfterResponse(object.afterResponse ?? null)
      .withErrorCallback(object.errorCallback ?? null)
      .withAfterErrorCallback(object.afterErrorCallback ?? null)
      .withExtraAttributes(object.extraAttributes ?? null)
  }
}
