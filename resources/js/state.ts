import { reactive } from "vue";
import { Page, User } from "./types";

class State {
    page: Page = Page.Start;
    user: User | undefined = undefined;
}

export default reactive(new State());

