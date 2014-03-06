/**
 * jQuery countdown Words[输入框字数倒计时]
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2013.09.30.1614
 *  Example
 * 	<code>
 * 	    <textarea id="id_sms_msg" rows="5" cols="40" placeholder="输入短信内容" onkeyup="$.countdownWords($(this), $('#show_counter'), 20);"></textarea>
 * 	    剩余<span id="show_counter">0</span>个字
 * 	</code>
 */
;(function($)
{
    $.extend(
    {
        /**
         * 字数限制倒计显示控制（字符模式，不区分全角与半角）
         * @public
         * @param jQueryObj $Input 输入对象容器
         * @param jQueryObj $Counter 字数显示对象
         * @param int iMaxLen 最大长度
         * @return string
         */
        countdownWords:function($Input, $Counter, iMaxLen)
        {
            if ($Input.val().length > iMaxLen)
                $Input.val( $Input.val().substr(0, iMaxLen) );
            $Counter.text(iMaxLen - $Input.val().length);
        },
    });
})(jQuery);