<!-- Chat.vue -->
<script setup>
import { ref, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatLayout from '@/Layouts/ChatLayout.vue';
import ChatTitle from '@/Components/Chat/ChatTitle.vue';
import ChatMessageList from '@/Components/Chat/ChatMessageList.vue';
import ChatSendMessageForm from '@/Components/Chat/ChatSendMessageForm.vue';

const route = useRoute();
const chatId = computed(
    () => route.params.id
);

let props = defineProps({
    chats: Array,
    initialMessages: Array, 
});

const activeChat = ref(
    props.chats.find(chat => chat.id === parseInt(chatId.value))
);

const messages = ref(props.initialMessages); 


watch(chatId, (newId) => {
    activeChat.value = props.chats.find(chat => chat.id === parseInt(newId));
    if (!messages.value[newId]) {
        axios.get(route('chats.messages.index', newId)).then((res) => {
            messages.value[newId] = res.data.messages;
        });
    }
});

</script>

<template>
    <AuthenticatedLayout>

        <Head :title="`${$page.props.auth.user.name} ${activeChat.value.name}`" />
        <ChatLayout :chats="props.chats" :activeChatId="activeChat.value.id">
            <div class="flex flex-col h-full">
                <div class="px-3 pt-3 border-b border-gray-400">
                    <ChatTitle :title="activeChat.value.name" :chatUsers="activeChat.value.users" />
                </div>


                <ChatMessageList :messages="messages.value[activeChat.value.id]" />

                <div class="p-3 mt-auto border-t border-gray-400">
                    <ChatSendMessageForm :chatId="activeChat.value.id" />
                </div>
            </div>
        </ChatLayout>
    </AuthenticatedLayout>
</template>