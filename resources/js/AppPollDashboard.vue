<script setup>
  import PollTable from './components/PollTable.vue';
  import PollForm from './components/PollForm.vue';
  import { usePollStore } from '@/stores/usePollStore';

  const props = defineProps({
    polls: { type: Array, default: () => [] },
    loginUrl: { type: String, default: null },
    username: { type: String, default: null },
  });

  const { setPolls, view, openCreate } = usePollStore();
  setPolls(props.polls);
</script>

<template>
  <main class="max-w-4xl mx-auto p-6 space-y-6">
    <header class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Mes sondages</h1>
      <button v-if="view === 'list'" @click="openCreate" class="px-4 py-2 bg-teal-600 text-white rounded">
        Nouveau sondage
      </button>
    </header>

    <PollTable v-if="view === 'list'" />
    <PollForm v-else />
  </main>
</template>
