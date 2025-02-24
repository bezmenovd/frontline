<script setup lang="ts">
import { computed } from 'vue';
import { VueSpinner } from 'vue3-spinners';

const props = defineProps<{
    text: string,
    opacity: number,
}>()

const opacity = computed(() => {
    return `background-color: rgba(0, 0, 0, ${props.opacity})`;
})

</script>

<template>
    <div class="loading" :style="opacity">
        <VueSpinner color="#ff00009e" size="40"/>
        <div class="loading-text">{{ props.text }}</div>
    </div>
</template>

<style lang="scss" scoped>
.loading {
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 99999;
    flex-direction: column;

    &-text {
        margin-top: 20px;
        font-size: 14px;
        opacity: .6;
    }
}
.vue-spinner {
    width: 40px;
    height: 40px;
}
</style>
