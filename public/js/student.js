document.addEventListener("DOMContentLoaded", function () {
  // 1. SIDEBAR NAVIGATION
  const navButtons = document.querySelectorAll(".sd-nav-btn[data-page]");
  const pageTriggers = document.querySelectorAll("[data-page]");
  const pages = document.querySelectorAll(".sd-page");

  pageTriggers.forEach((button) => {
    button.addEventListener("click", function () {
      switchPage(button.dataset.page);
    });
  });

  // 2. MODAL CONTROLS
  const modal = document.getElementById("complaintModal");
  const openBtn = document.getElementById("openComplaintForm");
  const closeBtn = document.getElementById("closeComplaintForm");

  if (openBtn)
    openBtn.addEventListener("click", () => modal.classList.add("open"));
  if (closeBtn) closeBtn.addEventListener("click", closeModal);

  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModal();
    });
  }

  function closeModal() {
    modal.classList.remove("open");
  }

  // 3. SUBMIT COMPLAINT
  const submitBtn = document.getElementById("submitComplaint");

  if (submitBtn) {
    submitBtn.addEventListener("click", async function () {
      const form = document.getElementById("complaintForm");
      const titleInput = form.querySelector('input[name="title"]');
      const issue = titleInput?.value?.trim();

      if (!issue) {
        alert("Please fill in the Issue field.");
        return;
      }

      const formData = new FormData(form);

      try {
        const response = await fetch(
          BASE_URL + "index.php?action=complaint_add",
          { method: "POST", body: formData },
        );
        const result = await response.json();

        if (!result.success) {
          alert(result.message || "Failed to submit complaint");
          return;
        }

        addComplaintToUI(result.data);
        closeModal();
        form.reset();

        // Switch UI to complaints tab
        switchPage("complaints");
      } catch (error) {
        console.error("Error:", error);
        alert("Server error while submitting complaint.");
      }
    });
  }

  // 4. ADD COMPLAINT TO UI
  function addComplaintToUI(data) {
    const list = document.getElementById("sd-complaints-list");
    if (!list) return;

    // Remove "no complaints" placeholder if present
    const empty = list.querySelector("p");
    if (empty) empty.remove();

    const item = document.createElement("div");
    item.className = "sd-complaint-item";

    item.innerHTML = `
      <input type="radio" name="selected-complaint" value="${data.id}">
      <span class="sd-c-title">${escHtml(data.issue)}</span>
      <span class="sd-c-desc">${escHtml(data.description || "—")}</span>
      <span class="sd-c-room">${escHtml(data.room || "—")}</span>
      <span class="sd-badge">Pending</span>
    `;

    list.appendChild(item);
  }

  // 5. DELETE COMPLAINT
  const trashBtn = document.getElementById("deleteComplaint");

  if (trashBtn) {
    trashBtn.addEventListener("click", async function () {
      const selected = document.querySelector(
        ".sd-complaint-item input[type='radio']:checked",
      );

      if (!selected) {
        alert("Please select a complaint first.");
        return;
      }

      const complaintId = selected.value;

      try {
        const res = await fetch(
          BASE_URL + "index.php?action=complaint_delete",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: complaintId }),
          },
        );

        const result = await res.json();

        if (!result.success) {
          alert(result.message || "Delete failed");
          return;
        }

        selected.closest(".sd-complaint-item").remove();
      } catch (err) {
        console.error(err);
        alert("Server error while deleting complaint.");
      }
    });
  }

  // 6. LOGO : switch back to dashboard tab
  const logo = document.getElementById("goDashboard");
  if (logo) {
    logo.addEventListener("click", () => switchPage("dashboard"));
  }

  if (typeof SD_INITIAL_TAB !== "undefined" && SD_INITIAL_TAB) {
    switchPage(SD_INITIAL_TAB);
  }

  // 7. HELPERS
  function switchPage(pageKey) {
    const targetPage = document.getElementById(`page-${pageKey}`);
    if (!targetPage) return;

    navButtons.forEach((btn) => btn.classList.remove("active"));
    pages.forEach((page) => page.classList.remove("active"));

    document
      .querySelector(`.sd-nav-btn[data-page="${pageKey}"]`)
      ?.classList.add("active");
    targetPage.classList.add("active");
  }

  function escHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
});

