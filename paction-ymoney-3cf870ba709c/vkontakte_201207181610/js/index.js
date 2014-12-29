//Преобразует в строку в число
function toHumanNumber(num)
{
	return Number(num.replace(' ', '').replace(',', '.')); 
}

//Получаем количествово млн.
function mln(num)
{
	if(num%1000000>99999) {
		return (num/1000000).toFixed(1);
	} else {
		return Math.round(num/1000000);
	}
}

//Получаем количествово млрд.
function mlrd(num)
{
	if(num%1000000000>99999999) {
		return (num/1000000000).toFixed(1);
	} else {
		return Math.round(num/1000000000);
	}
}

function setrightwreqC(id, req, sum) 
{
	if(req>999999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
                $('.big_block .bb_collected')
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
                $('.big_block .bb_collected')
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
                $('.big_block .bb_collected')
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+' млрд</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} 

	else if(req<=999999999 && req<=999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
                $('.big_block .bb_collected')
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else if(req<=999999999 && req<=999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
                $('.big_block .bb_collected')
		.html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
                $('.big_block .bb_collected')
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	}
}

function setrightworeqC(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
	} else {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
	}
}

function setrightWoReqWBalC(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
	} else {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
	}
}

function setrightWithReqWoBalC(id, req, sum) 
{
	if(req>999999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+' млрд</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+' млрд</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} 

	else if(req<=999999999 && req<999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else if(req<=999999999 && req<=999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	}
}

function setrightwreq(id, req, sum) 
{
	if(req>999999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+' млрд</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+' млрд</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} 

	else if(req<=999999999 && req<999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else if(req<=999999999 && req<999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	}
}

function setrightworeq(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
	} else {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
	}
}

function setrightWoReqWBal(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span>млрд руб.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн руб.</span>');
	} else {
		$('#mb_collected' + id).html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
		$('.big_block .bb_collected').html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span>');
	}
}

function setrightWithReqWoBal(id, req, sum) 
{
	if(req>999999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+'</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ mlrd(req) + '</span> <span>млрд руб.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+' млрд</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+' млрд</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> <span>руб.</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> из <span class="s2">'
    		+ mln(req) + '</span> <span>млн руб.</span>');
	} 

	else if(req<=999999999 && req<999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span> млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mlrd(sum)+'</span> <span> млрд</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else if(req<=999999999 && req<999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+sum+'</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
		$('.big_block .bb_collected')
                .html('Собрано <span class="s1">'+mln(sum)+'</span> <span>млн</span> из <span class="s2">'
    		+ req + '</span> <span>руб.</span>');
	}
}

//Форматирует строку значения Всего
function formatMoney(newbal) {
    var newMoneyVal;
    if(newbal >= 1000000000) {
        newMoneyVal = mlrd(newbal) + ' млрд. ';
    } else if(newbal >= 1000000) {
        newMoneyVal = mln(newbal) + ' млн. ';
    } else {
        newMoneyVal = newbal;
    }
    
    return newMoneyVal;
}