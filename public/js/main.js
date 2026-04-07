/*
Highlights the active navigation link using JavaScript.
PHP already does this, but this is a backup for special cases.
*/

document.addEventListener("DOMContentLoaded", function () {
  // Get all nav links
  const navLinks = document.querySelectorAll(".nav-links a");

  navLinks.forEach(function (link) {
    // If the link's href matches the current page URL, mark it active
    if (link.href === window.location.href) {
      link.classList.add("active");
    }
  });
});
