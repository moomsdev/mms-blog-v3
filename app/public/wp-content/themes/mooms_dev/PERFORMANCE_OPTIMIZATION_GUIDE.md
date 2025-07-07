# 🚀 MoomsDev Theme - Performance Optimization Guide

## 📋 Tổng quan

Theme MoomsDev đã được tối ưu toàn diện để đạt điểm **PageSpeed Insights 95-100** và tuân thủ **Core Web Vitals** của Google. Theme được thiết kế với kiến trúc module hóa, bảo mật cao và hiệu suất tối đa.

## 🏗️ Cấu trúc Theme

```
mooms_dev/
├── theme/
│   ├── functions.php              # Core theme functionality
│   ├── setup/
│   │   ├── performance.php        # 🚀 Advanced Performance Module
│   │   ├── security.php          # 🔒 Security Hardening Module
│   │   ├── assets.php            # ⚡ Enhanced Asset Management
│   │   ├── theme-support.php     # WordPress theme support
│   │   ├── menus.php             # Navigation menus
│   │   └── ajax.php              # AJAX functionality
│   └── ...
├── dist/
│   ├── styles/
│   │   └── theme.css             # Main compiled CSS
│   ├── theme.js                  # Main compiled JavaScript
│   ├── critical.css              # Above-the-fold CSS (inline)
│   ├── critical.js               # Critical JavaScript
│   ├── script.js                 # AJAX search functionality
│   └── sw.js                     # 🔄 Service Worker
├── offline.html                  # 📱 PWA offline page
└── ...
```

## 🚀 Performance Optimizations

### 1. **ThemePerformance Class** (`setup/performance.php`)

#### ✅ **WordPress Bloat Removal**

-   ❌ Emoji support scripts (-15KB)
-   ❌ jQuery Migrate (-9KB)
-   ❌ Unnecessary WordPress features
-   ❌ Embeds, RSS feeds, RSD links
-   ❌ Block library styles (nếu không dùng Gutenberg)

#### ⚡ **Advanced Caching**

```php
// Static assets: 1 year cache
Cache-Control: public, max-age=31536000, immutable

// HTML pages: 1 hour cache
Cache-Control: public, max-age=3600, must-revalidate
```

#### 🖼️ **Image Optimizations**

-   **Lazy loading**: `loading="lazy"` tự động
-   **Async decoding**: `decoding="async"`
-   **WebP conversion**: Tự động khi upload
-   **Responsive images**: srcset tự động
-   **Alt text**: Fallback tự động

#### 💾 **Database Optimizations**

-   **Post revisions**: Giới hạn 3
-   **Autosave interval**: 5 phút
-   **Object caching**: Enabled
-   **Slow query monitoring**: Development mode

#### 🧠 **Memory Management**

-   **Memory limit**: 256MB minimum
-   **Garbage collection**: Enabled
-   **Memory cleanup**: End of request

### 2. **Enhanced Asset Management** (`setup/assets.php`)

#### 📦 **Conditional Loading**

```php
// Home/Archive pages
if (is_home() || is_archive()) {
    wp_enqueue_script('theme-archive-js');
}

// Single posts with comments
if (is_single() && comments_open()) {
    wp_enqueue_script('theme-comments-js');
}
```

#### ⚡ **Script Optimization**

-   **Defer**: Non-critical scripts
-   **Async**: Analytics, tracking
-   **Preload**: Critical resources

#### 🎨 **CSS Strategy**

-   **Critical CSS**: Inline in `<head>`
-   **Non-critical CSS**: Async load with preload
-   **Page-specific CSS**: Conditional loading

#### 🔗 **Resource Hints**

```html
<!-- DNS Prefetch -->
<link rel="dns-prefetch" href="//fonts.googleapis.com" />

<!-- Preconnect -->
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

<!-- Prefetch likely pages -->
<link rel="prefetch" href="/blog" />
```

### 3. **Service Worker** (`dist/sw.js`)

#### 🔄 **Caching Strategies**

