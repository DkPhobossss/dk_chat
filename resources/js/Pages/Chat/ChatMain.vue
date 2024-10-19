<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatLayout from '@/Layouts/ChatLayout.vue';
import ChatTitle from '@/Components/Chat/ChatTitle.vue';
import ChatMessageList from '@/Components/Chat/ChatMessageList.vue';
import ChatSendMessageForm from '@/Components/Chat/ChatSendMessageForm.vue';
import ChatHello from '@/Components/Chat/ChatHello.vue';
import { router } from '@inertiajs/vue3'
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            if ([401, 419, 500].includes(error.response.status)) {
                //router.reload({ preserveScroll: true, preserveState: true });
                //some annoying things >.<
                window.location.reload();
            }
        }
        handleError(error);
        return Promise.reject(error);
    }
);


let props = defineProps({
    initialChatId: {
        type: Number,
        default: null
    },
});

const chats = ref(null);
const activeChatId = ref(props.initialChatId ?? null);
const messages = ref([]);
const chatUsers = ref([]);
const activeChat = ref({});

const form = useForm({
    body: null
});
const savedMessage = ref(null);

const page = usePage();

onMounted(() => {
    if (activeChatId.value) {
        Promise.all(
            [initChatList(), requestChatDataById(activeChatId.value)])
            .then(res => {
                setChatListData(res[0]);
                setChatData(activeChatId.value, res[1]);
            })
            .catch((error) => {
                handleError(error);
            });
    } else {
        initChatList()
            .then(chatListRes => {
                setChatListData(chatListRes);
            })
            .catch((error) => {
                handleError(error);
            });
    }
});

watch(
    () => page.url,
    (newURL, oldURL) => {
        const chatIdMatch = newURL.match(/\/chats\/(\d+)/);
        if (chatIdMatch) {
            activeChatId.value = parseInt(chatIdMatch[1]);
            getChatDataById(activeChatId.value);
            activeChat.value = getChatById(activeChatId.value);
        } else {
            activeChatId.value = null;
            activeChat.value = null;
        }
        form.body = null;
        savedMessage.value = null;
    }
);

async function initChatList() {
    return axios.get(route('chats.list'))
        .then(res => res.data)
        .catch(e => handleError(e));
}

async function requestChatDataById(chatId) {
    return axios.get(route('chats.data', chatId))
        .then(res => res.data)
        .catch(e => handleError(e));
}

function setChatListData(data) {
    chats.value = data;
    activeChat.value = getChatById(activeChatId.value);
}

function setChatData(chatId, data) {
    messages.value[chatId] = data.messages;
    chatUsers.value[chatId] = data.users;
    const messageCount = data.messages.length;
    if (messageCount) {
        UpdateChatListLastMessage(getChatIndexById(chatId), data.messages[messageCount - 1], true);
    }
}

//Get Messages and Users in Chat via request
function getChatDataById(chatId) {
    if (!messages.value[chatId]) {
        requestChatDataById(chatId)
            .then(res => {
                setChatData(chatId, res);
            })
            .catch((error) => {
                handleError(error);
            });
    }
}

function UpdateChatListLastMessage(index, message, seen = true) {
    chats.value[index].last_message = {
        body: message.body,
        id: message.id
    };
    chats.value[index].updated_at = message.updated_at;
    if (seen)
        chats.value[index].last_seen_message_id = message.id;
}

function onEditClick(messageId, index) {
    savedMessage.value = {
        messageId,
        index,
        'body': savedMessage.value ? savedMessage.value.body : form.body
    }

    form.body = messages.value[activeChatId.value][index]['body'];
}

function getChatIndexById(id) {
    return chats.value.findIndex((element) => element.id === id);
}

function getChatById(id) {
    return chats.value.find(chat => chat.id === id);
}

function pushChat(index, message = null) {
    chats.value.unshift(chats.value.splice(index, 1)[0]);
    if (message) {
        UpdateChatListLastMessage(0, message, true);
    }
}

function onEditCancel() {
    form.body = savedMessage.value.body;
    savedMessage.value = null;
}

function onEditSubmit(messageId, index) {
    axios.put(route('chats.messages.update', [activeChat.value.id, messageId]), form).then((res) => {
        messages.value[activeChatId.value][index]['body'] = res.data.data.body;
        messages.value[activeChatId.value][index]['updated_at'] = res.data.data.updated_at;
        savedMessage.value = null;
        form.body = null;
    }).catch((error) => {
        handleError(error);
    });
}

function onSendMessage() {
    //edit message
    if (savedMessage.value !== null) {
        return onEditSubmit(savedMessage.value.messageId, savedMessage.value.index);
    }

    //create new message
    axios.post(route('chats.messages.store', activeChat.value.id), form).then((res) => {
        const index = getChatIndexById(activeChat.value.id);
        pushChat(index, res.data.data);

        form.body = null;
        res.data.data.user = {
            id: page.props.auth.user.id,
            name: page.props.auth.user.name
        }
        messages.value[activeChatId.value].push(res.data.data);
    }).catch((error) => {
        handleError(error);
    });
};

function onDeleteClick(messageId, index) {
    if (window.confirm('Are you sure u want to delete?')) {
        axios.delete(route('chats.messages.destroy', [activeChat.value.id, messageId])).then((res) => {
            messages.value[activeChatId.value][index]['body'] = res.data.data.body;
            messages.value[activeChatId.value][index]['updated_at'] = res.data.data.updated_at;
        }).catch((error) => {
            handleError(error);
        });
    }
}

function onRestoreClick(messageId, index) {
    axios.patch(route('chats.messages.restore', [activeChat.value.id, messageId])).then((res) => {
        messages.value[activeChatId.value][index]['body'] = res.data.data.body;
        messages.value[activeChatId.value][index]['updated_at'] = res.data.data.updated_at;
    }).catch((error) => {
        handleError(error);
    });
}

function openChat(userId, callBack) {
    axios.post(route('chats.store'), { user_id: userId }).then(res => {
        callBack();
        //chat created
        if (res.data.name) {
            chats.value.unshift(res.data);
        }
        router.visit(route('chats.show', res.data.id), { preserveScroll: true, preserveState: true })
    }).catch((error) => {
        handleError(error);
    });
}

function handleError(error) {
    console.log("Error:", error);
}
</script>

<template>
    <AuthenticatedLayout>

        <Head :title="(activeChatId ? activeChat.name : 'Chats')" />
        <ChatLayout :chats :activeChatId :onSearchResultsClick="openChat">
            <ChatHello v-if="!activeChatId" />
            <div v-else class="flex flex-col h-full">
                <div class="px-3 pt-3 border-b border-gray-400">
                    <ChatTitle :title="activeChat.name" :chatUsers="chatUsers[activeChatId]" />
                </div>

                <ChatMessageList :messages="messages[activeChatId]" :onEditClick :onDeleteClick :onRestoreClick />

                <div class="p-3 mt-auto border-t border-gray-400">
                    <ChatSendMessageForm :onSendMessage :onEditCancel :savedMessage :refForm="form" />
                </div>
            </div>
        </ChatLayout>
    </AuthenticatedLayout>
</template>
