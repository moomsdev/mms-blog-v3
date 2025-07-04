/**
 * Main JavaScript for MoomsDev Theme
 *
 * Non-critical functionality loaded after page load
 *
 * @version 1.0.0
 */

// Styles imported via separate CSS entry (main-css) for clean naming

// Wait for critical scripts to load
document.addEventListener("DOMContentLoaded", function () {
  // Initialize theme
  initTheme();
  // Setup lazy loading
  setupLazyLoading();
  // Initialize forms
  initForms();
  // Setup smooth scrolling
  setupSmoothScrolling();
  // Initialize dark mode toggle
  initToggleDarkMode();
  // Setup header scroll behavior
  setupHideHeaderOnScroll();
  // Initialize page-specific features
  initPageSpecificFeatures();
});

/**
 * Initialize main theme functionality
 */
function initTheme() {
  // Add main theme initialization
  console.log("MoomsDev Main JS loaded");

  // Add theme version info
  if (window.MoomsCritical) {
    console.log("Theme version:", window.MoomsCritical.version);
  }
}

/**
 * Setup lazy loading for images without native support
 */
function setupLazyLoading() {
  if (
    !window.MoomsCritical?.supports?.lazyLoading &&
    window.MoomsCritical?.supports?.intersectionObserver
  ) {
    const lazyImages = document.querySelectorAll("img[data-src]");

    const imageObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.remove("lazy");
          img.classList.add("loaded");
          imageObserver.unobserve(img);
        }
      });
    });

    lazyImages.forEach((img) => imageObserver.observe(img));
  }
}

/**
 * Initialize form functionality
 */
function initForms() {
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    // Add basic form enhancements
    form.addEventListener("submit", function (e) {
      const submitButton = form.querySelector('[type="submit"]');
      if (submitButton) {
        submitButton.classList.add("loading");
        submitButton.disabled = true;
      }
    });
  });
}

/**
 * Setup smooth scrolling for anchor links
 */
function setupSmoothScrolling() {
  const anchorLinks = document.querySelectorAll('a[href^="#"]');

  anchorLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href");
      const target = document.querySelector(targetId);

      if (target) {
        e.preventDefault();
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });
}

/**
 * Initialize dark/light mode toggle (enhanced with debugging)
 */
function initToggleDarkMode() {
  const toggleInput = document.querySelector(".darkmode-icon input");
  const rootElement = document.documentElement;
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

  // Set initial theme based on system preference or saved preference
  const savedTheme = localStorage.getItem("theme");
  const initialTheme = savedTheme || (prefersDark ? "dark" : "light");
  rootElement.setAttribute("data-theme", initialTheme);
  if (toggleInput) {
    toggleInput.checked = initialTheme === "dark";
  }

  // Handle theme toggle
  if (toggleInput) {
    toggleInput.addEventListener("change", (event) => {
      const isDark = event.target.checked;
      const newTheme = isDark ? "dark" : "light";

      if (document.startViewTransition) {
        document.startViewTransition(() => {
          rootElement.setAttribute("data-theme", newTheme);
          localStorage.setItem("theme", newTheme);
        });
      } else {
        rootElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);
      }
    });
  }

  // Listen for system theme changes
  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", (e) => {
      if (!localStorage.getItem("theme")) {
        const newTheme = e.matches ? "dark" : "light";
        rootElement.setAttribute("data-theme", newTheme);
        if (toggleInput) {
          toggleInput.checked = e.matches;
        }
      }
    });
}

/**
 * Hide/show header when scrolling (from theme/index.js)
 */
function setupHideHeaderOnScroll() {
  let lastScrollTop = 0;
  let header = document.getElementById("header");
  let scrollTimeout;

  if (!header) return;

  window.addEventListener("scroll", function () {
    clearTimeout(scrollTimeout);

    let currentScrollTop =
      window.pageYOffset || document.documentElement.scrollTop;

    if (currentScrollTop > lastScrollTop && currentScrollTop > 100) {
      // Scrolling down & past threshold
      header.classList.add("header-hidden");
    } else {
      // Scrolling up
      header.classList.remove("header-hidden");
    }

    lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;

    // Show header after scroll stops
    scrollTimeout = setTimeout(() => {
      header.classList.remove("header-hidden");
    }, 500);
  });
}

