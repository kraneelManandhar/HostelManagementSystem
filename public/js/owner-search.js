(function () {
  const searchConfigs = [
    { input: '.ms-search input', items: '.ms-student-item' },
    { input: '.mr-search input', items: '.mr-room-item' },
    { input: '.mc-search input', items: '.mc-row' },
    { input: '.mn-search input', items: '.mn-card.searchable-owner-row' },
    { input: '.sf-search input', items: '.sf-row', serverBacked: true }
  ];

  searchConfigs.forEach(({ input, items, serverBacked }) => {
    const field = document.querySelector(input);
    if (!field || serverBacked) return;

    field.addEventListener('input', () => {
      const query = field.value.trim().toLowerCase();
      document.querySelectorAll(items).forEach((item) => {
        const haystack = (item.dataset.search || item.textContent || '').toLowerCase();
        item.hidden = query !== '' && !haystack.includes(query);
      });
    });
  });

  const feeSearch = document.querySelector('.mf-search input');
  const feeTable = document.querySelector('.mf-table');

  if (feeSearch && feeTable) {
    feeSearch.addEventListener('input', () => {
      const query = feeSearch.value.trim().toLowerCase();
      const columns = Array.from(feeTable.querySelectorAll('.mf-column'));
      const rowCount = Math.max(...columns.map((column) => column.children.length - 1), 0);

      for (let index = 0; index < rowCount; index += 1) {
        const rowItems = columns
          .map((column) => column.children[index + 1])
          .filter(Boolean);
        const haystack = rowItems.map((item) => item.textContent || '').join(' ').toLowerCase();
        const hidden = query !== '' && !haystack.includes(query);

        rowItems.forEach((item) => {
          item.hidden = hidden;
        });
      }
    });
  }
})();
