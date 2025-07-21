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


document.addEventListener('DOMContentLoaded', function () {
  // Tìm trường password bằng thuộc tính data-field
  const passwordField = document.querySelector('input[data-field="password-field"]');
  if (passwordField) {
      // Tạo nút toggle
      const toggleButton = document.createElement('button');
      toggleButton.type = 'button';
      toggleButton.innerHTML = 'Show'; // Biểu tượng con mắt
      toggleButton.style.marginLeft = '5px';
      toggleButton.style.cursor = 'pointer';
      toggleButton.setAttribute('data-toggle', 'password-toggle'); // Thêm data-* cho nút toggle

      // Chèn nút toggle ngay sau trường password
      passwordField.parentNode.appendChild(toggleButton);

      // Xử lý sự kiện click để toggle hiển thị/ẩn
      toggleButton.addEventListener('click', function () {
          if (passwordField.type === 'password') {
              passwordField.type = 'text';
              toggleButton.innerHTML = 'Hide'; // Biểu tượng ẩn
          } else {
              passwordField.type = 'password';
              toggleButton.innerHTML = 'Show'; // Biểu tượng hiển thị
          }
      });
  }
});