/**
 * Initialize page-specific features
 */
function initPageSpecificFeatures() {
  // Initialize Swiper sliders if present
  const swiperContainers = document.querySelectorAll(
    ".swiper-container, .sliders"
  );
  if (swiperContainers.length > 0) {
    initSwiperSliders();
  }

  // Initialize 404 animations if on 404 page
  if (document.body.classList.contains("error404")) {
    init404Animations();
  }

  // Initialize text animations
  const animatedTexts = document.querySelectorAll(".slogan p, .animate-text");
  if (animatedTexts.length > 0) {
    initTextAnimations();
  }
}

/**
 * Initialize Swiper sliders using vendor.js lazy loading
 */
function initSwiperSliders() {
  if (window.MoomsVendor?.loadSwiper) {
    window.MoomsVendor.loadSwiper().then(() => {
      // Initialize all Swiper instances
      document.querySelectorAll(".sliders").forEach((container) => {
        new window.Swiper(container, {
          spaceBetween: 30,
          centeredSlides: true,
          effect: "fade",
          speed: 1500,
          autoplay: {
            delay: 5000,
            disableOnInteraction: false,
          },
        });
      });
    });
  }
}

/**
 * Initialize 404 page animations using vendor.js lazy loading
 */
function init404Animations() {
  if (window.MoomsVendor?.loadGSAP) {
    window.MoomsVendor.loadGSAP().then((gsap) => {
      // 404 animations from theme/index.js
      gsap.set("svg", { visibility: "visible" });

      gsap.to("#spaceman", {
        y: 5,
        rotation: 2,
        yoyo: true,
        repeat: -1,
        ease: "sine.inOut",
        duration: 1,
      });

      gsap.to("#starsBig line", {
        rotation: "random(-30,30)",
        transformOrigin: "50% 50%",
        yoyo: true,
        repeat: -1,
        ease: "sine.inOut",
      });

      gsap.fromTo(
        "#starsSmall g",
        { scale: 0 },
        {
          scale: 1,
          transformOrigin: "50% 50%",
          yoyo: true,
          repeat: -1,
          stagger: 0.1,
        }
      );

      gsap.to("#circlesSmall circle", {
        y: -4,
        yoyo: true,
        duration: 1,
        ease: "sine.inOut",
        repeat: -1,
      });

      gsap.to("#circlesBig circle", {
        y: -2,
        yoyo: true,
        duration: 1,
        ease: "sine.inOut",
        repeat: -1,
      });

      gsap.set("#glassShine", { x: -68 });
      gsap.to("#glassShine", {
        x: 80,
        duration: 2,
        rotation: -30,
        ease: "expo.inOut",
        transformOrigin: "50% 50%",
        repeat: -1,
        repeatDelay: 8,
        delay: 2,
      });
    });
  }
}

/**
 * Initialize text animations using vendor.js lazy loading
 */
function initTextAnimations() {
  if (window.MoomsVendor?.loadGSAP) {
    window.MoomsVendor.loadGSAP().then((gsap) => {
      // Load SplitText as additional module
      import("gsap/SplitText").then(({ SplitText }) => {
        const animatedTexts = document.querySelectorAll(
          ".slogan p, .animate-text"
        );

        animatedTexts.forEach((element) => {
          const splitto = new SplitText(element, {
            type: "lines, chars",
            linesClass: "anim_line",
            charsClass: "anim_char",
            wordsClass: "anim_word",
          });

          const chars = element.querySelectorAll(".anim_char");

          gsap.fromTo(
            chars,
            { y: "100%" },
            {
              y: "0%",
              duration: 0.8,
              stagger: 0.01,
              ease: "power2.out",
            }
          );
        });
      });
    });
  }
}

// Export for global access
window.MoomsMain = {
  version: "1.0.0",
  initTheme,
  setupLazyLoading,
  initForms,
  setupSmoothScrolling,
  initToggleDarkMode,
  setupHideHeaderOnScroll,
  initSwiperSliders,
  init404Animations,
  initTextAnimations,
};
