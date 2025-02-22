import state from "../state";

export default function alert(title: string, text: string, onClose: Function = () => {}) {
    state.alert.title = title;
    state.alert.text = text;
    state.alert.onClose = onClose;
    state.alert.showModal = true;
}
