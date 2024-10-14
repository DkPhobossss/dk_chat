<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatLayout from '@/Layouts/ChatLayout.vue';

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
            hello!
        </ChatLayout>

    </AuthenticatedLayout>
</template>
