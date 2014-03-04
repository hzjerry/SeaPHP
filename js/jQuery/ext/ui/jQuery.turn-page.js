/**
 * jQuery Turn page [翻页处理函数]
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2013.08.07.1614
 *  Example
 * 	<code>
 * 	    $('#id_pagectl').html($.turn_page(1, 100, 15, true)); //翻页初始化
 *
 *      //下面是需要有一个外部函数处理分页选中后需要执行的表单动作
 *      function changePage(iPage)
 *      {
 *          frm.page = iPage;
 *          frm.submit();
 *      }
 * 	</code>
 */
;(function($)
{
    /**
     * 翻页处理的外部执行函数名
     * @private
     */
    var sTurnFunc = 'changePage';

    $.extend(
    {
        /**
         * 翻页处理函数(输出翻页控制代码)
         * @public
         * @param int iPage 当前页
         * @param int iRowTotal 总记录条数
         * @param int iPageSize 页大小
         * @param bool bShowSelect 是否显示下拉框翻页
         * @return string
         */
        turn_page:function(iPage, iRowTotal, iPageSize, bShowSelect)
        {
            var aBuf = Array();
            var iPageTotal = 0;//总分页数
            iPage = parseInt(iPage);//强制转换成数字

            //取浮点的最大整数值，计算总共需要的页数
            iPageTotal = Math.ceil(iRowTotal / iPageSize);//取浮点的最大整数值，计算总共需要的页数
            //---判断页面长度是否超过总页数，如果超过则修正
            if (iPage > Math.ceil(iRowTotal / iPageSize))	iPage = Math.ceil(iRowTotal / iPageSize);
            //---判断页面长度有否小于1，小于1则修正
            if (iPage < 1)	iPage = 1;

            aBuf.push("<b>找到 "+ iRowTotal +" 条记录</b>， ");
            aBuf.push("共<strong> "+ iPageTotal +"</strong> 页(每页<strong>"+ iPageSize +"</strong>条)，当前第 <strong>"+ iPage +"</strong> 页.");
            if (1 == iPage && iRowTotal > iPageSize)//当前为第一页时显示
                aBuf.push("翻页:<a href='javascript:void(0);' onclick='"+ sTurnFunc +"("+ (iPage + 1) +");'>下页</a>&nbsp;");
            else if (1 < iPage && iPage < iPageTotal)//当前页码在中间部位
            {
                aBuf.push("翻页:<a href='javascript:void(0);' onclick='"+ sTurnFunc +"("+ (iPage - 1) +");'>上页</a>&nbsp;");
                aBuf.push("<a href='javascript:void(0);' onclick='"+ sTurnFunc +"("+ (iPage + 1) +");'>下页</a>");
            }
            else if (1 < iPage && iPage >= iPageTotal)//以到最后一页时显示
                aBuf.push("翻页:<a href='javascript:void(0);' onclick='"+ sTurnFunc +"("+ (iPage - 1) +");'>上页</a>");

            //超过2页时显示跨页跳转
            if (bShowSelect == true && iPageTotal > 2)
            {
                aBuf.push("<select size='1' onchange='"+ sTurnFunc +"(this.value);'>");
                for (var i=1; i<=iPageTotal; i++)
                {
                    if (iPage == i)
                        aBuf.push("<option value='"+ i +"' selected>"+ i +"</option>");
                    else
                        aBuf.push("<option value='"+ i +"'>"+ i +"</option>");
                }
                aBuf.push("</select>");
            }
            return aBuf.join("\n") + "\n";
        },
    });
})(jQuery);