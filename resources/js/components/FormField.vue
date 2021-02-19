<template>
  <default-field :field="field" :errors="errors">
    <template slot="field">
      <div :style="{height: field.height ? field.height : 'auto'}" class="relative">
        <div
          v-if="loading"
          class="py-6 px-8 flex justify-center items-center absolute pin z-50 bg-white"
        >
          <loader class="text-60"/>
        </div>
        <div v-if="this.field.selectAll" class="mb-2">
          <input type="checkbox" id="checkbox" class="checkbox" v-model="selectAll">
          <label for="checkbox">{{this.field.messageSelectAll}}</label>
        </div>
          <multi-select ref="multiselect" @open="() => repositionDropdown(true)" :options="options"
                        v-bind="multiSelectProps" v-model="value" :custom-label="nameWithColor">
            <template slot="tag" slot-scope="props">
              <span class="inline-flex items-center multiselect__tag">
                <span v-if="hasColor(props.option, 'second')" class="mr-2">
                  <span class="rounded" :style="{background: getColor(props.option), width: '20px', height: '20px', display: 'inline-block'}"/>
                </span>
                <span>{{props.option[optionsLabel]}}</span>
                <i aria-hidden="true" @click="props.remove(props.option)" class="multiselect__tag-icon"></i>
              </span>
            </template>
            <template slot="option" slot-scope="props">
              <color-component :data="props.option">
                <div>{{props.option[optionsLabel]}}</div>
              </color-component>
            </template>
          </multi-select>
      </div>
    </template>
  </default-field>
</template>

