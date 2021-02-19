<template>
  <div>
    <span v-for="(resource, key) in val"
          :class="[resource.hasOwnProperty('color_view') ? 'inline-flex' : 'single']">
      <color-component :enabled="isEnabledColor(resource)" :data="resource" v-if="resource.hasOwnProperty('color_view')"/>
      <router-link
        :to="{
          name: 'detail',
          params: {
            resourceName: field.resourceNameRelationship,
            resourceId: resource[field.keyField],
          },
        }"
        class="no-underline dim text-primary font-bold"
        v-else-if="field.viewable"
      >{{resource[field.optionsLabel]}}</router-link>
      <span v-else>{{resource[field.optionsLabel]}}</span>
    </span>
  </div>
</template>

<script>
import ColorComponent from './ColorComponent'
export default {
  components: { ColorComponent },
  props: ["resourceName", "field"],
  computed: {
    allColors() {
      return this.field.allColors
    },
    val() {
      if (this.allColors.length > 0) {
        return this.allColors
      } else {
        return this.field.value
      }
    },
  },
  methods: {
    isEnabledColor(value) {
      console.log(this.field.value)
      console.log({ value })
      return this.field.value.filter(function (item) {
        return item.code === value.code
      }).length > 0
    },
  }
};
</script>

<style lang="scss" scoped>
  .single:not(:last-of-type) {
    &:after {
      content: ', ';
    }
  }
</style>
