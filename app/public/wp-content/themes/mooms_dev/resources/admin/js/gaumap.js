var mms = {
  init: function () {
    this.initSocialLogin();
  },
  initSocialLogin: function () {
    $(".gm-social-login__item").click(function (e) {
      e.preventDefault();
      let url = $(this).data("url");
      let title = $(this).data("title");
      let w = 800;
      let h = 400;
      let left = screen.width / 2 - w / 2;
      let top = screen.height / 2 - h / 2;
      window.open(
        url,
        title,
        "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=" +
          w +
          ", height=" +
          h +
          ", top=" +
          top +
          ", left=" +
          left
      );
    });
  },
  socialLoginReturn: function (result) {
    if (result.success === true) {
      window.location.href = result.redirect;
    }
  },
};

$(function () {
  mms.init();
});
