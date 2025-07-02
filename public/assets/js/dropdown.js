document.addEventListener("DOMContentLoaded", function () {
  // Sélectionner tous les boutons de déclenchement de dropdown
  const menuButtons = document.querySelectorAll("[data-dropdown-trigger]");

  // Fonction pour fermer tous les dropdowns
  function closeAllDropdowns() {
    const openButtons = document.querySelectorAll(
      '[data-dropdown-trigger][aria-expanded="true"]'
    );

    openButtons.forEach((btn) => {
      const targetId = btn.getAttribute("data-target");
      const dropdown = document.querySelector(`[data-dropdown="${targetId}"]`);

      if (dropdown) {
        btn.setAttribute("aria-expanded", "false");
        dropdown.classList.remove("opacity-100", "visible", "translate-y-0");
        dropdown.classList.add("opacity-0", "invisible", "translate-y-2");
      }
    });
  }

  menuButtons.forEach((button) => {
    const targetId = button.getAttribute("data-target");
    const dropdown = document.querySelector(`[data-dropdown="${targetId}"]`);

    if (!dropdown) return;

    button.addEventListener("click", function (event) {
      event.stopPropagation();
      const expanded = button.getAttribute("aria-expanded") === "true";

      if (!expanded) {
        // Fermer tous les autres dropdowns avant d'ouvrir celui-ci
        closeAllDropdowns();

        // Ouvrir ce dropdown
        button.setAttribute("aria-expanded", "true");
        dropdown.classList.remove("opacity-0", "invisible", "translate-y-2");
        dropdown.classList.add("opacity-100", "visible", "translate-y-0");
      } else {
        // Fermer ce dropdown
        button.setAttribute("aria-expanded", "false");
        dropdown.classList.remove("opacity-100", "visible", "translate-y-0");
        dropdown.classList.add("opacity-0", "invisible", "translate-y-2");
      }
    });
  });

  // Fermer les dropdowns quand on clique ailleurs
  document.addEventListener("click", function (event) {
    closeAllDropdowns();
  });
});
