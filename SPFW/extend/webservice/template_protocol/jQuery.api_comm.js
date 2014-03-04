var oAPI =
{
	/**接口地址(不能跨域访问)*/
	msURL : '',
	/**接口验证密码*/
	msPWD : '',
	/**
	 * 获取时间戳
	 * [内部函数]
	 * @return int
	 */
	getUnixTimestemp : function()
	{
		return Math.round(new Date().getTime()/1000);
	},
	/**
	 * 生成时间戳
	 * [内部函数]
	 * @param sPackage 包名
	 * @param sClass 类名
	 * @return string
	 */
	getCheckSum : function(sPackage, sClass)
	{
		return $.md5(sPackage + sClass + this.getUnixTimestemp() + this.msPWD );
	},
	/**
	 * 接口通信函数
	 * [内部函数]
	 * @private
	 * @param string sPkg 包名称
	 * @param string sCls 类名称
	 * @param object oJsonBuf 需要送出的参数
     * @param function funCallback 回调函数
	 * @return string
	 */
	execJson : function(sPkg, sCls, oJsonBuf, funCallback)
	{
		oJsonBuf = (typeof oJsonBuf == 'undefined' || typeof oJsonBuf != 'object')? {} : oJsonBuf;
		//输入
		oJsonBuf.package = {C:sPkg};
		oJsonBuf.class = {C:sCls};
		oJsonBuf.checksum = {A:{value:this.getCheckSum(sPkg, sCls), datetime:this.getDatetime()}};
		var sSendData = JSON.stringify(oJsonBuf);
		$.ajax(
		{
			url: this.msURL +'?protocol_type=json',
			dataType:'text',
			type:'post',
			data:sSendData,
			success:funCallback, /*处理结束*/
			error:function(XMLHttpRequest, textStatus, errorThrown)
			{
				alert('textStatus:'+textStatus +";\n readyState:"+ XMLHttpRequest.readyState);
			}
		});
	},
    /**
     * Get方式通信
     * [内部函数]
     * @private
     * @param string sPkg 包名称
     * @param string sCls 类名称
     * @param string sGetStr 需要送出的参数
     * @param function funCallback 回调函数
     * @return string
     */
    execGet : function(sPkg, sCls, sGetStr, funCallback)
    {
        //输入
        var aParam = new Array();
        aParam.push('package='+sPkg);
        aParam.push('class='+sCls);
        aParam.push('checksum.value='+ this.getCheckSum(sPkg, sCls));
        aParam.push('checksum.unix_timestamp='+ this.getUnixTimestemp());
        var sSendData = aParam.join('&') +'&'+ sGetStr;
        $.ajax(
            {
                url: this.msURL +'?protocol_type=xml&format_auto=true&'+ sSendData,
                dataType:'text',
                type:'GET',
                data:null,
                success:funCallback, /*处理结束*/
                error:function(XMLHttpRequest, textStatus, errorThrown)
                {
                    alert('textStatus:'+textStatus +";\n readyState:"+ XMLHttpRequest.readyState);
                }
            });
    },
	/**
	 * 对中文与全角字符进行Unicode字符编码
	 * 用于在GBK的情况下进行json的发送字符串编码
	 * @param str 待处理的字符串
	 * @return 编码后的字符集 \uxxxx形式
	 */
	toUnicodeStr : function toUnicodeStr(str)
	{
		var cx = /[\u4e00-\u9fa5]|[\uff00-\uffff]|[\"]/g;//中文或者全角字符
		var safe =/[\\]/g; //需要双字节替换的安全字符
		var list = str.split(""); //拆解输入字符串
		for(var i = 0; i < list.length; i++)
		{
			if (cx.test(list[i]))
			{
				list[i] = '\\u' + ('0000' + list[i].charCodeAt(0).toString(16)).slice(-4);
				cx.lastIndex = 0;
			}
			else if (safe.test(list[i]))
			{
				list[i] = '\\u005c\\u005c';
				safe.lastIndex = 0;
			}
		}
		return list.join('');
	}
};