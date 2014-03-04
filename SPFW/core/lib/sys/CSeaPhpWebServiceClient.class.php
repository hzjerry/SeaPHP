<?php
/**
 * Sea PHP架构的 Web Service专用客户端<br/>
 * 只需简单配置，就能与WebService的基本协议进行通信<br/>
 * 注意:此类只兼容 Sea PHP架构的 Web Service服务通信
 *
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130510
 * @package SPFW.core.lib.sys
 * @example
 * $o = new CSeaPhpWebServiceClient('http://....', 'pwd.....');<br />
 * $o->addNode('username', 'jerryli');<br />
 * $aTmp = array(array('name'=>'王海', 'id'=>'00001'), array('name'=>'王菲', 'id'=>'00002'));<br />
 * $o->addSameTagAttrib('row', $aTmp);<br />
 * $param = $o->exec('develop.test', 'GET_USER_INFO');<br />
 * */
class CSeaPhpWebServiceClient
{
	/**
	 * 根节点tag名称定义
	 * @var string
	 */
	const ROOT_TAG = 'boot';
	/**
	 * 根节点tag名称定义(不要修改WebService默认用utf-8)
	 * @var string
	 */
	const XML_CHARSET = 'utf-8';
	/**
	 * Xml结构数组公共存储区
	 * @var array
	 */
	private $maXml = array();
	/**
	 *	POST通信对象
	 * @var CPostHttp
	 */
	private $moPost = null;
	/**
	 *	Xml结构数组与xml数据包的转换类
	 * @var CXmlArrayConver
	 */
	private $moXmlConv = null;
	/**
	 * WebService访问入口地址
	 * @var string
	 */
	private $msURL = null;
	/**
	 * WebService约定的公钥
	 * @var string
	 */
	private $msPWD = null;
	/**
	 * 与Server的时差校准
	 * @var int
	 */
	static private $miCalibrate = null;

	/**
	 * 构造函数
	 * @param string $sURL WebService的服务地址
	 * @param string $sPWD WebService约定的公钥
	 * @param bool $bAutoCalibrateTime 自动校正时差[true:打开 | false:关闭]<br />
	 * (不建议打开，除非服务器之间的时差超过正负一小时，且无法手动调整服务器时间时才打开，影响性能)
	 */
	function __construct($sURL, $sPWD, $bAutoCalibrateTime=false)
	{
		$this->moPost = new CPostHttp();
		$this->moXmlConv = new CXmlArrayConver(self::ROOT_TAG, self::XML_CHARSET);
		$this->msURL = $sURL;
		$this->msPWD = $sPWD;

		$this->moPost->setPostType('xml'); //设置post为xml格式

		/*启用时差自动校正*/
		if ($bAutoCalibrateTime && is_null(self::$miCalibrate))
			self::$miCalibrate = $this->getTimedifference() * -1;
	}

	/**
	 * 析构函数
	 */
	function __destruct()
	{
		unset($this->moPost);
	}

	/**
	 * 清除存储区<br/>
	 * 每次进行新的发送数据包配置的时候，一定要进行一次清除操作（首次不用）
	 * @return void
	 * @access public
	 */
	public function clear()
	{
		unset($this->maXml);
		$this->maXml = array();
		$this->moPost->clearFields();
	}

	/**
	 * 建立一个叶子节点数据<br />
	 * 创建的节点保存在内部存储区
	 * @param string $sNodeName tag路径(以package格式表示)<br/>
	 *   &nbsp;&nbsp;注意:此为有限功能的表示方式，此功能不能建立同名兄弟节点
	 * @param string $sContent 节点的值content
	 * @param array $aAttribute 节点的属性array(key=>val,....)
	 * @return void
	 * @access public
	 */
	public function addNode($sNodeName, $sContent=null, $aAttribute=null)
	{
		$aNodeTmp = explode('.', trim($sNodeName)); //生成路径
		$aNode = array();
		/*构建叶子数据*/
		if (!empty($aAttribute) && count($aAttribute) > 0)
			$aNode['A'] = $aAttribute;
		if (!empty($sContent))
			$aNode['C'] = $sContent;
		if (count($aNode) == 0)
			$aNode['C'] = '';

		/*建立插入节点的路径*/
		$pNode = & $this->maXml;
		foreach ($aNodeTmp as $sNode)
		{
			$pNode[$sNode] = array();
			$pNode = & $pNode[$sNode];
		}
		$pNode = $aNode; //插入节点数据
	}

