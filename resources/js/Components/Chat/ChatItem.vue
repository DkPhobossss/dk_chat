<script setup>

import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs';
import 'dayjs/locale/ru';
dayjs.locale('ru');

defineProps({
    chat: {
        type: Object,
    },
    selected: {
        type: Boolean
    }
});

const today = dayjs();
const formatDate = (dateString) => {
    const date = dayjs(dateString);
    if (date.isSame(today, 'day')) {
        return date.format('HH:mm');
    } else {
        return date.format('DD.MM.YYYY');
    }
}
const openChat = (id) => {
    router.visit(route('chats.show', id), { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <a @click.prevent="openChat(chat.id)" :class="selected ? 'bg-indigo-600 !text-white' : 'hover:bg-gray-100/50'"
        class="relative flex flex-col p-3 cursor-pointer">
        <h4 class="flex">
            <strong>{{ chat.name }}</strong>
            <time :class="selected ? 'text-white' : 'text-gray-400'" class="ml-auto text-xs whitespace-nowrap">{{
                formatDate(chat.updated_at) }}</time>
        </h4>

        <div v-if="chat.last_message && chat.last_message.body && chat.last_message.id  !== chat.last_seen_message_id" class="absolute block -translate-y-1/2 bg-orange-800 rounded-full size-2 top-1/2 right-2"></div>
        

        <div class="overflow-hidden whitespace-nowrap text-ellipsis" :class="selected ? 'text-white' : 'text-gray-400'">
            {{ chat.last_message && chat.last_message.body ? chat.last_message.body : '&nbsp;' }}
        </div>
    </a>
</template>
