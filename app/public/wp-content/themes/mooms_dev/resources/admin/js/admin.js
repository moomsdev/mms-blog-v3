let scripts = {
  frame: null,
  init: function () {
    this.frame = wp.media({
      title: "Select image",
      button: {
        text: "Use this image",
      },
      multiple: false,
    });
  },
  disableTheGrid: function () {
    jQuery("form#posts-filter").append(`
            <div class="gm-loader" style="position:absolute;z-index:99999999;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:center;background-color:rgba(192,192,192,0.51);color:#000000">
                Updating
            </div>
        `);
  },
  enableTheGrid: function () {
    jQuery("form#posts-filter").find(".gm-loader").remove();
  },
};

// Xử lý khi nhấn vào nút thay đổi ảnh đại diện bài viết
jQuery(document).on("click", "[data-trigger-change-thumbnail-id]", function () {
  let postId = jQuery(this).data("post-id");
  let thisButton = jQuery(this);

  let frame = wp.media({
    title: "Select image",
    button: {
      text: "Use this image",
    },
    multiple: false,
  });

  frame.on("select", function () {
    let attachment = frame.state().get("selection").first().toJSON();
    let attachmentId = attachment.id;
    let originalImageUrl = attachment.url || null;

    scripts.disableTheGrid();

    jQuery
      .post(
        "/wp-admin/admin-ajax.php",
        {
          action: "update_post_thumbnail_id",
          post_id: postId,
          attachment_id: attachmentId,
        },
        function (response) {
          if (response.success === true) {
            let imgElement = thisButton.find("img");

            if (imgElement.length) {
              // Nếu có ảnh, cập nhật src
              imgElement.attr("src", originalImageUrl);
            } else {
              // Nếu không có ảnh, thay thế text bằng ảnh mới
              thisButton
                .find(".no-image-text")
                .replaceWith(`<img src="${originalImageUrl}" alt="Thumbnail">`);
            }
          } else {
            alert(response.data.message);
          }
          scripts.enableTheGrid();
        }
      )
      .fail(function () {
        alert("Failed to update image.");
        scripts.enableTheGrid();
      });
  });

  frame.open();
});

// Khi trang tải, kiểm tra ảnh đại diện
jQuery(function () {
  scripts.init();

  jQuery("[data-trigger-change-thumbnail-id]").each(function () {
    let imageElement = jQuery(this).find("img");

    if (!imageElement.attr("src") || imageElement.attr("src") === "") {
      imageElement.replaceWith('<div class="no-image-text">Choose Image</div>');
    }
  });
});

// Security Dashboard Functions
function blockNewIPSecurity() {
  const ip = document.getElementById("security-new-ip").value.trim();
  if (!ip) {
    alert("Vui lòng nhập địa chỉ IP");
    return;
  }

  if (!isValidIP(ip)) {
    alert("Vui lòng nhập địa chỉ IP hợp lệ");
    return;
  }

  // Sử dụng ajax_object nếu có, nếu không thì dùng ajaxurl global
  const ajaxurl =
    typeof ajax_object !== "undefined" ? ajax_object.ajaxurl : window.ajaxurl;
  const security_nonce =
    typeof ajax_object !== "undefined" ? ajax_object.nonce : "";

  fetch(ajaxurl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "block_ip",
      ip: ip,
      nonce: security_nonce,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("IP đã được chặn thành công!");
        document.getElementById("security-new-ip").value = "";
        location.reload();
      } else {
        alert("Lỗi: " + (data.data || "Không thể chặn IP"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Lỗi khi chặn địa chỉ IP");
    });
}

function unblockIPSecurity(ip) {
  if (!confirm("Bạn có chắc chắn muốn bỏ chặn " + ip + "?")) return;

  const ajaxurl =
    typeof ajax_object !== "undefined" ? ajax_object.ajaxurl : window.ajaxurl;
  const security_nonce =
    typeof ajax_object !== "undefined" ? ajax_object.nonce : "";

  fetch(ajaxurl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "unblock_ip",
      ip: ip,
      nonce: security_nonce,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("IP đã được bỏ chặn thành công!");
        location.reload();
      } else {
        alert("Lỗi: " + (data.data || "Không thể bỏ chặn IP"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Lỗi khi bỏ chặn địa chỉ IP");
    });
}

function clearSecurityLogsDashboard() {
  if (!confirm("Bạn có chắc chắn muốn xóa tất cả log bảo mật?")) return;

  const ajaxurl =
    typeof ajax_object !== "undefined" ? ajax_object.ajaxurl : window.ajaxurl;
  const security_nonce =
    typeof ajax_object !== "undefined" ? ajax_object.nonce : "";

  fetch(ajaxurl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "clear_security_logs",
      nonce: security_nonce,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Log bảo mật đã được xóa thành công!");
        location.reload();
      } else {
        alert("Lỗi khi xóa log");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Lỗi khi xóa log bảo mật");
    });
}

function isValidIP(ip) {
  const ipRegex =
    /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
  return ipRegex.test(ip);
}

// Debug info when DOM is ready
jQuery(document).ready(function () {
  if (typeof ajax_object !== "undefined") {
    console.log("Security Dashboard: ajax_object loaded", ajax_object);
  } else {
    console.log("Security Dashboard: ajax_object not found, using fallback");
  }
});
