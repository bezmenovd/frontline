import axios, { AxiosResponse } from "axios";
import { ChatMessage } from "../types";

export function fetchLobby() : Promise<{ chat_messages: ChatMessage[] }> {
    return new Promise((resolve, reject) => {
        axios.get("/api/fetch-lobby").then((r : AxiosResponse) => {
            if (typeof r.data.error === "string") {
                reject(r.data.error)
            } else {
                resolve(r.data)
            }
        })
    })
}
