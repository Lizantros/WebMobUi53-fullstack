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
  <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-md">
    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Lien de partage :</p>
    <code class="block break-all text-sm bg-white dark:bg-slate-800 text-gray-900 dark:text-gray-100 p-2 rounded border border-yellow-300 dark:border-yellow-700">{{ shareUrl }}</code>
    <p class="mt-2 text-xs text-yellow-800 dark:text-yellow-200">Sélectionner le lien pour le copier.</p>
  </div>
</template>
