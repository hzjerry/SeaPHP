/**
 * jQuery url get parameters function [获取URL的GET参数值]
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2012.12.11.1400
 *  Example
 * 	<code>
 *      var GET = $.urlGet(); //获取URL的Get参数
 *      var id = GET['id']; //取得id的值
 * 	</code>
 */
;(function($)
{
    $.extend(
    {
        /**
         * url get parameters
         * @public
         * @return array()
         */
        urlGet:function()
        {
            var aQuery = window.location.href.split("?");//取得Get参数
            var aGET = new Array();
            if(aQuery.length > 1)
            {
                var aBuf = aQuery[1].split("&");
                for(var i=0, iLoop = aBuf.length; i<iLoop; i++)
                {
                    var aTmp = aBuf[i].split("=");//分离key与Value
                    aGET[aTmp[0]] = aTmp[1];
                }
            }
            return aGET;
        },
    });
})(jQuery);