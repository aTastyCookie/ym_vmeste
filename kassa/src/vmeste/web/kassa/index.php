<?php 
$sent = $error = false;
if(isset($_POST['email']) && isset($_POST['phone'])) {
	require_once('config/config.php');
	$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, array( PDO::ATTR_PERSISTENT => true));
	$ip = $_SERVER['REMOTE_ADDR'];
	$sth = $pdo->prepare("SELECT time FROM `".DB_NAME."`.`".DB_TABLE_NAME."` WHERE ip = '".$ip."' ORDER BY time DESC LIMIT 1;");
	$sth->execute();
	$row = $sth->fetch();
	if(($row && ($row['time'] + TIME_LIMIT) < time()) || !$row) {
		$body = "Сайт: ".$_POST['site'] . "\r\n" . 
		"Контактное лицо: ".$_POST['contact'] . "\r\n" . 
		"Страна: ".$_POST['country'] . "\r\n" . 
		"Телефон: ".$_POST['phone'] . "\r\n" . 
		"Email: ".$_POST['email'] . "\r\n" . 
		"Терминал: ".$_POST['terminal'] . "\r\n" . 
		"Дата/Время: ". date("H:i:s d M Y") . "\r\n";
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/plain; charset=utf-8' . "\r\n";
		@mail("vmeste@yamoney.ru", "Новая заявка Касса для благотворительности", $body, $headers);
		
		$sth = $pdo->prepare("INSERT INTO `".DB_NAME."`.`".DB_TABLE_NAME."` (`ip` , `time`)
							VALUES ('$ip', ".time().");");
		$sth->execute();
	} else {
		$error = true;
	}
	$sent = true;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>promo</title>
  <link rel="stylesheet" href="css/font.css">
  <link rel="stylesheet" href="css/styles.css" />
  <script src="js/libs/jquery-2.1.1.min.js"></script>
  <script src="js/app.js"></script>
  <!-- google -->
  <script type="text/javascript">
    var _gaq = _gaq || [];

    _gaq.push(['_setAccount', 'UA-19216811-1']);

    _gaq.push(['_setDomainName', '.yandex.ru']);

    _gaq.push(['_addOrganic', 'Mail', 'q']);

    _gaq.push(['_addOrganic', 'Nigma', 'q']);

    _gaq.push(['_addOrganic', 'Webalta', 'q']);

    _gaq.push(['_addOrganic', 'Aport', 'r']);

    _gaq.push(['_addOrganic', 'Gogo', 'q']);

    _gaq.push(['_addOrganic', 'QIP', 'query']);

    _gaq.push(['_trackPageview']);

    (function () {
      var ga = document.createElement('script');
      ga.type = 'text/javascript';
      ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(ga, s);
    })();
  </script>
</head>
<body>
<!-- Яндекс.Метрика -->
<script type="text/javascript">
  (function (w, c) {

    (w[c] = w[c] || []).push(function () {

      try {

        w.yaCounter152220 = new Ya.Metrika({id: 152220,

          clickmap: true,

          trackLinks: true,

          accurateTrackBounce: true});

        w.jQuery('body').trigger('ya-counter-152220-inited');

      } catch (e) {
      }

    });

  })(window, 'yandex_metrika_callbacks');
</script>
<script src="//mc.yandex.ru/metrika/watch_visor.js" type="text/javascript" defer="defer"></script>
<noscript>
  <div><img src="//mc.yandex.ru/watch/152220" style="position:absolute; left:-9999px;" alt="" /></div>
</noscript>
<!-- VisualDNA -->
<script type="text/javascript">
  (function () {

    var s, e;

    s = document.createElement("script");

    s.src = "//a1.vdna-assets.com/analytics.js";

    s.async = true;

    e = document.getElementsByTagName("body")[0];

    e.insertBefore(s, e.firstChild);

    this.VDNA = this.VDNA || {};

    this.VDNA.queue = this.VDNA.queue || [];

  })();

  VDNA.queue.push({

    apiKey: "yandexmoney1393582641971",

    method: "reportPageView"

  });
</script>
<!-- -->
<div class="app">
  <header>
    <div class="content">
      <a class="logo" href="https://money.yandex.ru/"></a>
      <h1>Касса для благотворительности</h1>
      <p>Касса помогает тем, кто дарит надежду.<br>
      Получите готовые инструменты, чтобы собирать<br> 
      деньги на хорошие дела.</p>

      <div class="get_kassa">
        <a href="#kassa_section">Отправить заявку</a>
      </div>
    </div>
  </header>
  <div class="menu_block" id="menu_block">
    <div class="holder">
      <div class="items">
        <a href="#payment_options_section"><i class="cherry_icon"></i><span>Всё включено</span></a>
        <a href="#payments_conditions_section"><i class="chair_icon"></i><span>Отличные условия</span></a>
        <a href="#start_section" class="last"><i class="start_icon"></i><span>Быстрый старт</span></a>

        <div class="hid">
          <div class="phone"><i></i>8 800 555 80 99</div>

          <a class="get_kassa" href="#kassa_section" id="get_kassa_btn">Отправить заявку</a>

        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <a href="#payment_options_section" class="down_arrow"></a>
  </div>
  <div class="payment_options">
    <div class="front" id="payment_options_section">
      <h2>Всё включено</h2>
      <p class="slogan">Принимайте взносы самыми популярными способами.</p>

      <div class="items">
        <div class="item"><i class="bc"></i>
          <p>Банковские карты</p><span>Visa, MasterCard <br>и Maestro любого банка мира.</span></div>
        <div class="item"><i class="yd"></i>
          <p>Яндекс.Деньги</p><span>18 млн пользователей <br>с электронными кошельками.</span></div>
        <div class="item"><i class="wm"></i>
          <p>WebMoney</p><span>Популярная система международных расчетов.</span></div>
        <div class="item"><i class="nalik"></i>
          <p>Наличные</p><span>Через терминалы <br>и салоны связи — <br>более 170 тысяч пунктов.</span></div>
        <div class="item"><i class="sms"></i>
          <p>SMS</p><span>C номеров Билайн, МегаФон, МТС.</span></div>
        <div class="item"><i class="mobile"></i>
          <p>Мобильный терминал</p><span>Для приема карт <br>с помощью смартфона <br>или планшета.</span></div>
      </div>
    </div>
    <div class="over"></div>
    <a href="#payments_conditions_section" class="down_arrow"></a>

    <div class="bg"></div>
  </div>
  <div class="payments_conditions">
    <div class="over"></div>
    <div class="front" id="payments_conditions_section">
      <h2>Отличные условия</h2>
      <p class="slogan">Всё на виду: сравнивайте и принимайте решение.</p>

      <div class="items">
        <div class="item zero">
          <i></i>
          <p>Бесплатное подключение</p>
          <span>И никакой абонентской платы</span>
        </div>
        <div class="item percent">
          <i></i>
          <p>Доступные<br> тарифы</p>
          <span>От 2,5% за успешную<br> операцию</span>
        </div>
        <div class="item inf fright">
          <i></i>
          <p>Рекуррентные платежи</p>
          <span>А также другие встроенные опции</span>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <a href="#start_section" class="down_arrow"></a>
  </div>
  <div class="start">
    <div class="over"></div>
    <a href="#kassa_section" class="down_arrow"></a>

    <div class="front" id="start_section">
      <h2>Быстрый старт</h2>
      <p class="slogan">Не нужно ничего настраивать &mdash; берите и пользуйтесь.</p>

      <div class="items">
        <div class="item i1">
          <i></i>
          <p>Расскажите нам<br> о своём проекте</p>
        </div>
        <div class="item i2">
          <i></i>
          <p>Получите страницу<br> через несколько дней</p>
        </div>
        <div class="item i3">
          <i></i>
          <p>Готово! Делитесь ссылкой<br> и принимайте пожертвования</p>
        </div>
        <div class="steps_line"></div>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
  <div class="kassa">
    <div class="holder" id="kassa_section">
      <h2>Расскажите о себе</h2>

      <div class="kassa_form">
        <form autocomplete="off" id="form" method="POST" onsubmit="yaCounter152220.reachGoal('CHARITY_FORM_SUBMIT'); return true;"  enctype="application/x-www-form-urlencoded" action="<?php echo $_SERVER['PHP_SELF']; ?>#kassa_section">
          <div class="row">
            <div class="item">
              <label>Адрес сайта</label>
              <input type="text" placeholder="http://site.ru" name="site">
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="row">
            <div class="item">
              <label>Контактное лицо</label>
              <input type="text" placeholder="Иванов Иван" name="contact">
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="row">
            <div class="item">
              <label>Страна</label>
              <select name="country">
                <option value="Россия">Россия</option>
                <option value="Белоруссия">Белоруссия</option>
                <option value="Казахстан">Казахстан</option>
                <option value="Украина">Украина</option>
                <option value="Другая">Другая</option>
              </select>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="row">
            <div class="item">
              <label>Номер телефона</label>
              <input type="text" placeholder="+79601234567" id="phone" name="phone">

              <div class="error" id="phone_error"><span>Здесь что-то не так. Проверьте номер и попробуйте еще раз.</span></div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="row">
            <div class="item">
              <label>Почта</label>
              <input type="text" placeholder="address@domain.ru" id="email" name="email">

              <div class="error" id="email_error"><span>Здесь что-то не так. Проверьте адрес и попробуйте еще раз.</span></div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="row">
            <div class="item boolean">
                <label for="terminal">
                  <input type="checkbox" id="terminal" <?php if(!$sent) echo "checked"; ?> value="Да" name="terminal">
                  Хочу мобильный терминал
                </label>
              <div class="info_tip">
                <i class="info_icon"></i>

                <div class="tip_box">
                  <div class="tip_text">
                    <a class="close" href="#"></a>

                    Небольшое устройство, которое пригодится вам на 
                    благотворительных мероприятиях. Подключите его<br/> 
                    к смартфону и принимайте пожертвования с
                  банковских карт.
                  </div>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="row">
            <div class="item">
              <a name="form_submit"></a>
              <?php if($sent) { ?>
                <?php if(!$error) { ?>
                    <script type="text/javascript">
                        document.documentElement.dispatchEvent("charity_success");
                    </script>
                <?php } ?>
                <div class="submit_box done">
              <?php } else {?>
                <div class="submit_box">
              <?php } ?>
              <?php if(!$error) { ?>
              	<p class="success_submit">Приятно познакомиться! Менеджер перезвонит вам<br>
                  в течение двух дней, чтобы вы успели подготовить текст и картинки для вашей страницы.</p>
              <?php } else { ?>
              	<p class="error_submit">С Вашего IP-адреса недавно уже была отправлена заявка. Попробуйте снова немного позже.</p>
              <?php } ?>
                  <button type="submit" class="submit" id="form_submit" name="send">Отправить заявку</button>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <footer>
    <div class="content">
      <div class="phone"><i></i>8 800 555 80 99</div>
      <a class="email" href="mailto:vmeste@yamoney.ru"><i></i>Написать</a>

      <div class="copy">
        <a href="#">О компании</a>
        <span>&copy; 2014</span>
        <a href="#">Яндекс</a>
      </div>
    </div>
  </footer>
</div>
</body>
</html>
