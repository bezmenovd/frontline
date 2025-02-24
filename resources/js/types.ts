export enum Page {
    Start,
    Login,
    Register,
    Lobby,
    Game,
}

export type User = {
    id: number,
    name: string,
    rating: number,
}

export type ChatMessage = {
    id: number,
    host_id: number | null,
    datetime: string,
    user: {
        id: number,
        name: string,
    },
    text: string,
}

export type Host = {
    id: number,
    user: {
        id: number,
        name: string,
    },
    description: string,
    players: number,
    size: string,
    water: string,
    users: {
        id: number,
        name: string
    }[],
    chatMessages: ChatMessage[],
}
