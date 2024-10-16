<script setup>

import ChatList from '@/Components/Chat/ChatList.vue';
import ChatSearch from '@/Components/Chat/ChatSearch.vue';

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
    <div class="h-full py-3 text-gray-900">
        <div class="flex h-full border rounded-lg shadow-xl border-gray-500/50">
            <div class="flex flex-col w-1/5 py-3 border-r border-gray-500/50">
                <ChatSearch name="search" id="search"></ChatSearch>
                <ChatList :chats :activeChatId />
            </div>

            <div class="grow">
                <slot />
            </div>
        </div>
    </div>
</template>