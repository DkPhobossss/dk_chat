<script setup>
defineProps({
    title: {
        type: String,
    },
    chatUsers: {
        type: Array
    }
});

</script>

<template>
    <h1 class="font-bold ">
        {{ title }}
    </h1>
    <span class="text-xs text-gray-400" v-for="(user, index) in chatUsers">
        <template v-if="index > 0">,</template>
        {{ user.name }}
    </span>
</template>
