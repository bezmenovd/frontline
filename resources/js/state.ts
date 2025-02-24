import { reactive } from "vue";
import { ChatMessage, Host, Page, User } from "./types";

class State {
    page: Page = Page.Start;
    user: User = <User>{};
    logged_in: boolean = false;
    lobby: {
        chatMessages: ChatMessage[],
        hosts: {
            list: Host[],
            selected: Host | undefined,
            connected: Host | undefined,
        },
    } = {
        chatMessages: [],
        hosts: {
            list: [],
            selected: undefined,
            connected: undefined,
        },
    };
    alert: {
        showModal: boolean,
        title: string,
        text: string,
        onClose: Function,
    } = {
        showModal: false,
        title: "",
        text: "",
        onClose: () => {},
    }
}

export default reactive(new State());

