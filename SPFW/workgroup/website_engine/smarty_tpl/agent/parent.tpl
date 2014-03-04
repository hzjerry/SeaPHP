<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title><%block name="title"%>title:<%/block%></title>
<script type="text/javascript">
function getSpecificCookie(name)
{
	if(document.cookie.length > 0)
	{
		start = document.cookie.indexOf(name + "=");
		if( start != -1)
		{
			start = start + name.length + 1;
			end = document.cookie.indexOf(";",start);
			if( end == -1)
				end = document.cookie.length;
		}
		return decodeURI(document.cookie.substring(start,end));
	}
	return "";
}

window.onload = function()
{
	alert(getSpecificCookie('agent_usersex'));
}
</script>
 </head>

 <body>
	网站模板替换测试,我的名字是<%block name="username"%>小王<%/block%>
 </body>
</html>