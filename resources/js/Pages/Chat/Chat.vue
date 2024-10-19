<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatLayout from '@/Layouts/ChatLayout.vue';
import axios from 'axios';
import { ref } from 'vue';
import ChatTitle from '@/Components/Chat/ChatTitle.vue';
import ChatMessageList from '@/Components/Chat/ChatMessageList.vue';
import ChatSendMessageForm from '@/Components/Chat/ChatSendMessageForm.vue';


let props = defineProps({
    chats: {
        type: Array,
    },
    chat: {
        type: Object,
    },
    messages: {
        type: Array,
    },
    chatUsers: {
        type: Array
    }
});

const form = useForm({
    body: null
});

const savedMessage = ref(null);

const onEditClick = (messageId, index) => {
    savedMessage.value = {
        messageId,
        index,
        'body': savedMessage.value ? savedMessage.value.body : form.body
    }
    form.body = props.messages[index]['body'];
}

const getChatIndexById = (id) => {
    return props.chats.findIndex((element) => element.id === id);
}

const pushChat = (id) => {
    const index = getChatIndexById(id);
    props.chats.unshift(props.chats.splice(index, 1)[0]);
}

const onEditCancel = () => {
    form.body = savedMessage.value.body;
    savedMessage.value = null;
}

const onEditSubmit = (messageId, index) => {
    axios.put(route('chats.messages.update', [props.chat.id, messageId]), form).then((res) => {
        props.messages[index]['body'] = res.data.data.body;
        props.messages[index]['updated_at'] = res.data.data.updated_at;
        savedMessage.value = null;
        form.body = null;
    }).catch((error) => {
        console.error("Error:", error);
    });
}

const onSendMessage = () => {
    if (savedMessage.value !== null) {
        return onEditSubmit(savedMessage.value.messageId, savedMessage.value.index);
    }

    axios.post(route('chats.messages.store', props.chat.id), form).then((res) => {
        form.body = null;

        res.data.data.user = {
            name: usePage().props.auth.user.name
        }
        props.messages.push(res.data.data);

        pushChat(props.chat.id);
    }).catch((error) => {
        console.error("Error:", error);
    });
};

const onDeleteClick = (messageId, index) => {
    if (window.confirm('Are you sure u want to delete?')) {
        axios.delete(route('chats.messages.destroy', [props.chat.id, messageId])).then((res) => {
            props.messages[index]['body'] = res.data.data.body;
            props.messages[index]['updated_at'] = res.data.data.updated_at;
        }).catch((error) => {
            console.error("Error:", error);
        });
    }
}

const onRestoreClick = (messageId, index) => {
    axios.patch(route('chats.messages.restore', [props.chat.id, messageId])).then((res) => {
        props.messages[index]['body'] = res.data.data.body;
        props.messages[index]['updated_at'] = res.data.data.updated_at;
    }).catch((error) => {
        console.error("Error:", error);
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${$page.props.auth.user.name} ${chat.name}`" />
        <ChatLayout :chats :activeChatId="chat.id">
            <div class="flex flex-col h-full">
                <div class="px-3 pt-3 border-b border-gray-400">
                    <ChatTitle :title="chat.name" :chatUsers  />
                </div>

                <ChatMessageList :messages :onEditClick :onDeleteClick :onRestoreClick />

                <div class="p-3 mt-auto border-t border-gray-400">
                    <ChatSendMessageForm :onSendMessage :onEditCancel :savedMessage :refForm="form" />
                </div>
            </div>
        </ChatLayout>
    </AuthenticatedLayout>
</template>
