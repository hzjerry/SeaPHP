<?php
/**
 * 接口链接配置类<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20140702
 * @package SPFW.extend.webservice_client.config
 * @final
 *
 * */
final class CWscCfgTest implements IWSC_Cfg{
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see IWSC_Cfg::getUrl()
	 */
	public function getUrl() {
		return 'http://seaphp.fox.cn:8080/WebService.php';
	}

	/* !CodeTemplates.overridecomment.nonjd!
	 * @see IWSC_Cfg::getPublicKey()
	 */
	public function getPublicKey() {
		return array(
			'develop' => 'www.seaphp.org$develop*~`!#%^&',
			);
	}
}
?>