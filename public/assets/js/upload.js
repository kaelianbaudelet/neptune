document.addEventListener("DOMContentLoaded", function () {
  const uploadComponents = document.querySelectorAll("[data-upload-component]");

  uploadComponents.forEach((component) => {
    const input = component.querySelector("[data-upload-input]");
    const text = component.querySelector("[data-upload-text]");
    const defaultText = text.textContent;
    const errorText = component.querySelector("[data-upload-error]") || {
      classList: { add: () => {} },
    }; // Handle if errorText doesn't exist

    const allowedExtensions = input.getAttribute("data-allowed-extensions")
      ? input
          .getAttribute("data-allowed-extensions")
          .split(",")
          .map((ext) => ext.trim().toLowerCase())
      : [".png", ".jpg", ".gif", ".jpeg"];

    const allowedMimeTypes = input.getAttribute("data-allowed-mime-types")
      ? input
          .getAttribute("data-allowed-mime-types")
          .split(",")
          .map((mime) => mime.trim().toLowerCase())
      : ["image/png", "image/jpeg", "image/gif"];

    const maxFileSize = input.getAttribute("data-max-filesize")
      ? parseInt(input.getAttribute("data-max-filesize"))
      : null;

    input.addEventListener("change", function () {
      const file = this.files[0];

      if (file) {
        const fileName = file.name;
        const fileExtension = "." + fileName.split(".").pop().toLowerCase();
        const fileMimeType = file.type.toLowerCase();
        const fileSize = file.size;

        const isValidExtension = allowedExtensions.includes(fileExtension);
        const isValidMimeType = allowedMimeTypes.includes(fileMimeType);

        const isValidSize = maxFileSize ? fileSize <= maxFileSize : true;

        if (isValidExtension && isValidMimeType && isValidSize) {
          text.textContent = fileName;
          errorText.classList.add("hidden");
        } else {
          text.textContent = defaultText;
          this.value = "";

          if (!isValidSize) {
            alert(
              `Fichier trop volumineux. Taille maximale autorisée: ${(
                maxFileSize /
                (1024 * 1024)
              ).toFixed(2)} MB.`
            );
          } else {
            alert("Format de fichier non accepté.");
          }
        }
      } else {
        text.textContent = defaultText;
        errorText.classList.add("hidden");
      }
    });
  });
});
