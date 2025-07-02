document.addEventListener("DOMContentLoaded", function () {
  const openButtons = document.querySelectorAll(".open-modal");
  const closeButtons = document.querySelectorAll(".close-modal");
  const modals = document.querySelectorAll(".modal");

  function lockScroll() {
    document.body.style.overflow = "hidden";
  }

  function unlockScroll() {
    document.body.style.overflow = "";
  }

  function openModal(modalTarget, instant = false) {
    const modal = document.querySelector(`[data-modal="${modalTarget}"]`);
    if (!modal) return;

    modal.classList.remove("hidden");

    const backdrop = modal.querySelector(".modal-backdrop");
    const content = modal.querySelector(".modal-content");

    if (instant) {
      backdrop.classList.add("opacity-100");
      content.classList.add("opacity-100", "scale-100");
      content.classList.remove("scale-95");
      lockScroll();

      const firstFocusable = modal.querySelector(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );
      if (firstFocusable) {
        firstFocusable.focus();
      }
    } else {
      setTimeout(() => {
        backdrop.classList.add("opacity-100");
        content.classList.add("opacity-100", "scale-100");
        content.classList.remove("scale-95");
        lockScroll();
      }, 10);

      setTimeout(() => {
        const firstFocusable = modal.querySelector(
          'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        if (firstFocusable) {
          firstFocusable.focus();
        }
      }, 300);
    }
  }

  function closeModal(modal) {
    if (!modal) return;

    const backdrop = modal.querySelector(".modal-backdrop");
    const content = modal.querySelector(".modal-content");

    backdrop.classList.remove("opacity-100");
    content.classList.remove("opacity-100", "scale-100");
    content.classList.add("scale-95");

    setTimeout(() => {
      modal.classList.add("hidden");
      unlockScroll();
    }, 300);
  }

  openButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const modalTarget = button.dataset.modalTarget;
      openModal(modalTarget);
    });
  });

  closeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const modal = button.closest(".modal");
      closeModal(modal);
    });
  });

  document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
    backdrop.addEventListener("click", (e) => {
      if (e.target === backdrop) {
        const modal = backdrop.closest(".modal");
        closeModal(modal);
      }
    });
  });

  document.querySelectorAll(".modal-close").forEach((closeButton) => {
    closeButton.addEventListener("click", (e) => {
      const modal = closeButton.closest(".modal");
      closeModal(modal);
    });
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      modals.forEach((modal) => {
        if (!modal.classList.contains("hidden")) {
          closeModal(modal);
        }
      });
    }
  });

  document
    .querySelectorAll(".carousel-prev, .carousel-next, .carousel-dot")
    .forEach((navButton) => {
      navButton.addEventListener("click", (e) => {
        e.preventDefault();
        const targetSlide = navButton.dataset.target;
        const modal = navButton.closest(".modal");
        const slide = modal.querySelector(`[data-slide="${targetSlide}"]`);

        if (slide) {
          slide.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
      });
    });

  const urlParams = new URLSearchParams(window.location.search);
  const modalParam = urlParams.get("modal");
  if (modalParam) {
    openModal(modalParam, true);
  }
});
