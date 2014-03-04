<?php
/**
 * API服务接口类<br />
 * 备注：在这个类中编写业务接口服务的业务逻辑，使用时必须继承CWebServiceApiLogic类与IProtocolView接口。<br />
 * 如果不继承IProtocolView接口，那么当前类将不具备Protocol View的反射调用。
 * @access public
 * @final
 * @example 测试代码:<br />
 *  SeaPhp/WebService.php?package=system.public&class=GET_TIME_DIFFERENCE&datetime=2013-04-24%2016:24:50
 */
final class GET_TIME_DIFFERENCE extends CWebServiceApiLogic implements IProtocolView
{
	/**
	 * 构造函数
	 * @see CWebServiceApiLogic::__construct()
	 */
	function __construct()
	{
		parent::__construct();
	}

	/*
	 * @see CWebServiceApiLogic::initResultList()
	*/
	protected function initResultList()
	{
		return array
		(
			'0000'=>'处理成功',
			'0010'=>'unix_timestemp不存在',
			'0011'=>'unix_timestemp无效',
		);
	}

	/*
	 * @see CWebServiceApiLogic::run()
	*/
	public function run($aParam)
	{
		if (isset($aParam['unix_timestemp']['C']))
		{
			$iTimestamp = intval($aParam['unix_timestemp']['C']);
			if ($iTimestamp <= 0)
			{	//timestemp无效
				$this->maXML['deviation'] = parent::createNode('00');
				$this->maXML['server_timestemp'] = parent::createNode(time());
				$this->maXML['checksum_out_of_time_range'] = parent::createNode('false');
				$this->setResultState('0011');
				return;
			}

			$iDvi = time() - $iTimestamp;
			if ($iDvi === 0)
				$this->maXML['deviation'] = parent::createNode('00');
			else
				$this->maXML['deviation'] = parent::createNode($iDvi);

			if ($iDvi > 3600 || $iDvi < -3600)
				$this->maXML['checksum_out_of_time_range'] = parent::createNode('true');
			else
				$this->maXML['checksum_out_of_time_range'] = parent::createNode('false');

			$this->maXML['server_timestemp'] = parent::createNode(time());
			$this->setResultState('0000');
		}
		else
		{	//unix_timestemp不存在
			$this->maXML['deviation'] = parent::createNode('00');
			$this->maXML['server_timestemp'] = parent::createNode(time());
			$this->maXML['checksum_out_of_time_range'] = parent::createNode('false');
			$this->setResultState('0000'); //为兼容老版本的data验证方式，遇到接口参数不对时默认返回成功
		}
	}

	/**
	 * @see IProtocolView::getClassExplain()
	 */
	public function getClassExplain()
	{
		return  '获取与服务器之间的时间偏差';
	}

	/**
	 * @see IProtocolView::getUseExplain()
	 */
	public function getUseExplain()
	{
		return '返回值的：deviation,表示与Server之间的时间偏差值，正数表示client时间比Server慢，负数表示client时间比Server快'. "\n".
			   '此接口用于client与service之间的时间校对操作，以便于生成checksum节点值，正常情况checksum允许正负3600秒（1小时）的误差';
	}

	/**
	 * @see IProtocolView::getAccess()
	 */
	public function getAccess()
	{
		return 'public';
	}

	/**
	 * @see IProtocolView::getInProtocol()
	 */
	public function getInProtocol()
	{
		$aXml = array();
		$aXml['unix_timestemp'] = parent::createNode('Unix新纪元(格林威治时间1970年1月1日00:00:00)到当前时间的秒数;(php:time() | java:System.currentTimeMillis()/1000 | JavaScript:Math.round(new Date().getTime()/1000))');
		return $aXml;
	}

	/**
	 * @see IProtocolView::getOutProtocol()
	 */
	public function getOutProtocol()
	{
		$aXml = array();
		$aXml['deviation'] = parent::createNode('与Server的偏差秒数[int]');
		$aXml['server_timestemp'] = parent::createNode('服务器的时间戳[int]');
		$aXml['checksum_out_of_time_range'] = parent::createNode('是否超出checksum允许误差范围 [string:false|true]');
		return $aXml;
	}

	/**
	 * @see IProtocolView::getUpdaueLog()
	 */
	public function getUpdaueLog()
	{
		return array(array('date'=>'2013-08-07', 'author'=>'jerryli', 'memo'=>'接口创建'));
	}
}

?>