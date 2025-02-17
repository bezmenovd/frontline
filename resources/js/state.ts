import { reactive } from "vue";
import { ChatMessage, Page, User } from "./types";

class State {
    page: Page = Page.Start;
    user: User | undefined = undefined;
    lobby: {
        chat_messages: ChatMessage[]
    } = {
        chat_messages: []
    };
}

export default reactive(new State());

