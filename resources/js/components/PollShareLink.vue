<script setup>
  import { computed } from 'vue';

  const props = defineProps({
    poll: { type: Object, required: true },
  });

  const shareUrl = computed(() => {
    return window.location.origin + '/polls/' + props.poll.secret_token;
  });
</script>

<template>
  <div class="mt-2 p-3 bg-gray-50 border rounded text-sm">
    <p class="font-medium mb-1">Lien de partage :</p>
    <a :href="shareUrl" target="_blank" class="block break-all text-teal-700 underline">{{ shareUrl }}</a>
    <p class="mt-1 text-xs text-gray-500">Selectionner le lien pour le copier.</p>
  </div>
</template>
