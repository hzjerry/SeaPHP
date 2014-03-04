/**
 * jQuery HashMap function [Key - Value store]
 *
 * @character_set UTF-8
 * @author Jerry.li(lijian@dzs.mobi)
 * @version 1.2012.12.06.2308
 *  Example
 * 	<code>
 *      //Create new HashMap object
 *      var hmTest = new $.hashMap();
 *
 *      //put Key - Value[string/object/array/function]
 *      hmTest.put('test', 'guest').put('test1', {name:'xyz', sex:'M'}).put('test2', [1,2,3,4,5,6,7,8]);
 *      //get value
 *      alert(hmTest.get(test)); //output: 'guest'
 *
 *      //output Key-Value Serialization
 *      alert(hmTest.toString('&')); // output: key1=val1&key2=val2....
 *
 *      //Other methods
 *      hmTest.clear();
 *      hmTest.size();
 *      hmTest.getKeys('DESC'); //sort['ASC' / 'DESC'] output array()
 *      hmTest.containsKey('test'); //output:true
 *
 * 	</code>
 */
;(function($)
{
    $.hashMap = function()
    {
        /**
         * Map大小
         * @private
         */
        var miSize = 0;

        /**
         * Key-Val存储对象
         * @private
         */
        var moEntry = new Object();

        /**
         * 根据Key获取Val的值
         * @public
         * @param key string 关键字
         * @return void
         */
        this.get = function(key)
        {
            return this.containsKey(key) ? moEntry[key] : null;
        };
        /**
         * 设定一个Key的val
         * @public
         * @param key string 关键字
         * @param value object任意一个对象
         * @return this 返回对象本身(以便于进行链式操作)
         */
        this.put = function(key,value)
        {
            if(!this.containsKey(key))
            {
                miSize++;
                moEntry[key] = value;
            }
            return this;
        };

        /**
         * 是否包含Key
         * @public
         * @param key string 关键字
         * @return bool
         */
        this.containsKey = function (key)
        {
            return (key in moEntry);
        }

        /**
         * 是否包含Value
         * @param key string 关键字
         * @return bool
         */
        this.containsValue = function(value)
        {
            for(var prop in moEntry)
            {
                if(moEntry[prop] == value)
                    return true;
            }
            return false;
        }

        /**
         * 移除一个Key
         * @public
         * @param sKey string 关键字
         * @return bool true/false
         */
        this.remove = function(sKey)
        {
            if(this.containsKey(sKey) && ( delete moEntry[sKey] ))
            {
                miSize--;
                return true;
            }
            else
                return false;
        };

        /**
         * 输出一个排序好的Key数组
         * @public
         * @param sType string 'ASC':升序 / 'DESC':降序
         * @return array() 数组对象
         */
        this.getKeys = function(sType)
        {
            var keys = new Array();

            for(var prop in moEntry)
                keys.push(prop);
            if (typeof(sType) == 'string' && 'DESC' == sType)
                return keys.sort(1); //输出降序
            else
                return keys.sort(-1); //其他状态均为升序
        };

        /**
         * Map size
         * @public
         * @return int
         */
        this.size = function()
        {
            return miSize;
        }

        /**
         * 清空Map
         * @public
         * @return int
         */
        this.clear=function()
        {
            miSize = 0;
            moEntry = new Object();
        }

        /**
         * 序列化输出值
         * @public
         * @param separator string 每个项目之间的分隔符
         * @return null / string
         */
        this.toString = function(separator)
        {
            var aBuf = new Array();
            var sSp = (typeof(separator) == 'string')? separator : ', ';
            if (miSize > 0)
            {
                for(var sKey in moEntry)
                    aBuf.push(sKey + '='+ moEntry[sKey]);

                //输出合并后的字符串
                if ('' != sSp)
                    return aBuf.join(sSp);
            }
            else
                return null;
        };
    };
})(jQuery);