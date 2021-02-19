<template>
  <panel-item :field="field">
    <div slot="value">
      <div v-if="field.showAsList">
        <div class="relative rounded-lg bg-white shadow border border-60">
          <div class="overflow-hidden rounded-b-lg rounded-t-lg">
            <div class="border-b border-50 cursor-text font-mono text-sm py-2 px-4"
                 v-for="(resource, key) in val"
                 :key="key">
              <router-link
                :to="{
                name: 'detail',
                params: {
                  resourceName: field.resourceNameRelationship,
                  resourceId: resource[field.keyField],
                },
              }"
                class="no-underline dim text-primary font-bold"
                v-if="field.viewable"
              ><color-component :enabled="isEnabledColor(resource)" :data="resource">{{resource[field.optionsLabel]}}</color-component>
              </router-link>
              <span v-else>{{resource[field.optionsLabel]}}</span>
            </div>
          </div>
        </div>
      </div>
      <div v-else>
      <span v-for="(resource, key) in field.value" class="single">
        <router-link
          :to="{
            name: 'detail',
            params: {
              resourceName: field.resourceNameRelationship,
              resourceId: resource[field.keyField],
            },
          }"
          class="no-underline dim text-primary font-bold"
          v-if="field.viewable"
        >{{resource[field.optionsLabel]}}</router-link>
        <span v-else>{{resource[field.optionsLabel]}}</span>
      </span>
      </div>
    </div>
  </panel-item>
</template>

<script>
  import ColorComponent from './ColorComponent'
  export default {
    components: { ColorComponent },
    props: ["resource", "resourceName", "resourceId", "field"],
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
