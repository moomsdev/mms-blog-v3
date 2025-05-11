import "@images/favicon.ico";
import "@styles/theme";
import "airbnb-browser-shims";
import "./pages/*.js";
import 'popper.js';
import 'bootstrap/dist/js/bootstrap.bundle';
import 'bootstrap/scss/bootstrap.scss';
import "./vendor/imagesloaded.pkgd.min";
import "./vendor/jquery.isotope.v3.0.2";
import "./vendor/venobox.min";
import Mmenu from "mmenu-js/src/mmenu";
import GSAP from 'gsap';

// import Swup from 'swup';
import Swiper from 'swiper/swiper-bundle.min';

jQuery(document).ready(function () {
  // const swup = new Swup();
  initializePageFeatures();

  // swup.hooks.on('content:replace', () => {
  //   initializePageFeatures();
  // });
});

function initializePageFeatures() {
  initMmenu();
  initIsotop();
  initSwiperSlider();
  setupHideHeaderOnScroll();
  setupGsap404();
}

function initMmenu() {
  new Mmenu("#mobile_menu", {
    extensions: ["position-bottom", "fullscreen", "theme-black", "border-full"],
    searchfield: false,
    counters: false,
  });
}

function initSwiperSlider() {
  setTimeout(() => {
    new Swiper('.sliders', {
      spaceBetween: 30,
      centeredSlides: true,
      effect: 'fade',
      speed: 1500,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
    });
  }, 500);
}

function initIsotop() {
  $('.menu-wrapper').imagesLoaded(() => {
    const $menuWrapper = $(".menu-wrapper");

    $('.menu-filter li').on('click', function () {
      $(".menu-filter li").removeClass("active");
      $(this).addClass("active");

      $menuWrapper.isotope({
        filter: $(this).attr('data-filter'),
        animationOptions: {
          duration: 750,
          easing: 'linear',
          queue: false,
        }
      });
      return false;
    });

    $menuWrapper.isotope({
      itemSelector: '.loop-food',
      layoutMode: 'masonry',
    });
  });
}

/**
 * hide/show header when scrolling
 */
function setupHideHeaderOnScroll() {
  let lastScrollTop = 0;
  let header = document.getElementById('header');
  let scrollTimeout;

  window.addEventListener('scroll', function () {
    clearTimeout(scrollTimeout);

    let currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (currentScrollTop > lastScrollTop) {
      header.classList.add('hidden');
    } else {
      header.classList.add('hidden');
    }

    lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;

    scrollTimeout = setTimeout(() => {
      header.classList.remove('hidden');
    }, 500);
  });
}

function setupGsap404() {
  GSAP.set("svg", { visibility: "visible" });

  GSAP.to("#spaceman", {
      y: 5, rotation: 2, yoyo: true, repeat: -1, ease: "sine.inOut", duration: 1
  });

  GSAP.to("#starsBig line", {
      rotation: "random(-30,30)", transformOrigin: "50% 50%", yoyo: true, repeat: -1, ease: "sine.inOut"
  });

  GSAP.fromTo("#starsSmall g", { scale: 0 }, { scale: 1, transformOrigin: "50% 50%", yoyo: true, repeat: -1, stagger: 0.1 });

  GSAP.to("#circlesSmall circle", {
      y: -4, yoyo: true, duration: 1, ease: "sine.inOut", repeat: -1
  });

  GSAP.to("#circlesBig circle", {
      y: -2, yoyo: true, duration: 1, ease: "sine.inOut", repeat: -1
  });

  GSAP.set("#glassShine", { x: -68 });
  GSAP.to("#glassShine", {
      x: 80, duration: 2, rotation: -30, ease: "expo.inOut", transformOrigin: "50% 50%", repeat: -1, repeatDelay: 8, delay: 2
  });
}
