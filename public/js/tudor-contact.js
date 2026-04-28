(function () {
  // ── Prodotto: salva dati e reindirizza alla pagina contatti ──────────
  document.addEventListener("click", function (e) {
    const trigger = e.target.closest(".tudor-contact-trigger");
    if (!trigger) return;

    e.preventDefault();

    const data = {
      reference: trigger.dataset.reference || "",
      collection: trigger.dataset.collection || "",
      name: trigger.dataset.name || "",
      url: trigger.dataset.url || "",
    };

    sessionStorage.setItem("tudor_contact_watch", JSON.stringify(data));

    window.location.href = trigger.getAttribute("href");
  });

  // ── Pagina contatti: pre-compila il form CF7 dopo 3 secondi ──────────
  function prefillContactForm () {
    const raw = sessionStorage.getItem("tudor_contact_watch");
    if (!raw) return;

    let watch;
    try {
      watch = JSON.parse(raw);
    } catch (err) {
      return;
    }

    const oggettoField = document.querySelector(
      '.tudor-contact__form input[name="oggetto"]',
    );
    const messaggioField = document.querySelector(
      '.tudor-contact__form textarea[name="messaggio"]',
    );

    if (!oggettoField && !messaggioField) return;

    if (oggettoField) {
      const label = [watch.collection, watch.name, watch.reference]
        .filter(Boolean)
        .join(" – ");
      oggettoField.value = "Richiesta informazioni Tudor – " + label;
    }

    if (messaggioField && !messaggioField.value) {
      const lines = [
        "Sono interessato al seguente orologio:",
        "",
        "Collezione: " + watch.collection,
        "Modello: " + watch.name,
        "Referenza: " + watch.reference,
      ];
      if (watch.url) {
        lines.push("Link: " + watch.url);
      }
      messaggioField.value = lines.join("\n");
    }

    sessionStorage.removeItem("tudor_contact_watch");
  }

  document.addEventListener("DOMContentLoaded", function () {
    setTimeout(prefillContactForm, 3000);
  });

  document.addEventListener("wpcf7mailsent", function (event) {
    const wrapper = event.target.closest(".tudor-contact__form");
    if (!wrapper) return;

    const lang = wrapper.dataset.lang || "it";

    const thankYouUrls = {
      it: "/thank-you",
      en: "/en/thank-you",
    };

    setTimeout(() => {
      window.location.href = thankYouUrls[lang] || "/thank-you";
    }, 300);
  });
})();
