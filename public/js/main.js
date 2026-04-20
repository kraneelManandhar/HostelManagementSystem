<<<<<<< HEAD
// simple UI enhancement
document.addEventListener("DOMContentLoaded", () => {
    console.log("Hostel Management System Loaded");

    // highlight active sidebar link
    const links = document.querySelectorAll(".sidebar a");
    links.forEach(link => {
        if (link.href === window.location.href) {
            link.style.background = "#1abc9c";
        }
    });
=======
// simple UI enhancement
document.addEventListener("DOMContentLoaded", () => {
    console.log("Hostel Management System Loaded");

    // highlight active sidebar link
    const links = document.querySelectorAll(".sidebar a");
    links.forEach(link => {
        if (link.href === window.location.href) {
            link.style.background = "#1abc9c";
        }
    });
>>>>>>> a1168b8b45eef63cc27118b6696886423dcefc31
});