document.addEventListener("DOMContentLoaded", function () {
  // SIDEBAR NAVIGATION
  const navButtons = document.querySelectorAll(".sd-nav-btn[data-page]");
  const pages = document.querySelectorAll(".sd-page");

  navButtons.forEach((button) => {
    button.addEventListener("click", function () {
      navButtons.forEach((btn) => btn.classList.remove("active"));
      pages.forEach((page) => page.classList.remove("active"));
      button.classList.add("active");
      const targetPage = document.getElementById("page-" + button.dataset.page);
      if (targetPage) targetPage.classList.add("active");
    });
  });

  // COMPLAINT MODAL CONTROLS
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

  // SUBMIT COMPLAINT
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
          {
            method: "POST",
            body: formData,
          },
        );
        const result = await response.json();

        if (!result.success) {
          alert(result.message || "Failed to submit complaint.");
          return;
        }

        addComplaintToUI(result.data);
        closeModal();
        form.reset();
        switchPage("complaints");
        alert("Your complaint has been submitted successfully.");
      } catch (error) {
        console.error("Error:", error);
        alert("Server error while submitting complaint.");
      }
    });
  }

  // ADD COMPLAINT TO UI
  function addComplaintToUI(data) {
    const list = document.getElementById("sd-complaints-list");
    if (!list) return;
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

  // DELETE COMPLAINT
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

      const confirmed = confirm(
        "Are you sure you want to delete this complaint?",
      );
      if (!confirmed) return;

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
          alert(result.message || "Delete failed.");
          return;
        }

        selected.closest(".sd-complaint-item").remove();
        alert("Complaint deleted successfully.");
      } catch (err) {
        console.error(err);
        alert("Server error while deleting complaint.");
      }
    });
  }

  // LOGO: switch back to dashboard tab
  const logo = document.getElementById("goDashboard");
  if (logo) {
    logo.addEventListener("click", () => switchPage("dashboard"));
  }

  function switchPage(pageKey) {
    navButtons.forEach((btn) => btn.classList.remove("active"));
    pages.forEach((page) => page.classList.remove("active"));
    document
      .querySelector(`.sd-nav-btn[data-page="${pageKey}"]`)
      ?.classList.add("active");
    document.getElementById(`page-${pageKey}`)?.classList.add("active");
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

// ROOM SELECTION MODAL
if (typeof RS_SHOW !== "undefined" && RS_SHOW) {
  const rooms = RS_ROOMS || [];

  function getRefs() {
    return {
      pillList: document.getElementById("rsPillList"),
      roomLabel: document.getElementById("rsRoomLabel"),
      typeLabel: document.getElementById("rsTypeLabel"),
      bedsWrap: document.getElementById("rsBedsWrap"),
      inputRoomId: document.getElementById("rsInputRoomId"),
      inputBedSlot: document.getElementById("rsInputBedSlot"),
      saveBtn: document.getElementById("rsSaveBtn"),
    };
  }

  function bedSVG(occupied, selected) {
    let c = selected ? "#000000" : occupied ? "#e05252" : "#222";
    return `<svg viewBox="0 0 120 70" xmlns="http://www.w3.org/2000/svg" class="rs-bed-svg">
      <rect x="5"   y="28" width="110" height="36" rx="4" fill="none" stroke="${c}" stroke-width="5"/>
      <rect x="5"   y="10" width="18"  height="54" rx="3" fill="none" stroke="${c}" stroke-width="5"/>
      <rect x="28"  y="34" width="30"  height="20" rx="4" fill="none" stroke="${c}" stroke-width="3"/>
      <rect x="5"   y="58" width="8"   height="10" rx="2" fill="${c}"/>
      <rect x="108" y="58" width="8"   height="10" rx="2" fill="${c}"/>
    </svg>`;
  }

  function statusLabel(occupied, selected) {
    if (selected) return `<div class="rs-bed-status selected">SELECTED</div>`;
    if (occupied) return `<div class="rs-bed-status occupied">OCCUPIED</div>`;
    return `<div class="rs-bed-status empty">EMPTY</div>`;
  }

  function updateSaveBtn(inputBedSlot, saveBtn) {
    const ok = !!inputBedSlot.value;
    saveBtn.disabled = !ok;
    saveBtn.style.opacity = ok ? "1" : "0.4";
    saveBtn.style.cursor = ok ? "pointer" : "not-allowed";
  }

  function renderBeds(wrap, room, inputBedSlot, saveBtn) {
    const s1 = !!parseInt(room.student1_id);
    const s2 = !!parseInt(room.student2_id);

    inputBedSlot.value = "";

    if (room.type === "single") {
      // Auto-select if free
      const selected = !s1;
      if (selected) inputBedSlot.value = "student1";

      wrap.innerHTML = `
        <div class="rs-bed-block" data-slot="student1" data-occupied="${s1 ? "1" : "0"}">
          ${statusLabel(s1, selected)}
          ${bedSVG(s1, selected)}
        </div>`;
    } else {
      wrap.innerHTML = `
        <div class="rs-bed-block ${!s1 ? "rs-bed-selectable" : ""}" data-slot="student1" data-occupied="${s1 ? "1" : "0"}">
          ${statusLabel(s1, false)}
          ${bedSVG(s1, false)}
        </div>
        <div class="rs-bed-block ${!s2 ? "rs-bed-selectable" : ""}" data-slot="student2" data-occupied="${s2 ? "1" : "0"}">
          ${statusLabel(s2, false)}
          ${bedSVG(s2, false)}
        </div>`;

      wrap.addEventListener("click", function (e) {
        const block = e.target.closest(".rs-bed-block");
        if (!block || block.dataset.occupied === "1") return;

        inputBedSlot.value = block.dataset.slot;

        wrap.querySelectorAll(".rs-bed-block").forEach((b) => {
          const occ = b.dataset.occupied === "1";
          const isSel = b.dataset.slot === block.dataset.slot;
          b.innerHTML = statusLabel(occ, isSel) + bedSVG(occ, isSel);
        });

        updateSaveBtn(inputBedSlot, saveBtn);
      });
    }

    updateSaveBtn(inputBedSlot, saveBtn);
  }

  function selectRoom(room) {
    const refs = getRefs();
    refs.roomLabel.textContent = "Room number " + room.number;
    refs.typeLabel.textContent = room.type + " sitter room";
    refs.inputRoomId.value = room.id;

    // Replace bedsWrap to clear old listeners
    const fresh = refs.bedsWrap.cloneNode(false);
    refs.bedsWrap.parentNode.replaceChild(fresh, refs.bedsWrap);

    renderBeds(fresh, room, refs.inputBedSlot, refs.saveBtn);
  }

  // Init first room
  if (rooms.length > 0) {
    const refs = getRefs();
    if (refs.bedsWrap)
      renderBeds(refs.bedsWrap, rooms[0], refs.inputBedSlot, refs.saveBtn);
  }

  // Pill clicks
  const pillList = document.getElementById("rsPillList");
  if (pillList) {
    pillList.addEventListener("click", function (e) {
      const btn = e.target.closest(".rs-room-pill");
      if (!btn) return;
      document
        .querySelectorAll(".rs-room-pill")
        .forEach((p) => p.classList.remove("active"));
      btn.classList.add("active");
      const room = rooms.find((r) => r.id == btn.dataset.roomId);
      if (room) selectRoom(room);
    });
  }

  // SAVE BUTTON — submit via fetch, show success alert, redirect
  const saveBtn = document.getElementById("rsSaveBtn");
  if (saveBtn) {
    saveBtn.addEventListener("click", async function () {
      const refs = getRefs();
      const roomId = refs.inputRoomId.value;
      const bedSlot = refs.inputBedSlot.value;

      if (!roomId || !bedSlot) return;

      saveBtn.disabled = true;
      saveBtn.style.opacity = "0.6";

      try {
        const formData = new FormData();
        formData.append("room_id", roomId);
        formData.append("bed_slot", bedSlot);

        const res = await fetch(RS_SAVE_URL, {
          method: "POST",
          body: formData,
        });
        const result = await res.json();

        if (!result.success) {
          alert(result.message || "Could not save room. Please try again.");
          saveBtn.disabled = false;
          saveBtn.style.opacity = "1";
          return;
        }

        // Show success alert then redirect
        const alert = document.getElementById("rsSuccessAlert");
        const msg = document.getElementById("rsSuccessMsg");
        if (msg)
          msg.textContent = result.message || "Your room has been selected.";
        if (alert) alert.classList.add("visible");

        setTimeout(() => {
          window.location.href =
            result.redirect_url ||
            BASE_URL + "index.php?action=student_dashboard";
        }, 3000);
      } catch (err) {
        console.error(err);
        alert("Server error. Please try again.");
        saveBtn.disabled = false;
        saveBtn.style.opacity = "1";
      }
    });
  }
}
