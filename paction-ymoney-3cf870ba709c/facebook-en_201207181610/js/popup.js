/*
PARAMS REVIEW
tpl_id - popup object to be shown (jQuery style selector);

popup_id - necessary parap that stands for current popup 'id' attribute;

height - height of CONTENT area (.p_cn_wrapper .p_pad);

width - outer width of popup or 'auto' which sets width automatically by content

overflow - if param.height is defined, scrollbar may appear if cotnent is higher than popup content area (.p_cn_wrapper .p_pad);

title - custom popup title (.p_title) which overrites everything in mentioned block;

content_container - selector of block which innerHTML should be inserted to popup content area;

content - text which is inserted to popup content area;

info - text for info box (appears if param exists);

info_container - same as info but take innerHTML of block by selector;

layout - wraps selected elemen with popup layout (CSS selector);

custom_btn - object that consists of additional buttons exept for 'Cancel' button in nav panel;
EXAMPLE:
***********************
$('#show_popup').click(function(e) {
	e.preventDefault();
	$(this).fb_popup({
		content: 'Teros. Integer <a href="#">pellentesque</a> laoreet dui.',
		content: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ullamcorper eros nec ligula bibendum lacinia. Phasellus sagittis consequat ipsum, ut dapibus nisi laoreet ac. Etiam rhoncus odio vitae tellus varius pretium. Mauris vel nisl sem. Phasellus a nunc eros. Integer pellentesque laoreet dui. Vestibulum dignissim molestie ultrices. Etiam quis scelerisque felis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Duis mauris ipsum, placerat eu egestas eget, blandit ut ante. Mauris tristique mauris eu erat cursus elementum euismod neque hendrerit. Vestibulum porta consectetur leo sit amet posuere. Quisque ornare dictum ipsum, id rhoncus lacus consequat sit amet. Praesent sit amet lacus id odio ornare pretium. Quisque a libero in lectus auctor fringilla ac vehicula orci. In hac habitasse platea dictumst. Morbi suscipit pulvinar sagittis. Proin arcu magna, lacinia sed interdum sit amet, consequat in risus. Nulla at leo sit amet sapien tincidunt porttitor.',
		title: 'Sample title',
		info: 'Custom info text',
		popup_id: 'popup_tpl',
		width: 500,
		height: 100,
		overflow: 'auto',
		custom_btn: {
			btn1 : '<a href="#" class="p_btn" onclick="alert(\'ok\'); return false;">Button 1</a>',
			btn2 : '<a href="#" class="p_btn" onclick="alert(\'ok 2\'); return false;">Button 2</a>',
			btn_description_or_name : '<a href="#" class="p_btn" onclick="alert(\'ok 3\'); return false;">Button 3</a>'
		}
	});
});
**************************

*/


(function( $ ){
	$.fn.fb_popup = function(params) {
		var popup_id = '#'+params.popup_id;
		$('.popup_tpl').remove(); // removes existing popups from DOM
		function insert(param_1, param_2) { // param_1 is custom; param_2 is innerHTML of predefined selector
			var output;
			if(param_1) {
				output = param_1;
			}
			if (param_2) {
				output = $(param_2).html();
			}
			return output;
		}
		if(params.custom_btn) {
			var btns = '';
			for(btn in params.custom_btn) {
				btns += params.custom_btn[btn];
			}
		}
		var popup_html = {
			p1: '<div class="popup_tpl" id="'+((params.popup_id) ? params.popup_id : '')+'">',
			p2:	'<div class="p_top">',
			p3:		'<div class="p_bg1"></div>',
			p4:		'<div class="p_bg2"></div>',
			p5:	'</div>',
			p6:	'<div class="cn_popup">',
			p7:		'<div class="p_cn_wrapper">',
			p8:			(!params.layout) ? '<div class="p_title">' : '',
			p9:				(!params.layout) ? insert(params.title, params.title_container) : '',
			p10:			(!params.layout) ? '</div>' : '',
			p11:			(!params.layout) ? ((params.info || params.info_container) ? '<div class="p_info_bar" style="display:block;">'+insert(params.info, params.info_container)+'</div>' : '') : '',
			p12:			'<div class="p_pad">',
			p13:			(!params.layout) ? ((params.content || params.content_container) ? insert(params.content, params.content_container) : '') : $(params.layout).html(),
			p14:			'</div>',						p15:			'<div class="p_close">',						p16:				'<a href="#" class="close_x">x</a>',						p17:			'</div>',
			p18:		'</div>',
			p19:		'<div class="p_nav">',
			p20:			'<div class="p_nav_inner">',
			p21:				'<a href="#" class="p_btn p_cancel">ОК</a>',
			p22:				(btns) ? btns : '',
			p23:			'</div>',
			p24:		'</div>',
			p25:	'</div>',
			p26:	'<div class="p_bottom">',
			p27:		'<div class="p_bg1"></div>',
			p28:		'<div class="p_bg2"></div>',
			p29:	'</div>',
			p30: '</div>'
		};
                if(params.notshowpanel == true) {
                    popup_html.p19 = popup_html.p20 = popup_html.p21 = popup_html.p22 = popup_html.p23 = popup_html.p24 = '';
                }
                if(params.notshowok == true) {
                    popup_html.p21 = '';
                }
		var popupHTML = '';
		for(key in popup_html) {
			popupHTML += popup_html[key];
		}
		$('body').append(popupHTML);
		$(popup_id).show();
		
		
		if(params.width) {
			if(params.width == 'auto') {
				$(popup_id).width( $('.p_cn_wrapper .p_pad', params.tpl_id).width()+20 );
			}
			$(popup_id).width(params.width);
		}
		if(params.height) {
			$(popup_id+' .p_cn_wrapper .p_pad').height(params.height-20);
		}
		if(params.overflow) {
			$(popup_id+' .p_cn_wrapper .p_pad').css('overflow', params.overflow);
		}
		if(params.minheight) {
			$(popup_id+' .p_cn_wrapper .p_pad').css('min-height', params.minheight);
		}
		popup_position(popup_id); // set central position of popup
		$('.p_cancel, .close_x', popup_id).click(function() { // close popup
			FB.Canvas.setAutoGrow();
			$(popup_id).fadeOut(400);
		});
	};
	function popup_position(obj) {
	$(obj).css({
		top: 150,
		//top: ( $(window).height() - $(obj).height() )/2,
		left: ( $(window).width() - $(obj).width() )/2
	});
}

})( jQuery );
