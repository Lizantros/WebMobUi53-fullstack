<script setup>
  import { ref, computed } from 'vue';
  import { useFetchApi } from '@/composables/useFetchApi';
  import PollVoteForm from './components/PollVoteForm.vue';

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
    return poll.value.is_draft || (poll.value.ends_at && new Date(poll.value.ends_at) < new Date());
  });

  async function loadPoll() {
    error.value = null;
    try {
      const data = await fetchApi({ url: 'polls/' + props.token });
      if (data) poll.value = data;
    } catch (e) {
      error.value = e?.data?.message || 'Impossible de charger le sondage.';
    } finally {
      loading.value = false;
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
    loadPoll();
  }

  loadPoll().then(() => loadMyVote());
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
      </div>
    </div>
  </main>
</template>
