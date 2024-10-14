<script setup>
import { Link } from '@inertiajs/vue3';




const props = defineProps({
    message:  {
        type: Object
    },
    onEditClick: {
        type: Function
    },
    onDeleteClick: {
        type: Function
    }, 
    onRestoreClick: {
        type: Function
    }, 
    index: {
        
    }
});

</script>

<template>
    <div class="border border-indigo-600">
        <div class="flex justify-between">
            <strong>{{ message.user.name }}</strong>
            <div v-if="message.user_id === $page.props.auth.user.id">
                <a v-if="message.body !== null" class="cursor-pointer" @click.prevent="onEditClick(message.id, index, message)">Edit</a>
                <a v-if="message.body !== null" class="cursor-pointer" @click.prevent="onDeleteClick(message.id, index)" >Delete</a>
                <a v-else="" class="cursor-pointer" @click.prevent="onRestoreClick(message.id, index)" >Restore</a>
            </div>
        </div>
        
        <div class="whitespace-pre">{{ message.body ?? 'Message deleted.' }} <span>{{ message.updated_at }} {{ message.updated_at !== message.created_at ? '(Edited)' : '' }}</span></div>
    </div>
</template>
