document.addEventListener("DOMContentLoaded", function () {
  // Récupère les éléments du DOM
  const arrivalDate = document.getElementById("arrival-date");
  const departureDate = document.getElementById("departure-date");
  const numberOfGuests = document.getElementById("number-of-guests");
  const sortBy = document.getElementById("sort-by");
  const sortOrder = document.getElementById("sort-order");
  const minPrice = document.getElementById("min-price");
  const maxPrice = document.getElementById("max-price");
  const typeFilters = document.querySelectorAll(".room-type-filter");
  const equipmentFilters = document.querySelectorAll(".equipment-filter");

  // Ajoute des écouteurs d'événements pour chaque élément
  arrivalDate.addEventListener("change", performSearch);
  departureDate.addEventListener("change", performSearch);
  numberOfGuests.addEventListener("change", performSearch);
  sortBy.addEventListener("change", performSearch);
  sortOrder.addEventListener("change", performSearch);
  minPrice.addEventListener("input", debounce(performSearch, 500));
  maxPrice.addEventListener("input", debounce(performSearch, 500));

  typeFilters.forEach((filter) => {
    filter.addEventListener("change", performSearch);
  });

  equipmentFilters.forEach((filter) => {
    filter.addEventListener("change", performSearch);
  });

  // Recherche initiale
  performSearch();

  // Fonction pour obtenir les paramètres de recherche
  function getSearchParams() {
    const params = new FormData();

    // Paramètres de recherche
    params.append("arrival_date", arrivalDate.value || "");
    params.append("departure_date", departureDate.value || "");
    params.append("number_of_guests", numberOfGuests.value || "1");

    // Tri
    params.append("sort_by", sortBy.value || "price");
    params.append("sort_order", sortOrder.value || "asc");

    // Prix
    if (minPrice.value) params.append("min_price", minPrice.value);
    if (maxPrice.value) params.append("max_price", maxPrice.value);

    // Type de chambre - peut sélectionner plusieurs
    typeFilters.forEach((filter) => {
      if (filter.checked) {
        params.append("type_ids[]", filter.value);
      }
    });

    // Équipements - peut sélectionner plusieurs
    equipmentFilters.forEach((filter) => {
      if (filter.checked) {
        params.append("equipment_ids[]", filter.value);
      }
    });

    return params;
  }

  // Fonction pour effectuer la recherche
  function performSearch() {
    const loadingIndicator = document.getElementById("loading-indicator");
    const roomsContainer = document.getElementById("rooms-container");
    const noResults = document.getElementById("no-results");
    const resultsCount = document.getElementById("results-count");

    // Afficher l'indicateur de chargement
    loadingIndicator.classList.remove("hidden");
    roomsContainer.innerHTML = "";
    noResults.classList.add("hidden");

    // Enregistrement du temps de début
    const startTime = Date.now();

    // Obtenir les paramètres de recherche
    const params = getSearchParams();

    // Requête de recherche vers /search en POST
    fetch("/search", {
      method: "POST",
      body: params,
    })
      .then((response) => response.json())
      .then((data) => {
        // Calculer le temps écoulé pour l'affichage de l'indicateur de chargement
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, 200 - elapsedTime);

        // Simuler un temps de chargement minimum
        setTimeout(() => {
          // Masquer l'indicateur de chargement
          loadingIndicator.classList.add("hidden");

          // Mettre à jour le nombre de résultats
          resultsCount.textContent = `${data.length} chambre${
            data.length !== 1 ? "s" : ""
          } trouvée${data.length !== 1 ? "s" : ""}`;

          if (data.length === 0) {
            // Aucun résultat trouvé
            noResults.classList.remove("hidden");
          } else {
            // Afficher les résultats
            data.forEach((room) => {
              roomsContainer.appendChild(createRoomCard(room));
            });
          }
        }, remainingTime);
      })
      .catch((error) => {
        console.error("Error fetching search results:", error);

        // Masquer l'indicateur de chargement
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, 200 - elapsedTime);

        setTimeout(() => {
          loadingIndicator.classList.add("hidden");
          roomsContainer.innerHTML =
            '<p class="text-red-500">Une erreur est survenue lors de la recherche. Veuillez réessayer.</p>';
        }, remainingTime);
      });
  }

  // Fonction pour créer une carte de chambre
  function createRoomCard(room) {
    const card = document.createElement("a");
    card.href = `/rooms/${room.id}?arrival_date=${arrivalDate.value}&departure_date=${departureDate.value}&number_of_guests=${numberOfGuests.value}`;
    card.className =
      "group flex flex-col h-full bg-white border border-gray-200 rounded-xl hover:ring-2 hover:ring-primary overflow-hidden";

    // Récupérer l'image principale de la chambre
    const primaryImage = room.images.length > 0 ? room.images[0] : null;
    const imageUrl = primaryImage
      ? `/assets/upload/${primaryImage.fileKey}.${primaryImage.extension}`
      : "/assets/images/placeholder.jpg";

    let hasDiscount = room.discount > 0;
    let finalPrice = hasDiscount
      ? room.price * (1 - room.discount / 100)
      : room.price;

    card.innerHTML = `
      <div class="h-44 flex flex-col justify-center items-center bg-gray-50 overflow-hidden relative">
        <img class="object-cover w-full min-h-32 h-full" src="${imageUrl}" alt="${
      room.name
    }"/>
        ${
          hasDiscount
            ? `
          <div class="min-h-8 w-full bg-primary text-white text-xs font-medium justify-between items-center flex px-4">
            Remise
            <span class="text-xs font-semibold ml-2">${room.discount}%</span>
          </div>
        `
            : ""
        }
      </div>
      <div class="p-4">
        <span class="block mb-1 text-xs uppercase font-medium text-gray-500">
          ${room.type.name}
        </span>
        <span class="block mb-1 text-md font-medium text-primary">
          ${room.name}
        </span>
        <div class="mt-3 flex flex-wrap gap-3">
          <div class="flex items-center gap-1.5 text-xs font-medium">
            ${room.bedDouble} lit${room.bedDouble !== 1 ? "s" : ""} double${
      room.bedDouble !== 1 ? "s" : ""
    }
            <svg fill="#000000" class="w-4 h-4 flex-shrink-0" viewbox="0 0 56 56" xmlns="http://www.w3.org/2000/svg">
              <path d="M 5.2892 21.9935 L 10.9031 21.9935 L 10.9031 18.8154 C 10.9031 16.7507 12.0630 15.6372 14.1508 15.6372 L 22.3861 15.6372 C 24.4739 15.6372 25.6338 16.7507 25.6338 18.8154 L 25.6338 21.9935 L 30.6446 21.9935 L 30.6446 18.8154 C 30.6446 16.7507 31.8045 15.6372 34.0084 15.6372 L 41.7333 15.6372 C 43.9373 15.6372 45.0970 16.7507 45.0970 18.8154 L 45.0970 21.9935 L 50.7108 21.9935 L 50.7108 15.6604 C 50.7108 11.5544 48.5305 9.4665 44.5402 9.4665 L 11.4598 9.4665 C 7.4930 9.4665 5.2892 11.5544 5.2892 15.6604 Z M 0 44.8668 C 0 46.0035 .7423 46.7226 1.9022 46.7226 L 3.2013 46.7226 C 4.3380 46.7226 5.0803 46.0035 5.0803 44.8668 L 5.0803 41.5726 C 5.3355 41.6422 6.0779 41.6886 6.6114 41.6886 L 49.4118 41.6886 C 49.9454 41.6886 50.6647 41.6422 50.9198 41.5726 L 50.9198 44.8668 C 50.9198 46.0035 51.6619 46.7226 52.7988 46.7226 L 54.1210 46.7226 C 55.2579 46.7226 56 46.0035 56 44.8668 L 56 31.6670 C 56 27.4682 53.6573 25.1716 49.4118 25.1716 L 6.5883 25.1716 C 2.3430 25.1716 0 27.4682 0 31.6670 Z"/>
            </svg>
          </div>
          <div class="flex items-center gap-1.5 text-xs font-medium">
            ${room.bedSingle} lit${room.bedSingle !== 1 ? "s" : ""} simple${
      room.bedSingle !== 1 ? "s" : ""
    }

            <svg fill="#000000" class="w-4 h-4 flex-shrink-0" viewBox="0 -64 640 640" xmlns="http://www.w3.org/2000/svg"><path d="M176 256c44.11 0 80-35.89 80-80s-35.89-80-80-80-80 35.89-80 80 35.89 80 80 80zm352-128H304c-8.84 0-16 7.16-16 16v144H64V80c0-8.84-7.16-16-16-16H16C7.16 64 0 71.16 0 80v352c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16v-48h512v48c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16V240c0-61.86-50.14-112-112-112z"/></svg>
          </div>
          <div class="flex items-center gap-1.5 text-xs font-medium">
            ${room.capacity} personne${room.capacity !== 1 ? "s" : ""}
            <svg class="h-4 w-4 flex-shrink-0" fill="#000000" width="800px" height="800px" viewBox="0 0 56 56" xmlns="http://www.w3.org/2000/svg">
							<path d="M 38.7232 28.5490 C 43.1399 28.5490 46.9403 24.6047 46.9403 19.4690 C 46.9403 14.3949 43.1193 10.6356 38.7232 10.6356 C 34.3271 10.6356 30.5061 14.4771 30.5061 19.5101 C 30.5061 24.6047 34.3066 28.5490 38.7232 28.5490 Z M 15.0784 29.0215 C 18.8994 29.0215 22.2274 25.5703 22.2274 21.1125 C 22.2274 16.6958 18.8789 13.4294 15.0784 13.4294 C 11.2575 13.4294 7.8885 16.7779 7.9090 21.1536 C 7.9090 25.5703 11.2370 29.0215 15.0784 29.0215 Z M 3.6155 47.5717 L 19.2281 47.5717 C 17.0917 44.4697 19.7006 38.2247 24.1173 34.8146 C 21.8371 33.2944 18.8994 32.1645 15.0579 32.1645 C 5.7931 32.1645 0 39.0053 0 44.6957 C 0 46.5445 1.0271 47.5717 3.6155 47.5717 Z M 25.8018 47.5717 L 51.6241 47.5717 C 54.8493 47.5717 56 46.6472 56 44.8395 C 56 39.5394 49.3644 32.2261 38.7026 32.2261 C 28.0616 32.2261 21.4262 39.5394 21.4262 44.8395 C 21.4262 46.6472 22.5766 47.5717 25.8018 47.5717 Z"></path>
						</svg>
          </div>
        </div>
        <p class="mt-3 text-gray-500 line-clamp-3 text-xs">
          ${room.description}
        </p>
        <div class="flex mt-3 items-center gap-1.5">
          ${
            hasDiscount
              ? `
            <p class="text-xl font-semibold line-through decoration-2">${
              room.price
            } €</p>
            <p class="text-xl font-semibold text-primary">${finalPrice.toFixed(
              2
            )} €</p>
          `
              : `
            <p class="text-xl font-semibold text-primary">${finalPrice.toFixed(
              2
            )} €</p>
          `
          }
          <span class="text-gray-500 text-sm">/ nuit</span>
        </div>
      </div>
    `;

    return card;
  }

  // Fonction de debounce pour limiter la fréquence d'exécution
  function debounce(func, wait) {
    let timeout;
    return function () {
      const context = this;
      const args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }
});