| Asset Type    | Strategy               | Cache Time           |
| ------------- | ---------------------- | -------------------- |
| Static CSS/JS | Cache First            | 1 year               |
| Images        | Stale While Revalidate | Update in background |
| HTML Pages    | Network First          | Fallback to cache    |
| Admin/AJAX    | Network Only           | No cache             |

#### 📱 **PWA Features**

-   **Offline support**: Custom offline page
-   **Background sync**: Failed requests queue
-   **Push notifications**: Ready for implementation
-   **Cache management**: Auto cleanup old caches

#### 💾 **Cache Limits**

-   **Dynamic cache**: 50 items max
-   **Image cache**: 100 items max
-   **Cleanup interval**: Every 5 minutes

## 🔒 Security Hardening

### 1. **ThemeSecurity Class** (`setup/security.php`)

#### 🛡️ **Security Headers**

```http
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

#### 🔐 **Advanced Login Protection**

-   **Rate limiting**: 5 attempts / 15 minutes
-   **IP blocking**: 1 hour sau 5 failed attempts
-   **Brute force protection**: Advanced detection
-   **Generic error messages**: Prevent username enumeration
-   **Login monitoring**: Log successful/failed attempts
-   **Admin access control**: Limit admin requests per IP

#### 📁 **File Upload Security**

-   **Dangerous file types**: Blocked (.php, .exe, .bat, etc.)
-   **File size limit**: 10MB maximum
-   **Malware scanning**: Basic file validation
-   **WebP support**: Added safe image format
-   **Extension spoofing**: Detect disguised executables

#### 🚫 **WordPress Vulnerabilities Protection**

-   **XML-RPC**: Disabled
-   **REST API**: Restricted cho non-logged users
-   **User enumeration**: Blocked via author pages và API
-   **Version hiding**: Remove WordPress fingerprints
-   **File editing**: Disabled in admin
-   **Directory browsing**: Disabled
-   **Sensitive files**: Protected (wp-config.php, .htaccess, etc.)

#### 🔒 **Advanced Security Features**

-   **SQL Injection Protection**: URL parameter scanning
-   **XSS Protection**: Script injection detection
-   **Malicious Request Blocking**: Pattern-based filtering
-   **File Integrity Monitoring**: Critical file change detection
-   **Security Event Logging**: Comprehensive audit trail
-   **IP Management**: Block/unblock functionality

#### 🛠️ **Admin Security Dashboard**

-   **Real-time monitoring**: Security status overview
-   **IP management**: Block/unblock addresses
-   **Security logs**: Event tracking và analysis
-   **Threat detection**: Malicious activity alerts
-   **Recommendations**: Security best practices

### 2. **Security Event Monitoring**

#### 📊 **Logged Events**

-   Failed login attempts
-   Successful logins
-   Malicious request attempts
-   SQL injection attempts
-   File modification alerts
-   Rate limit violations
-   IP blocking/unblocking

#### 🔍 **Event Details**

```json
{
	"timestamp": "2024-01-01 12:00:00",
	"event": "Malicious request blocked",
	"ip": "192.168.1.100",
	"user_agent": "Mozilla/5.0...",
	"data": {
		"pattern": "/union\\s+select/i",
		"request_uri": "/page?id=1 UNION SELECT...",
		"blocked": true
	}
}
```

#### 📈 **Security Analytics**

-   **Attack patterns**: Common attack vector analysis
-   **Geographic tracking**: IP location mapping
-   **Threat timeline**: Chronological attack overview
-   **Risk assessment**: Automated threat scoring

### 3. **Database Security**

#### 🗄️ **Query Protection**

-   **Suspicious query monitoring**: Information schema access detection
-   **Query logging**: Development mode tracking
-   **Table prefix checking**: Default prefix warnings
-   **Performance monitoring**: Slow query detection

#### 🔐 **Access Control**

-   **Admin privilege checking**: Role-based restrictions
-   **Database connection security**: Secure credential handling
-   **Backup protection**: Automated backup verification

### 4. **Real-time Protection**

#### ⚡ **Active Monitoring**

-   **Request pattern analysis**: Real-time threat detection
-   **Behavioral analysis**: Unusual activity patterns
-   **Geographic anomalies**: Location-based alerts
-   **Volume monitoring**: Traffic spike detection

#### 🚨 **Automated Response**

-   **Instant IP blocking**: Automatic threat mitigation
-   **Progressive penalties**: Escalating security measures
-   **Whitelist management**: Trusted IP handling
-   **Emergency lockdown**: Site protection mode

### 5. **Configuration Options**

#### ⚙️ **Customizable Settings**

```php
// Security configuration
const SECURITY_SETTINGS = [
    'login_attempts' => 5,        // Max failed attempts
    'block_duration' => 3600,     // Block time (seconds)
    'rate_limit' => 100,          // Requests per minute
    'upload_limit' => 10,         // File size MB
    'log_retention' => 100,       // Number of log entries
    'monitor_files' => true,      // File integrity monitoring
    'strict_mode' => false,       // Enhanced security mode
];
```

#### 🎛️ **Admin Controls**

-   **Security status dashboard**: Real-time overview
-   **IP management interface**: Block/unblock controls
-   **Log viewer**: Security event browser
-   **Settings panel**: Configuration management
-   **Backup/restore**: Security settings backup

### 6. **Integration Features**

#### 🔗 **WordPress Integration**

-   **User role compatibility**: Seamless permission handling
-   **Plugin compatibility**: No conflicts với popular plugins
-   **Theme integration**: Built-in security features
-   **Multisite support**: Network-wide protection

#### 🛡️ **External Services**

-   **CDN compatibility**: Cloudflare, MaxCDN support
-   **Backup services**: Automated security backups
-   **Monitoring tools**: Integration với security scanners
-   **Notification systems**: Email/SMS alerts

## 📊 Performance Metrics

### 🎯 **Target Scores**

| Metric                 | Desktop | Mobile  |
| ---------------------- | ------- | ------- |
| **PageSpeed Insights** | 95-100  | 90-95   |
| **LCP**                | ≤ 1.2s  | ≤ 1.5s  |
| **FID**                | ≤ 100ms | ≤ 100ms |
| **CLS**                | ≤ 0.1   | ≤ 0.1   |
| **FCP**                | ≤ 0.8s  | ≤ 1.0s  |
| **TTI**                | ≤ 2.5s  | ≤ 3.0s  |

### 📈 **Core Web Vitals Monitoring**

Theme tự động track Core Web Vitals:

```javascript
// Largest Contentful Paint
new PerformanceObserver((entryList) => {
	for (const entry of entryList.getEntries()) {
		console.log("LCP:", entry.startTime);
	}
}).observe({ type: "largest-contentful-paint", buffered: true });
```

### 🔍 **Performance Monitoring Tools**

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

## 🛠️ Configuration & Usage

### 1. **Critical CSS Setup**

Để tối ưu Critical CSS:

1. Sử dụng công cụ online: [Critical Path CSS Generator](https://www.sitelocity.com/critical-path-css-generator)
2. Paste CSS vào `/dist/critical.css`
3. CSS sẽ tự động inline vào `<head>`

### 2. **Service Worker Configuration**

Service Worker tự động đăng ký. Để customize:

```javascript
// In dist/sw.js
const STATIC_ASSETS = [
	"/",
	"/dist/styles/theme.css",
	"/dist/theme.js",
	// Add your critical assets here
];
```

### 3. **Security Configuration**

Các security settings có thể điều chỉnh trong `setup/security.php`:

```php
// Rate limiting
if ($requests > 100) { // Thay đổi limit
    header('HTTP/1.1 429 Too Many Requests');
}

