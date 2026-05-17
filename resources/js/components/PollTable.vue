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
    if (!confirm('Supprimer ce sondage ?')) return;
    await deletePoll(id);
  }

  async function onLaunch(poll) {
    if (!confirm('Lancer ce sondage maintenant ?')) return;
    await launchPoll(poll);
  }
</script>

<template>
  <p v-if="polls.length === 0" class="text-gray-500">Aucun sondage pour l'instant.</p>

  <div v-if="polls.length > 0">
    <div v-if="expandedSharePoll" class="mb-4">
      <PollShareLink :poll="expandedSharePoll" />
    </div>

    <table class="w-full border-collapse text-left bg-white shadow rounded overflow-hidden">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-3 py-2">Titre</th>
          <th class="px-3 py-2">Question</th>
          <th class="px-3 py-2">Statut</th>
          <th class="px-3 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="poll in polls" :key="poll.id" class="border-t">
          <td class="px-3 py-2">{{ poll.title || '-' }}</td>
          <td class="px-3 py-2">{{ poll.question }}</td>
          <td class="px-3 py-2">{{ poll.is_draft ? 'Brouillon' : 'Lancé' }}</td>
          <td class="px-3 py-2 flex flex-wrap gap-2">
            <button v-if="poll.is_draft" @click="openEdit(poll)" class="px-2 py-1 bg-gray-200 rounded text-sm">Modifier</button>
            <button v-if="poll.is_draft" @click="onLaunch(poll)" class="px-2 py-1 bg-teal-600 text-white rounded text-sm">Lancer</button>
            <button @click="toggleShare(poll.id)" class="px-2 py-1 bg-blue-600 text-white rounded text-sm">
              {{ expandedShareId === poll.id ? 'Masquer' : 'Partager' }}
            </button>
            <button @click="onDelete(poll.id)" class="px-2 py-1 bg-red-600 text-white rounded text-sm">Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
