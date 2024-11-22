import {ComponentRequestData} from '../DTOs/ComponentRequestData.js'
import request from '../Request/Core.js'

export default () => ({
  saveField(route, column, value = null) {
    if (value === null) {
      value = this.$el.value
    }

    if (value === null && (this.$el.type === 'checkbox' || this.$el.type === 'radio')) {
      value = this.$el.checked
    }

    if (this.$el.tagName.toLowerCase() === 'select' && this.$el.multiple) {
      value = []
      for (let i = 0; i < this.$el.options.length; i++) {
        let option = this.$el.options[i]
        if (option.selected) {
          value.push(option.value)
        }
      }
    }

    const componentRequestData = new ComponentRequestData()
    componentRequestData.fromDataset(this.$el?.dataset ?? {})

    request(
      this,
      route,
      this.$el?.dataset?.asyncMethod ?? 'put',
      {
        value: value,
        field: column,
      },
      {},
      componentRequestData,
    )
  },
})
