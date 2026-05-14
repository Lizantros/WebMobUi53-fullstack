<script setup>
  import { usePollStore } from '@/stores/usePollStore';

  const { polls, deletePoll, openEdit } = usePollStore();

  async function onDelete(id) {
    if (!confirm('Supprimer ce sondage ?')) return;
    await deletePoll(id);
  }
</script>

<template>
  <p v-if="polls.length === 0" class="text-gray-500">Aucun sondage pour l'instant.</p>

  <div v-if="polls.length > 0">
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
          <td class="px-3 py-2">{{ poll.is_draft ? 'Brouillon' : 'Lance' }}</td>
          <td class="px-3 py-2 flex flex-wrap gap-2">
            <button v-if="poll.is_draft" @click="openEdit(poll)" class="px-2 py-1 bg-gray-200 rounded text-sm">Modifier</button>
            <button @click="onDelete(poll.id)" class="px-2 py-1 bg-red-600 text-white rounded text-sm">Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
