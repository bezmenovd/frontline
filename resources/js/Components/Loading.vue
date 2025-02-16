<script setup lang="ts">
import { VueSpinner } from 'vue3-spinners';
</script>

<template>
    <div class="loading">
        <VueSpinner color="#ff00009e" size="40"/>
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
    background: #0000005e;
    z-index: 99999;
}
.vue-spinner {
    width: 40px;
    height: 40px;
}
</style>
