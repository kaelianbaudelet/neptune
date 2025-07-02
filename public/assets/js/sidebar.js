document.addEventListener("DOMContentLoaded", function () {
  // Récupération des éléments du DOM
  const sidebar = document.getElementById("sidebar");
  const toggleButtons = document.querySelectorAll('[data-toggle="close"]');
  const openMenuButton = document.querySelector('button[data-toggle="open"]');

  // Fonction pour définir un cookie
  function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie =
      name + "=" + value + ";expires=" + expires.toUTCString() + ";path=/";
  }

  // Fonction pour récupérer un cookie
  function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  // Fonction pour ouvrir ou fermer la sidebar
  function toggleSidebar() {
    const isOpen = sidebar.getAttribute("data-state") === "open";

    // Toggle de la sidebar
    if (isOpen) {
      // On ferme la sidebar
      sidebar.setAttribute("data-state", "closed");
      sidebar.classList.add("w-0", "px-0", "pb-0", "translate-x-[-280px]");
      sidebar.classList.remove(
        "min-w-[280px]",
        "w-[280px]",
        "px-6",
        "pb-6",
        "translate-x-0"
      );
      setCookie("sidebarState", "closed", 30); // Enregistre l'état fermé
    } else {
      // On ouvre la sidebar
      sidebar.setAttribute("data-state", "open");
      sidebar.classList.remove("w-0", "px-0", "pb-0", "translate-x-[-280px]");
      sidebar.classList.add(
        "min-w-[280px]",
        "w-[280px]",
        "px-6",
        "pb-6",
        "translate-x-0"
      );
      setCookie("sidebarState", "open", 30); // Enregistre l'état ouvert
    }
  }

  // Initialisation de l'état de la sidebar en fonction du cookie
  const savedState = getCookie("sidebarState") || "open"; // Par défaut ouvert si pas de cookie
  if (savedState === "closed") {
    sidebar.setAttribute("data-state", "closed");
    sidebar.classList.add("w-0", "px-0", "pb-0", "translate-x-[-280px]");
    sidebar.classList.remove(
      "min-w-[280px]",
      "w-[280px]",
      "px-6",
      "pb-6",
      "translate-x-0"
    );
  } else {
    sidebar.setAttribute("data-state", "open");
    sidebar.classList.remove("w-0", "px-0", "pb-0", "translate-x-[-280px]");
    sidebar.classList.add(
      "min-w-[280px]",
      "w-[280px]",
      "px-6",
      "pb-6",
      "translate-x-0"
    );
  }

  // Quand on clique sur un bouton pour toggle la sidebar on appelle la fonction toggleSidebar
  toggleButtons.forEach((button) => {
    button.addEventListener("click", toggleSidebar);
  });

  // Quand on clique sur le bouton pour ouvrir la sidebar on appelle la fonction toggleSidebar
  if (openMenuButton) {
    openMenuButton.addEventListener("click", toggleSidebar);
  }
});
