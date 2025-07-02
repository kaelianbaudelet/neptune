let JScoupon = null;
const messageDiv = document.getElementById("coupon-message");
const discountSpan = document.getElementById("js-coupon-discount");
document
  .getElementById("apply-coupon-btn")
  .addEventListener("click", async function () {
    const code = document.getElementById("coupon").value.trim();
    let msg = "",
      color = "text-green-600";
    if (!code) {
      msg = "Veuillez entrer un code de réduction.";
      color = "text-red-500";
      JScoupon = null;
      discountSpan.textContent = "0";
    } else {
      const formData = new URLSearchParams();
      formData.append("coupon_code", code);
      try {
        const response = await fetch(window.location.href, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: formData.toString(),
        });
        const data = await response.json();
        if (data.status === "success") {
          JScoupon = {
            code,
            discount: data.discount,
          };
          discountSpan.textContent = Number(data.discount);
          msg = data.message;
          color = "text-green-600";
        } else {
          JScoupon = null;
          discountSpan.textContent = "0";
          msg = data.message || "Le code de réduction est invalide.";
          color = "text-red-500";
        }
      } catch (e) {
        JScoupon = null;
        discountSpan.textContent = "0";
        msg = "Erreur lors de la vérification du code.";
        color = "text-red-500";
      }
    }
    messageDiv.textContent = msg;
    messageDiv.className = "text-sm mt-2 " + color;
    messageDiv.style.display = msg ? "block" : "none";
  });
// Toujours cacher le message si vide au chargement
document.addEventListener("DOMContentLoaded", function () {
  messageDiv.textContent = "";
  messageDiv.style.display = "none";
});
