<?php
/**
 * API服务接口类<br />
 * 备注：在这个类中编写业务接口服务的业务逻辑，使用时必须继承CWebServiceApiLogic类与IProtocolView接口。<br />
 * 如果不继承IProtocolView接口，那么当前类将不具备Protocol View的反射调用。
 * @access public
 * @final
 */
final class GET_USER_INFO extends CWebServiceApiLogic implements IProtocolView{
	/**
	 * 构造函数
	 * @see CWebServiceApiLogic::__construct()
	 */
	function __construct(){
		parent::__construct();
	}

	/*
	 * @see CWebServiceApiLogic::initResultList()
	*/
	protected function initResultList(){
		return array		(
			'0000'=>'处理成功',
			'0010'=>'用户不存在',
		);
	}

	/*
	 * @see CWebServiceApiLogic::run()
	*/
	public function run($aParam){
		$this->maXML['level'] = parent::createNode('admin');
		$this->maXML['userid'] = parent::createNode(base64_encode($aParam['name']['C']));
		$this->maXML['username'] = parent::createNode($aParam['name']['C'] .'一家');
		$this->maXML['parent'] = parent::createNode(null,
			array('mother'=>'爱心觉罗-'. $aParam['family']['A']['mother'],
				  'father'=>'欧阳'. $aParam['family']['A']['father']));
		$this->maXML['code'] = parent::createNode('330104xxxxxxxxxxxx');
		$aRow = array();
		$aRow[] = array('uid'=>'101', 'pname'=>'admin');
		$aRow[] = array('uid'=>'102', 'pname'=>'guest');
		$aRow[] = array('uid'=>'103', 'pname'=>'manager');
		$this->maXML['row'] = parent::createSameTagAttrib($aRow);
		$this->setResultState('0000');
	}

	/**
	 * @see IProtocolView::getClassExplain()
	 */
	public function getClassExplain(){
		return  '获取用户注册信息';
	}

	/**
	 * @see IProtocolView::getUseExplain()
	 */
	public function getUseExplain(){
		return '返回用户的信息列表，只有注册用户才能看到这个信息，必须填写用户ID'. "\n".
			   '返回值中的 row 为记录集。';
	}

	/**
	 * @see IProtocolView::getAccess()
	 */
	public function getAccess(){
		return 'public';
	}

	/**
	 * @see IProtocolView::getInProtocol()
	 */
	public function getInProtocol(){
		$aXml = array();
		$aXml['name'] = parent::createNode('待查询的用户名字，测试一下这个备注名称会很长很长，长到超出输入框的长度，是否会被截断[max:32]');
		$aXml['userid'] = parent::createNode('用户ID[max:32]');
		$aXml['Family'] = parent::createNode(null,
				array('mother'=>'妈妈名字[max:16]',
					  'father'=>'爸爸名字[max:16]',
					  'brother'=>'兄弟名字[max:16]'));
		return $aXml;
	}

	/**
	 * @see IProtocolView::getOutProtocol()
	 */
	public function getOutProtocol(){
		$aXml = array();
		$aXml['level'] = parent::createNode('会员级别[manage|admin|guest]');
		$aXml['userid'] = parent::createNode('用户ID[fixed:13]');
		$aXml['username'] = parent::createNode('用户的名称[max:64]');
		$aXml['parent'] = parent::createNode(null,array('father'=>'爸爸名字', 'mother'=>'妈妈名字'));
		$aXml['code'] = parent::createNode('证件号码[max:32]');
		$aRow = array();
		$aRow[] = array('uid'=>'101', 'pname'=>'admin');
		$aRow[] = array('uid'=>'...', 'pname'=>'.....');
		$aRow[] = array('uid'=>'...', 'pname'=>'.....');
		$aXml['row'] = parent::createSameTagAttrib($aRow);
		return $aXml;
	}

	/**
	 * @see IProtocolView::getUpdaueLog()
	 */
	public function getUpdaueLog(){
		return array(array('date'=>'2013-06-02', 'author'=>'jerryli', 'memo'=>'接口创建'));
	}
}

?>