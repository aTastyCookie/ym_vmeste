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
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
                $('.big_block .bb_collected')
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
                $('.big_block .bb_collected')
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
                $('.big_block .bb_collected')
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+' billion</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} 

	else if(req<=999999999 && req<=999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
                $('.big_block .bb_collected')
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else if(req<=999999999 && req<=999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
                $('.big_block .bb_collected')
		.html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
                $('.big_block .bb_collected')
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	}
}

function setrightworeqC(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
	} else {
		$('#mb_collected' + id).html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
	}
}

function setrightWoReqWBalC(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
	} else {
		$('#mb_collected' + id).html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
	}
}

function setrightWithReqWoBalC(id, req, sum) 
{
	if(req>999999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+' billion</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+' billion</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} 

	else if(req<=999999999 && req<999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else if(req<=999999999 && req<=999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	}
}

function setrightwreq(id, req, sum) 
{
	if(req>999999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+' billion</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+' billion</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} 

	else if(req<=999999999 && req<999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else if(req<=999999999 && req<999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	}
}

function setrightworeq(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
	} else {
		$('#mb_collected' + id).html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
	}
}

function setrightWoReqWBal(id, req, sum) 
{
	if(sum>=1000000 && sum>=1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mlrd(sum)+'</span> <span>billion rub.</span>');
	} else if (sum>=1000000 && sum<1000000000) {
		$('#mb_collected' + id).html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+mln(sum)+'</span> <span>million rub.</span>');
	} else {
		$('#mb_collected' + id).html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
		$('.big_block .bb_collected').html('Collected <span class="s1">'+sum+'</span> <span>rub.</span>');
	}
}

function setrightWithReqWoBal(id, req, sum) 
{
	if(req>999999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+'</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} else if(req>999999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ mlrd(req) + '</span> <span>billion rub.</span>');
	} 

	else if(req<=999999999 && req>999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+' billion</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+' billion</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> <span>rub.</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} else if(req<=999999999 && req>999999 && sum<1000000000 && sum>=1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> of <span class="s2">'
    		+ mln(req) + '</span> <span>million rub.</span>');
	} 

	else if(req<=999999999 && req<999999 && sum>=1000000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mlrd(sum)+'</span> <span> billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mlrd(sum)+'</span> <span> billion</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else if(req<=999999999 && req<999999 && sum<1000000000 && sum<1000000) {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+sum+'</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	} else {
		$('#mb_collected' + id)
		.html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
		$('.big_block .bb_collected')
                .html('Collected <span class="s1">'+mln(sum)+'</span> <span>million</span> of <span class="s2">'
    		+ req + '</span> <span>rub.</span>');
	}
}

//Форматирует строку значения Всего
function formatMoney(newbal) {
    var newMoneyVal;
    if(newbal >= 1000000000) {
        newMoneyVal = mlrd(newbal) + ' billion ';
    } else if(newbal >= 1000000) {
        newMoneyVal = mln(newbal) + ' million ';
    } else {
        newMoneyVal = newbal;
    }
    
    return newMoneyVal;
}