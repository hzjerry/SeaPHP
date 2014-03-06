<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title><%block name="title"%>title:<%/block%></title>
<script type="text/javascript">
function getSpecificCookie(name){
	var start = null;
	var end = null;
	if(document.cookie.length > 0){
		start = document.cookie.indexOf(name + "=");
		if( start != -1){
			start = start + name.length + 1;
			end = document.cookie.indexOf(";",start);
			if( end == -1)
				end = document.cookie.length;
		}
		return decodeURI(document.cookie.substring(start,end));
	}
	return "";
}

window.onload = function(){
	alert('cooike test [rember]:'+ getSpecificCookie('agent_rember'));
}
</script>
 </head>

 <body>
	网站模板替换测试,我的用户名是<%block name="username"%>小王<%/block%>
	<div><a href="?LoginOpt-loginOut">退出登录</a></div>
 </body>
</html>