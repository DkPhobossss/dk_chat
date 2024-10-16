<script setup>

import { ref } from 'vue';
import { router } from '@inertiajs/vue3'

defineProps({

});

const searchUsers = ref([]);
let search = '';


let searchTimeout;
const onInput = () => {
    clearTimeout(searchTimeout);
    if ( search.trim() === '') {
        searchUsers.value = [];
        return;
    }

    searchTimeout = setTimeout(() => {
        axios.get(route('users.search'), {
            params: {
                name: search
            }
        }).then((res) => {
            searchUsers.value = res.data;
        }).catch((error) => {
            console.error("Error:", error);
        });
    }, 250)
}

const openChat = (userId) => {
    router.post(route('chats.store'), { user_id: userId }, {
        onSuccess: () => {
            searchUsers.value = [];
            search = '';
        },
        onError: (error) => {
            console.log(error);
        },
    });
}
</script>

<template>
    <div class="mb-3">
        <div class="mx-3">
            <input class="w-full text-sm bg-gray-300 border-gray-300 rounded-lg placeholder-gray-4500 focus:bg-white focus:text-black focus:border-indigo-600" type="text" placeholder="Search User" @input="onInput" v-model="search" max="32"  />
        </div>
        <ul v-if="searchUsers.length" class="pb-3 mt-3 border-b border-gray-400">
            <li v-for="user in searchUsers" class="relative p-3 font-bold cursor-pointer group hover:bg-gray-100/50" @click="openChat(user.id)">
                {{ user.name }}
                <svg class="absolute hidden -translate-y-1/2 right-3 top-1/2 -bottom-1/2 size-5 group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
            </li>
        </ul>
    </div>
</template>
