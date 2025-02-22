import axios, { AxiosResponse } from "axios";
import { ChatMessage, Host } from "../types";

export function fetchLobby() : Promise<{ chatMessages: ChatMessage[], hosts: Host[] }> {
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
