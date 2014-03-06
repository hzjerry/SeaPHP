/**
 * jQuery htmlendoce and htmldecode [Html字符串编码解码]
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2013.09.30.085000
 *  Example
 * 	<code>
 *      var jsonTmp = {msg:'<img src=""><a href="">test data</a>'};
 *      sTmp = '<div>'+ $.htmlencode(jsonTmp.msg) +'</div>'
 *      $('#show_msg').html(sTmp);
 * 	</code>
 */
;(function($)
{
    $.extend(
    {
        /**
         * html编码输出
         * @param string s 内容
         * @return html 格式编码
         */
        htmlencode:function(s)
        {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(s));
            return div.innerHTML;
        },
        /**
         * html解码输出
         * @param string s 内容
         * @return html 格式编码
         */
        htmldecode:function(s)
        {
            var div = document.createElement('div');
            div.innerHTML = s;
            return div.innerText || div.textContent;
        },
    });
})(jQuery);