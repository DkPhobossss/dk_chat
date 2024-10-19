<script setup>
import ChatMessageItem from './ChatMessageItem.vue';
import { ref, onMounted, onUpdated, computed } from 'vue';

const props = defineProps({
    messages: {
        type: Array,
        default: []
    },
    onEditClick: {
        type: Function
    },
    onDeleteClick: {
        type: Function
    },
    onRestoreClick : {
        type: Function
    },
});

const scrollContainer = ref(null);
const scrollToBottom = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
  }
};

onUpdated(() => {
    scrollToBottom();
});

onMounted(() => {
    scrollToBottom();
});

</script>

<template>
    <ul ref="scrollContainer"
        class="pt-1.5 overflow-x-hidden overflow-y-auto scrollbar grow bg-gradient-to-tr from-violet-600 via-purple-500 to-indigo-600">
        <li v-for="(message, key) in messages"
            :class="(message.user.id === $page.props.auth.user.id) ? 'text-right' : null">
            <ChatMessageItem :class="(key > 0 && message.user.id != messages[key - 1].user_id ? 'mt-1.5' : 'mt-0.5')"
                :message :key="key" :index="key" :onEditClick :onDeleteClick :onRestoreClick>
            </ChatMessageItem>
        </li>
    </ul>
</template>
