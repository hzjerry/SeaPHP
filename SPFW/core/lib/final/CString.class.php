<?php
/**
 * 字符串处理类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130513
 * @package SPFW.core.lib.final
 * @final
 * @global SEA_PHP_FW_VAR_ENV
 * */
final class CString
{
	/**
	 * 字符串长度统计(全角半角都按一个单位统计)
     *
	 * @param string $content
	 * @return int
	 */
	static public function getLength($content)
	{
		return mb_strlen($content, 'utf-8');
	}

    /**
     * 通过时间和随机数得到微缩唯一码<br />
     * 返回: 10位标识ID(不可能产生重复)<br />
     * 算法: 取Unix时间戳，秒数6位 + 微秒3位(微秒前5位取整) + 1位随机码 = 10位
     * @return string
     */
    static public function getMinSeqID()
    {
        $oLM = new CLongInt2MicoStr(6, 'All');
        // 取Unix时间戳，共10位
        list($usec, $sec) = explode(" ", microtime());
        $strsec	= $oLM->toEncode($sec);//六位微缩码，根据时间搓生成
        $oLM->__construct(3, 'All');
        $strusec= $oLM->toEncode(intval($usec*100000));//3位微缩码，根据时间搓生成
        $oLM->__construct(1, 'All');
        $cRand = $oLM->toEncode(rand(0, $oLM->getAccuracy()));//1位随机码,防止重复发生
        unset($oLM);
		usleep(8); //延迟8us（防止高频调用时可能出现的重复值的问题）
        return $strsec . $strusec . $cRand;
    }
}
?>