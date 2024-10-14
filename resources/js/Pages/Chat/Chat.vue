<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatLayout from '@/Layouts/ChatLayout.vue';
import ChatMessage from '../../Components/Chat/ChatMessage.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';
import { ref } from 'vue';


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

const sendMessage = () => {
    if (savedMessage.value !== null) {
        return onEditSubmit(savedMessage.value.messageId, savedMessage.value.index);
    }

    axios.post(route('chats.messages.store', props.chat.id), form).then((res) => {
        form.body = null;

        res.data.data.user = {
            name: usePage().props.auth.user.name
        }
        props.messages.push(res.data.data);
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
        <ChatLayout :chats>
            <div class="flex flex-col">
                <div class="h-5/6">
                    <h1>
                        {{ chat.name }}
                        (
                        <b v-for="(user, index) in chatUsers">
                            <template v-if="index > 0">,</template>
                            {{ user.name }}
                        </b>
                        )
                    </h1>
                    <ChatMessage v-for="(message, key) in messages" :message :key="key" :index="key" :onEditClick
                        :onDeleteClick :onRestoreClick></ChatMessage>
                </div>


                <div class="mt-auto">
                    <form class="flex justify-center" @submit.prevent="sendMessage">
                        <textarea name="message" id="message" v-model="form.body" required></textarea>
                        <div class="ml-auto">
                            <PrimaryButton>Send</PrimaryButton>
                            <PrimaryButton v-if="savedMessage !== null" @click.prevent="onEditCancel">Cancel
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </ChatLayout>
    </AuthenticatedLayout>
</template>
