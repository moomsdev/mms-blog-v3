# MoomsDev Theme - Cấu trúc & Documentation

## 📁 Tổng quan cấu trúc

```
mooms_dev/
├── app/                     # Application logic
│   ├── src/                 # PSR-4 autoloaded classes
│   │   ├── Controllers/     # Route controllers
│   │   ├── Models/          # Data models
│   │   ├── Services/        # Business logic services
│   │   ├── PostTypes/       # Custom post types
│   │   ├── Validators/      # Input validation
│   │   └── Widgets/         # Custom widgets
│   ├── helpers/             # Helper functions
│   ├── routes/              # Route definitions
│   │   ├── web.php          # Frontend routes
│   │   ├── admin.php        # Admin routes
│   │   └── ajax.php         # AJAX routes
│   ├── config.php           # WP Emerge configuration
│   ├── helpers.php          # Helper loader
│   └── hooks.php            # WordPress hooks
├── theme/                   # WordPress theme files
│   ├── setup/               # Theme initialization
│   │   ├── constants.php    # Constants & directories
│   │   ├── security.php     # Security configuration
│   │   ├── performance.php  # Performance optimizations
│   │   ├── ajax-search.php  # AJAX search functionality
│   │   ├── theme-support.php # WordPress features
│   │   ├── menus.php        # Navigation menus
│   │   ├── assets.php       # Scripts & styles
│   │   └── blocks/          # Gutenberg blocks
│   ├── templates/           # Page templates
│   ├── partials/            # Template partials
│   └── functions.php        # Theme bootstrap
├── resources/               # Assets & build files
│   ├── scripts/             # JavaScript files
│   ├── styles/              # SASS stylesheets
│   ├── images/              # Image assets
│   └── build/               # Build configuration
└── dist/                    # Compiled assets
```

## 🔧 Core Components

### 1. Security Module (`theme/setup/security.php`)

**Chức năng**: Quản lý tất cả các cài đặt bảo mật theme

**Features**:
- File upload restriction
- XSS protection  
- CSRF protection với nonce
- Remove WordPress vulnerabilities
- Security headers

**Sử dụng**:
```php
// Kiểm tra user permission
if (ThemeSecurity::is_super_user()) {
    // Code cho super user
}

// Verify AJAX nonce
if (ThemeSecurity::verify_ajax_nonce()) {
    // Process AJAX request
}
```

### 2. Performance Module (`theme/setup/performance.php`)

**Chức năng**: Tối ưu hiệu suất theme

**Features**:
- Remove emoji support
- Optimize style loading với preload
- Image size optimization
- Remove unnecessary WP features
- Dashboard cleanup

**Configuration**:
```php
// Customize preload styles
$preload_styles = ['theme-styles', 'bootstrap', 'custom-styles'];

// Customize image sizes to keep
$keep_sizes = ['thumbnail', 'medium', 'large'];
```

### 3. AJAX Search Module (`theme/setup/ajax-search.php`)

**Chức năng**: Tìm kiếm AJAX an toàn và hiệu quả

**Features**:
- Nonce verification
- Input sanitization
- Customizable post types
- Response templating
- Error handling

**Frontend Usage**:
```javascript
// AJAX search với nonce
$.ajax({
    url: themeAjax.ajaxurl,
    type: 'POST',
    data: {
        action: 'theme_ajax_search',
        search_query: query,
        nonce: themeAjax.nonce
    },
    success: function(response) {
        // Handle success
    }
});
```

## 🎨 Custom Post Types

### Blog Post Type (`app/src/PostTypes/blog.php`)

**Chức năng**: Custom post type cho blog posts

**Features**:
- Custom fields support
- Archive pages
- Taxonomy support
- Meta boxes

**Sử dụng**:
```php
// Query blog posts
$blogs = new WP_Query([
    'post_type' => 'blog',
    'posts_per_page' => 10
]);
```

## 🔌 Services & Utilities

### Controllers (`app/src/Controllers/`)

**Chức năng**: MVC pattern implementation

**Example**:
```php
namespace App\Controllers;

class HomeController {
    public function index() {
        return app_view('pages.home', [
            'posts' => get_posts(['numberposts' => 5])
        ]);
    }
}
```

### Models (`app/src/Models/`)

**Chức năng**: Data abstraction layer

**Example**:
```php
namespace App\Models;

class Post {
    public static function getLatest($count = 5) {
        return get_posts([
            'numberposts' => $count,
            'post_status' => 'publish'
        ]);
    }
}
```

### Services (`app/src/Services/`)

**Chức năng**: Business logic separation

**Example**:
```php
namespace App\Services;

class EmailService {
    public function sendWelcomeEmail($user) {
        // Email logic here
    }
}
```

## 🛠️ Development Guidelines

### 1. Security Best Practices

- **Luôn** sử dụng nonce cho AJAX requests
- **Luôn** sanitize user input
- **Không** sử dụng `ALLOW_UNFILTERED_UPLOADS = true`
- **Sử dụng** proper permission checks

### 2. Performance Guidelines

- **Sử dụng** lazy loading cho images
- **Minify** CSS/JS trong production
- **Optimize** database queries
- **Cache** expensive operations

### 3. Code Organization

- **Tách** logic thành modules nhỏ
- **Sử dụng** PSR-4 autoloading
- **Comment** code phức tạp
- **Follow** WordPress coding standards

## 🚀 Deployment

### Development
```bash
yarn dev          # Development build với BrowserSync
yarn lint         # Check code quality
yarn lint-fix     # Fix linting issues
```

### Production
```bash
yarn build        # Production build
yarn release      # Create deployment package
```

## 📞 Support

- **Email**: support@mooms.dev
- **Website**: https://mooms.dev
- **Documentation**: Xem file README.md

---

*Tài liệu này được cập nhật cho phiên bản 1.0.0* 