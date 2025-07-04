/**
 * Vendor JavaScript for MoomsDev Theme
 * 
 * Third-party libraries and dependencies
 * This file is loaded separately for better caching
 * 
 * @version 1.0.0
 */

// Import core dependencies
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import utility libraries
import jQuery from 'jquery';

// Import animation libraries (conditionally)
if (window.innerWidth > 768) {
  import('aos').then(AOS => {
    AOS.default.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  });
}

// Import GSAP for advanced animations (lazy loaded)
let gsapLoaded = false;
function loadGSAP() {
  if (gsapLoaded) return Promise.resolve();
  
  gsapLoaded = true;
  return import('gsap').then(gsap => {
    window.gsap = gsap.default;
    return gsap.default;
  });
}

// Import Swiper for carousels (lazy loaded)
let swiperLoaded = false;
function loadSwiper() {
  if (swiperLoaded) return Promise.resolve();
  
  swiperLoaded = true;
  return import('swiper').then(Swiper => {
    window.Swiper = Swiper.default;
    return Swiper.default;
  });
}

// Import SweetAlert2 for modals (lazy loaded)
let sweetAlertLoaded = false;
function loadSweetAlert() {
  if (sweetAlertLoaded) return Promise.resolve();
  
  sweetAlertLoaded = true;
  return import('sweetalert2').then(Swal => {
    window.Swal = Swal.default;
    return Swal.default;
  });
}

// jQuery validation (lazy loaded)
let validationLoaded = false;
function loadValidation() {
  if (validationLoaded) return Promise.resolve();
  
  validationLoaded = true;
  return import('jquery-validation').then(() => {
    return jQuery.validator;
  });
}

// Setup global jQuery (if needed for compatibility)
window.$ = window.jQuery = jQuery;

// Export lazy loaders for main theme
window.MoomsVendor = {
  loadGSAP,
  loadSwiper,
  loadSweetAlert,
  loadValidation,
  version: '1.0.0'
};

// Preload critical third-party resources
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const element = entry.target;
      
      // Load Swiper when carousel is visible
      if (element.classList.contains('swiper-container')) {
        loadSwiper().then(() => {
          initializeSwiper(element);
        });
      }
      
      // Load validation when form is visible
      if (element.tagName === 'FORM') {
        loadValidation();
      }
      
      observer.unobserve(element);
    }
  });
});

// Auto-observe elements that need lazy loading
document.addEventListener('DOMContentLoaded', () => {
  // Observe carousels
  document.querySelectorAll('.swiper-container').forEach(el => {
    observer.observe(el);
  });
  
  // Observe forms
  document.querySelectorAll('form').forEach(el => {
    observer.observe(el);
  });
});

// Initialize Swiper instance
function initializeSwiper(container) {
  if (!window.Swiper) return;
  
  const config = {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev'
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true
    },
    breakpoints: {
      768: {
        slidesPerView: 2
      },
      1024: {
        slidesPerView: 3
      }
    }
  };
  
  return new window.Swiper(container, config);
} 