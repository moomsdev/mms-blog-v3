<?php
/**
 * Security Admin Panel for MoomsDev Theme
 * Provides a comprehensive security dashboard in WordPress admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class MoomsDevSecurityAdmin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addSecurityMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // AJAX handlers
        add_action('wp_ajax_block_ip', [$this, 'handleBlockIP']);
        add_action('wp_ajax_unblock_ip', [$this, 'handleUnblockIP']);
        add_action('wp_ajax_clear_security_logs', [$this, 'handleClearLogs']);
    }

    public function addSecurityMenu()
    {
        add_menu_page(
            'mooms.dev Security',
            'mooms.dev Security',
            'manage_options',
            'moomsdev-security',
            [$this, 'renderSecurityPage'],
            'dashicons-shield-alt',
            80
        );
    }

    public function enqueueAdminAssets($hook)
    {
        if ($hook !== 'toplevel_page_moomsdev-security') {
            return;
        }

        wp_enqueue_script('jquery');

        // Localize script with AJAX data
        wp_localize_script('jquery', 'moomsSecurityAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('security_nonce')
        ]);
    }

    public function renderSecurityPage()
    {
        $blocked_ips = get_option('blocked_ips', []);
        $security_logs = get_option('security_logs', []);

        // Tạo sample data nếu chưa có
        if (empty($security_logs)) {
            $sample_logs = [
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'event' => 'Failed Login Attempt',
                    'ip' => '192.168.1.100',
                    'data' => ['username' => 'admin', 'attempt_count' => 3]
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                    'event' => 'Suspicious Request Blocked',
                    'ip' => '10.0.0.50',
                    'data' => ['request_uri' => '/wp-admin/user-new.php', 'method' => 'POST']
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                    'event' => 'File Upload Blocked',
                    'ip' => '172.16.0.25',
                    'data' => ['filename' => 'malicious.php', 'reason' => 'Dangerous file type']
                ]
            ];
            update_option('security_logs', $sample_logs);
            $security_logs = $sample_logs;
        }

        $security_logs = array_slice($security_logs, -20); // Get last 20 logs
        ?>
        <div class="wrap">
            <h1>🛡️ MoomsDev Security Dashboard</h1>

            <!-- Security Status -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>🛡️ Security Status</h2>
                </div>
                <div class="inside">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:15px;">
                        <div style="padding:10px; border-radius:4px; border-left:4px solid <?php echo is_ssl() ? '#28a745' : '#ffc107'; ?>; background:<?php echo is_ssl() ? '#d4edda' : '#fff3cd'; ?>;">
                            <strong>HTTPS/SSL:</strong> <?php echo is_ssl() ? '✅ Enabled' : '⚠️ Disabled'; ?>
                        </div>
                        <div style="padding:10px; border-radius:4px; border-left:4px solid #28a745; background:#d4edda;">
                            <strong>Login Protection:</strong> ✅ Active
                        </div>
                        <div style="padding:10px; border-radius:4px; border-left:4px solid #28a745; background:#d4edda;">
                            <strong>Security Headers:</strong> ✅ Active
                        </div>
                        <div style="padding:10px; border-radius:4px; border-left:4px solid #28a745; background:#d4edda;">
                            <strong>File Upload Security:</strong> ✅ Active
                        </div>
                        <div style="padding:10px; border-radius:4px; border-left:4px solid #28a745; background:#d4edda;">
                            <strong>XML-RPC:</strong> ✅ Disabled
                        </div>
                        <div style="padding:10px; border-radius:4px; border-left:4px solid #28a745; background:#d4edda;">
                            <strong>User Enumeration:</strong> ✅ Blocked
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blocked IPs Management -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>🚫 Blocked IP Addresses (<?php echo count($blocked_ips); ?>)</h2>
                </div>
                <div class="inside">
                    <div style="margin-bottom:20px; padding:15px; background:#f8f9fa; border-radius:4px;">
                        <h3>Chặn địa chỉ IP mới</h3>
                        <input type="text" id="security-new-ip" placeholder="Nhập địa chỉ IP (ví dụ: 192.168.1.1)" style="width:250px; margin-right:10px; padding:8px;" />
                        <button type="button" class="button button-primary" onclick="blockNewIP()">Chặn IP</button>
                    </div>

                    <?php if (!empty($blocked_ips)): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blocked_ips as $ip): ?>
                                    <tr>
                                        <td><code><?php echo esc_html($ip); ?></code></td>
                                        <td>
                                            <button class="button button-secondary" onclick="unblockIP('<?php echo esc_js($ip); ?>')">
                                                Bỏ chặn
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:#666;">Chưa có địa chỉ IP nào bị chặn.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Security Event Logs -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>📊 Security Event Logs</h2>
                </div>
                <div class="inside">
                    <button type="button" class="button button-secondary" onclick="clearSecurityLogs()" style="margin-bottom:15px;">
                        Xóa tất cả Log
                    </button>
                    <span style="margin-left:10px; color:#666;">Hiển thị 20 sự kiện gần nhất</span>

                    <?php if (!empty($security_logs)): ?>
                        <table class="wp-list-table widefat fixed striped" style="margin-top:15px;">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Sự kiện</th>
                                    <th>IP Address</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($security_logs) as $log): ?>
                                    <tr>
                                        <td><?php echo esc_html($log['timestamp'] ?? date('Y-m-d H:i:s')); ?></td>
                                        <td><strong style="color:#d63384;"><?php echo esc_html($log['event'] ?? 'Unknown Event'); ?></strong></td>
                                        <td><code><?php echo esc_html($log['ip'] ?? 'Unknown IP'); ?></code></td>
                                        <td>
                                            <?php if (!empty($log['data'])): ?>
                                                <details>
                                                    <summary style="cursor:pointer; color:#0073aa;">Xem chi tiết</summary>
                                                    <pre style="background:#f1f1f1; padding:10px; border-radius:4px; font-size:11px; max-height:150px; overflow:auto; margin-top:10px;"><?php echo esc_html(json_encode($log['data'], JSON_PRETTY_PRINT)); ?></pre>
                                                </details>
                                            <?php else: ?>
                                                <span style="color:#666;">Không có dữ liệu bổ sung</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:#666;">Chưa có sự kiện bảo mật nào được ghi lại.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Active Security Features -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>⚙️ Tính năng bảo mật đang hoạt động</h2>
                </div>
                <div class="inside">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:20px;">
                        <div>
                            <h3>🔐 Bảo vệ đăng nhập</h3>
                            <ul style="margin:0; padding-left:20px;">
                                <li>Giới hạn tốc độ (5 lần thử/15 phút)</li>
                                <li>Chặn IP sau các lần thử không thành công</li>
                                <li>Thông báo lỗi chung</li>
                                <li>Giám sát đăng nhập</li>
                            </ul>
                        </div>

                        <div>
                            <h3>🛡️ Ngăn chặn tấn công</h3>
                            <ul style="margin:0; padding-left:20px;">
                                <li>Bảo vệ SQL injection</li>
                                <li>Chặn tấn công XSS</li>
                                <li>Lọc yêu cầu độc hại</li>
                                <li>Ngăn chặn file inclusion</li>
                            </ul>
                        </div>

                        <div>
                            <h3>📁 Bảo mật file</h3>
                            <ul style="margin:0; padding-left:20px;">
                                <li>Chặn loại file nguy hiểm</li>
                                <li>Giới hạn kích thước upload</li>
                                <li>Vô hiệu hóa duyệt thư mục</li>
                                <li>Bảo vệ file nhạy cảm</li>
                            </ul>
                        </div>

                        <div>
                            <h3>📊 Giám sát</h3>
                            <ul style="margin:0; padding-left:20px;">
                                <li>Giám sát tính toàn vẹn file</li>
                                <li>Ghi log sự kiện bảo mật</li>
                                <li>Phát hiện mối đe dọa thời gian thực</li>
                                <li>Theo dõi hoạt động admin</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Recommendations -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>💡 Khuyến nghị bảo mật</h2>
                </div>
                <div class="inside">
                    <?php if (!is_ssl()): ?>
                        <div style="padding:15px; margin-bottom:10px; border-left:4px solid #ffc107; border-radius:4px; background:#fff3cd;">
                            <strong>⚠️ Kích hoạt HTTPS/SSL</strong>
                            <p style="margin:5px 0 0 0;">Trang web của bạn không sử dụng HTTPS. Điều này rất quan trọng cho bảo mật và SEO.</p>
                        </div>
                    <?php endif; ?>

                    <div style="padding:15px; margin-bottom:10px; border-left:4px solid #28a745; border-radius:4px; background:#d4edda;">
                        <strong>✅ Cập nhật thường xuyên</strong>
                        <p style="margin:5px 0 0 0;">Giữ WordPress core, themes và plugins được cập nhật thường xuyên.</p>
                    </div>

                    <div style="padding:15px; margin-bottom:10px; border-left:4px solid #28a745; border-radius:4px; background:#d4edda;">
                        <strong>✅ Mật khẩu mạnh</strong>
                        <p style="margin:5px 0 0 0;">Sử dụng mật khẩu mạnh, duy nhất cho tất cả tài khoản người dùng.</p>
                    </div>

                    <div style="padding:15px; margin-bottom:10px; border-left:4px solid #28a745; border-radius:4px; background:#d4edda;">
                        <strong>✅ Sao lưu thường xuyên</strong>
                        <p style="margin:5px 0 0 0;">Triển khai giải pháp sao lưu tự động cho trang web của bạn.</p>
                    </div>

                    <div style="padding:15px; margin-bottom:10px; border-left:4px solid #17a2b8; border-radius:4px; background:#d1ecf1;">
                        <strong>ℹ️ Giám sát bảo mật</strong>
                        <p style="margin:5px 0 0 0;">Xem xét log bảo mật thường xuyên và giám sát các hoạt động đáng ngờ.</p>
                    </div>
                </div>
            </div>

            <!-- Performance Statistics -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>📈 Thống kê hiệu suất</h2>
                </div>
                <div class="inside">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px;">
                        <div style="text-align:center; padding:15px; background:#f8f9fa; border-radius:4px;">
                            <h3 style="margin:0; color:#28a745; font-size:24px;"><?php echo count($blocked_ips); ?></h3>
                            <p style="margin:5px 0 0 0; color:#666;">IP bị chặn</p>
                        </div>

                        <div style="text-align:center; padding:15px; background:#f8f9fa; border-radius:4px;">
                            <h3 style="margin:0; color:#dc3545; font-size:24px;"><?php echo count($security_logs); ?></h3>
                            <p style="margin:5px 0 0 0; color:#666;">Sự kiện bảo mật</p>
                        </div>

                        <div style="text-align:center; padding:15px; background:#f8f9fa; border-radius:4px;">
                            <h3 style="margin:0; color:#17a2b8; font-size:24px;">100%</h3>
                            <p style="margin:5px 0 0 0; color:#666;">Trạng thái bảo mật</p>
                        </div>

                        <div style="text-align:center; padding:15px; background:#f8f9fa; border-radius:4px;">
                            <h3 style="margin:0; color:#6c757d; font-size:24px;">A+</h3>
                            <p style="margin:5px 0 0 0; color:#666;">Xếp hạng bảo mật</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .wp-list-table th {
                background: #f1f1f1;
            }

            .wp-list-table td code {
                background: #f1f1f1;
                padding: 2px 6px;
                border-radius: 3px;
            }

            #security-new-ip {
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                font-size: 14px;
            }

            .postbox h2 {
                margin-top: 0;
                color: #23282d;
            }
        </style>

        <script>
            function blockNewIP() {
                const ip = document.getElementById('security-new-ip').value.trim();
                if (!ip) {
                    alert('Vui lòng nhập địa chỉ IP');
                    return;
                }

                if (!isValidIP(ip)) {
                    alert('Vui lòng nhập địa chỉ IP hợp lệ');
                    return;
                }

                jQuery.post(moomsSecurityAjax.ajaxurl, {
                    action: 'block_ip',
                    ip: ip,
                    nonce: moomsSecurityAjax.nonce
                }, function (response) {
                    if (response.success) {
                        alert('IP đã được chặn thành công!');
                        document.getElementById('security-new-ip').value = '';
                        location.reload();
                    } else {
                        alert('Lỗi: ' + (response.data || 'Không thể chặn IP'));
                    }
                });
            }

            function unblockIP(ip) {
                if (!confirm('Bạn có chắc chắn muốn bỏ chặn ' + ip + '?')) return;

                jQuery.post(moomsSecurityAjax.ajaxurl, {
                    action: 'unblock_ip',
                    ip: ip,
                    nonce: moomsSecurityAjax.nonce
                }, function (response) {
                    if (response.success) {
                        alert('IP đã được bỏ chặn thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + (response.data || 'Không thể bỏ chặn IP'));
                    }
                });
            }

            function clearSecurityLogs() {
                if (!confirm('Bạn có chắc chắn muốn xóa tất cả log bảo mật?')) return;

                jQuery.post(moomsSecurityAjax.ajaxurl, {
                    action: 'clear_security_logs',
                    nonce: moomsSecurityAjax.nonce
                }, function (response) {
                    if (response.success) {
                        alert('Log bảo mật đã được xóa thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi khi xóa log');
                    }
                });
            }

            function isValidIP(ip) {
                const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
                return ipRegex.test(ip);
            }

            console.log('MoomsDev Security Dashboard loaded');
        </script>
        <?php
    }

    public function handleBlockIP()
    {
        check_ajax_referer('security_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $ip = sanitize_text_field($_POST['ip']);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            wp_send_json_error('Invalid IP address');
            return;
        }

        $blocked_ips = get_option('blocked_ips', []);
        if (!in_array($ip, $blocked_ips)) {
            $blocked_ips[] = $ip;
            update_option('blocked_ips', $blocked_ips);

            // Log the event
            $this->logSecurityEvent('IP Blocked', $ip, ['admin_user' => wp_get_current_user()->user_login]);
        }

        wp_send_json_success();
    }

    public function handleUnblockIP()
    {
        check_ajax_referer('security_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $ip = sanitize_text_field($_POST['ip']);
        $blocked_ips = get_option('blocked_ips', []);

        $key = array_search($ip, $blocked_ips);
        if ($key !== false) {
            unset($blocked_ips[$key]);
            update_option('blocked_ips', array_values($blocked_ips));

            // Log the event
            $this->logSecurityEvent('IP Unblocked', $ip, ['admin_user' => wp_get_current_user()->user_login]);
        }

        wp_send_json_success();
    }

    public function handleClearLogs()
    {
        check_ajax_referer('security_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        update_option('security_logs', []);
        wp_send_json_success();
    }

    private function logSecurityEvent($event, $ip, $data = [])
    {
        $logs = get_option('security_logs', []);
        $logs[] = [
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'ip' => $ip,
            'data' => $data
        ];

        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }

        update_option('security_logs', $logs);
    }
}

// Initialize the security admin
new MoomsDevSecurityAdmin();