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

  // 2. COMPLAINT MODAL CONTROLS
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

  // 3. COMPLAINT FORM VALIDATION
  const complaintForm = document.getElementById("complaintForm");
  if (complaintForm) {
    complaintForm.addEventListener("submit", function (e) {
      const titleInput = complaintForm.querySelector('input[name="title"]');
      const descriptionInput = complaintForm.querySelector(
        'textarea[name="description"]',
      );
      const issue = titleInput?.value?.trim();
      const description = descriptionInput?.value?.trim();

      if (!issue) {
        e.preventDefault();
        showStudentToast("Please fill in the Issue field.", "error");
        titleInput?.focus();
        return;
      }

      if (!description) {
        e.preventDefault();
        showStudentToast("Please fill in the Description field.", "error");
        descriptionInput?.focus();
      }
    });
  }

  // 4. DELETE COMPLAINT CONFIRMATION
  const deleteComplaintForm = document.getElementById("deleteComplaintForm");
  if (deleteComplaintForm) {
    deleteComplaintForm.addEventListener("submit", async function (e) {
      const selected = document.querySelector(
        ".sd-complaint-item input[name='complaint_id']:checked",
      );

      if (!selected) {
        e.preventDefault();
        showStudentToast("Please select a complaint first.", "error");
        return;
      }

      e.preventDefault();
      const confirmed = await showStudentConfirm({
        title: "Are you sure you want to delete the complaint?",
        confirmText: "Delete",
      });
      if (!confirmed) return;

      deleteComplaintForm.submit();
    });
  }

  // 5. PROFILE FORM VALIDATION
  const profileForm = document.getElementById("studentProfileForm");
  if (profileForm) {
    profileForm.addEventListener("submit", function (e) {
      const firstName = profileForm
        .querySelector('input[name="first_name"]')
        ?.value.trim();
      const lastName = profileForm
        .querySelector('input[name="last_name"]')
        ?.value.trim();
      const contact = profileForm
        .querySelector('input[name="contact_number"]')
        ?.value.trim();
      const guardianContact = profileForm
        .querySelector('input[name="guardian_contact"]')
        ?.value.trim();
      const photo = profileForm.querySelector('input[name="profile_photo"]');

      if (!firstName || !lastName) {
        e.preventDefault();
        showStudentToast("First name and last name are required.", "error");
        return;
      }

      if (!/^\d{10}$/.test(contact) || !/^\d{10}$/.test(guardianContact)) {
        e.preventDefault();
        showStudentToast("Contact numbers must be exactly 10 digits.", "error");
        return;
      }

      if (photo?.files?.length) {
        const allowedTypes = ["image/jpeg", "image/png", "image/webp"];
        if (!allowedTypes.includes(photo.files[0].type)) {
          e.preventDefault();
          showStudentToast("Please upload a JPG, PNG, or WEBP image.", "error");
        }
      }
    });
  }

  // 6. TIMING FORM VALIDATION
  const timingForm = document.getElementById("studentTimingForm");
  if (timingForm) {
    timingForm.addEventListener("submit", function (e) {
      const checkInInput = timingForm.querySelector('input[name="check_in"]');
      const checkOutInput = timingForm.querySelector('input[name="check_out"]');
      const checkIn = checkInInput?.value || "";
      const checkOut = checkOutInput?.value || "";

      if (!checkIn && !checkOut) {
        e.preventDefault();
        showStudentToast("Please enter at least one timing.", "error");
        checkInInput?.focus();
      }
    });
  }

  if (typeof SD_INITIAL_TAB !== "undefined" && SD_INITIAL_TAB) {
    switchPage(SD_INITIAL_TAB);
    clearInitialTabFromUrl();
  }

  if (typeof SD_FLASH !== "undefined" && SD_FLASH?.message) {
    showStudentToast(SD_FLASH.message, SD_FLASH.type || "success");
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

  function clearInitialTabFromUrl() {
    if (!window.history?.replaceState) return;

    const url = new URL(window.location.href);
    if (!url.searchParams.has("tab")) return;

    url.searchParams.delete("tab");
    window.history.replaceState({}, "", url.toString());
  }

  function showStudentToast(message, type) {
    const toast = document.getElementById("sdToast");
    if (!toast) return;

    toast.textContent = message;
    toast.classList.remove(
      "sd-toast-success",
      "sd-toast-error",
      "sd-toast-show",
    );
    toast.classList.add(
      type === "error" ? "sd-toast-error" : "sd-toast-success",
    );

    window.setTimeout(() => toast.classList.add("sd-toast-show"), 30);
    window.setTimeout(() => toast.classList.remove("sd-toast-show"), 3200);
  }

  function showStudentConfirm({ title, message, confirmText }) {
    const dialog = document.getElementById("studentConfirmDialog");
    const titleEl = document.getElementById("studentConfirmTitle");
    const messageEl = document.getElementById("studentConfirmMessage");
    const cancelBtn = document.getElementById("studentConfirmCancel");
    const okBtn = document.getElementById("studentConfirmOk");

    if (!dialog || !titleEl || !messageEl || !cancelBtn || !okBtn) {
      return Promise.resolve(false);
    }

    titleEl.textContent = title || "Are you sure?";
    if (message) {
      messageEl.textContent = message;
      messageEl.hidden = false;
    } else {
      messageEl.textContent = "";
      messageEl.hidden = true;
    }
    okBtn.textContent = confirmText || "Confirm";
    dialog.classList.add("open");
    dialog.setAttribute("aria-hidden", "false");
    okBtn.focus();

    return new Promise((resolve) => {
      function close(result) {
        dialog.classList.remove("open");
        dialog.setAttribute("aria-hidden", "true");
        cancelBtn.removeEventListener("click", onCancel);
        okBtn.removeEventListener("click", onOk);
        dialog.removeEventListener("click", onBackdrop);
        document.removeEventListener("keydown", onKeydown);
        resolve(result);
      }

      function onCancel() {
        close(false);
      }

      function onOk() {
        close(true);
      }

      function onBackdrop(e) {
        if (e.target === dialog) close(false);
      }

      function onKeydown(e) {
        if (e.key === "Escape") close(false);
      }

      cancelBtn.addEventListener("click", onCancel);
      okBtn.addEventListener("click", onOk);
      dialog.addEventListener("click", onBackdrop);
      document.addEventListener("keydown", onKeydown);
    });
  }
});

