<?php
/**
 * Http Post处理类 CURL版本<br />
 *  简要描述:采用get或post方法将数据发送到http服务器<br />
 *  运行环境:php5.1或以上
 * @author Jerry Li(lijian@dzs.mobi)
 * @final
 * @version 1.0.3<br />
 *  2013-04-17 JerryLi 程序创建<br />
 *  2013-08-16 fixd bug:首次访问的时候速度非常慢,增加了
 *   curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);与$aHeader[] = 'Expect: ';<br />
 *  2013-11-13 fixd bug:增加了HTTPS的访问方式
 * @example <pre>
$o = new CPostHttp();
$o->setCommFunc('file_get_contents'); //可选，默认为curl(curl 的效率极高)
$o->showDebug(); //参数可选默认为关闭
$o->showError(); //参数可选默认为关闭
//POST方式获取数据
$o->addField('name', 'tester');
$o->setPostData('xml or json data'); //这个使用后addField设置的值将无效
$o->setPostType('html'); //xml或json或html
if ($o->post('http://sac.fox.cn:8080/InterFace/'))
{
	if (200 == $o->getResponseStatus())
	{	//请求成功，取出数据
		return $o->getContent();
	}
	else
	{	//服务器返回异常
		print_r($o->getErrorInfo());
	}
}
else
	return null;

//GET方式获取数据
if ($o->getPage('http://diz.fox.cn:8080/air_id/Interface/getCheckInList.php?date=2012-08-29&mode=general'))
{
	if (200 == $o->getResponseStatus())
	{	//请求成功，取出数据
		return $o->getContent();
	}
	else
	{	//服务器返回异常
		print_r($o->getErrorInfo());
	}
}
else
	return null
</pre>
*/
final class CPostHttp
{
	/**
	 * 版本号
	 * @var string
	 * */
	const _VERSION = '1.0.2';
	/**
	 * http建立通信连接等待时间(秒)
	 * @var int
	 * */
	const _CONNECTTIMEOUT = 15;
	/**
	 * http连接连接后，请求等待时间(秒)
	 * @var int
	 * */
	const _TIMEOUT = 20;
	/**
	 * http连接时域名解析缓存时间(秒)
	 * @var int
	 * */
	const _DNS_CACHE_TIMEOUT = 600;
	/**
	 * 是否显示error信息
	 * @var bool
	 * */
	private $mbShow_errors = false;
	/**
	 * 是否打开debug
	 * @var bool
	 * @access private
	 * */
	private $mbShow_debug = false;
	/**
	 * 引用者URL
	 * @var string
	 * @access private
	 * */
	private $msReferer = null;
	/**
	 * Post的参数对象 array(array('key'=>'val'), ...)
	 * @var array
	 * @access private
	 * */
	private $maPostFields = null;
	/**
	 * Post时提交的整个数据字符串内容包
	 * @var string
	 * @access private
	 * */
	private $msPostData = null;
	/**
	 * Post类型 [html:普通的from]/[xml:XML数据包方式UTF-8]/[json:Json数据包方式UTF-8]
	 * @var string
	 * @access private
	 * */
	private $msPostType = 'html';
	/**
	 * cookie数据
	 * @var array
	 * @access private
	 * */
	private $maCookies = array();
	/**
	 * 接收到的回复信息
	 * @var string
	 * @access private
	 * */
	private $msReceivedContent = null;
	/**
	 * httppost通信回复状态
	 * @var int
	 * @access private
	 * */
	private $miResponseStatus = null;
	/**
	 * httppost通信回复的头Header信息列表
	 * @var string
	 * @access private
	 * */
	private $msResponseHeaders = array();
	/**
	 * httppost通信失败时的错误信息代号
	 * @var int
	 * @access private
	 * */
	private $miErrorNum = 0;
	/**
	 * httppost通信失败时的错误信息
	 * @var string
	 * @access private
	 * */
	private $msErrorMsg = null;
	/**
	 * 使用的通信函数(有些平台不支持curl时使用file_get_contents)<br/>
	 * 值为 curl 或 file_get_contents
	 * @var string
	 * */
	private $msCommFunc = 'curl'; //默认:curl | file_get_contents