<script>
  import {FormField, HandlesValidationErrors} from "laravel-nova";
  import MultiSelect from "vue-multiselect";
  import ColorComponent from './ColorComponent'

  export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ["resourceName", "resourceId", "field"],

    components: {
      ColorComponent,
      MultiSelect
    },
    data() {
      return {
        options: [],
        optionsLabel: "name",
        dependsOnValue: null,
        isDependant: false,
        shouldClear: false,
        loading: true,
        selectAll:false,
      };
    },
    mounted() {
        window.addEventListener('scroll', this.repositionDropdown);
    },
    destroyed() {
      window.removeEventListener('scroll', this.repositionDropdown);
    },
    created() {
      if (this.field.dependsOn !== undefined || this.field.dependsOnField !== undefined) {
        this.isDependant = true;
        this.registerDependencyWatchers(this.$root)
      }
    },

    computed: {
      multiSelectProps() {
        return {
          multiple: true,
          label: this.optionsLabel,
          trackBy: this.optionsLabel,
          preselectFirst: false,
          class: this.errorClasses,
          placeholder: this.field.name,
          ...(this.field.multiselectOptions ? this.field.multiselectOptions : {})
        };
      },
    },
    watch: {
      selectAll(value){
        if(value){
          this.value = [...this.options];
        } else {
          this.value = [];
        }
      }
    },
    methods: {
      repositionDropdown(onOpen = false) {
        const ms = this.$refs.multiselect;
        if (!ms) return;
        const el = ms.$el;
        const handlePositioning = () => {
          const {top, height, bottom} = el.getBoundingClientRect();
          if (onOpen) ms.$refs.list.scrollTop = 0;
          const fromBottom = (window.innerHeight || document.documentElement.clientHeight) - bottom;
          ms.$refs.list.style.position = 'fixed';
          ms.$refs.list.style.width = `${el.clientWidth}px`;
          if (fromBottom < 300) {
            ms.$refs.list.style.top = 'auto';
            ms.$refs.list.style.bottom = `${fromBottom + height}px`;
            ms.$refs.list.style['border-radius'] = '5px 5px 0 0';
          } else {
            ms.$refs.list.style.bottom = 'auto';
            ms.$refs.list.style.top = `${top + height}px`;
            ms.$refs.list.style['border-radius'] = '0 0 5px 5px';
          }
        };
        if (onOpen) this.$nextTick(handlePositioning);
        else handlePositioning();
      },
      hasColor(option) {
        return (option.hasOwnProperty('additional')
          && option.additional.hasOwnProperty('color_view')) || option.hasOwnProperty('color_view')
      },
      getColor(option) {
        if (option.hasOwnProperty('additional') && option.additional.hasOwnProperty('color_view')) {
          return option.additional.color_view
        } else if (option.hasOwnProperty('color_view')) {
          return option.color_view
        } else {
          return '#000000'
        }
      },
      registerDependencyWatchers(root) {
        root.$children.forEach(component => {
          if (this.componentIsDependency(component)) {
              let attribute = this.findWatchableComponentAttribute(component);
              let self = this;
              component.$watch(attribute, function (value) {
                  if (value === undefined) return;
                  self.dependencyWatcher(this, value)
              }, {immediate: true});
              this.dependencyWatcher(component, component.value)
          }
          this.registerDependencyWatchers(component)
        })
      },

      findWatchableComponentAttribute(component) {
        let attribute;
        if (component.field.component === 'belongs-to-field') {
          attribute = 'selectedResource';
        } else {
          attribute = 'value';
        }
        return attribute;
      },

      componentIsDependency(component) {
        if (component.field === undefined) {
          return false
        }
        return component.field.attribute === this.field.dependsOn
          || component.field.attribute === this.field.dependsOnField
      },

      dependencyWatcher(component, value) {
        if (value === this.dependsOnValue || value === undefined) {
          return
        }
        if (component.field.component === 'nova-belongsto-depend' && value) {
            this.dependsOnValue = value[component.field.modelPrimaryKey]
        } else if (value) {
            this.dependsOnValue = value.value;
        } else {
            this.dependsOnValue = null
        }
        this.fetchOptions()
      },
      /*
       * Set the initial, internal value for the field.
       */
      setInitialValue() {
        this.optionsLabel = this.field.optionsLabel
          ? this.field.optionsLabel
          : "name";
        this.value = this.field.value || "";
        this.fetchOptions();
      },

      fetchOptionsFromField() {
          let baseUrl = "/nova-vendor/belongs-to-many-field/field/";
          this.loading = true;
          Nova.request(
              baseUrl +
              this.resourceName +
              "/" +
              "options/" +
              this.field.attribute +
              "/" +
              this.optionsLabel +
              "/" +
              this.field.dependsOnField +
              "/" +
              this.dependsOnValue
          ).then(data => {
              this.options = data.data;
              this.loading = false;
          });
      },

      fetchOptions() {
        if (this.field.options) {
          this.options = this.field.options;
          this.loading = false;
          return;
        }

        let baseUrl = "/nova-vendor/belongs-to-many-field/";
        if (this.isDependant) {
          if (this.field.dependsOnField && this.dependsOnValue) {
            this.fetchOptionsFromField()
          } else if (this.dependsOnValue) {
            this.loading = true;
            Nova.request(
              baseUrl +
              this.resourceName +
              "/" +
              "options/" +
              this.field.attribute +
              "/" +
              this.optionsLabel +
              "/" +
              this.dependsOnValue +
              "/" +
              this.field.dependsOnKey
            ).then(data => {
              this.options = data.data;
              this.loading = false;
            });
          } else {
            this.options = [];
            this.loading = false;
          }
        } else {
          Nova.request(
            baseUrl +
            this.resourceName +
            "/" +
            "options/" +
            this.field.attribute +
            "/" +
            this.optionsLabel
          ).then(data => {
            this.options = data.data;
            this.loading = false;
          });
        }
      },

      /**
       * Fill the given FormData object with the field's internal value.
       */
      fill(formData) {
        formData.append(this.field.attribute, JSON.stringify(this.value) || "");
      },

      /**
       * Update the field's internal value.
       */
      handleChange(value) {
        this.value = value;
        this.$nextTick(() => this.repositionDropdown());
      },
      nameWithColor(field) {
        return field[this.optionsLabel]
      },
    }
  };
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style type="text/css">
  .multiselect__tag.inline-flex {
    display: inline-flex;
  }
  .multiselect__tag-icon {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .multiselect__select::before {
    border-width: 0; /* Reset default style */

    /*position: absolute;*/
    top: 0;
    width: 22px;
    height: 6px;
    margin: 0;

    background-repeat: no-repeat;
    background-position: center center;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 6"><path fill="%2335393C" fill-rule="nonzero" d="M8.293.293a1 1 0 0 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4A1 1 0 0 1 1.707.293L5 3.586 8.293.293z"/></svg>');
  }

</style>
