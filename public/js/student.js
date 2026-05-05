document.addEventListener("DOMContentLoaded", function () {
  // 1. SIDEBAR NAVIGATION
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

  // 2. MODAL CONTROLS
  const modal = document.getElementById("complaintModal");
  const openBtn = document.getElementById("openComplaintForm");
  const closeBtn = document.getElementById("closeComplaintForm");

  if (openBtn) {
    openBtn.addEventListener("click", () => modal.classList.add("open"));
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", closeModal);
  }

  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModal();
    });
  }

  function closeModal() {
    modal.classList.remove("open");
  }

  // 3. SUBMIT COMPLAINT
  // FIX: reference the form inside the modal by id, not document.querySelector("form")
  const submitBtn = document.getElementById("submitComplaint");

  if (submitBtn) {
    submitBtn.addEventListener("click", async function () {
      const form = document.getElementById("complaintForm");

      // Read values directly from inputs (more reliable than FormData on hidden modals)
      const titleInput = form.querySelector('input[name="title"]');
      const issue = titleInput?.value?.trim();

      if (!issue) {
        alert("Please fill in the Issue field.");
        return;
      }

      const formData = new FormData(form);

      try {
        const response = await fetch(
          "/HostelManagementSystem/index.php?action=complaint_add",
          { method: "POST", body: formData },
        );
        const result = await response.json();

        if (!result.success) {
          alert(result.message || "Failed to submit complaint");
          return;
        }

        addComplaintToUI(result.data);
        modal.classList.remove("open");
        form.reset();

        navButtons.forEach((btn) => btn.classList.remove("active"));
        pages.forEach((page) => page.classList.remove("active"));
        document
          .querySelector('.sd-nav-btn[data-page="complaints"]')
          ?.classList.add("active");
        document.getElementById("page-complaints")?.classList.add("active");
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

    // Remove "no complaints" message if present
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
          "/HostelManagementSystem/index.php?action=complaint_delete",
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

  // 6. HTML ESCAPE
  function escHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  // 7. LOGO NAVIGATION
  // Kept here as fallback for the JS-only click
  const logo = document.getElementById("goDashboard");
  if (logo) {
    logo.addEventListener("click", () => {
      navButtons.forEach((btn) => btn.classList.remove("active"));
      pages.forEach((page) => page.classList.remove("active"));
      document
        .querySelector('.sd-nav-btn[data-page="dashboard"]')
        ?.classList.add("active");
      document.getElementById("page-dashboard")?.classList.add("active");
    });
  }
});
