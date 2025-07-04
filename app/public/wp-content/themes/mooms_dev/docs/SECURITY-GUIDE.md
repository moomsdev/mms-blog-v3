# MoomsDev Theme - Security Guide

## 🛡️ Tổng quan Bảo mật

Theme này được thiết kế với bảo mật ưu tiên hàng đầu. Tài liệu này mô tả các biện pháp bảo mật đã được triển khai và cách sử dụng chúng một cách an toàn.

## 🔒 Biện pháp Bảo mật đã triển khai

### 1. File Upload Security

**Vấn đề đã sửa**: Loại bỏ `ALLOW_UNFILTERED_UPLOADS = true`

**Biện pháp hiện tại**:
- Whitelist file extensions
- File type validation
- User permission checks
- Size limits

```php
// File types được phép
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'zip'];

// Các file type bị cấm
unset($mimes['exe'], $mimes['php'], $mimes['php3'], $mimes['phtml']);
```

### 2. AJAX Security

**Vấn đề đã sửa**: AJAX requests không có nonce verification

**Biện pháp hiện tại**:
- Nonce verification cho mọi AJAX request
- Input sanitization
- Rate limiting
- Error handling

```php
// Verify nonce
if (!wp_verify_nonce($_POST['nonce'] ?? '', 'theme_ajax_search')) {
    wp_die(__('Security check failed', 'mms'));
}

// Sanitize input
$search_query = sanitize_text_field($_POST['search_query'] ?? '');
```

### 3. Editor Security

**Vấn đề đã sửa**: `allow_unsafe_link_target = true`

**Biện pháp hiện tại**:
- Tắt unsafe link targets
- Content filtering
- Script removal

### 4. WordPress Hardening

**Biện pháp đã triển khai**:
- Ẩn WordPress version
- Tắt XML-RPC
- Remove generator tags
- Security headers

```php
// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

## ⚠️ Cảnh báo Bảo mật

### 1. File Permissions

**Khuyến nghị**:
- Folders: 755
- Files: 644
- wp-config.php: 600

```bash
# Set proper permissions
find /path/to/theme -type d -exec chmod 755 {} \;
find /path/to/theme -type f -exec chmod 644 {} \;
```

### 2. Sensitive Data

**Không bao giờ**:
- Commit API keys vào Git
- Hard-code passwords
- Expose debug information

**Sử dụng**:
```php
// Environment variables
$api_key = $_ENV['API_KEY'] ?? '';

// WordPress constants
define('API_SECRET', 'your-secret-here');
```

### 3. User Input Validation

**Luôn luôn**:
- Sanitize input data
- Validate data types
- Escape output data

```php
// Input sanitization
$email = sanitize_email($_POST['email']);
$text = sanitize_text_field($_POST['message']);
$html = wp_kses_post($_POST['content']);

// Output escaping
echo esc_html($user_input);
echo esc_url($url);
echo esc_attr($attribute);
```

## 🔐 Authentication & Authorization

### 1. User Roles

**Super Users**:
```php
// Check super user
if (ThemeSecurity::is_super_user()) {
    // Admin-only functionality
}
```

**Capability Checks**:
```php
// Check specific capability
if (current_user_can('manage_options')) {
    // Admin functionality
}

if (current_user_can('edit_posts')) {
    // Editor functionality
}
```

### 2. Nonce Implementation

**AJAX Requests**:
```javascript
// Frontend
$.ajax({
    url: themeAjax.ajaxurl,
    data: {
        action: 'my_action',
        nonce: themeAjax.nonce,
        data: myData
    }
});
```

```php
// Backend
if (!wp_verify_nonce($_POST['nonce'], 'my_action_nonce')) {
    wp_die('Security check failed');
}
```

**Forms**:
```php
// Generate nonce
wp_nonce_field('my_form_action', 'my_form_nonce');

// Verify nonce
if (!wp_verify_nonce($_POST['my_form_nonce'], 'my_form_action')) {
    wp_die('Security check failed');
}
```

## 🛠️ Security Testing

### 1. Vulnerability Scanning

**Tools khuyến nghị**:
- WPScan
- Sucuri SiteCheck
- Wordfence

### 2. Code Review Checklist

- [ ] Input sanitization
- [ ] Output escaping
- [ ] Nonce verification
- [ ] Permission checks
- [ ] SQL injection prevention
- [ ] XSS prevention

### 3. Regular Audits

**Hàng tháng**:
- Update dependencies
- Review user permissions
- Check file integrity
- Monitor error logs

## 🚨 Incident Response

### 1. Phát hiện Breach

**Bước đầu tiên**:
1. Thay đổi tất cả passwords
2. Update WordPress core & plugins
3. Scan malware
4. Review access logs

### 2. Recovery Plan

1. **Backup**: Restore từ backup sạch
2. **Patch**: Fix lỗ hổng bảo mật
3. **Monitor**: Theo dõi hoạt động bất thường
4. **Document**: Ghi lại incident để học hỏi

## 📋 Security Checklist

### Pre-deployment
- [ ] Remove debug code
- [ ] Update all dependencies
- [ ] Run security scan
- [ ] Test input validation
- [ ] Verify file permissions

### Post-deployment
- [ ] Monitor error logs
- [ ] Check for unusual traffic
- [ ] Verify backup functionality
- [ ] Test incident response

## 📞 Báo cáo Lỗ hổng

Nếu phát hiện lỗ hổng bảo mật, vui lòng liên hệ:

- **Email**: security@mooms.dev
- **Mô tả**: Chi tiết lỗ hổng và cách exploit
- **Timeline**: Thời gian phát hiện

---

*Tài liệu này được cập nhật cho phiên bản 1.0.0* 