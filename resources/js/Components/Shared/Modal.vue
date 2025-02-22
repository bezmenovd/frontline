<script setup lang="ts">

let props = defineProps<{
    title: string;
}>()

</script>

<template>
    <div class="modal-background">
        <div class="modal-panel panel">
            <div class="modal-panel-title panel-title">
                {{ props.title }}
            </div>
            <div class="modal-panel-body">
                <slot name="body"/>
            </div>
            <div class="modal-panel-buttons buttons">
                <slot name="buttons"/>
            </div>
        </div>
    </div>
</template>

<style lang="scss">
.modal {
    &-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 99999;
        background: rgba(0, 0, 0, 0.329);
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }
    &-panel {
        padding: 20px;
        box-sizing: border-box;
        gap: 30px;
        display: grid;
        grid-template-rows: 40px 1fr 40px;
        &-title {
            justify-content: center;
        }
    }
}
</style>
