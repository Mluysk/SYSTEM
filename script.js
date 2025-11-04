const bodyElement = document.body;
const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
let shouldReduceMotion = motionQuery.matches;

const setMotionPreference = (matches) => {
  shouldReduceMotion = matches;
  document.documentElement.classList.toggle('reduced-motion', matches);
  if (!bodyElement) return;
  if (matches) {
    bodyElement.classList.remove('animations-ready');
  } else {
    bodyElement.classList.add('animations-ready');
  }
};

setMotionPreference(shouldReduceMotion);

const handleMotionChange = (event) => setMotionPreference(event.matches);
if (typeof motionQuery.addEventListener === 'function') {
  motionQuery.addEventListener('change', handleMotionChange);
} else if (typeof motionQuery.addListener === 'function') {
  motionQuery.addListener(handleMotionChange);
}

const yearSpan = document.getElementById('year');
if (yearSpan) {
  yearSpan.textContent = new Date().getFullYear();
}

const scrollButton = document.querySelector('.scroll-top');
const toggleScrollButton = () => {
  if (!scrollButton) return;
  if (window.scrollY > 400) {
    scrollButton.classList.add('visible');
  } else {
    scrollButton.classList.remove('visible');
  }
};

window.addEventListener('scroll', toggleScrollButton);
toggleScrollButton();
scrollButton?.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: shouldReduceMotion ? 'auto' : 'smooth' });
});

document.querySelectorAll('nav a, .hero__cta .btn-glass').forEach((link) => {
  link.addEventListener('click', (event) => {
    const target = event.currentTarget;
    if (target instanceof HTMLAnchorElement && target.hash) {
      event.preventDefault();
      const section = document.querySelector(target.hash);
      section?.scrollIntoView({ behavior: shouldReduceMotion ? 'auto' : 'smooth', block: 'start' });
    }
  });
});

if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    },
    { threshold: 0.2 }
  );

  document.querySelectorAll('.card, .service-card, .case-card, .insight-card').forEach((element) => {
    observer.observe(element);
  });
} else if (bodyElement) {
  bodyElement.classList.remove('animations-ready');
}
