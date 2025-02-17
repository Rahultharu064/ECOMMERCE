lucide.createIcons();
      // Logout functionality
      document
        .getElementById("logout")
        .addEventListener("click", function (event) {
          event.preventDefault(); // Prevent the default link behavior
          const confirmLogout = confirm("Are you sure you want to logout?");
          if (confirmLogout) {
            // Perform logout actions (e.g., clear session, redirect to login page)
            alert("Logging out...");
            window.location.href = "/login"; // Redirect to login page
          }
        });