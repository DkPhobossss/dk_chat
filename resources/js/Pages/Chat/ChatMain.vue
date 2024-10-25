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
import '../../echo';

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            if ([401, 419, 500].includes(error.response.status)) {
                //router.reload({ preserveScroll: true, preserveState: true });
                window.location.reload();
            }
        }
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
//Save message for restoring if u decide to cancel Editing
const savedMessage = ref(null);

const page = usePage();

onMounted(() => {
    if (activeChatId.value) {
        //We should load and init chat list before chat data loaded, but we can do it in async way
        Promise.all(
            [initChatList(), requestChatDataById(activeChatId.value)])
            .then(([chatListRes, chatDataRes]) => {
                setChatListData(chatListRes);
                setChatData(activeChatId.value, chatDataRes);

                listenToChatCreatedEvent(page.props.auth.user.id);
                chatListRes.forEach(chat => listenToChatMessageEvent(chat.id));
            })
            .catch((error) => {
                handleError(error);
            });
    } else {
        //no chat selected from the start
        initChatList()
            .then(chatListRes => {
                setChatListData(chatListRes);

                listenToChatCreatedEvent(page.props.auth.user.id);
                chatListRes.forEach(chat => listenToChatMessageEvent(chat.id));
            })
            .catch((error) => {
                handleError(error);
            });
    }
});

function listenToChatCreatedEvent(userId) {
    return Echo.private(`user.${userId}`)
        .listen('.chat_created', (chat) => {
            addChatToChatList(chat);
        }).error((error) => {
            handleError(error);
        })
}

function listenToChatMessageEvent(chatId) {
    return Echo.private(`chat.${chatId}`)
        .listen('.message_sent', (message) => {
            if (activeChatId.value === chatId) {
                //You're already in chat, so you'll see the message and we should update it
                addMessageToChat(chatId, message, true);
                updateLastSeenMessage(chatId, message.id);
            } else {
                addMessageToChat(chatId, message);
            }
        })
        .listen('.message_updated', (message) => {
            editMessageInChat(chatId, message);
        })
        .listen('.message_restored', (message) => {
            restoreMessageInChat(chatId, message);
        }).
        listen('.message_destroyed', (message) => {
            destroyMessageInChat(chatId, message);
        });
}

