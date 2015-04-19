<?php
/**
 * 变量值校验类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20150211
 * @package SPFW.core.lib.final
 * @final
 * @example
 * CValCheck::CK($sVal, $sRule);<br />
 * CValCheck::CK('hzjerry@gmail.com', array('email', 'strlen'=>array(5, 16)))<br />
 * */
final class CValCheck{
	/**
	 * 检查变量
	 * @param mixed $mixed	待检查的变量
	 * @param array $aRule	检查规则参数(一维数组),多个参数','分隔<br />
	 * 'isnum': //是否为数字或数字字符串<br />
	 * 'isint': //是否为整型或整型字符串(只包含0～9的数字)<br />
	 * 'strlen'=>array(最小长度int , 最大长度int) //字符串长度域检查(utf-8)注意:全角字符与半角字符同样对待<br />
	 * 'range'=>array(最小值int , 最大值int) //数值范围域检查<br />
	 * 'date': //是否为日期(yyyy/mm/dd | yyyy-mm-dd)<br />
	 * 'time': //是否为时间(hh:mm:ss)<br />
	 * 'datetime': //是否为时间日期([yyyy/mm/dd | yyyy-mm-dd] hh:mm:ss)<br />
	 * 'ipv4': //是否为IPV4<br />
	 * 'domin': //是否为IPV4<br />
	 * 'email': //是否为Email<br />
	 * 'chinese': //是否为全中文(utf-8)<br />
	 * 'having_chinese': //是包含中文中文(utf-8)<br />
	 * 'istel': //是否为合法电话号码<br />
	 * 'isphone': //是否为合法手机号码<br />
	 * 'isurl': //是否为合法URL地址<br />
	 * @return boolean
	 * @example
	 * CValCheck::CK($sVal, $sRule);<br />
	 * CValCheck::CK('lijian@dns.com.cn', array('email', 'strlen'=>array(5, 16)))<br />
	 */
	static public function CK($mixed, $aRule){
		//分离数组中的值与属性
		$aSingle = array(); //单值
		$aProp = array(); //属性
		if (empty($aRule))
			return false;
		if (is_string($aRule)) //自动校正（默认输入应该为数组）
			$aRule = array($aRule);
		foreach ($aRule as $Key=>$Val){
			if(is_int($Key))
				array_push($aSingle, $Val);
			else
				$aProp[$Key] = $Val;
		}
		unset($aRule);

		/*是否为数字或数字字符串*/
		if (in_array('isnum', $aSingle) && !is_numeric($mixed))
			return false;
		/*是否为整型或整型字符串(只包含0～9的数字)*/
		if (in_array('isint', $aSingle) && !ctype_digit(strval($mixed)))
			return false;
		/*输入字符串长度检查*/
		if (isset($aProp['strlen']) && !self::strlen($mixed, $aProp['strlen']))
			return false;
		/*数字域范围检查*/
		if (isset($aProp['range']) && !self::range($mixed, $aProp['range']))
			return false;
		/*是否为日期(yyyy/mm/dd | yyyy-mm-dd)*/
		if (in_array('date', $aSingle) && !self::isData($mixed))
			return false;
		/*是否为时间(hh:mm:ss)*/
		if (in_array('time', $aSingle) && !self::isTime($mixed))
			return false;
		/*是否为时间日期([yyyy/mm/dd | yyyy-mm-dd] hh:mm:ss)*/
		if (in_array('datetime', $aSingle) && !(self::isData(substr($mixed, 0, 10)) && self::isTime(substr($mixed, 11, 8))) )
			return false;
		/*是否为Email*/
		if (in_array('email', $aSingle) && !self::isEmail($mixed))
			return false;
		/*是否为IPV4*/
		if (in_array('ipv4', $aSingle) && !self::isIPV4($mixed))
			return false;
		/*是否为域名*/
		if (in_array('domin', $aSingle) && !self::isDomain($mixed))
			return false;
		/*是否为全中文(utf-8)*/
		if (in_array('chinese', $aSingle) && !self::isChinese($mixed))
			return false;
		/*是否包含中文(utf-8)*/
		if (in_array('having_chinese', $aSingle) && !self::havingChinese($mixed))
			return false;
		/*是否为合法电话号码*/
		if (in_array('istel', $aSingle) && !self::isTel($mixed))
			return false;
		/*是否为合法URL地址*/
		if (in_array('isurl', $aSingle) && !self::isUrl($mixed))
			return false;
		/*是否为合法手机号*/
		if (in_array('isphone', $aSingle) && !self::isPhone($mixed))
			return false;

		return true;
	}