	/**
	 * 构造函数
	 * */
	public function __construct()
	{
		//检查curl_init函数是否存在
		if (!function_exists('curl_init'))
		{
			echo '<br />';
			echo 'Fatal Error!<br />';
			echo 'The function the curl_init not exist, ';
			echo 'please check the php version, the version number must be 5.1+.';
			exit();
		}
		else
		{	//初始化
			$this->maPostFields = array();
			$this->msReferer = 'http://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		}
	}

	/**
	 * 设置使用的通信函数
	 * @param unknown $sFuncName curl函数 | file_get_contents函数
	 */
	public function setCommFunc($sFuncName)
	{
		if (in_array($sFuncName, array('curl', 'file_get_contents')))
		{
			if ('file_get_contents' === $sFuncName && !function_exists('file_get_contents'))
			{	//不支持file_get_contents函数
				echo '<br />';
				echo 'Fatal Error!<br />';
				echo 'The function the file_get_contents not exist, ';
				exit();
			}
			$this->msCommFunc = $sFuncName;
		}
	}

	/**
	 * 遇到错误时打印错误信息(一般调试时使用)
	 * @return void
	 * @access public
	 */
	public function showError()
	{
		$this->mbShow_errors = true;
	}

	/**
	 * 显示调试信息（会显示发送与接收到的数据包）
	 * @return void
	 * @access public
	 */
	public function showDebug()
	{
		$this->mbShow_debug = true;
	}

	/**
	 * 设置引用者URL(当需要欺骗对方来访者地址时使用)
	 * @param string $sRef 调用者的URL地址 http://....
	 * @return void
	 * @access public
	 * */
	public function setReferer($sRef)
	{
		$this->msReferer = $sRef;
	}

	/**
	 * 增加一个Post方式提交的变量字段
	 * @param string $sKey 变量名
	 * @param string $sVal 变量值(会自动对这个值做urlencode编码)
	 * @return void
	 * @access public
	 * */
	public function addField($sKey, $sVal)
	{
		$this->maPostFields[trim($sKey)] = urlencode(trim($sVal));
	}

	/**
	 * 设置需要提交的POST数据包（字符串）
	 * @param string $sData 数据包字符串(如XML/JSON)
	 * @return void
	 * @access public
	 * */
	public function setPostData($sData)
	{
		$this->msPostData = $sData;
	}

	/**
	 * 设置POST的数据类型
	 * @param string $sType [html:普通的from]/[xml:XML数据包方式UTF-8]/[json:Json数据包方式UTF-8]
	 * @return void
	 * @access public
	 * */
	public function setPostType($sType)
	{
		$aType = array('html', 'xml', 'json');
		if (in_array($sType, $aType))
			$this->msPostType = $sType;
		else
		{
			$this->print_error(__CLASS__ .'::'. __FUNCTION__ .': invalid type. <br>'.
							   'Optional type: '. implode('', $aType));
			exit(); //强制终止
		}
	}

	/**
	 * 输出序列化后的PostFields值，用于post的内容提交
	 * @return null | string
	 * @access private
	 * */
	private function serialPostFields()
	{
		if (count($this->maPostFields) > 0)
		{
			$aTmp = array();
			foreach ($this->maPostFields as $sKey => $sVal)
				$aTmp[] = $sKey .'='. $sVal;
			return implode('&', $aTmp);
		}
		else
			return null;
	}

	/**
	 * 清除待提交的POST信息
	 * @return void
	 * @access public
	 * */
	public function clearFields()
	{
		$this->msPostData = null;
		$this->maPostFields = array();
	}

	/**
	 * 增加一个Cookie值，会随着post或get一同发出<br/>
	 * 需要注意，cookie根据协议标准，最多只能有20项
	 * @param string $sKey 变量名
	 * @param string $sVal 变量值(会自动对这个值做urlencode编码)
	 * @return boolean true:增加成功 / false:字段已满不能添加
	 * @access public
	 * */
	public function setCookies($sKey, $sVal)
	{
		if (count($this->maCookies) < 20)
		{
			$this->maCookies[trim($sKey)] = urlencode(trim($sVal));
			return true;
		}
		else
			return false;
	}

	/**
	 * 输出序列化后的Cookie值，用于post或get中的header
	 * @return null / string
	 * @access private
	 * */
	private function serialCookies()
	{
		if (count($this->maCookies) > 0)
		{
			$aTmp = array();
			foreach ($this->maCookies as $sKey => $sVal)
				$aTmp[] = $sKey .'='. $sVal .';';
			return implode('', $aTmp);
		}
		else
			return null;
	}

	/**
	 * 清除待提交的Cookie信息
	 * @return void
	 * @access public
	 * */
	public function clearCookies()
	{
		$this->maCookies = array();
	}

	/**
	 * 获取得到的服务器反馈信息
	 * @return null | string
	 * @access public
	 * */
	public function getContent()
	{
		if( is_null($this->msReceivedContent) )
		{
			$this->print_error(__CLASS__ .'::'. __FUNCTION__ .': Can not get the post of feedback results.');
			return null;
		}
		else
		{
			return $this->msReceivedContent;
		}
	}

	/**
	 * 取得回复的服务器Header信息<br/>
	 *  请在post()或getPage()之后使用这个函数。
	 * @return null | array('key'=>'val')
	 * @access public
	 * */
	public function getHeaders()
	{
		if (!is_null($this->msResponseHeaders))
			return $this->msResponseHeaders;
		else
			return null;
	}

	/**
	 * 取得回复的服务器Header信息<br/>
	 *  请在post()或getPage()之后使用这个函数。
	 * @param string $sName
	 * @return null | string
	 * @access public
	 * */
	public function getHeader($sName)
	{
		if (!is_null($this->msResponseHeaders))
			return isset($this->msResponseHeaders[$sName])? $this->msResponseHeaders[$sName] : null;
		else
			return null;
	}

	/**
	 * 取得回复的服务器Cookie信息<br/>
	 *  请在post()或getPage()之后使用这个函数。
	 * @return null | array
	 * @access public
	 * */
	public function getCookies()
	{
		$sCookis = null;
		foreach ($this->msResponseHeaders as $sKey => $sVal)
		{
			if ('set-cookie' == strtolower($sKey))
			{
				$sCookis = $sVal;
				break;
			}
		}

		if (!is_null($sCookis))
		{	//当存在cookis时处理
			$aJmp = array('path', 'expires', 'domain', 'secure'); //需要丢弃的内容
			$aTmp =  explode(';', $sCookis);
			unset($sCookis);

			$aCookies = array();
			foreach ($aTmp as $sNode)
			{
				$aT = explode('=', $sNode);
				$sKey = trim($aT[0]);
				if (!in_array($sKey, $aJmp))
					$aCookies[$sKey] = urldecode(trim($aT[1]));//对结果进行解码
			}
			unset($aTmp);
			if (count($aCookies) > 0)
				return $aCookies;
			else
				return null;
		}
		else
			return null;
	}

	/**
	 * 获取http Response 返回状态号<br/>
	 * 请在post()或getPage()之后使用这个函数<br/>
	 * 返回值:(200:OK / 404:Not Found /301:Moved Permanently / 302:Moved Temporarily / 500:Server Error)
	 * @return int
	 * @access public
	 * */
	public function getResponseStatus()
	{
		return $this->miResponseStatus;
	}

	/**
	 * 分离出头部信息字符串中的内容
	 * @param string $sHeader 接收到的http头部解析
	 * @return void
	 * @access private
	 * */
	private function splitHeader($sHeader)
	{
		$aTmp = explode("\r\n", $sHeader);
		if (count($aTmp) > 0)
		{
			/*取出相应状态头*/
			$aT = explode(' ', $aTmp[0]);
			if (isset($aT[1]))
				$this->miResponseStatus = intval($aT[1]);
			else
				$this->miResponseStatus = null;
			unset($aTmp[0], $aT);
			/*取出其他相应状态信息*/
			$aHead = array();
			foreach ($aTmp as $sNode)
			{
				$aT = explode(': ', $sNode);
				$aHead[trim($aT[0])] = trim($aT[1]);
			}
			if (count($aHead) > 0)
				$this->msResponseHeaders = $aHead;
			else
				$this->msResponseHeaders = null;
		}
	}

	/**
	 * 如果post()或getPage()遇到错误返回false，可取出错误信息<br/>
	 *  返回值:array('no'=>错误号, 'msg'=>错误信息)
	 * @return array
	 * @access public
	 * */
	public function getErrorInfo()
	{
		return array('no'=>$this->miErrorNum, 'msg'=>$this->msErrorMsg);
	}

	/**
	 * 执行post操作，送出设定好的POST数据
	 * @param string $sURL 提交页面的地址
	 * @return bool
	 * @access public
	 * */
	public function post($sURL)
	{
		/*整理待提交的POST数据*/
		if (!is_null($this->msPostData)) //直接处理数据包的提交
			$sPostFields = $this->msPostData;
		else //没有数据包，生成字段提交
			$sPostFields = $this->serialPostFields();
		/*header定义*/
		$aURL = parse_url($sURL);
		$iPort = (isset($aURL['port']) && $aURL['port'] !== 80)? $aURL['port'] :null;
		$aHeader = array();
 		$aHeader[] = 'POST '. (isset($aURL['path'])?$aURL['path']:'/') .' HTTP/1.0'; //访问的目标文件
		$aHeader[] = 'Date: '. date('r');
		$aHeader[] = 'Host: '. $aURL['host'] . ((is_null($iPort))? '': ':'.$iPort);
		$aHeader[] = 'Connection: close';
		if ('xml' == $this->msPostType || 'json' == $this->msPostType)
			$aHeader[] = 'Content-Type: text/xml; charset=utf-8';
		else
			$aHeader[] = 'Content-type: application/x-www-form-urlencoded';

		if(!empty($this->msReferer))
			$aHeader[] = 'Referer: '. $this->msReferer; //请求者地址
		$aHeader[] = 'User-Agent: PostHttp CURL/'. self::_VERSION .' (by JerryLi hzjerry@gmail.com)';
		$aHeader[] = 'Content-length: '. strlen($sPostFields);
		$aHeader[] = 'Cache-Control: no-cache';
// 		/*如果有Cookie则取出*/
		$sCookie = $this->serialCookies();
		if (!is_null($sCookie))
			$aHeader[] = 'Cookie: '. $sCookie;
		/*
		 * 解决由于Expect: 100-continue导致可能造成的首次访问速度非常慢的问题
		 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec8.html#sec8.2.3
		 */
		$aHeader[] = 'Expect: ';
		/*数据发送*/
		if ('curl' == $this->msCommFunc)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $sURL); //设置POST的URL地址
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //强制协议为1.0
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//将结果保存成字符串
			curl_setopt($ch, CURLOPT_HEADER, true); //需要获得HTTP头信息
			curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, self::_DNS_CACHE_TIMEOUT);//DNS解析缓存保存时间半小时
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::_CONNECTTIMEOUT);//连接超时时间
			curl_setopt($ch, CURLOPT_TIMEOUT, self::_TIMEOUT);//执行超时时间
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //php版本5.3及以上，可关闭IPV6，只使用IPV4

			if (!is_null($sPostFields)) //存在post提交数据时
			{
				curl_setopt($ch, CURLOPT_POST, true);//POST方式
				curl_setopt($ch, CURLOPT_POSTFIELDS, $sPostFields);//POST数据内容
			}

			$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Send data to a remote URL',
							   implode("\r\n", $aHeader) ."\r\n\r\n". $sPostFields);
			$sData = curl_exec($ch); //得到服务器的回复内容
			if (false === $sData)
			{	//通信失败
				$this->msErrorMsg = curl_error($ch);
				$this->miErrorNum = curl_errno($ch);
				curl_close($ch);//关闭连接
				unset($ch);//释放curl_init

				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Communication failure', $sData);
				$this->print_error(__CLASS__ .'::'. __FUNCTION__ .': Communication failure,'.
								   'error code: '. $this->miErrorNum .', '.
								   'error msg: '. $this->msErrorMsg
								  );
				return false;
			}
			else
			{	//通信成功，取出得到的内容
				$this->msErrorMsg = null;
				$this->miErrorNum = 0;
				curl_close($ch);//关闭连接
				unset($ch);//释放curl_init

				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Successfully received data', $sData);

				/*分离出Header与content*/
				$sHeader = null;
				$iSite = strpos($sData, "\r\n\r\n");
				$sHeader = substr($sData, 0, $iSite);
				$this->msReceivedContent = substr($sData, $iSite+4);
				unset($sData);

				/*分离头部信息*/
				$this->splitHeader($sHeader);
				return true;
			}
		}
		else
		{	//file_get_contents方式通信
			array_shift($aHeader); //弹掉header配置中的第一项（不弹出会出现重读定义）
			$opts = array('http'=>array(
				'method'=>'POST',
				'content'=>$sPostFields, //post数据
 				'header'=>implode("\r\n", $aHeader) ."\r\n",
				'timeout'=>self::_TIMEOUT)
			);
			if (($sData = file_get_contents($sURL, false, stream_context_create($opts))) !== false)
			{	//得到正常结果返回
				$this->msErrorMsg = null;
				$this->miErrorNum = 0;
				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Successfully received data', $sData);
				$this->msReceivedContent = $sData;
				$this->msResponseHeaders = null; //没有头部信息
				unset($sData);//释放
				return true;
			}
			else
			{	//遇到错误
				$this->msErrorMsg = 'file_get_contents: Unable to get data';
				$this->miErrorNum = -1;
				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Communication failure', $sData);
				$this->print_error(__CLASS__ .'::'. __FUNCTION__ .': Communication failure,'.
						'error code: '. $this->miErrorNum .', '.
						'error msg: '. $this->msErrorMsg
				);
				return false;
			}
		}
	}

	/**
	 * 执行GET操作
	 * @return bool
	 * @access public
	 * */
	public function getPage($sURL)
	{
		$aURL = parse_url($sURL);
		$iPort = (isset($aURL['port']))? intval($aURL['port']) : 80; //端口号
		$aHeader = array();
		$aHeader[] = 'GET '. (isset($aURL['path'])?$aURL['path']:'/') .' HTTP/1.1'; //访问的目标文件
		$aHeader[] = 'Date: '. date('r');
		$aHeader[] = 'Host: '. $aURL['host'] . ((is_null($iPort))? '': ':'.$iPort);
		$aHeader[] = 'Connection: close';
		if(!empty($this->msReferer))
			$aHeader[] = 'Referer: '. $this->msReferer; //请求者地址
		$aHeader[] = 'User-Agent: PostHttp CURL/'. self::_VERSION .' (by JerryLi hzjerry@gmail.com)';
		$aHeader[] = 'Cache-Control: no-cache';
		/*如果有Cookie则取出*/
		$sCookie = $this->serialCookies();
		if (!is_null($sCookie))
			$aHeader[] = 'Cookie: '. $sCookie;

		/*数据发送*/
		if ('curl' == $this->msCommFunc)
		{	//curl方式的通信
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $sURL); //设置GET的URL地址
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//将结果保存成字符串
			curl_setopt($ch, CURLOPT_HEADER, true); //需要获得HTTP头信息
			curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, self::_DNS_CACHE_TIMEOUT);//DNS解析缓存保存时间半小时
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::_CONNECTTIMEOUT);//连接超时时间
			curl_setopt($ch, CURLOPT_TIMEOUT, self::_TIMEOUT);//执行超时时间

			$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Send data to a remote URL',
							   implode("\r\n", $aHeader));
			$sData = curl_exec($ch); //得到服务器的回复内容
			if (false === $sData)
			{	//通信失败
				$this->msErrorMsg = curl_error($ch);
				$this->miErrorNum = curl_errno($ch);
				curl_close($ch);//关闭连接
				unset($ch);//释放curl_init

				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Communication failure', $sData);
				$this->print_error(__CLASS__ .'::'. __FUNCTION__ .': Communication failure,'.
								   'error code: '. $this->miErrorNum .', '.
								   'error msg: '. $this->msErrorMsg
								  );
				return false;
			}
			else
			{	//通信成功，取出得到的内容
				$this->msErrorMsg = null;
				$this->miErrorNum = 0;
				curl_close($ch);//关闭连接
				unset($ch);//释放curl_init

				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Successfully received data', $sData);

				/*分离出Header与content*/
				$sHeader = null;
				list($sHeader, $this->msReceivedContent) = explode("\r\n\r\n", $sData);
				unset($sData);

				/*分离头部信息*/
				$this->splitHeader($sHeader);
				return true;
			}
		}
		else
		{	//file_get_contents方式通信
			array_shift($aHeader); //弹掉header配置中的第一项（不弹出会出现重读定义）
			$opts = array('http'=>array('method'=> 'GET', 'header'=>implode("\r\n", $aHeader). "\r\n", 'timeout'=>self::_TIMEOUT));
			if (($sData = file_get_contents($sURL, false, stream_context_create($opts))) !== false)
			{	//得到正常结果返回
				$this->msErrorMsg = null;
				$this->miErrorNum = 0;
				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Successfully received data', $sData);
				$this->msReceivedContent = $sData;
				$this->msResponseHeaders = null; //没有头部信息
				unset($sData);//释放
				return true;
			}
			else
			{	//遇到错误
				$this->msErrorMsg = 'file_get_contents: Unable to get data';
				$this->miErrorNum = -1;
				$this->print_debug(__CLASS__ .'::'. __FUNCTION__ .': Communication failure', $sData);
				$this->print_error(__CLASS__ .'::'. __FUNCTION__ .': Communication failure,'.
						'error code: '. $this->miErrorNum .', '.
						'error msg: '. $this->msErrorMsg
				);
				return false;
			}
		}
	}

	/**
	 * 输出错误信息
	 *
	 * @param string $str 错误提示信息
	 * @return void
	 * @access private
	 * */
	private function print_error($str = '')
	{
		if ( $this->mbShow_errors )
		{
			echo '<blockquote>', "\n";
			echo '<b><span style="color:red;">PostHttp Error --</span></b>', '\n';
			echo "[<span style='color:000077;'>$str</span>]\n";
			echo "</blockquote>\n";
		}
	}//end func

	/**
	 * 打印调试信息<br />
	 * 通过$o->show_debug = true的方式打开调试信息
	 *
	 * @param string $sTitle 提示标题
	 * @param string $sMsg 提示内容
	 * @return void
	 * @access private
	 * */
	private function print_debug($sTitle = '', $sMsg = '')
	{
		//判断是否显示debug输出...
		if ( $this->mbShow_debug )
		{
			echo '<span style="font-size:16px;font-weight:bolder;color:green;">';
			echo __CLASS__ , ' Debug --</span>', "\n";
			echo '[<span style="color:#000077;">', $sTitle ,'</span>]<br>', "\n";
			if( !empty($sMsg) )
			{
				echo '<div style="background:#EEEEEE; border:1px solid black;padding:5px;font-size:12px;margin-bottom:10px;">';
				highlight_string($sMsg);
				echo '</div>';
			}
		}
	}
}
?>