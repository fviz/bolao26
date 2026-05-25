<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';

type Props = {
    userName: string;
    userAvatar?: string | null;
};

const props = defineProps<Props>();

const { getInitials } = useInitials();

const showAvatar = computed(
    () => props.userAvatar && props.userAvatar !== '',
);
</script>

<template>
    <Avatar class="size-8 shrink-0 overflow-hidden rounded-full">
        <AvatarImage
            v-if="showAvatar"
            :src="userAvatar!"
            :alt="userName"
            class="object-cover object-center"
        />
        <AvatarFallback class="rounded-full text-xs text-black dark:text-white">
            {{ getInitials(userName) }}
        </AvatarFallback>
    </Avatar>
</template>
