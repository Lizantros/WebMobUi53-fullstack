<script setup>
  import { ref, computed } from 'vue';
  import { useFetchApi } from '@/composables/useFetchApi';
  import { usePolling } from '@/composables/usePolling';
  import PollVoteForm from './components/PollVoteForm.vue';
  import PollResults from './components/PollResults.vue';

  const props = defineProps({
    token: { type: String, required: true },
    isAuthenticated: { type: Boolean, default: false },
    loginUrl: { type: String, default: null },
  });

  const { fetchApi } = useFetchApi();

  const poll = ref(null);
  const error = ref(null);
  const loading = ref(true);
  const mySelection = ref([]);

  const isClosed = computed(() => {
    if (!poll.value) return false;
    return poll.value.is_draft || poll.value.is_ended;
  });
  const canSeeResults = computed(() => {
    const opts = poll.value?.options;
    return opts?.length > 0 && opts[0].votes_count !== undefined;
  });

  async function loadPoll(silent) {
    if (!silent) error.value = null;
    try {
      const data = await fetchApi({ url: 'polls/' + props.token });
      if (data) poll.value = data;
    } catch (e) {
      if (!silent) error.value = e?.data?.message || 'Impossible de charger le sondage.';
    } finally {
      if (!silent) loading.value = false;
    }
  }

  async function loadMyVote() {
    if (!props.isAuthenticated) return;
    try {
      const res = await fetchApi({ url: 'polls/' + props.token + '/my-vote' });
      if (res?.option_ids) mySelection.value = res.option_ids;
    } catch (e) {
      // pas vote, on ignore
    }
  }

  function onVoted(optionIds) {
    mySelection.value = [...optionIds];
    loadPoll(true);
  }

  loadPoll(false).then(() => loadMyVote());

  usePolling(() => {
    if (poll.value && !poll.value.is_draft) loadPoll(true);
  }, 5000);
</script>

<template>
  <main class="max-w-2xl mx-auto p-6 space-y-4">
    <p v-if="loading" class="text-gray-500">Chargement...</p>
    <p v-if="!loading && error" class="text-red-600">{{ error }}</p>

    <div v-if="!loading && !error && poll" class="space-y-4">
      <header>
        <h1 v-if="poll.title" class="text-2xl font-bold">{{ poll.title }}</h1>
        <p class="text-lg mt-2">{{ poll.question }}</p>
      </header>

      <p v-if="poll.is_draft" class="p-3 bg-amber-50 text-amber-800 rounded">
        Ce sondage n'a pas encore ete lance.
      </p>

      <div v-else class="space-y-4">
        <p v-if="isClosed" class="p-3 bg-amber-50 text-amber-800 rounded">
          Ce sondage est termine. Les votes ne sont plus acceptes.
        </p>

        <PollVoteForm
          v-if="isAuthenticated"
          :poll="poll"
          :token="token"
          :initial-selection="mySelection"
          :is-closed="isClosed"
          :on-voted="onVoted"
        />
        <p v-else class="p-3 bg-blue-50 rounded">
          <a :href="loginUrl" class="text-blue-700 underline">Connectez-vous</a>
          pour voter a ce sondage.
        </p>

        <PollResults v-if="canSeeResults" :poll="poll" />
        <p v-else class="p-3 bg-gray-50 text-gray-600 rounded text-sm">
          Les resultats de ce sondage ne sont pas publics.
        </p>
      </div>
    </div>
  </main>
</template>
