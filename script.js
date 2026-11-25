const descriptions = {
  Explore: 'Start with a question and map the possibilities.',
  Build: 'Turn a clear plan into a focused first draft.',
  Review: 'Check the details before anything is shared.',
};

document.querySelectorAll('[data-model]').forEach((button) => {
  button.addEventListener('click', () => {
    document.querySelectorAll('[data-model]').forEach((item) => {
      item.setAttribute('aria-selected', String(item === button));
    });
    document.getElementById('selected-model').textContent = button.dataset.model;
    document.getElementById('model-description').textContent = descriptions[button.dataset.model];
  });
});

document.getElementById('demo-form').addEventListener('submit', (event) => {
  event.preventDefault();
  const prompt = new FormData(event.currentTarget).get('prompt').trim();
  document.getElementById('demo-output').textContent = prompt
    ? `Local preview received: “${prompt}”. No request was sent to a server.`
    : 'Enter an idea to try the local preview.';
});

// Search only the local public resource catalog. No requests are sent.
const search = document.getElementById('ecosystem-search');
if (search) search.addEventListener('input', () => {
  const query = search.value.trim().toLowerCase();
  let count = 0;
  document.querySelectorAll('.showcase-group').forEach(group => {
    let visible = 0;
    group.querySelectorAll('.showcase-card').forEach(card => {
      card.hidden = !card.dataset.search.includes(query);
      if (!card.hidden) { count++; visible++; }
    });
    group.hidden = visible === 0;
  });
  document.getElementById('ecosystem-count').textContent = count + (count === 1 ? ' resource' : ' resources');
  document.getElementById('ecosystem-empty').hidden = count !== 0;
});
