/**
 * jQuery TopPrompt function [顶部弹出状态提示框]
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2012.11.22.1256
 *  Example
 * 	<code>
 * 	    //Initialization [初始化顶部弹出提示框]
 *      $.initTopPrompt(); //or $.initTopPrompt(300);
 *
 *      //show Top Prompt [显示顶部提示框]
 *      $.showTopPrompt('Loading is complete'); // or $.showTopPrompt(); Maintaining the original content
 *
 *      //hide Top Prompt [隐藏顶部提示框]
 *      $.hideTopPrompt(); // or $.hideTopPrompt('bye'); Maintaining the original content
 *
 *      //如果你有ajax的处理，可以把全局函数注册在这个对象上
 *      //获取提示框的jQuery对象
 *      $.get$ObjTopPrompt().ajaxStart(function ()
 *      {	//显示后台运行状态提示条
 *          $.showTopPrompt('正在与服务器通信中...');
 *      }).ajaxStop(function ()
 *      {	//隐藏后台运行状态提示条
 *          $.hideTopPrompt('处理完成');
 *      });
 * 	</code>
 * @see
 * Tip:
 * Use <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>, there will be bounce.
 * 使用<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>后，出现时会有弹跳效果
 */
;(function($)
{
    /**
     * 是否已初始化过的标志
     * @private
     */
    var frameTopPrompt_init = false;

    $.extend(
    {
        /**
         * 顶部弹出窗口初始化
         * @public
         * @param iWidth int 状态条宽度[默认250],单位px
         * @return void
         */
        initTopPrompt:function(iWidth)
        {
            if (!frameTopPrompt_init)
            {
                iWidth = (typeof(iWidth) == 'undefined')? 200 : parseInt(iWidth);
                iWidth = (iWidth < 150)? 150:iWidth;
                var aBuf = new Array();
                /*关闭按钮图标*/
                var sImgData = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACxSURBVChTVY87CgJBEERbDLyTIqKgIF7AQ3iDDQYvYWS0eCAxMBIMNTARRFjRHau6p6UtKKam+jEfyVC7S/kzF8Y/seOMkned8msGqO7ZWhQ7MvKcYLjpmgG3C5wCM3tPRo+4j7CsO2YCdNnrjFf7VY8x4gpgsHZFv3QbIlYAl8XI2kXw1Lc3+fviO3XGq48FaqaS6fPA7HseQEau25QPCPwZV1fsyOjkgrAPkMPsOKO+C40dIio+BBcAAAAASUVORK5CYII=";
                /*构造css样式*/
                aBuf.push('<style>');
                aBuf.push('#gc-frameTopPrompt{');
                aBuf.push('display:none;'); //默认不显示
                aBuf.push('position:fixed;');
                aBuf.push('z-index:1;');
                aBuf.push('clear:both:');
                aBuf.push('border:1px solid #e5e5e5;');
                aBuf.push('background:#FFFFFF;');
                aBuf.push('padding:5px 15px;');
                aBuf.push('width:'+ iWidth +'px;');
                aBuf.push('left:50%;');
                aBuf.push('margin-left:-'+ parseInt(iWidth/2) +'px;');
                aBuf.push('-moz-border-radius-bottomleft:5px;');
                aBuf.push('-moz-border-radius-bottomright:5px;');
                aBuf.push('-webkit-border-bottom-left-radius:5px;');
                aBuf.push('-webkit-border-bottom-right-radius:5px;');
                aBuf.push('-khtml-border-bottom-left-radius:5px;');
                aBuf.push('-khtml-border-bottom-right-radius:5px;');
                aBuf.push('border-bottom-left-radius:5px;');
                aBuf.push('border-bottom-right-radius:5px;');
                aBuf.push('-moz-box-shadow:rgba(200,200,200,1) 0 4px 18px;');
                aBuf.push('-webkit-box-shadow:rgba(200,200,200,1) 0 4px 18px;');
                aBuf.push('-khtml-box-shadow:rgba(200,200,200,1) 0 4px 18px;');
                aBuf.push('box-shadow:rgba(200,200,200,1) 0 4px 18px;');
                aBuf.push('}</style>');
                aBuf.push('<div id="gc-frameTopPrompt">');
                aBuf.push('<div id="async_TopPrompt_textarea" style="float:left;"></div>');
                aBuf.push('<span style="float:right;cursor:pointer;"><img src="'+ sImgData +'" width="10" height="10" alt="X"></span>');
                aBuf.push('</div>');
                $('body').append(aBuf.join(''));//注入代码
                var $my = $('#gc-frameTopPrompt');//取出gc-frameTopPrompt对象引用
                $my.find('span').find('img').bind('click',function()
                {
                    $my.fadeOut(1000);
                })
                delete aBuf;
                frameTopPrompt_init = true; //初始化完成
            }
        },
        /**
         * 弹出状态条
         * @public
         * @param sMsg 状态条显示内容
         * @return void
         */
        showTopPrompt:function(sMsg)
        {
            var bodyTop = 0;
            if (frameTopPrompt_init)
            {
                var $my = $('#gc-frameTopPrompt');
/*
                if (typeof window.pageYOffset != 'undefined')
                    bodyTop = window.pageYOffset;
                else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat')
                    bodyTop = document.documentElement.scrollTop;
                else if (typeof document.body != 'undefined')
                    bodyTop = document.body.scrollTop;
                $my.css("top", bodyTop);
*/
                $my.css("top", 0);
                if (typeof(sMsg) == 'string')
                    $my.find('#async_TopPrompt_textarea').html(sMsg);
                if (typeof($.ui) != 'undefined' && typeof($.ui.version) != 'undefined')
                    $my.show("bounce", 1100);
                else
                    $my.slideDown("fast");
            }
        },
        /**
         * 隐藏窗口
         * @public
         * @param sMsg 状态条显示内容[可以不填]
         * @return void
         */
        hideTopPrompt:function(sMsg)
        {
            if (frameTopPrompt_init)
            {
                var $my = $('#gc-frameTopPrompt');
                if (typeof(sMsg) == 'string')
                    $my.find('#async_TopPrompt_textarea').html(sMsg);
                $my.fadeOut(3000);
            }
        },
        /**
         * 返回顶部状态条对象的jQuert引用
         * @public
         * @return jQuery
         */
        get$ObjTopPrompt:function()
        {
            if (frameTopPrompt_init)
                return $('#gc-frameTopPrompt');
            else
                return null;
        },
    });
})(jQuery);