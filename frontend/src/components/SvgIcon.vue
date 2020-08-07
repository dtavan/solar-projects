<template>
  <div :style="iconStyle">
    <svg
      width="100%"
      preserveAspectRatio="xMidYMid meet"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 373 373"
    >
      <g :fill="iconColor">
        <component :is="iconType"></component>
      </g>
    </svg>
  </div>
</template>

<script>
import icons from "@/components/icons";

export default {
  name: "SvgIcon",
  components: {
    ...icons
  },
  props: {
    iconType: {
      type: String
    },
    iconColor: {
      type: String,
      default: "currentColor"
    },

    width: {
      type: [Number, String],
      default: 18
    },
    height: {
      type: [Number, String],
      default: 18
    },

  },
  computed: {
    iconStyle() {
      const { width, height } = this;
      return {
        width: `${width}px`,
        height: `${height}px`
      };
    }
  }
};
</script>
