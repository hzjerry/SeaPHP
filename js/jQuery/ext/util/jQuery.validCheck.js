/**
 * 校验函数
 * @character_set UTF-8
 * @author Jerry.li(hzjerry@gmail.com)
 * @version 1.2013.08.04.1264
 *  Example
 * 	<code>
 *      $.isEmail(str); //检查是否为邮件地址
 *      $.isSafeText(str); //检查是否存在不安全字符
 *      $.isDate(str); //检查是否为日期(格式: yyyy-mm-dd)
 *      $.isTime(str); //检查是否为时间(格式: hh:ii:ss)
 *      $.isDateTime(str); //检查是否为日期时间(格式: yyyy-mm-dd hh:ii:ss)
 *      $.isIPV4(str); //检查是否为IPV4
 *      $.isURL(str); //检查是否为URL资源地址
 *      $.isMobilePhoneCn(str); //检查是否为手机号码（中国）
 *      $.isFloat(str); //检查是否为浮点数
 *      $.isInt(str); //检查是否为整形
 *      $.chkNumRange(str, fMin, fMax); //检查数字值的范围
 *      $.chkStrWidth(str, iMin, iMax); //检查字符串的宽度范围(字符版)
 *      $.chkMbStrWidth(str, iMin, iMax); //检查字符串的宽度范围(字节版)
 *      $.mbstrlen(str); //字符串长度统计(字节版:支持GBK|UTF8)
 * 	</code>
 */
;(function($)
{
    $.extend(
    {
        /**
         * 检查是否为邮件地址
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isEmail:function(str)
        {
            return (/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/).test(str);
        },

        /**
         * 检查是否存在不安全字符
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isSafeText:function(str)
        {
            if (str.lenght == 0)
                return false;

            var i, j;
            var fibdn = new Array ("'","\\",";","/","\"");

            for (i=0; i < fibdn.length; i++)
            {
                for (j=0; j < str.length; j++)
                {
                    if (str.charAt(j) == fibdn[i])
                        return false;
                }
            }
            return true;
        },

        /**
         * 检查是否为日期(格式: yyyy-mm-dd)
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isDate:function(str)
        {
            return (/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/).test(str);
        },

        /**
         * 检查是否为时间(格式: hh:ii:ss)
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isTime:function(str)
        {
            return (/^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/).test(str);
        },

        /**
         * 检查是否为日期时间(格式: yyyy-mm-dd hh:ii:ss)
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isDateTime:function(str)
        {
            var sDate = str.substr(0, 10);
            var sTime = str.substr(11, 8);
            return ($.isDate(sDate) && $.isTime(sTime));
        },

        /**
         * 检查是否为IPV4
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isIPV4:function(str)
        {
            return (/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])(\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])){3}$/).test(str);
        },

        /**
         * 检查是否为URL资源地址
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isURL:function(str)
        {
            var sRegex = "^((https|http|ftp|rtsp|mms)?://)"
            + "(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" // ftp的user@
            + "(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP形式的URL- 199.194.52.184
            + "|" // 允许IP和DOMAIN（域名）
            + "([0-9a-z_!~*'()-]+\.)*" // 域名- www.
            + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." // 二级域名
            + "[a-z]{2,6})" // first level domain- .com or .museum
            + "(:[0-9]{1,4})?" // 端口- :80
            + "((/?)|" // a slash isn't required if there is no file name
            + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$";
            return new RegExp(sRegex, 'i').test(str);
        },

        /**
         * 检查是否为手机号码（中国）
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isMobilePhoneCn:function(str)
        {
            return (/^(0|86|17951)?(13[0-9]|15[012356789]|18[0236789]|14[57])[0-9]{8}$/).test(str);
        },

        /**
         * 检查是否为浮点数
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isFloat:function(str)
        {
            return (/^(-?\d+)(\.\d+)?$/).test(str);
        },

        /**
         * 检查是否为整形
         * @public
         * @param string str 检查的关键字
         * @return bool
         */
        isInt:function(str)
        {
            return (/^-?\d+$/).test(str);
        },

        /**
         * 检查数字值的范围
         * @public
         * @param string str 检查的关键字
         * @param float fMin 最小值
         * @param float fMax 最大值
         * @return bool
         */
        chkNumRange:function(str, fMin, fMax)
        {
            var fCheckVal = parseFloat(str);

            if (isNaN(fCheckVal)) return false;//输入值非法

            if (fCheckVal <= fMax && fCheckVal >= fMin)
                return true;
            else
                return false;
        },

        /**
         * 检查字符串的宽度范围(字符版)
         * @public
         * @param string str 检查的关键字
         * @param float iMin 最短长度
         * @param float iMax 最长长度
         * @return bool
         */
        chkStrWidth:function(str, iMin, iMax)
        {
            if (iMin == 0 && str == '')
                return true;

            if (str.length >= iMin && str.length <= iMax)
                return true;
            else
                return false;
        },

        /**
         * 检查字符串的宽度范围(字节版)
         * @public
         * @param string str 检查的关键字
         * @param float iMin 最短长度
         * @param float iMax 最长长度
         * @return bool
         */
        chkMbStrWidth:function(str, iMin, iMax)
        {
            if (iMin == 0 && str == '')
                return true;

            var iStrlen = $.mbstrlen(str);
            if (iStrlen >= iMin && iStrlen <= iMax)
                return true;
            else
                return false;
        },

        /**
         * 字符串长度统计(字节版:支持GBK|UTF8)
         * @public
         * @param string s 检查的关键字
         * @return int
         */
        mbstrlen:function(s)
        {
            var totalLength = 0;
            var i;
            var charCode;
            for (i = 0; i < s.length; i++)
            {
                charCode = s.charCodeAt(i);
                if (charCode < 0x007f)
                    totalLength = totalLength + 1;
                else if ((0x0080 <= charCode) && (charCode <= 0x07ff))
                    totalLength += 2;
                else if ((0x0800 <= charCode) && (charCode <= 0xffff))
                    totalLength += 3;
            }
            return totalLength;
        },
    });
})(jQuery);