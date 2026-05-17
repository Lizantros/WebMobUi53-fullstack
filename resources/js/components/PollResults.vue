<script setup>
  import { computed } from 'vue';

  const props = defineProps({
    poll: { type: Object, required: true },
  });

  const totalVotes = computed(() => {
    let total = 0;
    (props.poll.options ?? []).forEach(o => { total += (o.votes_count ?? 0); });
    return total;
  });

  function percentageOf(option) {
    if (totalVotes.value === 0) return 0;
    return Math.floor(((option.votes_count ?? 0) / totalVotes.value) * 100);
  }
</script>

<template>
  <article class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
    <header class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Résultats</h2>
      <span class="text-sm text-gray-500 dark:text-gray-400">{{ totalVotes }} vote(s)</span>
    </header>

    <ul class="space-y-3">
      <li v-for="option in poll.options" :key="option.id">
        <div class="flex justify-between text-sm mb-1">
          <span class="text-gray-700 dark:text-gray-300">{{ option.label }}</span>
          <span class="text-gray-500 dark:text-gray-400">{{ option.votes_count ?? 0 }} ({{ percentageOf(option) }}%)</span>
        </div>
        <progress :value="option.votes_count ?? 0" :max="totalVotes || 1" class="w-full h-4"></progress>
      </li>
    </ul>
  </article>
</template>
