<script setup>

import { ref } from 'vue';

defineProps({
    onClick: {
        type: Function,
    },
});

const searchChats = ref([]);
let search = '';


let searchTimeout;
const onInput = () => {
    clearTimeout(searchTimeout);
    if (search.trim() !== '') {
        searchTimeout = setTimeout(() => {
            axios.get(route('users.search'), {'name' : search}).then((res) => {
                console.log(res.data);
            }).catch((error) => {
                console.error("Error:", error);
            });
        }, 250)
    }

}
</script>

<template>
    <input type="text" placeholder="Search" @input="onInput" v-model="search" max="32" />
</template>