// 8. ROOM SELECTION MODAL
// Runs only when RS_SHOW === true (student has no room yet).
if (typeof RS_SHOW !== "undefined" && RS_SHOW) {
  const rooms = RS_ROOMS || [];
  const overlay = document.getElementById("roomSelectionModal");
  const dashboardWrap = document.querySelector(".sd-page-wrap");
  const pillList = document.getElementById("rsPillList");
  const roomLabel = document.getElementById("rsRoomLabel");
  const typeLabel = document.getElementById("rsTypeLabel");
  let bedsWrap = document.getElementById("rsBedsWrap");
  const inputRoomId = document.getElementById("rsInputRoomId");
  const inputBedSlot = document.getElementById("rsInputBedSlot");
  const saveBtn = document.getElementById("rsSaveBtn");

  overlay?.classList.add("open");
  dashboardWrap?.classList.add("rs-blurred");

  // Track currently selected bed slot for double rooms
  let selectedSlot = "";

  /* SVG helpers */
  function rsBedSVG(occupied, selected) {
    let c;
    if (selected) c = "#f0b429";
    else if (occupied) c = "#e05252";
    else c = "#18a558";
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
    if (occupied) {
      cls = "occupied";
      text = "Occupied";
    } else if (selected) {
      cls = "selected";
      text = "Selected";
    } else {
      cls = "empty";
      text = "Available";
    }
    return `<div class="rs-bed-status ${cls}">${text}</div>`;
  }

  function rsChoiceLabel(occupied, selected) {
    const text = occupied
      ? "Not available"
      : selected
        ? "Your choice"
        : "Click to choose";
    return `<div class="rs-bed-choice">${text}</div>`;
  }

  function rsBedBlock(slot, occupied, selected) {
    const classes = ["rs-bed-block"];
    if (occupied) classes.push("is-occupied");
    if (!occupied) classes.push("rs-bed-selectable");
    if (selected) classes.push("selected");

    return `
      <div class="${classes.join(" ")}" data-slot="${slot}" data-occupied="${occupied ? "1" : "0"}">
        ${rsStatusLabel(occupied, selected)}
        ${rsBedSVG(occupied, selected)}
        ${rsChoiceLabel(occupied, selected)}
      </div>`;
  }

  /* save button state */
  function updateSaveBtn() {
    if (!saveBtn) return;
    const ok = !!inputBedSlot.value;
    saveBtn.disabled = !ok;
  }

  function renderBedsIn(wrap, room) {
    if (!wrap || !room) return;
    const s1 = !!parseInt(room.student1_id);
    const s2 = !!parseInt(room.student2_id);

    selectedSlot = "";
    inputBedSlot.value = "";

    if (room.type === "single") {
      if (!s1) {
        selectedSlot = "student1";
        inputBedSlot.value = "student1";
      }
      wrap.innerHTML = rsBedBlock("student1", s1, !s1);
    } else {
      wrap.innerHTML = `
        ${rsBedBlock("student1", s1, false)}
        ${rsBedBlock("student2", s2, false)}`;

      // Attach fresh listener
      wrap.addEventListener("click", function bedClick(e) {
        const block = e.target.closest(".rs-bed-block");
        if (!block || block.dataset.occupied === "1") return;

        selectedSlot = block.dataset.slot;
        inputBedSlot.value = selectedSlot;

        wrap.querySelectorAll(".rs-bed-block").forEach((b) => {
          const occ = b.dataset.occupied === "1";
          const isSel = b.dataset.slot === selectedSlot;
          b.classList.toggle("selected", isSel);
          b.innerHTML =
            rsStatusLabel(occ, isSel) +
            rsBedSVG(occ, isSel) +
            rsChoiceLabel(occ, isSel);
        });

        updateSaveBtn();
      });
    }

    updateSaveBtn();
  }

  /* init */
  if (rooms.length > 0 && bedsWrap) {
    const initialRoom =
      rooms.find(
        (room) =>
          typeof RS_INITIAL_ROOM_ID !== "undefined" &&
          room.id == RS_INITIAL_ROOM_ID,
      ) || rooms[0];
    if (roomLabel) roomLabel.textContent = "Room number " + initialRoom.number;
    if (typeLabel) typeLabel.textContent = initialRoom.type + " sitter room";
    if (inputRoomId) inputRoomId.value = initialRoom.id;
    renderBedsIn(bedsWrap, initialRoom);
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

      bedsWrap = document.getElementById("rsBedsWrap");
      renderBedsIn(bedsWrap, room);
    });
  }
}
