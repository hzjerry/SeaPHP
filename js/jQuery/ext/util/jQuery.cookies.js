/**
 * jQuery url get parameters function [获取cookie参数的值]
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2014.09.22.2111
 *  Example
 * 	<code>
 *      var sVal = $.getCookie('key'); //获取cookie参数的值
 * 	</code>
 */
;(function($)
{
    $.extend(
    {
        /**
         * url get parameters
         * @public
         * @param string name cookie关键字
         * @return string
         */
        getCookie:function(name)
        {
            if(document.cookie.length > 0)
            {
                start = document.cookie.indexOf(name + '=');
                if( start != -1)
                {
                    start = start + name.length + 1;
                    end = document.cookie.indexOf(';',start);
                    if( end == -1)
                        end = document.cookie.length;
                    return decodeURIComponent(document.cookie.substring(start,end));
                }
            }
            return '';
        },
    });
})(jQuery);