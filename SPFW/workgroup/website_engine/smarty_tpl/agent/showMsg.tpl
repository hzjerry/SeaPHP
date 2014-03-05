<!DOCTYPE html>
<html lang="zh-CN">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width"/>
		<title><%$Title%></title>

		<link rel="stylesheet" type="text/css" href="<%$webroot%>css/button_google_style.css" /><!--按钮样式-->
		<link rel="apple-touch-icon" href="#"><!--苹果移动终端放到桌面的具有玻璃效果的图标-->
		<%include file="include_jQuery.tpl"%>

<%if $TypeFlg eq 'succ'%>
<script>
var miTIME = <%$JumpTime%>;
var miCount = 0;
var $delay_show = null;
//页面跳转
function jump()
{
	if (miCount >= miTIME)
		window.location.href='<%$Redirect%>';
	else
	{
		setTimeout('jump()', 1000);//N秒后跳转
		$delay_show.html('<font color=red><b>'+ (miTIME - miCount) +'</b></font> 秒后将自动跳转');
		miCount++;
	}
}

$(function()
{
	$delay_show = $('#delay_show');
	jump();//开始跳转处理
});
</script>
<%/if%>
<style>
body
{
	font: 12px/22px "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif,"新宋体","宋体";
}
#gc-show_msg
{
	display: table;
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
}
#gc-show_msg p
{
	text-indent:2em; /*首行缩进*/
	font-size:15px;
	margin:0 5px 20px 5px;
}
#gc-show_msg > .icon
{
	display: table-cell;
	width:75px;
	background-repeat: no-repeat;
	background-position: 0 50%;
}
/*提示图标定义*/
#gc-show_msg > .icon.error{background-image: url(<%$webroot%>_static_img/b_error_75.png)}
#gc-show_msg > .icon.sccess{background-image: url(<%$webroot%>_static_img/b_success_75.png)}
#gc-show_msg > .icon.alert{background-image: url(<%$webroot%>_static_img/b_alert_75.png)}
#gc-show_msg > .content
{
	margin-left:15px;
	max-width:350px;
}
</style>
	</head>

	<body>
		<div style="margin-top:50px;"></div>

		<div style="width:100%;">

			<div id="gc-show_msg">
				<%if $TypeFlg eq 'succ'%>
					<div class="icon sccess"></div>
				<%elseif $TypeFlg eq 'fail'%>
					<div class="icon error"></div>
				<%elseif $TypeFlg eq 'warn'%>
					<div class="icon alert"></div>
				<%/if%>

				<div class="content">
				<%if $TypeFlg eq 'succ'%>
					<h1 style="color:#23b129">恭喜！处理成功。</h1>
				<%elseif $TypeFlg eq 'fail'%>
					<h1 style="color:#e00728">哎呀！遇到错误了。</h1>
					<span style="font-weight: bold;">原因:</span>
				<%elseif $TypeFlg eq 'warn'%>
					<h1 style="color:#ffda21">警告！请注意下面的提示。</h1>
					<span style="font-weight: bold;">原因:</span>
				<%/if%>
					<p><%$Msg%></p>

				<%if $TypeFlg eq 'succ'%>
					<label style="clear:both;"><div id="delay_show">N秒后跳转</div></label>
				<%/if%>

				<%if $Redirect eq ''%>
					<button class="action blue" style="float:right;" onclick="javascript:history.go( -1 );"><span class="label">返回</span></button>
				<%else%>
					<button class="action blue" style="float:right;" onclick="javascript:location.href='<%$Redirect%>';"><span class="label">确定</span></button>
				<%/if%>
				</div>
			</div>
		</div>
	</body>
</html>