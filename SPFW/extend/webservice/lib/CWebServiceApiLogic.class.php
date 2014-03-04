<?php
/**
 * Web Service API Logic 基类<br/>
 *  备注:必须实现 run(), initResultList()方法。<br/>
 *  在构造函数__construct()中要首先执行父类构造函数parent::__construct();
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130416
 * @package SPFW.extend.webservice.lib
 * @abstract
 */
abstract class CWebServiceApiLogic extends CXmlArrayNode
{
	/**
	 * XML数组对象存储区<br/>
	 * 用于保存需要返回给客户端的XML数组包
	 * @var array
	 */
	protected $maXML = null;
	/**
	 * API服务定义的状态值列表<br />
	 * 数组结构:array('状态代码'=>'状态代码解释');
	 * @var array
	 */
	protected $maResultStateList = null;
	/**
	 * 状态返回值<br/>
	 *  结构:array('code'=>'代码', 'msg'=>'代码信息解释');
	 * @var array
	 */
	private $maResultState = null;

	/**
	 * 构造函数<br />
	 * 注意:子类必须在构造函数中首先调用父类的构造函数
	 * @example
	 * <pre>
	 * public function __construct()
	 * {
	 *     parent::__construct();
	 * }
	 * </pre>
	 */
	public function __construct()
	{
		$this->maResultStateList = $this->initResultList();
	}

	/**
	 * 设置返回值
	 * @param string $sCode 返回值代码
	 * @return void
	 * @access protected
	 */
	protected function setResultState($sCode)
	{
		if (array_key_exists($sCode, $this->maResultStateList))
		{	//在状态返回值表中找到对应值
			$this->maResultState =
				array('code'=>$sCode, 'msg'=>$this->maResultStateList[$sCode]);
		}
		else //未找到对因状态值
			$this->maResultState =
				array('code'=>$sCode, 'msg'=>'Undefined state value.');
	}

	/**
	 * 获取送回的 XML结构数组<br/>
	 * 必须在 run()执行后执行本函数才能得到返回值
	 * @return array | null
	 */
	public function getResult()
	{
		if (isset($this->maResultState['code']))
		{
			$this->maXML['result']['A'] =
				array('value'=>$this->maResultState['code'],
					  'msg'=>$this->maResultState['msg']);
			return $this->maXML;
		}
		else
			return $this->maXML;
	}

	/**
	 * 获取返回状态列表<br />
	 *  备注：只有构造函数中先执行父类的构造函数，才能在这个函数中取到值。
	 * @return array
	 */
	public function getResultStateList()
	{
		return $this->maResultStateList;
	}

	/**
	 * 业务逻辑入口
	 * @param array $aParam 入口参数XML结构数组
	 * @return void
	 * @access public
	 * @abstract
	 * @example
	 * <pre>
	 * public function run($aParam)
	 * {
	 *   $this->maXML['username'] = parent::createNode('小王');
	 *   $this->maXML['parent'] = parent::createNode(null, array('mather'=>'小王妈', 'father'=>'小王爸'));
	 *   $aRow = array();
	 *   $aRow[] = array('uid'=>'101', 'pname'=>'admin');
	 *   $aRow[] = array('uid'=>'102', 'pname'=>'guest');
	 *   $aRow[] = array('uid'=>'103', 'pname'=>'manager');
	 *   $this->maXML['row'] = parent::CreateSameTagNode($aRow);
	 *   $this->setResultState('0000');
	 * }
	 * </pre>
	 */
	abstract public function run($aParam);

	/**
	 * 初始化返回状态值列表<br/>
	 *   备注：返回值数组结构 array('code'=>'value', ...);
	 * @return array
	 * @access protected
	 * @abstract
	 * @example
	 * <pre>
	 * protected function initResultList()
	 * {
	 *   return array
	 *   (
	 *   '0000'=>'处理成功',
	 *   '0010'=>'缺少xxx节点参数',
	 *   ....
	 *   );
	 * }
	 * </pre>
	 */
	abstract protected function initResultList();
}

?>