	/**
	 * 字符串长度范围验证(utf-8)
	 * @param string $sStr	待检查的字符串
	 * @param array $aRule 规则array(最小长度int , 最大长度int)或array(定长int)
	 * @return boolean
	 */
	static private function strlen(& $sStr, & $aRule){
		$i = mb_strlen($sStr, 'utf-8');
		if (count($aRule) == 1){
			return ($i == $aRule[0]);
		}else{
			sort($aRule);
			list($iMin, $iMax) = $aRule;
			return ($i >= $iMin && $i<=$iMax);
		}
	}

	/**
	 * 数字域范围检查
	 * @param number $mun
	 * @param array $aRule
	 * @return boolean
	 */
	static private function range(& $mun, & $aRule){
		sort($aRule);
		list($iMin, $iMax) = $aRule;
		return ($mun >= $iMin && $mun<=$iMax);
	}

	/**
	 * 是否为有效日期
	 * @param string $sData (yyyy/mm/dd | yyyy-mm-dd)
	 * @return boolean
	 */
	static private function isData($sData){
		if (empty($sData))
			return false;
		$test = strtotime($sData);
		return ($test !== -1 && $test !== false);
	}

	/**
	 * 是否为有效时间
	 * @param string $sTime (hh:mm:ss)
	 * @return boolean
	 */
	static private function isTime($sTime){
		if (empty($sTime))
			return false;
		$aTime = explode(':', $sTime);
		if (count($aTime) == 3){
	        if (intval($aTime[0]) < 0 || intval($aTime[0]) >23)
	        	return false;
	        elseif (intval($aTime[1]) < 0 || intval($aTime[1]) >59)
	        	return false;
	        elseif (intval($aTime[2]) < 0 || intval($aTime[2]) >59)
	        	return false;
	        else
	        	return true;
		}else
        	return false;
	}

	/**
	 * 是否为IPV4
	 * @param $sData $sip IP地址(255:255:255:255)
	 * @return boolean
	 */
	static private function isIPV4(& $sip){
		if (empty($sip))
			return false;
        $test = ip2long($sip);
        return $test !== -1 && $test !== false;
	}

	/**
	 * 是否为域名
	 * @param string $s
	 * @return boolean
	 */
	static private function isDomain(& $s){
		if (empty($s))
			return false;
		return preg_match('/[a-z0-9\.]+/i', $s) != 0;
	}

	/**
	 * 是否为邮件地址
	 * @param string $s
	 * @return boolean
	 */
	static private function isEmail(& $s){
		if (empty($s))
			return false;
        $spg = '/^[A-Za-z0-9]+([._\-\+]*[A-Za-z0-9]+)*@([A-Za-z0-9]+[-A-Za-z0-9]*[A-Za-z0-9]+\.)+[A-Za-z0-9]+$/';
        return preg_match($spg, $s) != 0;
    }

    /**
     * 是否为全中文(utf-8)
     * @param string $str
     * @return bool
     */
    static private function isChinese(& $s){
    	if (empty($s))
    		return false;
    	return preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$s) != 0;
    }

    /**
     * 是否包含中文全角字符(utf-8)
     * @param string $s
     * @return bool
     */
    static private function havingChinese(& $s){
    	if (empty($s))
    		return false;
    	return preg_match("/[\x{4e00}-\x{9fa5}]/u",$s)? true : false;
    }

    /**
     * 是否为有效的电话号码
     * @param string $s
     * @return bool
     */
	static private function isTel(& $s){
		return (ereg("^[+]?[0-9]+([-][0-9]+)*$", $s) > 0);
	}

	/**
	 * 是否为有效的URL地址
	 * @param string $s
	 * @return bool
	 */
	static private function isUrl(& $s){
		return preg_match('/^http[s]?:\/\/'.
			'(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184
			'|'. // 允许IP和DOMAIN（域名）
			'([0-9a-z_!~*\'()-]+\.)*'. // 域名- www.
			'([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域名
			'[a-z]{2,6})'.  // first level domain- .com or .museum
			'(:[0-9]{1,4})?'.  // 端口- :80
			'((\/\?)|'.  // a slash isn't required if there is no file name
			'(\/[0-9a-zA-Z_!~`\'\(\)\[\]\.;\?:@&=\+\$,%#-\/^\*\|]*)?)$/',
			$s) == 1;
	}
	/**
	 * 是否为有效的手机号
	 * @param string $s
	 * @return bool
	 */
	static private function isPhone($s){
		$aNumber = array('０'=>0, '１'=>1, '２'=>2, '３'=>3, '４'=>4, '５'=>5, '６'=>6, '７'=>7, '８'=>8, '９'=>9);
		$sPhone = strtr($s, $aNumber);//全角过滤成半角
		$regex = '/^1[3|4|7|8][0-9]{9}$|15[0|1|2|3|5|6|7|8|9]\d{8}$/';
		if ( preg_match($regex, $sPhone) ){
			return true;
		}else{
			return false;
		}
	}
}
?>