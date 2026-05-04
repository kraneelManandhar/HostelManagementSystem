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
const searchInput = document.getElementById('wardenSearch');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim().toLowerCase();

    document.querySelectorAll('.searchable-row').forEach(row => {
      const haystack = (row.dataset.search || row.textContent || '').toLowerCase();
      row.hidden = query !== '' && !haystack.includes(query);
    });
  });
}

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
