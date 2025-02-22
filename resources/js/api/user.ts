import axios, { AxiosResponse } from "axios";
import { User } from "../types";

export function login(name: string, password: string) : Promise<{user: User, token: string}> {
    return new Promise((resolve, reject) => {
        axios.post("/api/login", {name, password}).then((r : AxiosResponse) => {
            if (typeof r.data.error === "string") {
                reject(r.data.error)
            } else {
                resolve(r.data)
            }
        })
    })
}

export function register(name: string, email: string, password: string) : Promise<{user: User, token: string}> {
    return new Promise((resolve, reject) => {
        axios.post("/api/register", {name, email, password}).then((r : AxiosResponse) => {
            if (typeof r.data.error === "string") {
                reject(r.data.error)
            } else {
                resolve(r.data)
            }
        })
    })
}

export function getUser() : Promise<User> {
    return new Promise((resolve, reject) => {
        axios.get("/api/get-user").then((r : AxiosResponse<{ user: User }>) => {
            resolve(r.data.user)
        }).catch(reject)
    })
}
