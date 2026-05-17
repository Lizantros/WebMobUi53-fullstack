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
  <article class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
    <header class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ isEdit ? 'Modifier le sondage' : 'Nouveau sondage' }}
      </h1>
    </header>

    <form @submit.prevent="onSubmit">
      <div class="mb-4">
        <label for="poll-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Titre (optionnel)
        </label>
        <input id="poll-title" v-model="title" type="text" maxlength="255"
          class="w-full px-3 py-2 border rounded-md bg-white dark:bg-slate-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-teal-500 dark:focus:ring-purple-500 focus:border-transparent" />
      </div>

      <div class="mb-4">
        <label for="poll-question" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Question *
        </label>
        <input id="poll-question" v-model="question" type="text" required maxlength="255"
          class="w-full px-3 py-2 border rounded-md bg-white dark:bg-slate-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-teal-500 dark:focus:ring-purple-500 focus:border-transparent" />
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Options *</label>
        <div v-for="(opt, index) in options" :key="index" class="flex gap-2 mb-2">
          <input v-model="options[index]" type="text" maxlength="255" :placeholder="`Option ${index + 1}`"
            class="flex-1 px-3 py-2 border rounded-md bg-white dark:bg-slate-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-teal-500 dark:focus:ring-purple-500 focus:border-transparent" />
          <button type="button" v-if="options.length > 2" @click="removeOption(index)"
            class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 cursor-pointer">
            Retirer
          </button>
        </div>
        <button type="button" @click="addOption"
          class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 cursor-pointer">
          Ajouter une option
        </button>
      </div>

      <div class="mb-6">
        <fieldset>
          <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Paramètres</legend>
          <div class="flex items-center mb-2">
            <input type="checkbox" id="poll-multi" v-model="allowMultiple" class="mr-2">
            <label for="poll-multi" class="text-sm text-gray-700 dark:text-gray-300">Plusieurs choix autorisés</label>
          </div>
          <div class="flex items-center mb-2">
            <input type="checkbox" id="poll-change" v-model="allowChange" class="mr-2">
            <label for="poll-change" class="text-sm text-gray-700 dark:text-gray-300">Le vote peut être modifié</label>
          </div>
          <div class="flex items-center mb-2">
            <input type="checkbox" id="poll-public" v-model="isPublic" class="mr-2">
            <label for="poll-public" class="text-sm text-gray-700 dark:text-gray-300">Résultats publics</label>
          </div>
          <div class="flex items-center mb-2">
            <input type="checkbox" id="poll-launch" v-model="launchNow" class="mr-2">
            <label for="poll-launch" class="text-sm text-gray-700 dark:text-gray-300">Lancer immédiatement</label>
          </div>
        </fieldset>
      </div>

      <div class="mb-6">
        <label for="poll-duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Durée (minutes, optionnel)
        </label>
        <input id="poll-duration" v-model="durationMin" type="number" min="1"
          class="w-40 px-3 py-2 border rounded-md bg-white dark:bg-slate-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-teal-500 dark:focus:ring-purple-500 focus:border-transparent" />
      </div>

      <p v-if="error" class="mt-1 mb-4 text-sm text-red-600 dark:text-red-400">{{ error }}</p>

      <footer class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <button type="button" @click="backToList"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 cursor-pointer">
            Annuler
          </button>
          <button type="submit" :disabled="submitting"
            class="px-4 py-2 bg-teal-600 dark:bg-purple-900 text-white rounded-md hover:bg-teal-700 dark:hover:bg-purple-800 cursor-pointer disabled:opacity-50">
            {{ submitting ? 'Enregistrement...' : (isEdit ? 'Enregistrer' : 'Créer le sondage') }}
          </button>
        </div>
      </footer>
    </form>
  </article>
</template>
