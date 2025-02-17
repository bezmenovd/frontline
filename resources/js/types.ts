export enum Page {
    Start,
    Login,
    Register,
    Lobby,
}

export type User = {
    name: string,
    rating: number,
}

export type ChatMessage = {
    id: number,
    datetime: string,
    user: {
        id: number,
        name: string,
    },
    text: string,
}

