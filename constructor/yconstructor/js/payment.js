var Payment = (function (app, $) {

  function visual() {
  }

  app.init = function () {
    visual();
  };

  $(function () {
    app.init();
  });

  return app;
}(Payment || {}, $));
