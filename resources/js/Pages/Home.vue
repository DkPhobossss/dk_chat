<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';
import MainButton from '@/Components/MainButton.vue';

defineProps({

});
</script>

<template>
    <GuestLayout>

        <Head title="Welcome" />

        <h1 class="mb-4 text-lg font-medium text-center">
            Welcome
        </h1>

        <div class="flex items-center justify-center mt-4 ">
            <MainButton class="ms-4" :link="route('login')">
                Log in
            </MainButton>
            <MainButton class="ms-4" :link="route('register')">
                Register
            </MainButton>
            <MainButton class="ms-4" :link="route('logout')" method="post">
                Logout
            </MainButton>
        </div>
    </GuestLayout>
</template>