// Login attempts
if ($attempts >= 5) { // Thay đổi số lần thử
    set_transient('login_blocked_' . $ip, true, 3600);
}
```

### 4. **Asset Optimization**

Để thêm conditional assets:

```php
// In setup/assets.php
if (is_page('contact')) {
    wp_enqueue_script('contact-form-js');
}

if (is_woocommerce()) {
    wp_enqueue_style('woocommerce-styles');
}
```

## 🚀 Advanced Features

### 1. **PWA (Progressive Web App)**

Theme hỗ trợ PWA với:

-   **Service Worker**: Offline caching
-   **Offline page**: Beautiful fallback
-   **Push notifications**: Ready to implement
-   **Background sync**: Failed request handling

### 2. **Auto WebP Conversion**

```php
// Tự động chuyển JPG/PNG sang WebP khi upload
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    // Auto WebP conversion logic
});
```

### 3. **Image Optimization**

-   **Lazy loading**: Native `loading="lazy"`
-   **Responsive images**: Auto srcset
-   **WebP support**: Browser-compatible
-   **Alt text**: Auto-generated fallbacks

### 4. **Database Optimization**

-   **Query monitoring**: Slow query detection
-   **Object caching**: WordPress object cache
-   **Post revisions**: Limited to 3
-   **Autosave**: Optimized interval

## 🧪 Testing & Debugging

### 1. **Performance Testing**

```bash
# Local testing
npm run build
# Test với PageSpeed Insights

