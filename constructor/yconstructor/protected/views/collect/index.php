<?php
/* @var $this PagesController */
/* @var $model Pages */
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo htmlspecialchars($model->title); ?></title>
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/payment.css" />
    <script src="/js/libs/jquery-2.1.1.min.js"></script>
    <script src="/js/payment.js"></script>
    <script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script>
    <!-- v 2014-10-27-004  -->
</head>
<body>
<div class="payment_page <?php echo $model->background ? $model->background : 'theme1'; ?> styled">
    <div class="page_kit">
        <section>
            <h1><?php echo htmlspecialchars($model->title); ?></h1>
        </section>
        <div class="sep_line"></div>
        <section>
            <div class="tile">
                <p><?php echo htmlspecialchars($model->text); ?></p>
            </div>
        </section>
        <div class="page_form" id="page_form">
            <form method="post" action="https://money.yandex.ru/quickpay/confirm.xml" autocomplete="off">
                <input type="hidden" name="receiver" value="<?php echo $model->account; ?>">
                <?php /* QWEB-14768
                <input type="hidden" name="formcomment" value="Форма приниматель: <?php echo htmlspecialchars($model->title); ?>">
                <input type="hidden" name="short-dest" value="Форма приниматель: <?php echo htmlspecialchars($model->title); ?>">
                */ ?>
                <input type="hidden" name="quickpay-form" value="shop">
                <input type="hidden" name="targets" value="<?php echo htmlspecialchars($model->title); ?>">
                <input type="hidden" name="need-fio" value="<?php echo $model->field_name ? 'true' : 'false'; ?>">
                <input type="hidden" name="need-phone" value="<?php echo $model->field_phone ? 'true' : 'false'; ?>">
                <input type="hidden" name="need-email" value="<?php echo $model->field_email ? 'true' : 'false'; ?>">
                <div class="page_data <?php if($model->photo):?> has_image<?php endif;?>" id="page_data">
                    <div class="holder">
                        <div class="amount_box">
                            <label>Мой взнос</label>
                            <div class="input">
                                <div class="amount">
                                    <input type="text" name="sum" value="<?php echo $model->default_amount; ?>">
                                    <span class="rouble">a</span>
                                </div>
                                <div class="payment_type">
                                    <label>
                                        <input type="radio" name="paymentType" value="AC" checked>
                                        <span class="card"></span>
                                    </label>
                                    <label>
                                        <input type="radio" name="paymentType" value="PC">
                                        <span class="yd"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="make_payment">
                                <button type="submit">Перевести деньги</button>
                            </div>
                        </div>
                        <div class="image_box" id="image_box">
                            <div class="image_holder">
                                <div class="image"><?php if($model->photo):?> <img src="/images/uploaded/<?php echo $model->photo; ?>"><?php endif;?></div>
                                <div class="mask"></div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="page-buffer"></div>
</div>
<footer>
    <section>
        <div class="share_box">
            <p>Поделиться:</p>

            <div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="vkontakte,facebook,twitter"></div>
        </div>
        <div class="right_side">
            <a href="http://yasobe.ru/">Сделать свою страницу для сбора денег</a>
        </div>
    </section>
</footer>
</body>
</html>

