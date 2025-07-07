# 🚀 MoomsDev Theme - WordPress Performance Champion

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![PageSpeed](https://img.shields.io/badge/PageSpeed-95%2B-green.svg)](https://pagespeed.web.dev/)
[![Security](https://img.shields.io/badge/Security-A%2B-red.svg)](https://securityheaders.com/)
[![PWA](https://img.shields.io/badge/PWA-Ready-orange.svg)](https://web.dev/progressive-web-apps/)

## 📋 Tổng quan

**MoomsDev Theme** là một WordPress theme được thiết kế để đạt **điểm PageSpeed Insights tối đa (95-100)** và tuân thủ hoàn toàn **Core Web Vitals** của Google. Theme được xây dựng với kiến trúc module hóa, bảo mật enterprise-level và performance tối ưu.

### ✨ Tính năng chính

-   🚀 **Performance tối đa**: PageSpeed Insights 95-100
-   🔒 **Bảo mật enterprise**: Security headers, rate limiting, malware protection
-   📱 **PWA Ready**: Service Worker, offline support, background sync
-   ⚡ **Core Web Vitals**: LCP ≤1.2s, FID ≤100ms, CLS ≤0.1
-   🎨 **Modern Architecture**: Modular design, conditional loading
-   🖼️ **Image Optimization**: Auto WebP, lazy loading, responsive images
-   📊 **Real-time Monitoring**: Performance tracking, error reporting

## 🎯 Performance Targets

| Metric                 | Desktop | Mobile  | Status |
| ---------------------- | ------- | ------- | ------ |
| **PageSpeed Insights** | 95-100  | 90-95   | ✅     |
| **LCP**                | ≤ 1.2s  | ≤ 1.5s  | ✅     |
| **FID**                | ≤ 100ms | ≤ 100ms | ✅     |
| **CLS**                | ≤ 0.1   | ≤ 0.1   | ✅     |
| **FCP**                | ≤ 0.8s  | ≤ 1.0s  | ✅     |
| **TTI**                | ≤ 2.5s  | ≤ 3.0s  | ✅     |

## 🏗️ Kiến trúc

```
mooms_dev/
├── theme/
│   ├── functions.php              # Core functionality
│   ├── setup/
│   │   ├── performance.php        # 🚀 Performance optimizations
│   │   ├── security.php          # 🔒 Security hardening
│   │   ├── assets.php            # ⚡ Asset management
│   │   ├── theme-support.php     # WordPress features
│   │   ├── menus.php             # Navigation
│   │   └── ajax.php              # AJAX functionality
│   └── ...
├── dist/
│   ├── styles/theme.css          # Compiled CSS
│   ├── theme.js                  # Main JavaScript
│   ├── critical.css              # Above-the-fold CSS
│   ├── critical.js               # Critical JavaScript
│   ├── script.js                 # AJAX search
│   └── sw.js                     # 🔄 Service Worker
├── offline.html                  # 📱 PWA offline page
└── resources/                    # Source assets
```

## 🚀 Installation & Setup

### Requirements

-   **WordPress**: 5.0+
-   **PHP**: 7.4+ (Recommended: 8.1+)
-   **Memory**: 256MB+ recommended
-   **HTTPS**: Required for PWA features

### Cài đặt

1. **Download & Upload**

    ```bash
    # Upload theme vào thư mục themes
    wp-content/themes/mooms_dev/
    ```

2. **Activate Theme**

    - Vào WordPress Admin → Appearance → Themes
    - Activate "MoomsDev Theme"

3. **Configure Settings**

    - Automatic optimization sẽ kích hoạt ngay lập tức
    - Service Worker sẽ tự động đăng ký
    - Security headers sẽ được áp dụng

4. **Verify Installation**

    ```bash
    # Test PageSpeed Insights
    https://pagespeed.web.dev/

    # Test Security Headers
    https://securityheaders.com/

    # Test PWA
    # Browser Dev Tools → Application → Service Workers
    ```

## ⚡ Performance Features

### 🎯 **WordPress Bloat Removal**

-   ❌ Emoji scripts (-15KB)
-   ❌ jQuery Migrate (-9KB)
-   ❌ Embeds, RSS feeds
-   ❌ Block library styles
-   ❌ Unnecessary meta tags

### 🗄️ **Advanced Caching**

-   **Static assets**: 1 year cache với immutable
-   **HTML pages**: 1 hour cache cho non-logged users
-   **Object caching**: WordPress native caching
-   **Database optimization**: Reduced revisions, optimized autosave

### 🖼️ **Image Optimizations**

-   **Lazy loading**: Native `loading="lazy"`
-   **WebP conversion**: Auto convert JPG/PNG → WebP
-   **Responsive images**: Auto srcset generation
-   **Alt text fallback**: SEO friendly
-   **Async decoding**: `decoding="async"`

### 📦 **Asset Management**

-   **Critical CSS**: Inline trong `<head>` (<14KB)
-   **Non-critical CSS**: Async load với preload
-   **JavaScript**: Defer/async strategies
-   **Conditional loading**: Page-specific assets
-   **Resource hints**: DNS prefetch, preconnect, prefetch

## 🔒 Security Features

### 🛡️ **Security Headers**

```http
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'...
```

### 🔐 **Login Protection**

-   **Rate limiting**: 5 attempts / 15 minutes
-   **IP blocking**: 1 hour sau 5 failed attempts
-   **Brute force protection**: Advanced detection
-   **Generic error messages**: Prevent username enumeration

### 📁 **File Upload Security**

-   **Dangerous files**: Blocked (.php, .exe, .bat, etc.)
-   **File size limit**: 10MB maximum
-   **Malware scanning**: Basic validation
-   **WebP support**: Safe image format

### 🚫 **WordPress Hardening**

-   **XML-RPC**: Disabled
-   **REST API**: Restricted cho non-logged users
-   **User enumeration**: Blocked
-   **Version hiding**: Remove WordPress fingerprints
-   **File editing**: Disabled in admin

## 📱 PWA Features

### 🔄 **Service Worker**

-   **Multi-strategy caching**: Cache-first, Network-first, Stale-while-revalidate
-   **Offline support**: Custom offline page
-   **Background sync**: Queue failed requests
-   **Push notifications**: Ready for implementation
-   **Cache management**: Auto cleanup

### 📴 **Offline Experience**

-   **Beautiful offline page**: Custom design
-   **Auto-retry**: When connection restored
-   **Real-time status**: Connection indicators
-   **Critical pages cached**: Always available offline

## 🛠️ Configuration

### Critical CSS Setup

1. Generate Critical CSS:

    ```bash
    # Sử dụng online tool
    https://www.sitelocity.com/critical-path-css-generator
    ```

2. Paste vào file:

    ```
    /dist/critical.css
    ```

3. CSS sẽ tự động inline vào `<head>`

### Service Worker Customization

```javascript
// In dist/sw.js - Custom cache assets
const STATIC_ASSETS = [
	"/",
	"/dist/styles/theme.css",
	"/dist/theme.js",
	// Add your critical pages/assets
];
```

### Security Configuration

```php
// In setup/security.php - Adjust limits
const MAX_LOGIN_ATTEMPTS = 5;        // Login attempts
const RATE_LIMIT_REQUESTS = 100;     // Requests per minute
const LOGIN_BLOCK_DURATION = 3600;   // Block duration (seconds)
```

## 📊 Monitoring & Testing

### Performance Testing Tools

1. **[PageSpeed Insights](https://pagespeed.web.dev/)**

    - Core Web Vitals
    - Performance recommendations
    - Real user data (CrUX)

2. **[GTmetrix](https://gtmetrix.com/)**

    - Waterfall analysis
    - Performance history
    - Video analysis

3. **[WebPageTest](https://www.webpagetest.org/)**
    - Multi-location testing
    - Connection simulation
    - Advanced metrics

### Security Testing

1. **[SecurityHeaders.com](https://securityheaders.com/)**

    - Security headers analysis
    - Grade A+ target

2. **[SSL Labs](https://www.ssllabs.com/ssltest/)**
    - SSL/TLS configuration
    - Certificate validation

### Core Web Vitals Monitoring

Theme tự động track performance metrics:

```javascript
// Real User Monitoring được enable mặc định
// Xem Console để theo dõi metrics:
// - LCP (Largest Contentful Paint)
// - FID (First Input Delay)
// - CLS (Cumulative Layout Shift)
```

## 🧪 Development

### Local Development

```bash
# Clone repository
git clone https://github.com/your-repo/mooms-dev-theme

# Install dependencies (nếu có)
npm install

# Build assets (nếu có build process)
npm run build

# Start development
npm run dev
```

### Debug Mode

Enable trong `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('SCRIPT_DEBUG', true);
```

Slow queries sẽ được log tự động khi debug mode active.

## 🔧 Troubleshooting

### Common Issues

1. **Service Worker không hoạt động**

    ```javascript
    // Check browser console
    if ("serviceWorker" in navigator) {
    	console.log("Service Worker supported");
    }
    ```

2. **Critical CSS không load**

    - Kiểm tra file `/dist/critical.css` tồn tại
    - Verify file permissions (644)
    - Check file size (<14KB recommended)

3. **Performance scores thấp**

    - Run PageSpeed Insights multiple times
    - Check server response time
    - Verify CDN configuration
    - Test on staging environment

4. **Cache không clear**
    - Hard refresh: `Ctrl+Shift+R`
    - Clear browser cache
    - Check cache headers với Developer Tools

### Performance Issues

1. **High LCP**

    - Optimize above-the-fold images
    - Increase critical CSS coverage
    - Check server response time
    - Use WebP images

2. **High CLS**

    - Add image dimensions
    - Preload critical fonts
    - Avoid dynamic content insertion
    - Use aspect-ratio CSS

3. **High FID**
    - Reduce JavaScript execution time
    - Use defer/async attributes
    - Optimize third-party scripts
    - Split large bundles

## 📋 Maintenance

### Hàng tháng

-   [ ] Kiểm tra PageSpeed Insights scores
-   [ ] Update dependencies nếu cần
-   [ ] Clear expired caches
-   [ ] Review security logs
-   [ ] Monitor Core Web Vitals

### Hàng quý

-   [ ] Full performance audit
-   [ ] Security vulnerability scan
-   [ ] Update critical CSS
-   [ ] Review và optimize database
-   [ ] Check broken links

### Hàng năm

-   [ ] Major dependency updates
-   [ ] Architecture review
-   [ ] Performance benchmark comparison
-   [ ] Security policy review
-   [ ] Accessibility audit

## 📚 Documentation

-   [Performance Optimization Guide](./PERFORMANCE_OPTIMIZATION_GUIDE.md)
-   [Technical Specification](./TECHNICAL_SPECIFICATION.md)
-   [Security Best Practices](./docs/security.md)
-   [PWA Implementation](./docs/pwa.md)

## 🤝 Support

### Contact Information

-   **Author**: La Cà Dev
-   **Email**: support@mooms.dev
-   **Website**: https://mooms.dev
-   **Phone**: 0989 64 67 66

### Resources

-   [WordPress Performance](https://wordpress.org/support/article/optimization/)
-   [Core Web Vitals](https://web.dev/vitals/)
-   [Service Workers](https://developers.google.com/web/fundamentals/primers/service-workers)
-   [PWA Documentation](https://web.dev/progressive-web-apps/)

## 📄 License

**Commercial License** - Thuộc sở hữu của MoomsDev Team. Vui lòng liên hệ để được cấp phép sử dụng.

## 🎯 Changelog

### Version 1.0.0 (2024)

-   ✅ Initial release
-   ✅ Performance optimizations implemented
-   ✅ Security hardening completed
-   ✅ PWA features added
-   ✅ Service Worker implementation
-   ✅ Core Web Vitals optimization
-   ✅ Offline support
-   ✅ Real-time monitoring

---

## 🎉 Kết luận

**MoomsDev Theme** là giải pháp hoàn hảo cho:

-   🚀 **Businesses**: Cần website nhanh và bảo mật
-   💼 **Agencies**: Muốn deliver high-quality solutions
-   👨‍💻 **Developers**: Cần modern, maintainable codebase
-   📈 **SEO Specialists**: Yêu cầu Core Web Vitals compliance

**Ready for production với PageSpeed 95-100!** 🎯

---

_Made with ❤️ by MoomsDev Team_
