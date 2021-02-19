<template>
  <span class="inline-flex items-center">
    <span v-if="hasColor" class="mr-2 color" :class="{'disabled' : !enabled}">
      <span class="rounded inline-flex" :style="{background: color, width: '20px', height: '20px'}"/>
    </span>
    <slot/>
  </span>
</template>

<script>
export default {
  name: 'ColorComponent',
  props: {
    data: {
      type: Object,
      required: true,
    },
    enabled: {
      type: Boolean,
      default: true
    },
  },
  computed: {
    hasColor() {
      return (this.data.hasOwnProperty('additional')
        && this.data.additional.hasOwnProperty('color_view')) || this.data.hasOwnProperty('color_view')
    },
    color() {
      if (this.data.hasOwnProperty('additional') && this.data.additional.hasOwnProperty('color_view')) {
        return this.data.additional.color_view
      } else if (this.data.hasOwnProperty('color_view')) {
        return this.data.color_view
      } else {
        return '#000000'
      }
    },
  },
}
</script>
<style lang="scss">
.color {
  position: relative;
  &.disabled:after {
    content:"";
    display:block;
    position:absolute;
    left:0;
    top:40%;
    color:red;
    width:100%;
    height:0;
    border:1px solid red;
    transform:rotate(-45deg);
  }
}
</style>