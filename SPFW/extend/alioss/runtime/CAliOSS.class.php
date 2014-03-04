<?php
use Aliyun\OSS\OSSClient;
/**
 * 阿里OSS云存储操作模块<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130817
 * @package SPFW.extend.db.runtime
 * @final
 * @example OSS操作
 * <pre>
 * $oss = new CAliOSS(); //oss操作对象
 * </pre>
 *
 * */
final class CAliOSS extends CExtModule
{
	/**
	 * 阿里云OSS主机地址(用于生成访问链接)
	 * @var string
	 */
	static $msHost = null;
	/**
	 * OSS帐号AccessKey
	 * @var string
	 */
	static $msAccessKeyId = null;
	/**
	 * OSS帐号授权码
	 * @var string
	 */
	static $msAccessKeySecret = null;
	/**
	 * OSS的Bucket
	 * @var string
	 */
	private $msBucket = null;
	/**
	 * 阿里云OSS对象
	 * @var OSSClient
	 */
	private $mOSS = null;
	/**
	 * 内部转发网桥
	 * @var string
	 */
	private $msBridge = null;

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
		require_once getMAC_ROOT() . getFW_ROOT() . 'extend/alioss/lib/aliyun.php'; //引入阿里云OSS初始化文件
		//创建OSS操作对象
		$this->mOSS = OSSClient::factory(array('AccessKeyId'=>self::$msAccessKeyId, 'AccessKeySecret'=>self::$msAccessKeySecret));
	}

	/* (non-PHPdoc)
	 * @see CExtModule::__destruct()
	 */
	public function __destruct()
	{
		unset($this->mOSS);//释放对象
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
		self::$msHost = $aCfg['host'];
		$this->msBridge = $aCfg['bridge'];
		self::$msAccessKeyId = $aCfg['AccessKeyId'];
		self::$msAccessKeySecret = $aCfg['AccessKeySecret'];
		$this->msBucket = $aCfg['defalutBucket'];
		unset($aCfg);
	}

	/**
	 * 保存文件到阿里云OSS存储的Bucket内
	 * @param string $sKey 文件访问键(不能'\'开头)
	 * @param resource $resFile 文件的资源对象 例如:fopen('./uploadify-cancel.png', 'r')
	 * @param int $iFileSize 文件大小
	 * @return string 返回文件访问URL地址
	 */
	public function put($sKey, $resFile, $iFileSize)
	{
		$this->mOSS->putObject(array
		(
			'Bucket'	=> $this->msBucket,
			'Key'		=> $sKey,
			'Content'	=> $resFile,
			'ContentLength' => $iFileSize,
		));
		return 'http://'. $this->msBucket .'.'. self::$msHost .'/'. $sKey;
	}

	/**
	 * 获取阿里云OSS存储的文件,保存到本地
	 * @param string $sKey 文件访问键(不能'\'开头)
	 * @param string $sSaveFile 保存到文件路径与文件名(可以是相对路径或绝对路径)
	 * @return bool
	 */
	public function pull($sKey, $sSaveFile)
	{
		try
		{
			$oObj = $this->mOSS->getObject(array('Bucket'=> $this->msBucket, 'Key'=> $sKey));
			$sf = fopen($sSaveFile, 'wb');
			$res = $oObj->getObjectContent();
			while (!feof ($res))
				fwrite($sf, fgets($res, 4096) );
			fclose($sf);
			fclose($res);
			unset($sf, $r);
			return true;
		}
		catch (\Aliyun\OSS\Exceptions\OSSException $ex)
		{
// 			$ex->getErrorCode();
			return false;
		}
		catch (\Aliyun\Common\Exceptions\ClientException $ex)
		{
// 			$ex->getMessage();
			return false;
		}
	}

	/**
	 * 删除阿里云OSS存储的文件
	 * @param string $sKey 文件访问键(不能'\'开头)
	 * @return void
	 */
	public function del($sKey)
	{
		$this->mOSS->deleteObject(array('Bucket'=> $this->msBucket, 'Key'=> $sKey));
	}

	/**
	 * 设置BUCKET
	 * @param string $sBacket
	 */
	public function setBucket($sBacket)
	{
		$this->msBucket = $sBacket;
	}

	/**
	 * 批量删除文件<br>
	 * 注意: 文件会被直接删除
	 * @param string $Prefix 需要批量删除的object的key前缀(不能为空)
	 * @return void
	 */
	public function deleteAll($Prefix)
	{
		static $iGroup = 50; //每次批量删除处理的分组大小

		if (empty($Prefix))
			return;

		$aGetListParam = array('Bucket'=> $this->msBucket, 'Prefix'=> $Prefix, 'MaxKeys'=>$iGroup);
		$aDeleteParam = array('Bucket'=> $this->msBucket, 'Key'=>'');
		do
		{
			$iCnt = 0;
			$objectListing = $this->mOSS->listObjects($aGetListParam); //取一组分组数据
			$aFileBuf = array(); //初始化缓存
			/*取出列表*/
			foreach ($objectListing->getObjectSummarys() as $objectSummary)
		    	$aFileBuf[] = $objectSummary->getKey();
			$iCnt = count($aFileBuf);
			unset($objectListing);
			/*删除列表内的object*/
			foreach ($aFileBuf as $sNode)
			{
				$aDeleteParam['Key'] = $sNode;
 				$this->mOSS->deleteObject($aDeleteParam);
			}
			unset($aFileBuf);
		}while($iCnt === $iGroup);
	}

	/**
	 * 获取OSS空间的主机头(http://backet + .host/)
	 * @return string
	 */
	public function getHostHead()
	{
		return 'http://'. $this->msBucket .'.'. self::$msHost. '/';
	}

	/**
	 * 将OSS外网地址替换成内网访问服务<br/>
	 * 返回: 如果是oss服务地址则输出内网转发地址，否则原样返回地址
	 * @param string $sOssKeyUrl 外网的OSS访问Key的Url
	 * @return string
	 */
	public function replaceInternalUrl($sOssKeyUrl)
	{
		if (strpos($sOssKeyUrl, self::$msAccessKeySecret) !== false)
			return 'http://'. CENV::getHost() . getWEB_ROOT() . $this->msBridge .'?u='. rawurlencode($sOssKeyUrl);
		else
			return $sOssKeyUrl;
	}
}

?>