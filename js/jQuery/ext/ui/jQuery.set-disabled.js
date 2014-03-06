/**
 * jQuery Set the object's availability function [设置对象的可用性]
 *
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2012.11.30.0901
 *  Example
 * 	<code>
 *      //Set the object to disabled [设置对象为禁用状态]
 *      $('#ddd').disabled('action half-hidden'); //"action half-hidden" is append class
 *
 *      //Set the object to active state [设置对象为活跃状态]
 *      $('#ddd').enabled();
 *
 *      //Switch the object available [设置对象为活跃状态(让对象在disabled与enabled之间进行切换)]
 *      $('#ddd').toggleAvailable('action half-hidden'); // "action half-hidden" is append class
 * 	</code>
 */
;(function($)
{
    $.fn.extend(
    {
        /**
         * 设置对象为禁用状态
         * @public
         * @param appendClss string 追加的Class样式
         * @return void
         */
        disabled:function(appendClss)
        {
            var sClass = '';
            if (typeof($(this).data('disabled')) == 'undefined')
            {
                if (typeof(appendClss) == 'string')
                    sClass = appendClss; //得到追加的属性

                //将追加的Class保留一个备份(便于移除样式的时候使用)
                $(this).data('appendClss', sClass);
                //设置属性
                $(this).addClass(sClass)
                    .attr('disabled','true')
                    .data('disabled', 'true');
            }
        },
        /**
         * 设置对象为活跃状态
         * (操作成功后，会剥离disabled的时候追加class)
         * @public
         * @return void
         */
        enabled:function()
        {
            var sClass = '';
            if (typeof($(this).data('disabled')) == 'string' && $(this).data('disabled') === 'true')
            {
                sClass = $(this).data('appendClss'); //取出属性
                //移除属性
                if (sClass != '')
                    $(this).removeClass(sClass);

                $(this).removeAttr('disabled')
				    .removeData();
            }
        },
        /**
         * 切换对象的可用度
         * (让对象在disabled与enabled之间进行切换)
         * @public
         * @return void
         */
        toggleAvailable:function(appendClss)
        {
            var sClass = '';
            if (typeof($(this).data('disabled')) == 'undefined')
            {
                if (typeof(appendClss) == 'string')
                    sClass = appendClss; //得到追加的属性

                //将追加的Class保留一个备份(便于移除样式的时候使用)
                $(this).data('appendClss', sClass);
                if (sClass != '')
                    $(this).addClass(sClass);
                //设置属性
                $(this).attr('disabled','true')
                    .data('disabled', 'true');
            }
            else
            {
                sClass = $(this).data('appendClss'); //取出属性
                //移除属性
                if (sClass != '')
                    $(this).removeClass(sClass);

                $(this).removeAttr('disabled')
				    .removeData();
            }
        },
    });
})(jQuery);