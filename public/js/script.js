/* ===== STATUS TOGGLE ===== */
document.querySelectorAll('.status-pill').forEach(btn => {

  btn.addEventListener('click', function(){

    const isYes = this.classList.contains('yes');
    const type = this.dataset.type;
    const activeText = type === 'cleaning' ? 'Done' : 'Yes';
    const inactiveText = type === 'cleaning' ? 'Pending' : 'No';

    this.classList.toggle('yes');
    this.classList.toggle('no');
    this.innerText = isYes ? inactiveText : activeText;

    const fd = new FormData();
    fd.append('id', this.dataset.id);
    fd.append('status', isYes ? 0 : 1);

    fetch(getWardenUrl(type),{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data => {
      if (!data.success) throw new Error('Update failed');
      showToast("Updated successfully");
    })
    .catch(() => {
      this.classList.toggle('yes');
      this.classList.toggle('no');
      this.innerText = isYes ? activeText : inactiveText;
      showToast("Could not update");
    });

  });

});

/* ===== TIMING AUTO SAVE ===== */
document.querySelectorAll('.row').forEach(row => {

  const tin=row.querySelector('.time-in');
  const tout=row.querySelector('.time-out');

  function send(){
    const fd=new FormData();
    fd.append('id',tin.dataset.id);
    fd.append('check_in',tin.value);
    fd.append('check_out',tout.value);

    fetch(getWardenUrl('timing'),{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data => {
      if (!data.success) throw new Error('Save failed');
      showToast("Time saved");
    })
    .catch(() => showToast("Could not save time"));
  }

  tin && tin.addEventListener('change',send);
  tout && tout.addEventListener('change',send);
});

/* ===== SEARCH ===== */
document.querySelectorAll('#wardenSearch, .warden-search').forEach(searchInput => {
  const scope = searchInput.closest('.wd-main') || document;
  const rows = Array.from(scope.querySelectorAll('.searchable-row'));
  const tableBox = scope.querySelector('.table-box') || rows[0]?.parentElement;
  let emptyState = scope.querySelector('.wd-search-empty');

  function normalize(value) {
    return (value || '')
      .toString()
      .replace(/[|_-]+/g, ' ')
      .replace(/\s+/g, ' ')
      .trim()
      .toLowerCase();
  }

  function rowText(row) {
    const controlValues = Array.from(row.querySelectorAll('input, select, textarea'))
      .filter(control => control.type !== 'hidden')
      .map(control => {
        if (control.tagName === 'SELECT') {
          return control.options[control.selectedIndex]?.text || control.value;
        }
        return control.value;
      })
      .filter(Boolean)
      .join(' ');

    const searchableText = [
      row.dataset.search,
      row.dataset.roomNumber ? `room ${row.dataset.roomNumber}` : '',
      controlValues,
      row.textContent
    ].filter(Boolean).join(' ');
    return `${searchableText.toLowerCase()} ${normalize(searchableText)}`;
  }

  function setVisible(row, isVisible) {
    row.hidden = !isVisible;
    row.style.display = isVisible ? '' : 'none';
  }

  if (!emptyState && tableBox) {
    emptyState = document.createElement('div');
    emptyState.className = 'wd-search-empty';
    emptyState.textContent = 'No matching records found.';
    emptyState.hidden = true;
    tableBox.after(emptyState);
  }

  function filterRows() {
    const terms = normalize(searchInput.value)
      .split(/\s+/)
      .filter(Boolean);
    const rawTerms = searchInput.value
      .trim()
      .toLowerCase()
      .split(/\s+/)
      .filter(Boolean);
    let visibleCount = 0;

    rows.forEach(row => {
      const haystack = rowText(row);
      const hasNormalizedMatch = terms.length > 0 && terms.every(term => haystack.includes(term));
      const hasRawMatch = rawTerms.length > 0 && rawTerms.every(term => haystack.includes(term));
      const isMatch = terms.length === 0 || hasNormalizedMatch || hasRawMatch;
      setVisible(row, isMatch);
      if (isMatch) visibleCount += 1;
    });

    if (emptyState) {
      emptyState.hidden = terms.length === 0 || visibleCount > 0;
    }
  }

  searchInput.addEventListener('input', filterRows);
  rows.forEach(row => {
    row.querySelectorAll('input, select, textarea').forEach(control => {
      control.addEventListener('change', filterRows);
    });
  });
  filterRows();
});

/* ===== OPEN ROW DETAILS ===== */
function isWardenInteractiveClick(target) {
  return Boolean(target.closest('a, button, input, select, textarea, label, form, .status-pill, [data-no-row-details]'));
}

function escapeDetailHtml(value) {
  return (value || '').toString().replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  })[char]);
}

