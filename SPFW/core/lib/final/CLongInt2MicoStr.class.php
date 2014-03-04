<?php
/**
 * 长整型数值微缩编码类
 *   位数与精度对应关系:
 *   All [1:0~74/2:0~5624/3:0~421874/4:0~31640624/5:0~2373046874/6:0~177978515624/7:0~13348388671874]
 *   Safe [1:0~28/2:0~840/3:0~24388/4:0~707280/5:0~20511148/6:0~594823320/7:0~17249876308]
 *   备注:2010-12-25 程序创立 v1.2
 *   'All':全字符集' 模式中 '*' '|' 这两个字符不能用于文件名，需要注意。
 * @version 1.5
 * @author Jerry.Li(hzjerry@gmail.com)
 * @package SPFW.core.lib.final
 * @final
 * @copyright http://b.dzs.mobi/
 * @update
 *   v1.3 对maPool缓冲池做了调整，按照ascii的顺序排列
 *   v1.4 2013-02-25对encode()与toEncode()做了修改，php存在取模溢出的bug，更换了函数
 *   v1.5 2013-05-14对encode()做了修改，fmod返回值需转成int
 * @example
 *   $obj = new CLongInt2MicoStr(5, 'All');
 *   echo $obj->toEncode(987654) ."\n"; //02I"Y
 *   echo $obj->toDecode('0tPaB') ."\n";//24290827
 *   print_r($obj->getAccuracyList());
 *   unset($obj);
 */
final class CLongInt2MicoStr
{
    /**
     * 基础字符串池
	 *   备注:字符串的排序均按照ascii的顺序规则，可用于生成数据库的char主键
     *
     * @var array
     */
    private static $maPool =
        array('!$(),-', //url可使用的非转义字符
              '0123456789',	//数字区
              ';', //url可使用的非转义字符
              'ABCDEFGHIJKLMNOPQRSTUVWXYZ',//大写英文区
              '[]^_`', //url可使用的非转义字符
              'abcdefghijklmnopqrstuvwxyz',//小写英文区
              '~', //url可使用的非转义字符
              '3478ABDEFGHIJLNRTadefghijnrtz',	//适合于用户观看输入的安全字符串
             );

    /**
     * 基础关键字排列可表达数范围
     *     备注:保存当前所用关键字的可表达最大的数字范围
     *
     * @var int
     */
    private $miBaseKeyNum;

    /**
     * 基础编码关键字字符集(字符串)
     *
     * @var string
     */
    private $msBaseKey;

    /**
     * 基础编码关键字字符集(数组:每个字符为一个单位)
     *
     * @var array
     */
    private $maBaseKey;

    /**
     * N进制基数
     *  例:29进制,77进制
     *
     * @var string
     */
    private $miNHex;

    /**
     * 输出精度位数
     *
     * @var int
     */
    private $miAccuracy;

    /**
     * 构造函数
     *
     * @param int $iAccuracy 精度位数(输出结果为有效定长位数)
     * @param string $sCharSet ('All':全字符集' / 'Safe':安全字符集)
     * @throws 0:构造失败。$sCharSet超范围
     *         1:构造失败。$iAccuracy精度必须≥2
     */
    function __construct($iAccuracy=5, $sCharSet='All')
    {
        if (in_array($sCharSet, array('All', 'Safe')))
        {
            if ($iAccuracy < 1)
                throw new Exception(__CLASS__ .' 构造失败。$iAccuracy精度必须≥2', 1);
            else
                $this->miAccuracy = $iAccuracy;//设置处理精度位数

            /*初始化基础数据*/
            //生成基础字符集
            $this->msBaseKey = ($sCharSet == 'All')? $this->getAllString() : $this->getSafeString();
            //计算字符集的最大可表示范围
            $this->miBaseKeyNum = pow(strlen($this->msBaseKey), $this->miAccuracy);
            //计算进制
            $this->miNHex = strlen($this->msBaseKey);
            //生成数组类型的基础数据集
            for($i=0; $i<$this->miNHex; $i++)
                $this->maBaseKey[] = $this->msBaseKey{$i};
        }
        else
        {
            throw new Exception(__CLASS__ .' 构造失败。$sCharSet超范围', 0);
        }
    }

