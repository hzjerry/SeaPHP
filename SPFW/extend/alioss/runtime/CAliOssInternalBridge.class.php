<?php
/**
 * 阿里OSS云存储操作内部转发网桥<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130817
 * @package SPFW.extend.db.runtime
 * @final
 * @example OSS操作
 * <pre>
 * 	CExtendManage::Run(new CAliOssInternalBridge());
 * </pre>
 *
 * */
final class CAliOssInternalBridge extends CExtModule implements IExtFramework
{
	/**
	 * 阿里云OSS内部主机地址(用于生成访问链接)
	 * @var string
	 */
	static $msInternalHost = null;
	/**
	 * 阿里云OSS外部访问主机地址
	 * @var string
	 */
	static $msHost = null;
	/**
	 * 文件类型映射表
	 * @var array
	 */
	static $maFileType = array
	(
		'gif'=>'image/gif', 'png'=>'image/png', 'jpg'=>'image/jpeg',
		'mp3'=>'audio/mp3'
	);

	/* (non-PHPdoc)
	 * @see CExtModule::autoloadProfile()
	 */
	protected function autoloadProfile()
	{
	}

	/* (non-PHPdoc)
	 * @see CExtModule::setName()
	 */
	protected function setName($sModule = __CLASS__)
	{
		$this->msModule = $sModule;
	}

	/**
	 * 构造函数<br />
	 * @param string $sDSN 数据库连接配置（配置文件必须存放于extend.db.dsn下，文件名与类名相同）
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loagCfg(); //载入全局配置信息
	}

	/* (non-PHPdoc)
	 * @see CExtModule::__destruct()
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	/* (non-PHPdoc)
	 * @see CExtModule::isAbleRun()
	 */
	public function isAbleRun()
	{
		$aRet = array();
		if(version_compare(PHP_VERSION, '5.3.2', '<=') )
			$aRet[__CLASS__. ' (OSS PHP SDK Must be greater than 5.3.2)'] = false;
		else
			$aRet[__CLASS__. ' (OSS PHP SDK Must be greater than 5.3.2)'] = true;

		return $aRet;
	}

	/**
	 * 载入环境配置文件
	 * @return void
	 * @access private
	 */
	private function loagCfg()
	{
		$aCfg = import('extend.alioss.config', 'environment.cfg.php');
		self::$msInternalHost = $aCfg['host_internal'];
		self::$msHost = $aCfg['host'];
		unset($aCfg);
	}
	/* !CodeTemplates.overridecomment.nonjd!
	 * @see IExtFramework::start()
	 */
	public function start()
	{
		if (!isset($_GET['u']))
		{
			ob_end_clean(); //清空输出缓存中的内容;
			header('HTTP/1.1 404 Not Found');
			exit();
		}
		else
		{
			$sUrl = $_GET['u'];
			if (strpos($sUrl, self::$msHost) === false)
			{
				ob_end_clean(); //清空输出缓存中的内容;
				echo 'Non-oss file.';
				exit();
			}
			else //将外网地址替换成内网访问地址
				$sUrl = str_replace(self::$msHost, self::$msInternalHost, $sUrl);

			$sExt = strtolower(substr($sUrl, strrpos($sUrl, '.')+1)); //获取扩展名
			if (!isset(self::$maFileType[$sExt]))
			{
				ob_end_clean(); //清空输出缓存中的内容;
				echo 'File extensions are not supported.';
				exit();
			}
			if (($f = fopen($sUrl, 'rb')) !== false)
			{	//读取数据并输出
				ob_end_clean(); //清空输出缓存中的内容;
				header('Content-type: '. self::$maFileType[$sExt]);
				do
				{
					$data = fread($f, 10240);
					if (strlen($data) === 0)
						break;
					else
						echo $data; //送出收到的内容
					unset($data);
				}while(true);
			}
		}
	}
}
?>