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
  <section class="p-4 bg-white rounded shadow">
    <header class="mb-3 flex items-center justify-between">
      <h2 class="font-bold">Résultats</h2>
      <span class="text-sm text-gray-500">{{ totalVotes }} vote(s)</span>
    </header>

    <ul class="space-y-3">
      <li v-for="option in poll.options" :key="option.id">
        <div class="flex justify-between text-sm mb-1">
          <span>{{ option.label }}</span>
          <span>{{ option.votes_count ?? 0 }} ({{ percentageOf(option) }}%)</span>
        </div>
        <progress :value="option.votes_count ?? 0" :max="totalVotes || 1" class="w-full h-4"></progress>
      </li>
    </ul>
  </section>
</template>
