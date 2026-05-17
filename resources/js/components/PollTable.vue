<script setup>
  import { ref, computed } from 'vue';
  import { usePollStore } from '@/stores/usePollStore';
  import PollShareLink from './PollShareLink.vue';

  const { polls, deletePoll, launchPoll, openEdit } = usePollStore();

  const expandedShareId = ref(null);

  const expandedSharePoll = computed(() => {
    return polls.value.find(p => p.id === expandedShareId.value) ?? null;
  });

  function toggleShare(id) {
    expandedShareId.value = expandedShareId.value === id ? null : id;
  }

  async function onDelete(id) {
    if (!confirm('Souhaitez-vous vraiment supprimer ce sondage ?')) return;
    await deletePoll(id);
  }

  async function onLaunch(poll) {
    if (!confirm('Lancer ce sondage maintenant ?')) return;
    await launchPoll(poll);
  }
</script>

<template>
  <div v-if="polls.length === 0" class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
    <p class="text-gray-500 dark:text-gray-400">Aucun sondage pour l'instant.</p>
  </div>

  <div v-else class="space-y-4">
    <PollShareLink v-if="expandedSharePoll" :poll="expandedSharePoll" />

    <article class="bg-white dark:bg-slate-800 rounded-lg shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Titre</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Question</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Statut</th>
              <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="poll in polls" :key="poll.id" class="border-b border-gray-100 dark:border-gray-800">
              <td class="px-4 py-3 text-sm dark:text-white">{{ poll.title || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ poll.question }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300">
                  {{ poll.is_draft ? 'Brouillon' : 'Lancé' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                  <button v-if="poll.is_draft" @click="openEdit(poll)"
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 cursor-pointer">
                    Modifier
                  </button>
                  <button v-if="poll.is_draft" @click="onLaunch(poll)"
                    class="px-3 py-1 text-sm bg-teal-600 dark:bg-purple-900 text-white rounded hover:bg-teal-700 dark:hover:bg-purple-800 cursor-pointer">
                    Lancer
                  </button>
                  <button @click="toggleShare(poll.id)"
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 cursor-pointer">
                    {{ expandedShareId === poll.id ? 'Masquer' : 'Partager' }}
                  </button>
                  <button @click="onDelete(poll.id)"
                    class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 cursor-pointer">
                    Supprimer
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </article>
  </div>
</template>
