<script setup>
let props = defineProps({
    savedMessage: Object,
    refForm: Object,
    onSendMessage: Function,
    onEditCancel: Function,
});
</script>

<template>
    <form class="flex justify-center" @submit.prevent="onSendMessage">
        <textarea @keydown.meta.enter="onSendMessage" @keydown.ctrl.enter="onSendMessage"
            class="w-full mr-3 border-none rounded-lg resize-none focus:ring-0 focus:outline-none scrollbar min-h-12"
            name="message" id="message" v-model="refForm.body" placeholder="Type message..." required></textarea>
        <div class="flex items-center ml-auto">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white uppercase bg-indigo-600 border rounded hover:bg-indigo-900 focus:outline-none focus:ring-2">
                Send
            </button>
            <button v-if="savedMessage !== null" @click.prevent="onEditCancel" type="submit"
                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white uppercase bg-indigo-600 border rounded hover:bg-indigo-900 focus:outline-none focus:ring-2">
                Cancel
            </button>
        </div>
    </form>
</template>