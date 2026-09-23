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
