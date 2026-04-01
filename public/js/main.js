/**
 * main.js
 * Main JavaScript file for Pentatonic Hostel.
 * Add interactive features here as the project grows.
 */

// =============================================
// Active nav link highlight (fallback for JS)
// The PHP already handles this server-side,
// but this JS version handles edge cases.
// =============================================
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