function getWardenDetailLabel(item, index) {
  const panel = item.closest('.table-box, .wd-notice-list');
  const header = panel?.querySelectorAll('.table-header span')[index];
  if (header) return header.textContent.trim();

  return `Detail ${index + 1}`;
}

function getWardenDetailValue(cell) {
  const control = cell.matches('input, select, textarea')
    ? cell
    : cell.querySelector('input:not([type="hidden"]), select, textarea');

  if (!control) return cell.textContent.trim();
  if (control.tagName === 'SELECT') {
    return control.options[control.selectedIndex]?.text.trim() || control.value.trim();
  }
  return control.value.trim();
}

function getWardenDetailRows(item) {
  const cells = Array.from(item.querySelectorAll(':scope > .cell, :scope > input.cell, :scope > select.cell'));
  if (cells.length > 0) {
    return cells
      .map((cell, index) => ({
        label: getWardenDetailLabel(item, index),
        value: getWardenDetailValue(cell)
      }))
      .filter(row => row.value);
  }

  const title = item.querySelector('h4')?.textContent.trim();
  const description = item.querySelector('p')?.textContent.trim();
  const meta = item.querySelector('.wd-notice-meta')?.textContent.trim();

  return [
    title ? { label: 'Title', value: title } : null,
    description ? { label: 'Description', value: description } : null,
    meta ? { label: 'Date', value: meta } : null
  ].filter(Boolean);
}

function ensureWardenDetailModal() {
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

  modal.addEventListener('click', event => {
    if (event.target.closest('[data-close-details]')) {
      modal.hidden = true;
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') modal.hidden = true;
  });

  return modal;
}

function openWardenDetails(item) {
  const rows = getWardenDetailRows(item);
  if (rows.length === 0) return;

  const modal = ensureWardenDetailModal();
  const content = modal.querySelector('.list-detail-content');
  content.innerHTML = rows.map(row => `
    <div class="list-detail-row">
      <span>${escapeDetailHtml(row.label)}</span>
      <strong>${escapeDetailHtml(row.value)}</strong>
    </div>
  `).join('');
  modal.hidden = false;
  modal.querySelector('.list-detail-close')?.focus();
}

document.querySelectorAll('.wd-main .searchable-row').forEach(row => {
  row.classList.add('can-open-details');
  if (!row.matches('form')) {
    row.setAttribute('tabindex', '0');
  }
});

document.addEventListener('click', event => {
  const item = event.target.closest('.wd-main .searchable-row');
  if (!item || isWardenInteractiveClick(event.target)) return;
  openWardenDetails(item);
});

document.addEventListener('keydown', event => {
  if (!['Enter', ' '].includes(event.key)) return;
  const item = event.target.closest('.wd-main .searchable-row');
  if (!item || isWardenInteractiveClick(event.target)) return;
  event.preventDefault();
  openWardenDetails(item);
});

/* ===== ROOM EDIT HELPERS ===== */
document.querySelectorAll('.rooms-row').forEach(row => {
  const typeSelect = row.querySelector('.room-type-select');
  const secondStudent = row.querySelector('.second-student-select');

  function syncRoomType(){
    if (!typeSelect || !secondStudent) return;
    const isSingle = typeSelect.value === 'single';
    secondStudent.disabled = isSingle;
    if (isSingle) secondStudent.value = '0';
  }

  typeSelect && typeSelect.addEventListener('change', syncRoomType);
  syncRoomType();
});

/* ===== TOAST ===== */
function showToast(msg){
  const t=document.getElementById('toast');
  if (!t) return;
  t.innerText=msg;
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2000);
}

function getWardenUrl(type){
  const baseUrl = window.BASE_URL || '/HostelManagementSystem/';
  const actions = {
    food: 'warden_update_food',
    laundry: 'warden_update_laundry',
    cleaning: 'warden_update_cleaning',
    timing: 'warden_update_timing'
  };

  return baseUrl + 'index.php?action=' + actions[type];
}
