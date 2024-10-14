<script setup>

import { Link } from '@inertiajs/vue3';

defineProps({
    chat: {
        type: Object,
    },
});
</script>

<template>
    <div>
        <Link :href="route('chats.show', chat.id)">
            {{ chat.name }}
        </Link>
        {{ chat.pivot.updated_at }}
    </div>
</template>
