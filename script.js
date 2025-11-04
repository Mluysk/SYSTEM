const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let reduceMotion = prefersReducedMotion.matches;

const yearEl = document.getElementById('year');
if (yearEl) {
  yearEl.textContent = new Date().getFullYear();
}

const scrollButton = document.querySelector('.scroll-top');
const toggleScrollButton = () => {
  if (!scrollButton) return;
  if (window.scrollY > 320) {
    scrollButton.classList.add('is-visible');
  } else {
    scrollButton.classList.remove('is-visible');
  }
};

const smoothScrollTo = (element) => {
  element.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
};

scrollButton?.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
});

document.querySelectorAll('.topbar__nav a').forEach((link) => {
  link.addEventListener('click', (event) => {
    const target = event.currentTarget;
    if (target instanceof HTMLAnchorElement && target.hash) {
      const section = document.querySelector(target.hash);
      if (section) {
        event.preventDefault();
        smoothScrollTo(section);
      }
    }
  });
});

window.addEventListener('scroll', toggleScrollButton);
toggleScrollButton();

let startAutoSlide = () => {};
let stopAutoSlide = () => {};

const slider = document.querySelector('.hero__slides');
if (slider) {
  const slides = Array.from(slider.children);
  let activeIndex = 0;
  const prevButton = document.querySelector('.hero__control--prev');
  const nextButton = document.querySelector('.hero__control--next');
  const total = slides.length;

  const updateSlider = () => {
    if (!slides[0]) return;
    const styles = getComputedStyle(slider);
    const isStacked = styles.gridAutoFlow.includes('row');
    if (isStacked) {
      slider.style.transform = 'translateX(0)';
      return;
    }
    const gap = parseFloat(styles.columnGap || '24');
    const offset = -activeIndex * (slides[0].clientWidth + gap);
    slider.style.transform = `translateX(${offset}px)`;
  };

  const goTo = (index) => {
    activeIndex = (index + total) % total;
    updateSlider();
  };

  prevButton?.addEventListener('click', () => goTo(activeIndex - 1));
  nextButton?.addEventListener('click', () => goTo(activeIndex + 1));

  let autoSlideId;
  startAutoSlide = () => {
    if (reduceMotion || autoSlideId) return;
    autoSlideId = window.setInterval(() => {
      goTo(activeIndex + 1);
    }, 5000);
  };

  stopAutoSlide = () => {
    if (!autoSlideId) return;
    window.clearInterval(autoSlideId);
    autoSlideId = undefined;
  };

  slider.addEventListener('pointerenter', stopAutoSlide);
  slider.addEventListener('pointerleave', startAutoSlide);
  prevButton?.addEventListener('pointerenter', stopAutoSlide);
  nextButton?.addEventListener('pointerenter', stopAutoSlide);
  prevButton?.addEventListener('pointerleave', startAutoSlide);
  nextButton?.addEventListener('pointerleave', startAutoSlide);

  if (typeof ResizeObserver === 'function') {
    const resizeObserver = new ResizeObserver(() => updateSlider());
    slides.forEach((slide) => resizeObserver.observe(slide));
  }

  updateSlider();
}

const applyMotionPreference = (matches) => {
  reduceMotion = matches;
  document.documentElement.classList.toggle('reduced-motion', matches);
  if (matches) {
    stopAutoSlide();
  } else {
    startAutoSlide();
  }
};

applyMotionPreference(reduceMotion);
if (typeof prefersReducedMotion.addEventListener === 'function') {
  prefersReducedMotion.addEventListener('change', (event) => applyMotionPreference(event.matches));
} else if (typeof prefersReducedMotion.addListener === 'function') {
  prefersReducedMotion.addListener((event) => applyMotionPreference(event.matches));
}

startAutoSlide();
