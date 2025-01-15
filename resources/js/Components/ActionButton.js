import selectorsParams from '../Support/SelectorsParams.js'
import {ComponentRequestData} from '../DTOs/ComponentRequestData.js'
import {dispatchEvents as de} from '../Support/DispatchEvents.js'
import request from '../Request/Core.js'
import {mergeURLString, prepareQueryParams} from '../Support/URLs.js'

export default () => ({
  url: '',
  method: 'GET',
  withParams: '',
  withQueryParams: false,
  loading: false,
  btnText: '',

  init() {
    this.url = this.$el.href
    this.btnText = this.$el.innerHTML
    this.method = this.$el?.dataset?.asyncMethod

    this.withParams = this.$el?.dataset?.asyncWithParams
    this.withQueryParams = this.$el?.dataset?.asyncWithQueryParams ?? false

    this.loading = false
    const el = this.$el
    const btnText = this.btnText

    this.$watch('loading', function (value) {
      el.setAttribute('style', 'opacity:' + (value ? '.5' : '1'))
      el.innerHTML = value
        ? '<div class="spinner spinner--primary spinner-sm"></div>' + btnText
        : btnText
    })
  },

  dispatchEvents(componentEvent, exclude = null, extra = {}) {
    let url = new URL(this.$el.href)

    if (this.withQueryParams) {
      const queryParams = new URLSearchParams(window.location.search)

      url = new URL(mergeURLString(url.toString(), queryParams))
    }

    const preparedURLParams =
      exclude === '*'
        ? {}
        : Object.fromEntries(prepareQueryParams(new URLSearchParams(url.search), exclude))

    extra['_data'] = Object.assign({}, preparedURLParams, selectorsParams(this.withParams))

    de(componentEvent, '', this, extra)
  },

  request() {
    this.url = this.$el.href

    if (this.loading || this.$el.dataset?.stopAsync) {
      return
    }

    this.loading = true

    if (this.withParams !== undefined && this.withParams) {
      this.method = this.method.toLowerCase() === 'get' ? 'post' : this.method
    }

    let body = selectorsParams(this.withParams)

    if (this.withQueryParams) {
      const queryParams = new URLSearchParams(window.location.search)

      this.url = mergeURLString(this.url, queryParams)
    }

    let stopLoading = function (data, t) {
      t.loading = false
    }

    let componentRequestData = new ComponentRequestData()
    componentRequestData
      .fromDataset(this.$el?.dataset ?? {})
      .withAfterResponse(() => this.$el?.dataset.asyncAfterResponse)
      .withBeforeHandleResponse(stopLoading)
      .withErrorCallback(stopLoading)

    request(this, this.url, this.method, body, {}, componentRequestData)
  },
})
