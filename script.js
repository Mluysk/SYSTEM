document.addEventListener("DOMContentLoaded", () => {
  const navToggle = document.querySelector(".menu-toggle");
  const navList = document.querySelector("#menu-principal");

  if (navToggle && navList) {
    const closeNav = () => {
      navList.dataset.open = "false";
      navToggle.setAttribute("aria-expanded", "false");
    };

    navToggle.addEventListener("click", () => {
      const isOpen = navList.dataset.open === "true";
      navList.dataset.open = String(!isOpen);
      navToggle.setAttribute("aria-expanded", String(!isOpen));
    });

    navList.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        if (window.innerWidth <= 1024) {
          closeNav();
        }
      });
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth > 1024) {
        navList.dataset.open = "false";
        navToggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  const tryLoadList = (element, urls, applyCallback) => {
    if (!urls.length) {
      element.classList.add("image-missing");
      return;
    }

    let index = 0;

    const tryNext = () => {
      if (index >= urls.length) {
        element.classList.add("image-missing");
        return;
      }

      const candidate = urls[index++].trim();
      if (!candidate) {
        tryNext();
        return;
      }

      const tester = new Image();
      tester.onload = () => {
        applyCallback(candidate);
        element.classList.add("image-loaded");
      };
      tester.onerror = () => {
        tryNext();
      };
      tester.src = candidate;
    };

    tryNext();
  };

  document.querySelectorAll("[data-fallbacks]").forEach((img) => {
    const fallbacks = img.getAttribute("data-fallbacks")?.split(",") ?? [];
    tryLoadList(img, fallbacks, (src) => {
      img.src = src;
      img.dataset.activeSrc = src;
    });
  });

  document.querySelectorAll("[data-backgrounds]").forEach((element) => {
    const fallbacks = element.getAttribute("data-backgrounds")?.split(",") ?? [];
    tryLoadList(element, fallbacks, (src) => {
      element.style.setProperty("--bg-url", `url("${src}")`);
      element.dataset.activeSrc = src;
    });
  });

  const yearElement = document.getElementById("ano-atual");
  if (yearElement) {
    yearElement.textContent = String(new Date().getFullYear());
  }

  const contactForm = document.querySelector(".contact-form");
  if (contactForm) {
    contactForm.addEventListener("submit", (event) => {
      event.preventDefault();
      const submitButton = contactForm.querySelector('button[type="submit"]');
      const feedbackId = "form-feedback";

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "Mensagem enviada";
      }

      let feedback = contactForm.querySelector(`#${feedbackId}`);
      if (!feedback) {
        feedback = document.createElement("p");
        feedback.id = feedbackId;
        feedback.className = "form-feedback";
        feedback.setAttribute("role", "status");
        feedback.setAttribute("aria-live", "polite");
        contactForm.appendChild(feedback);
      }

      feedback.textContent = "Recebemos sua mensagem! Em breve nossa equipe entrará em contato.";

      window.setTimeout(() => {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = "Enviar mensagem";
        }
        if (feedback) {
          feedback.textContent = "Você pode enviar outra mensagem se precisar de mais detalhes.";
        }
      }, 4500);

      contactForm.reset();
    });
  }
});
