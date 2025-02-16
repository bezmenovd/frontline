import { ref } from "vue";

export let loadingNow = ref(false);

export function loading(state: boolean = true) {
    loadingNow.value = state
}
