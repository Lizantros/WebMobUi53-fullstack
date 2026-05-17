<script setup>
  import { ref, computed } from 'vue';
  import { usePollStore } from '@/stores/usePollStore';

  const { editingPoll, backToList, createPoll, updatePoll } = usePollStore();

  const initial = editingPoll.value;
  const isEdit = computed(() => initial !== null);

  const labels = [];
  if (initial?.options?.length) {
    initial.options.forEach(o => labels.push(o.label));
  } else {
    labels.push('', '');
  }

  const title = ref(initial?.title ?? '');
  const question = ref(initial?.question ?? '');
  const options = ref(labels);
  const allowMultiple = ref(!!initial?.allow_multiple_choices);
  const allowChange = ref(!!initial?.allow_vote_change);
  const isPublic = ref(!!initial?.results_public);
  const durationMin = ref(initial?.duration ? String(Math.floor(initial.duration / 60)) : '');
  const launchNow = ref(false);
  const submitting = ref(false);
  const error = ref(null);

  function addOption() {
    options.value.push('');
  }

  function removeOption(index) {
    if (options.value.length > 2) {
      options.value.splice(index, 1);
    }
  }

  async function onSubmit() {
    error.value = null;

    const cleaned = [];
    options.value.forEach(o => {
      const t = o.trim();
      if (t.length > 0) cleaned.push(t);
    });

    if (cleaned.length < 2) {
      error.value = 'Au moins deux options sont requises.';
      return;
    }

    submitting.value = true;

    const payload = {
      title: title.value || null,
      question: question.value,
      is_draft: !launchNow.value,
      allow_multiple_choices: allowMultiple.value,
      allow_vote_change: allowChange.value,
      results_public: isPublic.value,
      duration: durationMin.value ? Math.floor(durationMin.value * 60) : null,
      options: cleaned,
    };

    try {
      const result = isEdit.value ? await updatePoll(initial.id, payload) : await createPoll(payload);
      if (result) backToList();
    } catch (e) {
      error.value = e?.data?.message || "Erreur lors de l'enregistrement.";
    } finally {
      submitting.value = false;
    }
  }
</script>

<template>
  <form @submit.prevent="onSubmit" class="space-y-4 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold">{{ isEdit ? 'Modifier le sondage' : 'Nouveau sondage' }}</h2>

    <div>
      <label class="block text-sm font-medium mb-1">Titre (optionnel)</label>
      <input v-model="title" type="text" maxlength="255" class="w-full px-3 py-2 border rounded" />
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Question *</label>
      <input v-model="question" type="text" required maxlength="255" class="w-full px-3 py-2 border rounded" />
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Options *</label>
      <div v-for="(opt, index) in options" :key="index" class="flex gap-2 mb-2">
        <input v-model="options[index]" type="text" maxlength="255" class="flex-1 px-3 py-2 border rounded" :placeholder="`Option ${index + 1}`" />
        <button type="button" v-if="options.length > 2" @click="removeOption(index)" class="px-3 py-2 bg-gray-200 rounded">Retirer</button>
      </div>
      <button type="button" @click="addOption" class="px-3 py-1 bg-gray-100 border rounded text-sm">Ajouter une option</button>
    </div>

    <fieldset class="space-y-2">
      <legend class="text-sm font-medium">Paramètres</legend>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="allowMultiple" /> Plusieurs choix autorisés</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="allowChange" /> Le vote peut être modifié</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="isPublic" /> Résultats publics</label>
      <label class="flex items-center gap-2"><input type="checkbox" v-model="launchNow" /> Lancer immédiatement</label>
    </fieldset>

    <div>
      <label class="block text-sm font-medium mb-1">Durée (minutes, optionnel)</label>
      <input v-model="durationMin" type="number" min="1" class="w-40 px-3 py-2 border rounded" />
    </div>

    <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>

    <div class="flex gap-2">
      <button type="submit" :disabled="submitting" class="px-4 py-2 bg-teal-600 text-white rounded">
        {{ submitting ? 'Enregistrement...' : (isEdit ? 'Enregistrer' : 'Créer le sondage') }}
      </button>
      <button type="button" @click="backToList" class="px-4 py-2 bg-gray-200 rounded">Annuler</button>
    </div>
  </form>
</template>
