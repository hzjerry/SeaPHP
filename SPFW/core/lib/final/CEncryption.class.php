<?PHP
/**
 * 字符串加密类
 * <li><strong>注意1:</strong>通过本加密字符串处理后的字符串，通过GET方式传输时，需要做urlencode编码处理</li>
 * <li><strong>注意2:</strong>本类之适合与对传输的数据加密，不适合对静态存储的数据加密。因为没有公钥与私钥算</li>
 * <li>加密原理，对字符串进行Base64编码，并且加入2位校验码来保证字符串完整性(数据区任何一位编码被修改将导致无法解码)。</li>
 * <li>针对源字符串:尝试gz压缩算法，如果压缩后的字符串比源字符串小，则进行压缩，并做上标记。</li>
 * <li>字符串码位:{数据区(N)}{0-1(1):gz压缩标志}{0-f(1):base64校验码}{0-f(1):md5校验码}</li>
 *
 * @author		Jerry.Li
 * @access		public
 * @package	CEncryption
 * @version	0.1 (2009/10/22)
 * @example
 * $str = B64SafeEncode('ssssssssss');
 * $str = B64SafeDecode($str);
 * echo $str;
*/
final class CEncryption
{
    /**
     * 构造
     */
    function __construct()
    {
        if ( !(function_exists("gzencode")) )
        {
            echo 'CEncryption 加载zlib库错误，请安装zlib库后再尝试。';
            exit(0);
        }
    }

    /**
     * 针对系统的gzencode函数，进行解码
     *
     * @param string $data
     * @return string
     */
    private function gzdecode($data)
    {
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4)
        {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8)
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 16)
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 2)
            $headerlen += 2;
            $unpacked = @gzinflate(substr($data, $headerlen));
        if ($unpacked === FALSE)
            $unpacked = $data;

        return $unpacked;
    }

    /**
     * 检测是否符合HEX进制的字符
     *
     * @param string $cChr 需要检测的一个字符串
     * @return bool
     */
    private function checkHex($cChr)
    {
        $iAsc = ord(strtolower($cChr));
        if (($iAsc >= 48 && $iAsc <= 57) || ($iAsc >= 97 && $iAsc <= 102))
			return true;
        else
			return false;
    }

    /**
     * 安全的字符串加密函数
     *   加密后的内容可用作传输，以base64编码为核心（带校验码）
     *
     * @param string $sBuf
     * @return string
     */
    public function B64SafeEncode($sBuf)
    {
        /*第一步:检测是否有压缩价值*/
        $sGCode = gzencode($sBuf, 9);
        $bGZflg = false;
        if (strlen($sBuf) > strlen($sGCode))
        {   //存在字符串压缩价值
            $sBuf = $sGCode;
            $bGZflg = true;
        }
        $aChecksum[] = ($bGZflg)? '1' : '0';//生成压缩标记

        /*第二步:对字符串做base64编码*/
        $sBCode = base64_encode($sBuf);
        /*第三步:计算base64校验码*/
        $iLen = strlen($sBCode);
        for($i=0, $iChk=0; $i < $iLen; $i++)
            $iChk += ord(substr($sBCode, $i, 1));
        $aChecksum[] = dechex($iChk % 16);
        /*第四步:计算md5校验码*/
        $sMCode = md5($sBCode);
        for($i=0, $iChk=0; $i < 32; $i++)
            $iChk += ord(substr($sMCode, $i, 1));
        $aChecksum[] = dechex($iChk % 16);

        return $sBCode . join('', $aChecksum);
    }

    /**
     * 安全的字符串加密解码函数
     *
     * @param string $sBuf
     * @return string
     */
    public function B64SafeDecode($sBuf)
    {
        /*第一步:取出附加码位*/
        $sTmp = substr($sBuf, -3);
        $sFlg = substr($sTmp, 0, 1);//取出压缩码
        if ($sFlg == '0' || $sFlg == '1')
            $aChecksum[] = ($sFlg == '1')? true : false;
        else//码位的值不正常
            return null;
        $sFlg = substr($sTmp, 1, 1);//取出base64校验码
        if ($this->checkHex($sFlg))
            $aChecksum[] = $sFlg;
        else//码位的值不正常
            return null;
        $sFlg = substr($sTmp, 2, 1);//取出md5校验码
        if ($this->checkHex($sFlg))
            $aChecksum[] = $sFlg;
        else //码位的值不正常
            return null;
        $sBCode = substr($sBuf, 0, -3);//取出待解码的内容部分

        /*第二步:检测校验码*/
        $iLen = strlen($sBCode);
        for($i=0, $iChk=0; $i < $iLen; $i++)
            $iChk += ord(substr($sBCode, $i, 1));
        if (dechex($iChk % 16) != $aChecksum[1])
            return null;//Base64编码校验未通过

        $sMCode = md5($sBCode);
        for($i=0, $iChk=0; $i < 32; $i++)
            $iChk += ord(substr($sMCode, $i, 1));
        if (dechex($iChk % 16) != $aChecksum[2])
            return null;//md5编码校验未通过

        /*第三步:字符串解压,与反编译处理*/
        if ($aChecksum[0])
            return $this->gzdecode(base64_decode($sBCode));
        else
			return base64_decode($sBCode);
    }
}
?>