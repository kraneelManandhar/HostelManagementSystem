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

  function setVisible(element, isVisible) {
    element.hidden = !isVisible;
    element.style.display = isVisible ? '' : 'none';
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
          setVisible(item, isMatch);
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

  function bindTableOrColumnSearch() {
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
            setVisible(row, isMatch);
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
            setVisible(item, isMatch);
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

  function isInteractiveClick(target) {
    return Boolean(target.closest('a, button, input, select, textarea, label, form, [data-no-row-details]'));
  }

  function getDetailLabel(item, index) {
    const table = item.closest('table');
    if (table) {
      const header = table.querySelectorAll('thead th')[index];
      if (header) return header.textContent.trim();
    }

    const panel = item.closest('.mc-panel, .table-box, .wd-room-table');
    const header = panel?.querySelectorAll('.mc-header-pill, .table-header span')[index];
    if (header) return header.textContent.trim();

    return `Detail ${index + 1}`;
  }

  function escapeHtml(value) {
    return (value || '').toString().replace(/[&<>"']/g, (char) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    })[char]);
  }

  function getDetailValue(cell) {
    const control = cell.matches('input, select, textarea')
      ? cell
      : cell.querySelector('input:not([type="hidden"]), select, textarea');

    if (!control) return cell.textContent.trim();
    if (control.tagName === 'SELECT') {
      return control.options[control.selectedIndex]?.text.trim() || control.value.trim();
    }
    return control.value.trim();
  }

  function getDetailRows(item) {
    const cells = Array.from(item.querySelectorAll(':scope > .mc-cell, :scope > .cell, :scope > td'));
    if (cells.length > 0) {
      return cells
        .map((cell, index) => ({
          label: getDetailLabel(item, index),
          value: getDetailValue(cell)
        }))
        .filter((row) => row.value);
    }

    const title = item.querySelector('.mn-card-title, h4')?.textContent.trim();
    const description = item.querySelector('.mn-description, p')?.textContent.trim();
    const meta = item.querySelector('.mn-date, .wd-notice-meta')?.textContent.trim();
    return [
      title ? { label: 'Title', value: title } : null,
      description ? { label: 'Description', value: description } : null,
      meta ? { label: 'Date', value: meta } : null
    ].filter(Boolean);
  }

  function ensureDetailModal() {
    let modal = document.querySelector('.list-detail-modal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.className = 'list-detail-modal';
    modal.hidden = true;
    modal.innerHTML = `
      <div class="list-detail-backdrop" data-close-details></div>
      <section class="list-detail-card" role="dialog" aria-modal="true" aria-labelledby="listDetailTitle">
        <button class="list-detail-close" type="button" data-close-details aria-label="Close details">&times;</button>
        <h3 id="listDetailTitle">Details</h3>
        <div class="list-detail-content"></div>
      </section>
    `;
    document.body.appendChild(modal);

    modal.addEventListener('click', (event) => {
      if (event.target.closest('[data-close-details]')) {
        modal.hidden = true;
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') modal.hidden = true;
    });

    return modal;
  }

  function openDetails(item) {
    const url = item.dataset.detailUrl;
    if (url) {
      window.location.href = url;
      return;
    }

    const rows = getDetailRows(item);
    if (rows.length === 0) return;

    const modal = ensureDetailModal();
    const content = modal.querySelector('.list-detail-content');
    content.innerHTML = rows.map((row) => `
      <div class="list-detail-row">
        <span>${escapeHtml(row.label)}</span>
        <strong>${escapeHtml(row.value)}</strong>
      </div>
    `).join('');
    modal.hidden = false;
    modal.querySelector('.list-detail-close')?.focus();
  }

  function bindDetailOpeners() {
    const selectors = [
      '.mc-row',
      '.mf-fees-table tbody tr.searchable-row',
      '.mn-card.searchable-owner-row',
      '.sf-row',
      '.ms-student-item',
      '.mr-room-item'
    ].join(', ');

    document.querySelectorAll(selectors).forEach((item) => {
      item.classList.add('can-open-details');
      item.setAttribute('tabindex', item.matches('a') ? item.getAttribute('tabindex') || '0' : '0');
    });

    document.addEventListener('click', (event) => {
      const item = event.target.closest(selectors);
      if (!item || isInteractiveClick(event.target)) return;
      if (item.matches('a')) return;

      openDetails(item);
    });

    document.addEventListener('keydown', (event) => {
      if (!['Enter', ' '].includes(event.key)) return;
      const item = event.target.closest(selectors);
      if (!item || isInteractiveClick(event.target)) return;
      event.preventDefault();
      if (item.matches('a')) {
        item.click();
        return;
      }
      openDetails(item);
    });
  }

  bindSimpleSearch('.ms-search input', '.ms-student-item');
  bindSimpleSearch('.mr-search input', '.mr-room-item');
  bindSimpleSearch('.mc-search input', '.mc-row');
  bindSimpleSearch('.mn-search input', '.mn-card.searchable-owner-row');
  bindSimpleSearch('.sf-search input', '.sf-row');
  bindTableOrColumnSearch();
  bindDetailOpeners();
})();
