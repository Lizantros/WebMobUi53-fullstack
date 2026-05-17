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
      // pas voté, on ignore
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
  <div class="space-y-4">
    <p v-if="loading" class="text-gray-500 dark:text-gray-400">Chargement...</p>
    <p v-if="!loading && error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>

    <div v-if="!loading && !error && poll" class="space-y-4">
      <article class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-6">
        <header>
          <h1 v-if="poll.title" class="text-2xl font-bold text-gray-900 dark:text-white">{{ poll.title }}</h1>
          <p class="text-lg mt-2 text-gray-700 dark:text-gray-300">{{ poll.question }}</p>
        </header>
      </article>

      <div v-if="poll.is_draft" class="p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-md">
        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
          Ce sondage n'a pas encore été lancé.
        </p>
      </div>

      <template v-else>
        <div v-if="isClosed" class="p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-md">
          <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
            Ce sondage est terminé. Les votes ne sont plus acceptés.
          </p>
        </div>

        <PollVoteForm
          v-if="isAuthenticated"
          :poll="poll"
          :token="token"
          :initial-selection="mySelection"
          :is-closed="isClosed"
          @voted="onVoted"
        />
        <div v-else class="p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 rounded-md">
          <p class="text-sm text-yellow-800 dark:text-yellow-200">
            <a :href="loginUrl" class="font-medium underline">Connectez-vous</a>
            pour voter à ce sondage.
          </p>
        </div>

        <PollResults v-if="canSeeResults" :poll="poll" />
        <p v-else class="text-sm text-gray-500 dark:text-gray-400">
          Les résultats de ce sondage ne sont pas publics.
        </p>
      </template>
    </div>
  </div>
</template>
