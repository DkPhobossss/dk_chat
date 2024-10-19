<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatLayout from '@/Layouts/ChatLayout.vue';
import ChatHello from '@/Components/Chat/ChatHello.vue';

defineProps({
    chats: {
        type: Array,
    },
});
</script>



<template>
    <AuthenticatedLayout>

        <Head :title="`${$page.props.auth.user.name} Chats`" />

        <ChatLayout :chats>
            <ChatHello />
        </ChatLayout>

    </AuthenticatedLayout>
</template>