// 8. ROOM SELECTION MODAL
// Runs only when RS_SHOW === true (student has no room yet).
if (typeof RS_SHOW !== "undefined" && RS_SHOW) {
  const rooms = RS_ROOMS || [];
  const pillList = document.getElementById("rsPillList");
  const roomLabel = document.getElementById("rsRoomLabel");
  const typeLabel = document.getElementById("rsTypeLabel");
  const bedsWrap = document.getElementById("rsBedsWrap");
  const inputRoomId = document.getElementById("rsInputRoomId");
  const inputBedSlot = document.getElementById("rsInputBedSlot");
  const saveBtn = document.getElementById("rsSaveBtn");

  // Track currently selected bed slot for double rooms
  let selectedSlot = "";

  /* SVG helpers */
  function rsBedSVG(occupied, selected) {
    let c;
    if (selected) c = "#e8b84b";
    else if (occupied) c = "#e05252";
    else c = "#222";
    return `<svg viewBox="0 0 120 70" xmlns="http://www.w3.org/2000/svg" class="rs-bed-svg">
      <rect x="5"   y="28" width="110" height="36" rx="4" fill="none" stroke="${c}" stroke-width="5"/>
      <rect x="5"   y="10" width="18"  height="54" rx="3" fill="none" stroke="${c}" stroke-width="5"/>
      <rect x="28"  y="34" width="30"  height="20" rx="4" fill="none" stroke="${c}" stroke-width="3"/>
      <rect x="5"   y="58" width="8"   height="10" rx="2" fill="${c}"/>
      <rect x="108" y="58" width="8"   height="10" rx="2" fill="${c}"/>
    </svg>`;
  }

  function rsStatusLabel(occupied, selected) {
    let cls, text;
    if (selected) {
      cls = "selected";
      text = "SELECTED";
    } else if (occupied) {
      cls = "occupied";
      text = "OCCUPIED";
    } else {
      cls = "empty";
      text = "EMPTY";
    }
    return `<div class="rs-bed-status ${cls}">${text}</div>`;
  }

  /* render beds */
  function rsRenderBeds(room) {
    const s1 = !!parseInt(room.student1_id);
    const s2 = !!parseInt(room.student2_id);

    // Reset selected slot when switching rooms
    selectedSlot = "";

    if (room.type === "single") {
      // Single room: only one bed; auto-select if free
      if (!s1) {
        selectedSlot = "student1";
        inputBedSlot.value = "student1";
      } else {
        inputBedSlot.value = "";
      }

      bedsWrap.innerHTML = `
        <div class="rs-bed-block" data-slot="student1" data-occupied="${s1 ? "1" : "0"}">
          ${rsStatusLabel(s1, !s1)}
          ${rsBedSVG(s1, !s1)}
        </div>`;
    } else {
      // Double room: user must click a free bed to select it
      bedsWrap.innerHTML = `
        <div class="rs-bed-block ${!s1 ? "rs-bed-selectable" : ""}"
             data-slot="student1" data-occupied="${s1 ? "1" : "0"}">
          ${rsStatusLabel(s1, false)}
          ${rsBedSVG(s1, false)}
        </div>
        <div class="rs-bed-block ${!s2 ? "rs-bed-selectable" : ""}"
             data-slot="student2" data-occupied="${s2 ? "1" : "0"}">
          ${rsStatusLabel(s2, false)}
          ${rsBedSVG(s2, false)}
        </div>`;

      inputBedSlot.value = "";

      // Click handler on the beds wrap (event delegation)
      bedsWrap.addEventListener("click", onBedClick);
    }

    updateSaveBtn();
  }

  /* bed click */
  function onBedClick(e) {
    const block = e.target.closest(".rs-bed-block");
    if (!block) return;
    if (block.dataset.occupied === "1") return;

    selectedSlot = block.dataset.slot;
    inputBedSlot.value = selectedSlot;

    // Re-render all bed blocks to reflect selection
    const allBlocks = bedsWrap.querySelectorAll(".rs-bed-block");
    allBlocks.forEach((b) => {
      const occ = b.dataset.occupied === "1";
      const isSel = b.dataset.slot === selectedSlot;
      b.innerHTML = rsStatusLabel(occ, isSel) + rsBedSVG(occ, isSel);
    });

    updateSaveBtn();
  }

  /* save button state */
  function updateSaveBtn() {
    const ok = !!inputBedSlot.value;
    saveBtn.disabled = !ok;
    saveBtn.style.opacity = ok ? "1" : "0.4";
    saveBtn.style.cursor = ok ? "pointer" : "not-allowed";
  }

  /* select room */
  function rsSelectRoom(room) {
    // Remove old bed-click listener before re-rendering
    bedsWrap.replaceWith(bedsWrap.cloneNode(false));
    // Re-grab reference after DOM swap
    const newBedsWrap = document.getElementById("rsBedsWrap");

    roomLabel.textContent = "Room number " + room.number;
    typeLabel.textContent = room.type + " sitter room";
    inputRoomId.value = room.id;

    // Reassign module-level reference
    Object.assign(window, { _rsBedsWrap: newBedsWrap });
    renderBedsIn(newBedsWrap, room);
  }

  function renderBedsIn(wrap, room) {
    const s1 = !!parseInt(room.student1_id);
    const s2 = !!parseInt(room.student2_id);

    selectedSlot = "";
    inputBedSlot.value = "";

    if (room.type === "single") {
      if (!s1) {
        selectedSlot = "student1";
        inputBedSlot.value = "student1";
      }
      wrap.innerHTML = `
        <div class="rs-bed-block" data-slot="student1" data-occupied="${s1 ? "1" : "0"}">
          ${rsStatusLabel(s1, !s1)}
          ${rsBedSVG(s1, !s1)}
        </div>`;
    } else {
      wrap.innerHTML = `
        <div class="rs-bed-block ${!s1 ? "rs-bed-selectable" : ""}"
             data-slot="student1" data-occupied="${s1 ? "1" : "0"}">
          ${rsStatusLabel(s1, false)}
          ${rsBedSVG(s1, false)}
        </div>
        <div class="rs-bed-block ${!s2 ? "rs-bed-selectable" : ""}"
             data-slot="student2" data-occupied="${s2 ? "1" : "0"}">
          ${rsStatusLabel(s2, false)}
          ${rsBedSVG(s2, false)}
        </div>`;

      // Attach fresh listener
      wrap.addEventListener("click", function bedClick(e) {
        const block = e.target.closest(".rs-bed-block");
        if (!block || block.dataset.occupied === "1") return;

        selectedSlot = block.dataset.slot;
        inputBedSlot.value = selectedSlot;

        wrap.querySelectorAll(".rs-bed-block").forEach((b) => {
          const occ = b.dataset.occupied === "1";
          const isSel = b.dataset.slot === selectedSlot;
          b.innerHTML = rsStatusLabel(occ, isSel) + rsBedSVG(occ, isSel);
        });

        updateSaveBtn();
      });
    }

    updateSaveBtn();
  }

  /* init */
  if (rooms.length > 0 && bedsWrap) {
    renderBedsIn(bedsWrap, rooms[0]);
  }

  /* pill clicks */
  if (pillList) {
    pillList.addEventListener("click", function (e) {
      const btn = e.target.closest(".rs-room-pill");
      if (!btn) return;

      document
        .querySelectorAll(".rs-room-pill")
        .forEach((p) => p.classList.remove("active"));
      btn.classList.add("active");

      const room = rooms.find((r) => r.id == btn.dataset.roomId);
      if (!room) return;

      roomLabel.textContent = "Room number " + room.number;
      typeLabel.textContent = room.type + " sitter room";
      inputRoomId.value = room.id;

      const fresh = bedsWrap.cloneNode(false);
      bedsWrap.parentNode.replaceChild(fresh, bedsWrap);

      const liveWrap = document.getElementById("rsBedsWrap");
      renderBedsIn(liveWrap, room);
    });
  }
}