# Staging testing
# Deploy to staging environment
# Run full performance audit
```

### 2. **Debug Mode**

Bật debug mode trong `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Slow queries sẽ được log tự động.

### 3. **Service Worker Debug**

```javascript
// In browser console
navigator.serviceWorker.ready.then((registration) => {
	console.log("SW registered:", registration);
});

// Check cache
caches.keys().then((keys) => console.log("Cache keys:", keys));
```

## 📋 Maintenance Checklist

### 🔄 **Hàng tháng**

-   [ ] Kiểm tra PageSpeed Insights scores
-   [ ] Update dependencies nếu cần
-   [ ] Clear expired caches
-   [ ] Review security logs

### 🔄 **Hàng quý**

-   [ ] Full performance audit
-   [ ] Security vulnerability scan
-   [ ] Update critical CSS
-   [ ] Review and optimize database

### 🔄 **Hàng năm**

-   [ ] Major dependency updates
-   [ ] Architecture review
-   [ ] Performance benchmark comparison
-   [ ] Security policy review

## 🆘 Troubleshooting

### ❗ **Common Issues**

1. **Service Worker không hoạt động**

    ```javascript
    // Check in browser console
    if ("serviceWorker" in navigator) {
    	console.log("SW supported");
    }
    ```

2. **Critical CSS không load**

    - Kiểm tra file `/dist/critical.css` tồn tại
    - Verify file permissions (644)

3. **Cache không clear**

    - Hard refresh: Ctrl+Shift+R
    - Clear browser cache
    - Check cache headers

4. **Security headers missing**
    - Verify server configuration
    - Check .htaccess conflicts
    - Test with SecurityHeaders.com

### 🔧 **Performance Issues**

1. **High LCP**

    - Optimize above-the-fold images
    - Increase critical CSS coverage
    - Check server response time

2. **High CLS**

    - Add image dimensions
    - Preload critical fonts
    - Avoid dynamic content insertion

3. **High FID**
    - Reduce JavaScript execution time
    - Use defer/async attributes
    - Optimize third-party scripts

## 📞 Support & Documentation

### 📚 **Resources**

-   [WordPress Performance](https://wordpress.org/support/article/optimization/)
-   [Core Web Vitals](https://web.dev/vitals/)
-   [Service Workers](https://developers.google.com/web/fundamentals/primers/service-workers)

### 🏢 **Contact**

-   **Author**: La Cà Dev
-   **Email**: support@mooms.dev
-   **Website**: https://mooms.dev
-   **Phone**: 0989 64 67 66

---

## 🎉 **Kết luận**

Theme MoomsDev đã được tối ưu toàn diện với:

-   **Performance**: Điểm PageSpeed 95-100
-   **Security**: Bảo mật enterprise-level
-   **PWA**: Progressive Web App features
-   **Maintainability**: Code module hóa, dễ maintain

**Ready for production!** 🚀
