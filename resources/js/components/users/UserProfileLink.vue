<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show as showUser } from '@/routes/users';

type Props = {
    userId: number;
    userName: string;
    isCurrentUser?: boolean;
};

defineProps<Props>();
</script>

<template>
    <Link
        :href="showUser(userId)"
        class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
    >
        {{ userName }}
        <span
            v-if="isCurrentUser"
            class="font-normal text-muted-foreground"
        >
            (você)
        </span>
    </Link>
</template>
