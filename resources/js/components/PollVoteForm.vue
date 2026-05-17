<script setup>
  import { ref, computed } from 'vue';
  import { useFetchApi } from '@/composables/useFetchApi';

  const props = defineProps({
    poll: { type: Object, required: true },
    token: { type: String, required: true },
    initialSelection: { type: Array, default: () => [] },
    isClosed: { type: Boolean, default: false },
  });

  const emit = defineEmits(['voted']);

  const { fetchApi } = useFetchApi();

  const single = ref(props.initialSelection[0] ?? null);
  const multiple = ref([...props.initialSelection]);
  const submitting = ref(false);
  const error = ref(null);
  const ok = ref(null);

  const hasVoted = computed(() => props.initialSelection.length > 0);
  const locked = computed(() => props.isClosed || (hasVoted.value && !props.poll.allow_vote_change));
  const canSubmit = computed(() => {
    if (locked.value) return false;
    if (props.poll.allow_multiple_choices) return multiple.value.length > 0;
    return single.value !== null;
  });

  function inMultiple(id) {
    return multiple.value.indexOf(id) >= 0;
  }

  function toggle(id) {
    const idx = multiple.value.indexOf(id);
    if (idx >= 0) multiple.value.splice(idx, 1);
    else multiple.value.push(id);
  }

  async function onSubmit() {
    error.value = null;
    ok.value = null;
    submitting.value = true;

    const ids = props.poll.allow_multiple_choices ? [...multiple.value] : [single.value];

    try {
      const res = await fetchApi({ url: 'polls/' + props.token + '/votes', method: 'POST', data: { option_ids: ids } });
      if (res) {
        ok.value = 'Vote enregistré.';
        emit('voted', ids);
      }
    } catch (e) {
      error.value = e?.data?.message || 'Erreur lors du vote.';
    } finally {
      submitting.value = false;
    }
  }
</script>

<template>
  <article class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
    <form @submit.prevent="onSubmit">
      <div v-if="isClosed" class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-md">
        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Ce sondage est fermé.</p>
      </div>
      <div v-if="!isClosed && hasVoted && !poll.allow_vote_change" class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-md">
        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Vous avez déjà voté.</p>
      </div>

      <ul class="space-y-2 mb-4">
        <li v-for="option in poll.options" :key="option.id">
          <label class="flex items-center gap-2 p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-slate-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-600">
            <input
              v-if="poll.allow_multiple_choices"
              type="checkbox"
              :value="option.id"
              :checked="inMultiple(option.id)"
              :disabled="locked"
              @change="toggle(option.id)"
            />
            <input
              v-if="!poll.allow_multiple_choices"
              type="radio"
              name="vote-option"
              :value="option.id"
              v-model="single"
              :disabled="locked"
            />
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ option.label }}</span>
          </label>
        </li>
      </ul>

      <p v-if="error" class="mt-1 mb-3 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
      <div v-if="ok" class="mb-3 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-md">
        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ ok }}</p>
      </div>

      <footer class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="submit" :disabled="!canSubmit || submitting"
          class="px-4 py-2 bg-teal-600 dark:bg-purple-900 text-white rounded-md hover:bg-teal-700 dark:hover:bg-purple-800 cursor-pointer disabled:opacity-50">
          {{ submitting ? 'Envoi...' : (hasVoted ? 'Modifier mon vote' : 'Voter') }}
        </button>
      </footer>
    </form>
  </article>
</template>
