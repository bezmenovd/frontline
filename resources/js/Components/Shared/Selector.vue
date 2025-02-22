<script setup lang="ts">

const props = defineProps<{
    values: Array<number|string>,
    default?: number|string,
    disabled?: boolean,
}>()

let model = defineModel<number|string>()
if (props.default) {
    model.value = props.default;
}

</script>

<template>
    <div :class="{'selector': true, 'disabled': props.disabled}">
        <div v-for="value in props.values" :class="{'selector-value': true, 'selected': model === value}" @click="model = value">
            {{ value }}
        </div>
    </div>
</template>

<style lang="scss">
.selector {
    display: flex;
    border: 1px solid rgba(229, 231, 235, 0.1450980392);
    border-radius: 6px;
    gap: 1px;

    &.disabled {
        pointer-events: none;
    }

    &-value {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: monospace;
        padding: 8px 12px;
        font-size: 16px;
        background: #49494a;
        color: #b7b7b7;
        font-weight: 600;

        &:first-child {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
        }
        &:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        &:not(.selected) {
            &:hover {
                outline: 2px solid rgba(255, 43, 32, 0.438);
                z-index: 9999;
            }
            cursor: pointer;
        }
        &.selected {
            outline: 2px solid rgba(255, 43, 32, 0.8078431373);
            z-index: 10000;
            color: white;
        }
    }
}
</style>
