import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const polls = ref([]);
const editingPoll = ref(null);
const view = ref('list');

export function usePollStore() {
  const { fetchApi } = useFetchApi();

  function setPolls(data) {
    polls.value = data;
  }

  function openCreate() {
    editingPoll.value = null;
    view.value = 'form';
  }

  function openEdit(poll) {
    editingPoll.value = poll;
    view.value = 'form';
  }

  function backToList() {
    editingPoll.value = null;
    view.value = 'list';
  }

  async function createPoll(data) {
    const created = await fetchApi({ url: 'polls', method: 'POST', data });
    if (created) polls.value = [created, ...polls.value];
    return created;
  }

  async function updatePoll(id, data) {
    const updated = await fetchApi({ url: 'polls/' + id, method: 'PATCH', data });
    if (updated) {
      const idx = polls.value.findIndex(p => p.id === updated.id);
      if (idx >= 0) polls.value[idx] = updated;
    }
    return updated;
  }

  async function launchPoll(poll) {
    const labels = [];
    (poll.options ?? []).forEach(o => labels.push(o.label));
    return await updatePoll(poll.id, {
      title: poll.title,
      question: poll.question,
      is_draft: false,
      allow_multiple_choices: !!poll.allow_multiple_choices,
      allow_vote_change: !!poll.allow_vote_change,
      results_public: !!poll.results_public,
      duration: poll.duration ?? null,
      options: labels,
    });
  }

  async function deletePoll(id) {
    const result = await fetchApi({ url: 'polls/' + id, method: 'DELETE' });
    if (result) polls.value = polls.value.filter(p => p.id !== id);
  }

  return {
    polls,
    editingPoll,
    view,
    setPolls,
    openCreate,
    openEdit,
    backToList,
    createPoll,
    updatePoll,
    launchPoll,
    deletePoll,
  };
}
