(function () {
  function normalize(value) {
    return (value || '')
      .toString()
      .replace(/[|_-]+/g, ' ')
      .replace(/\s+/g, ' ')
      .trim()
      .toLowerCase();
  }

  function getTerms(field) {
    return normalize(field.value).split(/\s+/).filter(Boolean);
  }

  function matches(item, terms) {
    if (terms.length === 0) return true;
    const haystack = searchableText(item);
    return terms.every((term) => haystack.includes(term));
  }

  function searchableText(item) {
    const controlText = Array.from(item.querySelectorAll('input, select, textarea'))
      .filter((control) => control.type !== 'hidden')
      .map((control) => {
        if (control.tagName === 'SELECT') {
          return control.options[control.selectedIndex]?.text || control.value;
        }
        return control.value;
      })
      .filter(Boolean)
      .join(' ');

    const rawText = [
      item.dataset.search,
      item.dataset.roomNumber ? `room ${item.dataset.roomNumber}` : '',
      controlText,
      item.textContent
    ].filter(Boolean).join(' ');

    return normalize(rawText);
  }

  function getScope(field) {
    return field.closest('main') || document;
  }

  function getEmptyState(scope, anchor) {
    let emptyState = scope.querySelector('.owner-search-empty');

    if (!emptyState && anchor) {
      emptyState = document.createElement('div');
      emptyState.className = 'owner-search-empty';
      emptyState.textContent = 'No matching records found.';
      emptyState.hidden = true;
      anchor.after(emptyState);
    }

    return emptyState;
  }

  function bindSimpleSearch(inputSelector, itemSelector) {
    document.querySelectorAll(inputSelector).forEach((field) => {
      const scope = getScope(field);
      const items = Array.from(scope.querySelectorAll(itemSelector));
      if (items.length === 0) return;

      const emptyState = getEmptyState(scope, items[items.length - 1].parentElement);

      function filterItems() {
        const terms = getTerms(field);
        let visibleCount = 0;

        items.forEach((item) => {
          const isMatch = matches(item, terms);
          item.hidden = !isMatch;
          if (isMatch) visibleCount += 1;
        });

        if (emptyState) {
          emptyState.hidden = terms.length === 0 || visibleCount > 0;
        }
      }

      field.addEventListener('input', filterItems);
      items.forEach((item) => {
        item.querySelectorAll('input, select, textarea').forEach((control) => {
          control.addEventListener('change', filterItems);
        });
      });
      filterItems();
    });
  }

  function bindFeeSearch() {
    document.querySelectorAll('.mf-search input').forEach((field) => {
      const scope = getScope(field);
      const table = scope.querySelector('.mf-table');
      if (!table) return;

      const rows = Array.from(table.querySelectorAll('tbody tr.searchable-row'));
      if (rows.length > 0) {
        const emptyState = getEmptyState(scope, table);

        function filterTableRows() {
          const terms = getTerms(field);
          let visibleCount = 0;

          rows.forEach((row) => {
            const isMatch = matches(row, terms);
            row.hidden = !isMatch;
            if (isMatch) visibleCount += 1;
          });

          if (emptyState) {
            emptyState.hidden = terms.length === 0 || visibleCount > 0;
          }
        }

        field.addEventListener('input', filterTableRows);
        filterTableRows();
        return;
      }

      const columns = Array.from(table.querySelectorAll('.mf-column'));
      if (columns.length === 0) return;

      const rowCount = Math.max(...columns.map((column) => column.children.length - 1), 0);
      const feeRows = Array.from({ length: rowCount }, (_, index) => (
        columns.map((column) => column.children[index + 1]).filter(Boolean)
      ));
      const emptyState = getEmptyState(scope, table);

      function filterFeeRows() {
        const terms = getTerms(field);
        let visibleCount = 0;

        feeRows.forEach((rowItems) => {
          const haystack = normalize(rowItems.map((item) => item.textContent || '').join(' '));
          const isMatch = terms.length === 0 || terms.every((term) => haystack.includes(term));

          rowItems.forEach((item) => {
            item.hidden = !isMatch;
          });

          if (isMatch) visibleCount += 1;
        });

        if (emptyState) {
          emptyState.hidden = terms.length === 0 || visibleCount > 0;
        }
      }

      field.addEventListener('input', filterFeeRows);
      filterFeeRows();
    });
  }

  bindSimpleSearch('.ms-search input', '.ms-student-item');
  bindSimpleSearch('.mr-search input', '.mr-room-item');
  bindSimpleSearch('.mc-search input', '.mc-row');
  bindSimpleSearch('.mn-search input', '.mn-card.searchable-owner-row');
  bindSimpleSearch('.sf-search input', '.sf-row');
  bindFeeSearch();
})();
