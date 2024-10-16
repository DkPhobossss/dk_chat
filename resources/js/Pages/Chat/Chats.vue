<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChatLayout from '@/Layouts/ChatLayout.vue';

defineProps({
    chats: {
        type: Array,
    },
});

const randomImagesSrc = [
    '/images/1.avif',
    '/images/2.jpg',
    '/images/3.avif',
    '/images/4.avif',
]
</script>

<template>
    <AuthenticatedLayout>

        <Head :title="`${$page.props.auth.user.name} Chats`" />

        <ChatLayout :chats>
            <div class="flex items-center justify-center h-full" >
                <img class="max-w-full max-h-full" :src="randomImagesSrc[Math.floor(Math.random()*4)]" alt="hello" />
            </div>
        </ChatLayout>

    </AuthenticatedLayout>
</template>
