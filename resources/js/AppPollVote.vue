<script setup>
  import { ref } from 'vue';
  import { useFetchApi } from '@/composables/useFetchApi';

  const props = defineProps({
    token: { type: String, required: true },
    isAuthenticated: { type: Boolean, default: false },
    loginUrl: { type: String, default: null },
  });

  const { fetchApi } = useFetchApi();

  const poll = ref(null);
  const error = ref(null);
  const loading = ref(true);

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

  loadPoll();
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

      <ul class="space-y-2">
        <li v-for="option in poll.options" :key="option.id" class="p-2 border rounded">
          {{ option.label }}
        </li>
      </ul>
    </div>
  </main>
</template>
