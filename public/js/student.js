// Handles: page navigation, complaint form modal,
// dynamic complaint adding, complaint deletion

document.addEventListener("DOMContentLoaded", function () {
  // 1. SIDEBAR NAVIGATION
  const navButtons = document.querySelectorAll(".sd-nav-btn[data-page]");
  const pages = document.querySelectorAll(".sd-page");

  navButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      navButtons.forEach(function (btn) {
        btn.classList.remove("active");
      });
      pages.forEach(function (page) {
        page.classList.remove("active");
      });

      button.classList.add("active");

      const targetId = "page-" + button.dataset.page;
      const targetPage = document.getElementById(targetId);
      if (targetPage) {
        targetPage.classList.add("active");
      }
    });
  });

  // 2. COMPLAINT FORM MODAL
  const modal = document.getElementById("complaintModal");
  const openBtn = document.getElementById("openComplaintForm");
  const closeBtn = document.getElementById("closeComplaintForm");
  const submitBtn = document.getElementById("submitComplaint");

  if (openBtn) {
    openBtn.addEventListener("click", function () {
      modal.classList.add("open");
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      closeModal();
    });
  }

  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        closeModal();
      }
    });
  }

  function closeModal() {
    modal.classList.remove("open");
    var issueInput = document.getElementById("inputIssue");
    var descInput = document.getElementById("inputDesc");
    if (issueInput) issueInput.value = "";
    if (descInput) descInput.value = "";
  }

  // 3. SUBMIT COMPLAINT: appends a new row to the grid list
  if (submitBtn) {
    submitBtn.addEventListener("click", function () {
      var issue = document.getElementById("inputIssue").value.trim();
      var desc = document.getElementById("inputDesc").value.trim();
      var room = document.getElementById("inputRoom").value.trim();

      if (issue === "") {
        alert("Please fill in the Issue field.");
        return;
      }

      var list = document.getElementById("sd-complaints-list");
      if (list) {
        var item = document.createElement("div");
        item.className = "sd-complaint-item";
        item.innerHTML =
          '<input type="radio" name="selected-complaint">' +
          '<span class="sd-c-title">' +
          escHtml(issue) +
          "</span>" +
          '<span class="sd-c-desc">' +
          escHtml(desc || "—") +
          "</span>" +
          '<span class="sd-c-room">' +
          escHtml(room) +
          "</span>" +
          '<span class="sd-badge badge-pending">Pending</span>';
        list.appendChild(item);
      }

      closeModal();
    });
  }

  // 4. DELETE SELECTED COMPLAINT
  var trashBtn = document.getElementById("deleteComplaint");

  if (trashBtn) {
    trashBtn.addEventListener("click", function () {
      var selected = document.querySelector(
        ".sd-complaint-item input[type='radio']:checked",
      );

      if (!selected) {
        alert("Please select a complaint first.");
        return;
      }

      selected.closest(".sd-complaint-item").remove();
    });
  }

  // 5. HELPER: escape HTML to prevent XSS
  function escHtml(str) {
    return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  // 6. LOGO CLICK: GO TO DASHBOARD
  const logo = document.getElementById("goDashboard");

  if (logo) {
    logo.addEventListener("click", function () {
      // Remove active from all nav buttons
      navButtons.forEach(function (btn) {
        btn.classList.remove("active");
      });

      // Remove active from all pages
      pages.forEach(function (page) {
        page.classList.remove("active");
      });

      // Activate dashboard button
      const dashboardBtn = document.querySelector(
        '.sd-nav-btn[data-page="dashboard"]',
      );
      if (dashboardBtn) {
        dashboardBtn.classList.add("active");
      }

      // Show dashboard page
      const dashboardPage = document.getElementById("page-dashboard");
      if (dashboardPage) {
        dashboardPage.classList.add("active");
      }
    });
  }
});
