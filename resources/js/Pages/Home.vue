<script setup>
import { Head } from '@inertiajs/vue3';
import MainButton from '@/Components/PrimaryLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({

});
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Welcome" />

        <h1 class="mb-4 text-lg font-medium">
            Welcome  {{ $page.props.auth.user.name }}
        </h1>

        <div class="flex items-center justify-center mt-4 ">
           qweqwe
            
        </div>
    </AuthenticatedLayout>
</template>
