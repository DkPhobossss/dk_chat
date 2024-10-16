<script setup>
import ChatItem from '@/Components/Chat/ChatItem.vue';

const props = defineProps({
    chats: {
        type: Array,
    },
    activeChatId: {
        type: [Number, null],
        default: null
    }
});

</script>

<template>
    <ul class="overflow-x-hidden overflow-y-auto scrollbar ">
        <li v-for="(chat, key) in chats">
            <ChatItem :key="key" :chat :selected="activeChatId === chat.id" />
        </li>
    </ul>
</template>