<?php
header('Content-Type: text/html; charset=UTF-8');
/*seaphp框架引入*/
define('SEA_PHP_ROOT', '/');
require './SPFW/sea_php_init.php';

/**
 * PHP的aJax文件上传服务端处理程序<br/>
 * 上传时需要通过POST方式把文件存储的本地路径指定,参数名:package(必须), rename(可选)
 * package:内容格式为基于uploadfile::sUPLOAD_ROOT这个根目录下的相对路径包表示方式，目录层次关系以'.'分割。
 * 例如:package=user.imgs 指uploadfile::sUPLOAD_ROOT.'user/img/'目录<br/>
 * rename:如果需要将上传文件名改名保存，可以供这个参数（是限于文件名，扩展名不会被修改）
 * 页面返回json格式数据.数据格式如下:
 * 正常: {'state':true, 'webpath':'本地保存路径', 'alioss':'阿里oss访问路径', 'size':'文件大小byte', 'runtime':'处理时间ms'};
 * 错误: {'state':false,};
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130819
 * @see 程序依赖Web前台的Js异步处理程序提交过来的数据,详情参见:upload_test.htm
 * */
class uploadfile
{
	/**
	 * 相对于网站根的上传文件保存目录
	 * @var string
	 */
	const sUPLOAD_ROOT = 'FileStore/';
	/**
	 * 是否保存文件到本地
	 * @var bool
	 */
	static $mbToLocal = true;
	/**
	 * 是否上传到阿里OSS云存储空间
	 * @var bool
	 */
	static $mbToAliOSS = false;

	/**
	 * 构造函数
	 * @return void
	 */
	function __construct()
	{
		$aUpload = self::checkUpload();
		if (!is_null($aUpload) && $aUpload['state'])
		{
			$sPkg = (isset($_POST['package']))? $_POST['package'] : null; //相对于上传文件存储区的包路径
			$sRename = (isset($_POST['rename']))? $_POST['rename'] : null; //是否要对文件名改名
			$sLocalPath = getMAC_ROOT() . getWEB_ROOT() . self::sUPLOAD_ROOT . str_replace('.', '/', $sPkg) .'/';
			$sWebRoot= getWEB_ROOT() . self::sUPLOAD_ROOT . str_replace('.', '/', $sPkg) .'/';
			$sFileName = substr($aUpload['name'], 0, strrpos($aUpload['name'], '.')); //取出文件名字
			$sFileExt = substr($aUpload['name'],strpos($aUpload['name'], '.')+1); //取出文件扩展名
			//文件下载成功,移动到指定目录
			if (!file_exists($sLocalPath)) //目录不存在时创建
			{
				if (!CFileOperation::creatDir($sLocalPath))
				{	//目录创建失败提示用户
					echo json_encode(array('state'=>false, 'code'=>-1, 'msg'=>'无法创建保存目录'));
					return;
				}
			}
			//文件移动到下载存储区(本地存储备份)
			if (!empty($sRename))
				$sFileName = $sRename;
			else
				$sFileName = md5($sFileName);//对文件名称做md5编码

			if(self::$mbToLocal)
				move_uploaded_file($aUpload['tmp_name'], $sLocalPath . $sFileName .'.'. $sFileExt);

			$sAliOSSPath = null;
			if (self::$mbToAliOSS)
			{	//文件同时上传到阿里OSS云空间
				$sKey = substr($sWebRoot, 1) . $sFileName .'.'. $sFileExt;
				$resFile = fopen($sLocalPath . $sFileName .'.'. $sFileExt, 'r');
				$iFileSize = filesize($sLocalPath . $sFileName .'.'. $sFileExt);
				$oss = new CAliOSS(); //oss操作对象
				$sAliOSSPath = $oss->put($sKey, $resFile, $iFileSize); //上传文件
				unset($oss);//释放对象
			}

			//输出上传后的文件参数信息
			echo json_encode(array('state'=>true,
				'webpath'=>'http://'. $GLOBALS['SEA_PHP_FW_VAR_HOST'] . $sWebRoot . $sFileName .'.'. $sFileExt,
				'alioss'=>$sAliOSSPath, //输出阿里云OSS访问路径
				'size'=>$aUpload['size'],
				'runtime'=>CENV::getRuntime(),
				));
			return;
		}
		else
		{	//返回错误信息
			if (!is_null($aUpload))
			{
				echo json_encode(array('state'=>false,
						'code'=>$aUpload['code'],
						'msg'=>$aUpload['msg'],
				));
				return;
			}
			else
			{
				echo json_encode(array('state'=>false, 'code'=>-2, 'msg'=>'未上传文件,非法访问.'));
				return;
			}
		}
	}

	/**
	 * 检查是否存在上传文件<br/>
	 * 返回结果集合: <br/>
	 * array('state'=>true, 'tmp_name'=>'临时文件路径', 'name'=>'文件名', 'size'=>'文件大小', 'type'=>'文件类型'); //成功<br/>
	 * array('state'=>false, 'code'=>'错误代码', 'msg'=>'错误解释');//失败<br/>
	 * @return array|null
	 */
	static private function checkUpload()
	{
		if (isset($_FILES) && is_array($_FILES))
		{
			if (0 == $_FILES["Filedata"]['error'])
			{
				$aRet = array();
				$aRet['state'] = true;
				$aRet['tmp_name'] = $_FILES['Filedata']['tmp_name'];
				$aRet['name'] = $_FILES['Filedata']['name'];
				$aRet['size'] = intval($_FILES['Filedata']['size']);
				$aRet['type'] = $_FILES['Filedata']['type'];
				return $aRet;
			}
			else
			{
				$aRet = array();
				$aRet['state'] = false;
				$aRet['code'] = $_FILES['Filedata']['error']; //获得错误代码
				switch ($_FILES['Filedata']['error'])
				{
					case 1:
						$aRet['msg'] = 'The file is bigger than this PHP installation allows';
						break;
					case 2:
						$aRet['msg'] = 'The file is bigger than this form allows';
						break;
					case 3:
						$aRet['msg'] = 'Only part of the file was uploaded';
						break;
					case 4:
						$aRet['msg'] = 'No file was uploaded';
						break;
					case 6:
						$aRet['msg'] = 'Missing a temporary folder';
						break;
					case 7:
						$aRet['msg'] = 'Failed to write file to disk';
						break;
					case 8:
						$aRet['msg'] = 'File upload stopped by extension';
						break;
					default:
						$aRet['msg'] = 'unknown error';
						break;
				}
			}
		}
		else //没有上传文件,非法访问
			return null;
	}
}

//执行上传类
new uploadfile();
?>