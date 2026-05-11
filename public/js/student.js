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

  // 3. SUBMIT COMPLAINT
  const submitBtn = document.getElementById("submitComplaint");
  if (submitBtn) {
    submitBtn.addEventListener("click", async function () {
      const form = document.getElementById("complaintForm");
      const titleInput = form.querySelector('input[name="title"]');
      const descriptionInput = form.querySelector(
        'textarea[name="description"]',
      );
      const issue = titleInput?.value?.trim();
      const description = descriptionInput?.value?.trim();

      if (!issue) {
        showStudentToast("Please fill in the Issue field.", "error");
        titleInput?.focus();
        return;
      }

      if (!description) {
        showStudentToast("Please fill in the Description field.", "error");
        descriptionInput?.focus();
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
          showStudentToast(
            result.message || "Failed to submit complaint.",
            "error",
          );
          return;
        }

        addComplaintToUI(result.data);
        closeModal();
        form.reset();

        // Switch UI to complaints tab
        switchPage("complaints");
        showStudentToast("Complaint submitted successfully.", "success");
      } catch (error) {
        console.error("Error:", error);
        showStudentToast("Server error while submitting complaint.", "error");
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
        showStudentToast("Please select a complaint first.", "error");
        return;
      }

      const confirmed = await showStudentConfirm({
        title: "Are you sure you want to delete the complaint?",
        confirmText: "Delete",
      });
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
          showStudentToast(result.message || "Delete failed.", "error");
          return;
        }

        selected.closest(".sd-complaint-item").remove();
        showStudentToast("Complaint deleted successfully.", "success");
      } catch (err) {
        console.error(err);
        showStudentToast("Server error while deleting complaint.", "error");
      }
    });
  }

  // 6. PROFILE FORM VALIDATION
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

  // 7. TIMING FORM
  const timingForm = document.getElementById("studentTimingForm");
  if (timingForm) {
    timingForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const checkInInput = timingForm.querySelector('input[name="check_in"]');
      const checkOutInput = timingForm.querySelector('input[name="check_out"]');
      const checkIn = checkInInput?.value || "";
      const checkOut = checkOutInput?.value || "";

      if (!checkIn && !checkOut) {
        showStudentToast("Please enter at least one timing.", "error");
        checkInInput?.focus();
        return;
      }

      if (checkIn && checkOut && new Date(checkIn) < new Date(checkOut)) {
        showStudentToast("Check in cannot be before check out.", "error");
        checkInInput?.focus();
        return;
      }

      const formData = new FormData(timingForm);

      try {
        const response = await fetch(
          BASE_URL + "index.php?action=student_timing_update",
          { method: "POST", body: formData },
        );
        const result = await response.json();

        if (!result.success) {
          showStudentToast(result.message || "Could not save timing.", "error");
          return;
        }

        updateTimingSummary(result.data || {});
        showStudentToast(result.message || "Timing saved successfully.", "success");
      } catch (error) {
        console.error("Error:", error);
        showStudentToast("Server error while saving timing.", "error");
      }
    });
  }

  function updateTimingSummary(data) {
    const status = document.getElementById("studentTimingStatus");
    const inText = document.getElementById("studentTimingInText");
    const outText = document.getElementById("studentTimingOutText");

    if (status && data.status) {
      status.textContent = data.status;
      status.classList.toggle("is-out", data.status === "OUT");
      status.classList.toggle("is-in", data.status !== "OUT");
    }

    if (inText) inText.textContent = formatTiming(data.check_in);
    if (outText) outText.textContent = formatTiming(data.check_out);
  }

  function formatTiming(value) {
    if (!value) return "Not set";

    const date = new Date(String(value).replace(" ", "T"));
    if (Number.isNaN(date.getTime())) return "Not set";

    return date.toLocaleString(undefined, {
      month: "long",
      day: "numeric",
      year: "numeric",
      hour: "numeric",
      minute: "2-digit",
    });
  }

  // 8. LOGO : switch back to dashboard tab
  const logo = document.getElementById("goDashboard");
  if (logo) {
    logo.addEventListener("click", () => switchPage("dashboard"));
  }

  if (typeof SD_INITIAL_TAB !== "undefined" && SD_INITIAL_TAB) {
    switchPage(SD_INITIAL_TAB);
  }

  if (typeof SD_FLASH !== "undefined" && SD_FLASH?.message) {
    showStudentToast(SD_FLASH.message, SD_FLASH.type || "success");
  }

  // 9. HELPERS
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