    /**
     * 返回整个可用的不重复字符串
     *
     * @return string
     */
    private final function getAllString()
    {
        return self::$maPool[0] . self::$maPool[1] . self::$maPool[2] . self::$maPool[3] .
			   self::$maPool[4] . self::$maPool[5] . self::$maPool[6];
    }

    /**
     * 返回适合手写的字符串
     *
     * @return string
     */
    private final function getSafeString()
    {
        return self::$maPool[7];
    }

    /**
     * 获取关键字的权值
     * @param char $cChr
     * @return int 0<= ret <=strlen($this->miNHex)
     */
    private final function getKeyRight($cChr)
    {
        return strpos($this->msBaseKey, $cChr);
    }

    /**
     * 十进制，转为N进制(内部函数)
     *
     * @param int $iSro 待编码的数字
     * @param array $aBuf 输出缓存(需要外部传入存储变量)
     */
    private final function encode($fSro, & $aBuf)
    {
        if ($fSro > $this->miNHex)
            $this->encode(floor($fSro / $this->miNHex), $aBuf);
        elseif($fSro == $this->miNHex)
            $aBuf[] = $this->msBaseKey{1};
         $aBuf[] = $this->msBaseKey{intval(fmod($fSro, $this->miNHex))};
    }

    /**
     * N进制编码->十进制解码
     *
     * @param string $sSro N进制数值字符串
     * @return int
     */
    public final function toDecode($sSro)
    {
        /*剔除前导0占位符*/
        while($sSro{0}=='!')
            $sSro = substr($sSro, 1);
        /*开始解码*/
        $iBuf = 0;
        $iLen = strlen($sSro);
        for ($i=$iLen-1; $i>=0; $i--)
            $iBuf += pow($this->miNHex, $i) * $this->getKeyRight($sSro{abs($i - $iLen)-1});
        return $iBuf;
    }

    /**
     * 十进制->N进制编码
     *
     * @param int $iSro 需要编码的十进制数
     * @return string
     * @throws 2:编码失败$iSro超精度
     */
    public function toEncode($iSro)
    {
        if ($iSro < $this->miBaseKeyNum) //超精度判断
        {
        	settype($iSro, 'float'); //指定为浮点类型
            $aBuf = array();
            $this->encode($iSro, $aBuf);
            //当长度不够时前导字符补!
            return str_pad(join('', $aBuf), $this->miAccuracy ,'!', STR_PAD_LEFT);
        }
        else
        {
            throw new Exception(__CLASS__ .' 编码失败!'. $iSro .'超精度，'.
                                '有效范围:0-'. $this->miBaseKeyNum, 2);
            return null;
        }
    }

    /**
     * 返回各个位数精度的范围值
     *  备注:（主要用于查询位数的精度范围，无实用价值）
     *
     * @return array(array('Accuracy'=>'位数', 'Range'=>'范围')....)
     */
    public function getAccuracyList()
    {
        $aTmp = array();
        for ($i=1; $i<=7; $i++)
            $aTmp[] = array('Accuracy'=>$i, 'Range'=>'0~'. (pow($this->miNHex, $i) - 1));
        return $aTmp;
    }

    /**
     * 返回当前精度位的标示范围0~N
     *
     * @return int
     */
    public function getAccuracy()
    {
        return $this->miBaseKeyNum - 1;
    }

    /**
     * 检查待转换的字符是否合法
     *
     * @param string $sCode N进制代码
     * @return bool
     */
    public function checkSafe($sCode)
    {
        $iCodeLen = strlen($sCode);
        for($i=0; $i<$iCodeLen; $i++)
            if (!in_array($sCode{$i}, $this->maBaseKey))
                return false;
        return true;
    }
}
?>