	/**
	 * 添加同名兄弟节点(节点只包含属性)
	 * @param string $sNodeName tag路径(以package格式表示)<br/>
	 *   &nbsp;&nbsp;注意:此为有限功能的表示方式，此功能不能建立同名兄弟节点
	 * @param array $aArray 属性数组 二维数组:array(array('field1'=>'...', 'field2'=>'...'), ...)
	 * @return void
	 * @access public
	 * @see SPFW.core.lib.base::CXmlArrayNode::createSameTagAttrib()
	 */
	public function addSameTagAttrib($sNodeName, $aArray)
	{
		$aNodeTmp = explode('.', trim($sNodeName)); //生成路径
		$aNode = array();
		/*建立插入节点的路径*/
		$pNode = & $this->maXml;
		foreach ($aNodeTmp as $sNode)
		{
			$pNode[$sNode] = array();
			$pNode = & $pNode[$sNode];
		}
		$pNode = CXmlArrayNode::createSameTagAttrib($aArray); //插入属性数组
	}

	/**
	 * 执行API调用请求，并得到服务端的相应回复<br/>
	 * 备注:先调用addNode()或addSameTagAttrib()设置参数然后再调用本方法
	 * @param string $sPackage 访问包路径(无大小写限制)
	 * @param string $sClass 执行的类名(约定必须用大写)
	 * @return array | null  xml结构数组
	 * @access public
	 */
	public function exec($sPackage, $sClass)
	{
		static $aFilter = array(' '=>'', '-'=>'', ':'=>'');
		/*system.public区，不需要生成校验节点*/
		if ($sPackage !== 'system.public')
		{
			if (is_null(self::$miCalibrate))
				$iUinxTimestemp = time();
			else
				$iUinxTimestemp = time() + self::$miCalibrate; //时间差计算
			$sChecksum = md5($sPackage . $sClass . $iUinxTimestemp . $this->msPWD);
			$this->addNode('checksum', null, array('value'=>$sChecksum, 'unix_timestamp'=>$iUinxTimestemp));
		}
		$this->addNode('package', $sPackage);
		$this->addNode('class', $sClass);
		//发送数据包给WebService,并得到服务器的回复数据
		$this->moPost->setPostData($this->moXmlConv->getStrPacket($this->maXml));
		if ($this->moPost->post($this->msURL))
			//将回复数据解码成Xml解构数组返回
			return $this->moXmlConv->getArray($this->moPost->getContent());
		else
			return null;
	}

	/**
	 * 获取存储区的Xml结构数组
	 * @return array
	 * @access public
	 */
	public function getXmlArray()
	{
		return $this->maXml;
	}

	/**
	 * 打开调试模式<br/>
	 * 打开调试模式后执行exec()是，会出现发送与接收的调试信息。提啊是信息直接打印到屏幕
	 * @return void
	 * @access public
	 */
	public function openDebug()
	{
		$this->moPost->showDebug();
		$this->moPost->showError();
		$this->moXmlConv->setShowFormat(true);
	}

	/**
	 * 矫正时差(与WebServie的公共接口校对时差)<br />
	 * 返回:正数表示比Server快，负数表示比Server慢;
	 * @return number
	 * @access protected
	 */
	protected function getTimedifference()
	{
		$this->addNode('datetime', date('Y-m-d H:i:s'));
		$aParam = $this->exec('system.public', 'GET_TIME_DIFFERENCE');
		if (is_array($aParam))
		{
			if (isset($aParam['result']['A']['value']) && $aParam['result']['A']['value'] === '0000')
				return intval($aParam['deviation']['C']);
		}
		return 0;
	}
}

?>