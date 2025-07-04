/**
 * Critical JavaScript for MoomsDev Theme
 * 
 * Minimal above-the-fold functionality loaded immediately
 * Keep this file as small as possible (< 5KB)
 * 
 * @version 1.0.0
 */

(function() {
  'use strict';
  
  // Feature detection
  const supports = {
    webp: false,
    lazyLoading: 'loading' in HTMLImageElement.prototype,
    intersectionObserver: 'IntersectionObserver' in window,
    serviceWorker: 'serviceWorker' in navigator
  };
  
  // WebP detection
  function detectWebP() {
    const webP = new Image();
    webP.onload = webP.onerror = function () {
      supports.webp = (webP.height === 2);
      document.documentElement.classList.toggle('webp', supports.webp);
      document.documentElement.classList.toggle('no-webp', !supports.webp);
    };
    webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
  }
  
  // Critical loading optimization
  function optimizeCriticalLoading() {
    // Add loading class to body
    document.body.classList.add('loading');
    
    // Remove loading class when DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.remove('loading');
        document.body.classList.add('loaded');
      });
    } else {
      document.body.classList.remove('loading');
      document.body.classList.add('loaded');
    }
  }
  
  // Critical font loading
  function loadCriticalFonts() {
    // Use font loading API if available
    if ('fonts' in document) {
      Promise.all([
        document.fonts.load('1em "Primary Font"'),
        document.fonts.load('bold 1em "Primary Font"')
      ]).then(function() {
        document.documentElement.classList.add('fonts-loaded');
      });
    }
  }
  
  // Immediate layout shift prevention
  function preventLayoutShift() {
    // Reserve space for common dynamic elements
    const style = document.createElement('style');
    style.textContent = `
      .lazy-image { min-height: 200px; background: #f0f0f0; }
      .skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); }
      .loading .hero { min-height: 60vh; }
    `;
    document.head.appendChild(style);
  }
  
  // Critical navigation functionality (enhanced from theme/index.js)
  function initCriticalNavigation() {
    // Modern selectors to match theme/index.js
    const menuBtn = document.getElementById('btn-hamburger');
    const navMenu = document.querySelector('nav.nav-menu');
    
    // Fallback selectors
    const menuToggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.navigation');
    
    // Use theme/index.js style navigation if elements exist
    if (menuBtn && navMenu) {
      menuBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Toggle menu states
        navMenu.classList.toggle('actived');
        document.body.classList.toggle('overflow-hidden');
        
        // Animate hamburger button (from theme/index.js)
        this.classList.toggle('animeOpenClose');
        
        // Update aria attributes for accessibility
        const isExpanded = navMenu.classList.contains('actived');
        menuBtn.setAttribute('aria-expanded', isExpanded);
      });
    }
    // Fallback to generic navigation
    else if (menuToggle && navigation) {
      menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        navigation.classList.toggle('active');
        menuToggle.classList.toggle('active');
        
        // Update aria attributes
        const isExpanded = navigation.classList.contains('active');
        menuToggle.setAttribute('aria-expanded', isExpanded);
      });
    }
  }
  
  // Critical error handling
  function setupErrorHandling() {
    window.addEventListener('error', function(e) {
      // Log critical errors
      if (typeof gtag !== 'undefined') {
        gtag('event', 'exception', {
          description: e.error?.message || e.message,
          fatal: false
        });
      }
    });
    
    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', function(e) {
      console.warn('Unhandled promise rejection:', e.reason);
    });
  }
  
  // Performance monitoring for Core Web Vitals
  function monitorCoreWebVitals() {
    // Only in production
    if (window.location.hostname === 'localhost') return;
    
    // LCP (Largest Contentful Paint)
    if (supports.intersectionObserver) {
      new PerformanceObserver(function(list) {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        
        // Send to analytics
        if (typeof gtag !== 'undefined') {
          gtag('event', 'lcp', {
            event_category: 'Web Vitals',
            value: Math.round(lastEntry.startTime),
            non_interaction: true
          });
        }
      }).observe({entryTypes: ['largest-contentful-paint']});
    }
  }
  
  // Initialize critical functionality immediately
  function init() {
    // Feature detection first
    detectWebP();
    
    // Critical optimizations
    optimizeCriticalLoading();
    preventLayoutShift();
    
    // Load critical fonts
    loadCriticalFonts();
    
    // Initialize critical navigation
    initCriticalNavigation();
    
    // Setup error handling
    setupErrorHandling();
    
    // Monitor performance
    monitorCoreWebVitals();
    
    // Add feature classes to HTML
    Object.keys(supports).forEach(function(feature) {
      document.documentElement.classList.add(
        supports[feature] ? feature : 'no-' + feature
      );
    });
  }
  
  // Run immediately if DOM is ready, otherwise wait
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
  // Expose critical utilities globally
  window.MoomsCritical = {
    supports: supports,
    version: '1.0.0'
  };
  
})(); 