watch(
    () => page.url,
    (newURL, oldURL) => {
        const chatIdMatch = newURL.match(/\/chats\/(\d+)/);
        if (chatIdMatch) {
            //we've opened new chat
            activeChatId.value = parseInt(chatIdMatch[1]);
            getChatDataById(activeChatId.value);
            activeChat.value = getChatById(activeChatId.value);
        } else {
            //no chat selected
            activeChatId.value = null;
            activeChat.value = null;
        }
        //clear form data
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
    //cache results
    chats.value = data;
    activeChat.value = getChatById(activeChatId.value);
}

function setChatData(chatId, data) {
    //cache results
    messages.value[chatId] = data.messages;
    chatUsers.value[chatId] = data.users;
    const messageCount = data.messages.length;
    if (messageCount) {
        changeChatListLastMessage(getChatById(chatId), data.messages[messageCount - 1], true);
    }
}

//Get Messages and Users in Chat via request
function getChatDataById(chatId) {
    //if messages of current chat wasnt loaded and cached
    if (!messages.value[chatId]) {
        requestChatDataById(chatId)
            .then(res => {
                setChatData(chatId, res);
            })
            .catch((error) => {
                handleError(error);
            });
    } else {
        //mb some unreaded message if data was loaded, cached and then again received
        const messageCount = messages.value[chatId].length;
        const lastMessageId = messageCount && messages.value[chatId][messageCount - 1].id;
        const index = getChatIndexById(chatId);
        if (lastMessageId && lastMessageId !== chats.value[index].last_seen_message_id) {
            chats.value[index].last_seen_message_id = lastMessageId;
            updateLastSeenMessage(chatId, lastMessageId);
        }
    }
}

function changeChatListLastMessage(chat, message, seen = false) {
    Object.assign(chat, {
        last_message: {
            body: message.body,
            id: message.id
        },
        updated_at: message.updated_at
    })

    if (seen)
        chat.last_seen_message_id = message.id;
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

function getMessageIndexById(chatId, messageId) {
    const chatMessages = messages.value[chatId];
    return chatMessages && chatMessages.findIndex(message => message.id === messageId);
}

function getMessageById(chatId, messageId) {
    const chatMessages = messages.value[chatId];
    return chatMessages && chatMessages.find(message => message.id === messageId);
}

function pushChat(chatId) {
    const chat = chats.value.splice(getChatIndexById(chatId), 1)[0];
    chats.value.unshift(chat);
    return chat;
}

function onEditCancel() {
    form.body = savedMessage.value.body;
    savedMessage.value = null;
}

function onSendMessage() {
    //edit message
    if (savedMessage.value !== null) {
        return onEditSubmit(savedMessage.value.messageId, savedMessage.value.index);
    }

    //create new message
    axios.post(route('chats.messages.store', activeChat.value.id), form).then((res) => {
        addMessageToChat(activeChat.value.id, res.data, true, true);
    }).catch((error) => {
        handleError(error);
    });
};

function addMessageToChat(chatId, message, messageSeen = false, authUserIsOwner = false) {
    //authUser submited message
    if (authUserIsOwner) {
        message.user = {
            id: page.props.auth.user.id,
            name: page.props.auth.user.name
        }
        form.body = null;
    }

    //no data in cache
    if (messages.value[chatId])
        messages.value[chatId].push(message);

    const chat = pushChat(chatId);
    changeChatListLastMessage(chat, message, messageSeen);
}

function onEditSubmit(messageId, messageIndex) {
    axios.put(route('chats.messages.update', [activeChat.value.id, messageId]), form).then((res) => {
        editMessageInChat(activeChatId.value, res.data, messageIndex);
        savedMessage.value = null;
        form.body = null;
    }).catch((error) => {
        handleError(error);
    });
}

function editMessageInChat(chatId, messageData) {
    //we got update event, but mb we have not loaded this message, let try to check it
    const message = updateMessageBodyInChat(chatId, messageData);

    if (message) {
        const chat = getChatById(chatId);
        if (message.id === chat.last_message.id)
            changeChatListLastMessage(chat, message, true);
    }
}

function onDeleteClick(messageId, index) {
    if (window.confirm('Are you sure u want to delete?')) {
        axios.delete(route('chats.messages.destroy', [activeChat.value.id, messageId])).then((res) => {
            destroyMessageInChat(activeChat.value.id, res.data);
        }).catch((error) => {
            handleError(error);
        });
    }
}

function destroyMessageInChat(chatId, messageData) {
    return updateMessageBodyInChat(chatId, messageData);
}

function onRestoreClick(messageId, index) {
    axios.patch(route('chats.messages.restore', [activeChat.value.id, messageId])).then((res) => {
        restoreMessageInChat(activeChat.value.id, res.data);
    }).catch((error) => {
        handleError(error);
    });
}

function restoreMessageInChat(chatId, messageData) {
    return updateMessageBodyInChat(chatId, messageData);
}

function updateMessageBodyInChat(chatId, { id: messageId, body, updated_at }) {
    const message = getMessageById(chatId, messageId)
    if (message) {
        Object.assign(message, {
            body,
            updated_at
        })
    }

    return message;
}

function openChat(userId, callBack) {
    axios.post(route('chats.store'), { user_id: userId }).then(res => {
        callBack();
        //chat created
        if (res.data.name) {
            addChatToChatList(res.data);
        }
        router.visit(route('chats.show', res.data.id), { preserveScroll: true, preserveState: true })
    }).catch((error) => {
        handleError(error);
    });
}

function addChatToChatList(chat) {
    chats.value.unshift(chat);
    listenToChatMessageEvent(chat.id);
}

function updateLastSeenMessage(chatIid, messageId) {
    axios.patch(route('chats.messages.update.last_seen', [chatIid, messageId])).then(res => {
        return res.id;
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
