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
      controlValues
    ].filter(Boolean).join(' ');
    return normalize(searchableText || row.textContent);
  }

  if (!emptyState && tableBox) {
    emptyState = document.createElement('div');
    emptyState.className = 'wd-search-empty';
    emptyState.textContent = 'No matching records found.';
    emptyState.hidden = true;
    tableBox.after(emptyState);
  }

  function filterRows() {
    const terms = searchInput.value
      .trim()
      .toLowerCase()
      .split(/\s+/)
      .filter(Boolean);
    let visibleCount = 0;

    rows.forEach(row => {
      const haystack = rowText(row);
      const isMatch = terms.length === 0 || terms.every(term => haystack.includes(term));
      row.hidden = !isMatch;
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
