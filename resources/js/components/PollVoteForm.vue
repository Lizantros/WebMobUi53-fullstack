<script setup>
  import { ref, computed } from 'vue';
  import { useFetchApi } from '@/composables/useFetchApi';

  const props = defineProps({
    poll: { type: Object, required: true },
    token: { type: String, required: true },
    initialSelection: { type: Array, default: () => [] },
    isClosed: { type: Boolean, default: false },
    onVoted: { type: Function, default: null },
  });

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
        ok.value = 'Vote enregistre.';
        if (typeof props.onVoted === 'function') props.onVoted(ids);
      }
    } catch (e) {
      error.value = e?.data?.message || 'Erreur lors du vote.';
    } finally {
      submitting.value = false;
    }
  }
</script>

<template>
  <form @submit.prevent="onSubmit" class="space-y-3 p-4 bg-white rounded shadow">
    <p v-if="isClosed" class="text-amber-700 bg-amber-50 p-2 rounded">Ce sondage est ferme.</p>
    <p v-if="!isClosed && hasVoted && !poll.allow_vote_change" class="text-blue-700 bg-blue-50 p-2 rounded">Vous avez deja vote.</p>

    <ul class="space-y-2">
      <li v-for="option in poll.options" :key="option.id">
        <label class="flex items-center gap-2 p-2 border rounded cursor-pointer hover:bg-gray-50">
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
          <span>{{ option.label }}</span>
        </label>
      </li>
    </ul>

    <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
    <p v-if="ok" class="text-green-700 text-sm">{{ ok }}</p>

    <button type="submit" :disabled="!canSubmit || submitting" class="px-4 py-2 bg-teal-600 text-white rounded disabled:opacity-50">
      {{ submitting ? 'Envoi...' : (hasVoted ? 'Modifier mon vote' : 'Voter') }}
    </button>
  </form>
</template>
