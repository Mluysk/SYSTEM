const navList = document.getElementById("nav-list");
const menuToggle = document.querySelector(".menu-toggle");
const accordion = document.querySelector('[data-accordion]');
const yearSpan = document.getElementById("year");

if (yearSpan) {
  yearSpan.textContent = new Date().getFullYear();
}

if (menuToggle && navList) {
  menuToggle.addEventListener("click", () => {
    const expanded = menuToggle.getAttribute("aria-expanded") === "true";
    menuToggle.setAttribute("aria-expanded", String(!expanded));
    navList.dataset.open = String(!expanded);
  });

  navList.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      menuToggle.setAttribute("aria-expanded", "false");
      navList.dataset.open = "false";
    });
  });
}

if (accordion) {
  const triggers = accordion.querySelectorAll(".accordion-trigger");

  triggers.forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const isExpanded = trigger.getAttribute("aria-expanded") === "true";

      triggers.forEach((other) => {
        const panel = other.nextElementSibling;
        if (other === trigger) {
          other.setAttribute("aria-expanded", String(!isExpanded));
          panel.hidden = isExpanded;
        } else {
          other.setAttribute("aria-expanded", "false");
          if (panel) panel.hidden = true;
        }
      });
    });
  });
}

const form = document.querySelector(".contact-form");

if (form) {
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(form);
    const name = data.get("nome");
    const eventType = data.get("evento");

    const message = [
      `Obrigada(o), ${name || "cliente"}!`,
      "Recebemos sua mensagem.",
      eventType ? `Tipo de evento: ${eventType}.` : null,
      "Nossa equipe retornará em até 1 hora útil."
    ]
      .filter(Boolean)
      .join("\n");

    alert(message);
    form.reset();
  });
}
