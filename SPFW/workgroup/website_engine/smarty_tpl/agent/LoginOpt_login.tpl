<!DOCTYPE html>
<html lang="zh-CN">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width"/>

		<link rel="stylesheet" type="text/css" href="<%$webroot%>css/button_google_style.css" /><!--按钮样式-->
		<link rel="apple-touch-icon" href="#"><!--苹果移动终端放到桌面的具有玻璃效果的图标-->
		<style>body{font: 12px/22px "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif,"新宋体","宋体";}</style>
		<%include file="include_jQuery.tpl"%>
		<script src="<%$webroot%>js/jQuery/ext/util/jQuery.cookies.min.js"></script>
		<script src="<%$webroot%>js/jQuery/ext/ui/jQuery.top-prompt.min.js"></script><!--interface-->
		<title><%$sAct%></title>

	<style>
	#gc-lgoin
	{
		margin-left:auto;
		margin-right:auto;
		padding:16px 16px 30px 16px;
		font-weight:normal;
		-moz-border-radius:11px;
		-khtml-border-radius:11px;
		-webkit-border-radius:11px;
		border-radius:5px;
		border:1px solid #e5e5e5;
		-moz-box-shadow:rgba(200,200,200,1) 0 4px 18px;
		-webkit-box-shadow:rgba(200,200,200,1) 0 4px 18px;
		-khtml-box-shadow:rgba(200,200,200,1) 0 4px 18px;
		box-shadow:rgba(200,200,200,1) 0 4px 18px;
		/*background:rgba(0,0,0,0.5);*/
	}
	#gc-lgoin label
	{
		color:darkgray;
	}
	#gc-lgoin input
	{
		color:#555;
	}
	.user_pass,.user_login
	{
		font-size:24px;
		width:97%;
		padding:3px;
		margin-top:2px;
		margin-right:6px;
		margin-bottom:8px;
		border:1px solid #e5e5e5;
		background:lemonchiffon;
	}
	</style>

	<script>
$(function()
{
    var sUrl = '?ajax.LoginOrReg-loginAgentCheck-login'; //aJax服务地址
    var $frame = $("#gc-lgoin"); //登录框对象
    var $uname = $('#id_uname');
    var $pwd = $('#id_pwd');
    $.initTopPrompt(); //初始化顶部弹出提示框

    if ($.getCookie('agent_rember') == 'true')
    {   //判断是否取出记住的用户名
        $('#id_rember').attr('checked', true);
        $uname.val($.getCookie('agent_logname'));
        $pwd.val($.getCookie('agent_pwd'));
    }
    else
    {   //清除登录信息
        $('#id_rember').attr('checked', false);
        $uname.val('');
        $pwd.val('');
    }

    $('#btn_submit').click(function()
    {
        var sUName = $.trim($uname.val());
        var sPwd = $.trim($pwd.val());
        if (sUName == '' || sPwd == '')
        {
            $frame.hide();
            $frame.show("shake", 450);
        }
        else
        {
            $(this).attr('disabled', true); //锁定按钮
            $('#btn_submit_name').text('登录中...'); //登录中
            $.showTopPrompt('登录验证中...');
            $.getJSON(sUrl, {uname:sUName, pwd:sPwd}, function (json)
            {   //成功后回调
                if (json.login_state)
                {
                    //alert('login ok');
                    frm.submit();
                }
                else
                {
                    $frame.hide();
                    $frame.show("shake", 450);
                    $('#btn_submit').attr('disabled', false); //激活按钮
                    $('#btn_submit_name').text('登录'); //登录中
                    $.hideTopPrompt('用户名与密码无效');//隐藏提示
					alert('用户名与密码都尝试用test登录');
                }
            });
        }
    });
});
	</script>

	</head>

	<body>
		<div style="margin-top:50px;"></div>

		<div style="width:100%;">
			<div style="width:250px;margin-left:auto;margin-right:auto;text-align:center;">
				<h1>代理登录</h1>
			</div>

			<div id="gc-lgoin" style="width:250px;"><!--登录表单-->
				<form method="post" action="?LoginOpt-login-do" name="frm">
				<label>代理号:<br/><input class="user_login" id="id_uname" type="text" maxlength="20" name="uname" required="true"></label>
				<label>密码:<br/><input class="user_pass" id="id_pwd" type="password" maxlength="20" name="pwd" required="true"></label>
				<div style="width:100%">
					<label style="clear:both;"><input type="checkbox" name="rember" id="id_rember" value="true">记住我的登录信息</label>
					<button type="button" id="btn_submit" class="action blue" style="float:right;"><span class="label" id="btn_submit_name">登录</span></button>
				</div>
				</form>
			</div>
			<div style="margin-top:15px;text-align:center;">
				本站点使用html5技术<br/>推荐您安装<a href="http://www.google.cn/intl/zh-CN/chrome/browser/">Google Chrome</a>浏览器<br/>
			</div>
		</div>
	</body>
</html>