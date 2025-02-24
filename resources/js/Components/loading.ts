import { reactive } from "vue";

export let loadingState = reactive({
    show: false,
    text: "",
    opacity: 0.37,
});

export function loading(state: boolean = true, text: string = "", opacity: number = 0.37) {
    loadingState.show = state
    loadingState.text = text
    loadingState.opacity = opacity
}
