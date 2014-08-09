<?php
/**
 * WebService的Client逻辑实例类<br />
 * @access public
 * @final
 */
final class develop_test_GET_USER_INFO extends CXmlArrayNode implements IWebServiceClientFunc{
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see IWebServiceClientFunc::inParamInit()
	 */
	public function inParamInit($aParam) {
		/*
		 *入口参数
		 * $aParam['name'] = '名字'
		 * $aParam['userid'] = '用户id'
		 * $aParam['mother'] = '母亲名字'
		 * $aParam['father'] = '父亲名字'
		 * $aParam['brother'] = '兄弟名字'
		 */
		$aXml = array();
		$aXml['name'] = parent::createNode($aParam['name']);
		$aXml['userid'] = parent::createNode($aParam['userid']);
		$aXml['family'] = parent::createNode(null,
			array('mother'=>$aParam['mother'], 'father'=>$aParam['father'], 'brother'=>$aParam['brother'])
		);
		return $aXml;
	}

	/* !CodeTemplates.overridecomment.nonjd!
	 * @see IWebServiceClientFunc::retParam()
	 */
	public function retParam($aResultParam) {
		$aBuf = array();
		$aBuf['level'] = $aResultParam['level']['C'];
		$aBuf['userid'] = $aResultParam['userid']['C'];
		$aBuf['username'] = $aResultParam['username']['C'];
		$aBuf['code'] = $aResultParam['code']['C'];
		$aBuf['parent'] =
			array('father'=>$aResultParam['parent']['A']['father'],
				'mother'=>$aResultParam['parent']['A']['mother']);
		return $aBuf;
	}

}

?>