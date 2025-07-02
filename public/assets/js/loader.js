window.addEventListener("load", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const modalParam = urlParams.get("modal");

  if (modalParam) {
    document.getElementById("loader").classList.add("hidden");
  } else {
    setTimeout(function () {
      document.getElementById("loader").classList.add("hidden");
    }, 300);
  }
});
