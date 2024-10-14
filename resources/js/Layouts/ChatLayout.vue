<script setup>

import ChatItem from '@/Components/Chat/ChatItem.vue';
import ChatSearch from '@/Components/Chat/ChatSearch.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    chats: {
        type: Array,
    },
});

const onClick = (chatId) => {

}
</script>

<template>
    <div class="flex flex-row border border-indigo-600">
        <div class="p-4 border border-indigo-600">
            <ChatSearch name="search" id="search" :onClick></ChatSearch>

            <ChatItem v-for="(chat,key) in chats" :key="key" :chat >
                
            </ChatItem>
        </div>

        <div class="p-4 border border-indigo-600">
            <slot />
        </div>
    </div>
</template>
