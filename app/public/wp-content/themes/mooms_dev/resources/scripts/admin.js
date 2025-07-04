/**
 * Admin JavaScript for MoomsDev Theme
 *
 * WordPress admin area enhancements
 *
 * @version 1.0.0
 */

// Styles imported via separate CSS entry (admin-css) for clean naming

jQuery(document).ready(function ($) {
  // Initialize admin functionality
  initAdmin();

  // Setup media uploader enhancements
  setupMediaUploader();

  // Add theme options helpers
  setupThemeOptions();
});

/**
 * Initialize admin functionality
 */
function initAdmin() {
  // Debug info removed for production
  // console.log('MoomsDev Admin loaded');

  // Add admin body class
  document.body.classList.add("mooms-admin");

  // Show theme info (debug version)
  // showThemeInfo();
}

/**
 * Setup media uploader enhancements
 */
function setupMediaUploader() {
  // Add WebP notice
  if (window.wp && window.wp.media) {
    const originalFrame = window.wp.media.view.MediaFrame.Select;

    window.wp.media.view.MediaFrame.Select = originalFrame.extend({
      initialize: function () {
        originalFrame.prototype.initialize.apply(this, arguments);

        // Add WebP support notice
        this.on("open", function () {
          if (!document.querySelector(".webp-notice")) {
            const notice = document.createElement("div");
            notice.className = "notice notice-info webp-notice";
            notice.innerHTML =
              "<p><strong>MoomsDev:</strong> WebP images are automatically generated for better performance.</p>";

            const mediaModal = document.querySelector(".media-modal-content");
            if (mediaModal) {
              mediaModal.insertBefore(notice, mediaModal.firstChild);
            }
          }
        });
      },
    });
  }
}

/**
 * Setup theme options helpers
 */
function setupThemeOptions() {
  // Performance optimization notices
  addPerformanceNotices();

  // Add build status indicator
  addBuildStatus();
}

/**
 * Add performance optimization notices
 */
function addPerformanceNotices() {
  const notices = [
    {
      id: "critical-css-notice",
      message: "Critical CSS is automatically inlined for better performance.",
      type: "success",
    },
    {
      id: "service-worker-notice",
      message: "Service Worker is active for faster loading.",
      type: "info",
    },
  ];

  notices.forEach((notice) => {
    if (!document.querySelector(`#${notice.id}`)) {
      const noticeEl = document.createElement("div");
      noticeEl.id = notice.id;
      noticeEl.className = `notice notice-${notice.type} is-dismissible`;
      noticeEl.innerHTML = `
        <p><strong>MoomsDev Performance:</strong> ${notice.message}</p>
        <button type="button" class="notice-dismiss">
          <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
      `;

      // Add to notices area
      const noticesArea = document.querySelector(".wrap h1");
      if (noticesArea) {
        noticesArea.parentNode.insertBefore(noticeEl, noticesArea.nextSibling);
      }
    }
  });
}

/**
 * Add build status indicator
 */
function addBuildStatus() {
  // Check if dist files exist
  const distFiles = [
    "/wp-content/themes/mooms_dev/dist/js/critical.min.js",
    "/wp-content/themes/mooms_dev/dist/css/critical.min.css",
    "/wp-content/themes/mooms_dev/dist/sw.js",
  ];

  let loadedFiles = 0;
  const totalFiles = distFiles.length;

  distFiles.forEach((file) => {
    fetch(file, { method: "HEAD" })
      .then((response) => {
        if (response.ok) loadedFiles++;
      })
      .catch(() => {})
      .finally(() => {
        if (loadedFiles === totalFiles) {
          showBuildStatus(
            "success",
            "All optimized assets loaded successfully."
          );
        } else if (loadedFiles > 0) {
          showBuildStatus(
            "warning",
            `${loadedFiles}/${totalFiles} optimized assets loaded.`
          );
        } else {
          showBuildStatus(
            "error",
            'Run "npm run optimize" to build optimized assets.'
          );
        }
      });
  });
}

/**
 * Show build status
 */
function showBuildStatus(type, message) {
  const statusEl = document.createElement("div");
  statusEl.id = "build-status-notice";
  statusEl.className = `notice notice-${type}`;
  statusEl.innerHTML = `<p><strong>Build Status:</strong> ${message}</p>`;

  const themePage = document.querySelector(".theme-info, .wrap h1");
  if (themePage) {
    themePage.parentNode.insertBefore(statusEl, themePage.nextSibling);
  }
}

/**
 * Show theme information
 */
function showThemeInfo() {
  const themeInfo = {
    name: "MoomsDev",
    version: "1.0.0",
    performance: "Optimized for 95+ PageSpeed",
    features: [
      "Critical CSS",
      "Service Worker",
      "WebP Images",
      "Code Splitting",
    ],
  };
}

// Export for global access
window.MoomsAdmin = {
  version: "1.0.0",
  initAdmin,
  setupMediaUploader,
  setupThemeOptions,
};
