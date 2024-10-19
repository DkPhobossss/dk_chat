<script setup>

const randomImagesSrc = [
    '/images/1.avif',
    '/images/2.jpg',
    '/images/3.avif',
    '/images/4.avif',
]
</script>

<template>
    <div class="flex items-center justify-center h-full" >
        <img class="max-w-full max-h-full" :src="randomImagesSrc[Math.floor(Math.random()*4)]" alt="hello" />
    </div>
</template>
