<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    chats: {
        type: Object,
    },
});

</script>

<template>
    <AuthenticatedLayout>

        <Head title="Welcome" />

        <h1 class="mb-4 text-lg font-medium">
            Welcome  {{ $page.props.auth.user.name }}
        </h1>

        <div class="flex items-center justify-center mt-4 ">
            <div v-for="chat in chats">
                {{ chat.id }}
                {{ chat.updated_at }}
                {{ chat.name}}
            </div>
        </div>
    </AuthenticatedLayout>
</template>
