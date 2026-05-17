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
  <div class="space-y-6">
    <header class="flex items-center justify-between">
      <h1 class="text-2xl font-bold dark:text-white">Mes sondages</h1>
      <button v-if="view === 'list'" @click="openCreate"
        class="px-4 py-2 bg-teal-600 dark:bg-purple-900 text-white rounded-md hover:bg-teal-700 dark:hover:bg-purple-800 cursor-pointer">
        Nouveau sondage
      </button>
    </header>

    <PollTable v-if="view === 'list'" />
    <PollForm v-else />
  </div>
</template>
