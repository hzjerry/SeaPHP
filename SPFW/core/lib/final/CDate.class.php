<?php
/**
 * 日期处理类
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130413
 * @package SPFW.core.lib.final
 * @global SEA_PHP_FW_VAR_ENV
 * */
final class CDate
{
    /**
     * 初始记录时间，用于即时函数
     *
     * @var float
     */
    private $m_fStartTime;

    /**
     * 构造函数
     * 初始化计时器
     * */
    function CDate()
    {
        $this->Counter();//默认初始化计数器
    }

    /**
     * 计时器，用于进行毫秒级的统计(毫秒级别)
     * @param string    $Ctrl   控制[Start/End]
     * @return float    单位(ms)
     * @access public
     */
    public function Counter($sCtrl='Start')
    {
        if ($sCtrl == 'Start')
        {
            //记录开始时间
            list($usec, $sec) = explode(' ',microtime());
            $this->m_fStartTime = (float)$usec + (float)$sec;
            return null;
        }
        else
        {
            list($usec, $sec) = explode(' ',microtime());
            return ((float)$usec + (float)$sec - (float)$this->m_fStartTime) * 1000;
        }
    }

    /**
     * 返回日期增减值
     *
     * @param   int     $num        增减值
     * @param   char    $type       操作标志位[Y年/M月/D日]
     * @param   string  $BeginDate  日期(null为当前时间):格式1999-01-01
     * @return  string
     * @access public
     * @static
     */
    static public function getEndDate($num, $type='Y', $BeginDate=null)
	{
		if (!empty($BeginDate))
		{
			$BeginDate = strftime('%Y-%m-%d',strtotime($BeginDate));
			$Y = substr($BeginDate,0,4);
			$M = substr($BeginDate,5,2);
			$D = substr($BeginDate,8,2);
		}
		else
		{
			$Y = date('Y');//年
			$M = date('m');//月
			$D = date('d');//日
		}
		if ($type == 'Y')
			$nextyear  = mktime(0,0,0,$M,$D,$Y+$num);
		elseif ($type == 'M')
			$nextyear  = mktime(0,0,0,$M+$num,$D,$Y);
		else
			$nextyear  = mktime(0,0,0,$M,$D+$num,$Y);

		$date = date('Y-m-d',$nextyear);
		return $date;
	}

    /**
     * 返回时间增减值
     *
     * @param   int     $num        增减值
     * @param   char    $type       操作标志位[H/I/S(时分秒)]
     * @param   string  $BeginDate  时间(可以为空):格式00:00:00，24小时制
     * @return  string
     * @access public
     * @static
     */
	static public function getEndTime($num=0, $type='H', $BeginTime=null)
	{
		$aTmp = array();
	    if (!empty($BeginTime))
		{
			$BeginTime = strftime('%T',strtotime(date('Y-m-d ') . $BeginTime));
			$aTmp['H'] = substr($BeginTime,0,2);
			$aTmp['I'] = substr($BeginTime,3,2);
			$aTmp['S'] = substr($BeginTime,6,2);
		}
		else
		{
			$aTmp['H'] = date('H');//时
			$aTmp['I'] = date('i');//分
			$aTmp['S'] = date('s');//秒
		}
	    if ($type == 'H')
	    {
			$nextyear  = mktime($aTmp['H'] + $num, $aTmp['I'], $aTmp['S'],
			                    date('m'), date('d'), date('Y'));
	    }
		elseif ($type == 'I')
		{
			$nextyear  = mktime($aTmp['H'], $aTmp['I'] + $num, $aTmp['S'],
			                    date('m'), date('d'), date('Y'));
		}
		else
		{
			$nextyear  = mktime($aTmp['H'], $aTmp['I'], $aTmp['S'] + $num,
			                    date('m'), date('d'), date('Y'));
		}
	    $Time = date('H:i:s',$nextyear);
		return $Time;
	}

    /**
     * 返回时间日期增减值
     *
     * @param   int     $num        增减值
     * @param   char    $type       操作标志位[Y:年/M:月/D:日/H:时/I:分/S:秒]
     * @param   string  $BeginDate  日期(可以为空):1999-01-01 00:00:00，24小时制
     * @return  string
     * @access public
     * @static
     */
	static public function getEndDateTime($num=0, $type='H', $BeginDateTime=null)
	{
		$aDT = array();
		if (!empty($BeginDateTime))
		{
			$BeginDateTime = strftime('%Y-%m-%d %H:%M:%S',strtotime($BeginDateTime));//格式化字符串
			$aDT['Y'] = intval(substr($BeginDateTime,0,4));
			$aDT['M'] = intval(substr($BeginDateTime,5,2));
			$aDT['D'] = intval(substr($BeginDateTime,8,2));
			$aDT['H'] = intval(substr($BeginDateTime,11,2));
			$aDT['I'] = intval(substr($BeginDateTime,14,2));
			$aDT['S'] = intval(substr($BeginDateTime,17,2));
		}
		else
		{
			$aDT['Y'] = intval(date('Y'));
			$aDT['M'] = intval(date('m'));
			$aDT['D'] = intval(date('d'));
			$aDT['H'] = intval(date('H'));//时
			$aDT['I'] = intval(date('i'));//分
			$aDT['S'] = intval(date('s'));//秒
		}

		//增减处理
		$aDT[$type] += $num;
		return date('Y-m-d H:i:s',
		            mktime($aDT['H'],$aDT['I'],$aDT['S'],$aDT['M'],$aDT['D'],$aDT['Y'])
		           );
	}

    /**
     * 返回当前输入日期的星期号
     *
     * @param   string  $Date  日期，不输入表示当天
     * @return  string  [0:日-6:六]
     * @access public
     * @static
     */
	static public function getDateWeek($Date=null)
	{
		if (empty($Date))
		{
			$Y = date('Y');//年
			$M = date('m');//月
			$D = date('d');//日
		}
		else
		{
			$Date = strftime('%Y-%m-%d',strtotime($Date));
			$Y = substr($Date,0,4);
			$M = substr($Date,5,2);
			$D = substr($Date,8,2);
		}
		return date('w',mktime(0,0,0,$M,$D,$Y));
	}

    /**
     * 返回输入某月最后一天的日期
     *
     * @param   int     $Year   年
     * @param   int     $Month  月
     * @return  string  YYYY-MM-DD
     * @access public
     * @static
     */
	static public function getMonthEndDay($Year = '', $Month='')
	{
		$iYear = (trim($Year) == '')? intval(date('Y')) : intval($Year);
		$iMonth = (trim($Month) == '')? intval(date('m')) : intval($Month);
        if ($iMonth >= 12)
        {
            $iYear++;
            $iMonth = 1;
        }
        else
           $iMonth++;
        return self::getEndDate(-1, 'D', strftime('%Y-%m-%d', strtotime($iYear .'-'. $iMonth .'-01')));
	}

	/**
	 * 返回两个日期时间的差值， 时间格式可以为 Y-m-d 或 Y-m-d H:i:s,
	 * 返回 $DateTime2 - $DateTime1的差值，返回值单位[D:天/H:小时/I:分钟/S:秒]
	 *
	 * @param string    $DateTime1  减数日期
	 * @param string    $DateTime2  被减日期
	 * @param char      $RetFormat  返回值单位:[D:天/H:小时/I:分钟/S:秒]
	 * @return float
     * @access public
     * @static
	 */
	static public function DateTimeSub($DateTime1, $DateTime2=null, $RetFormat='S')
	{
		//如果$DateTime2为空，设置为当前的时间
	    if (empty($DateTime2)) $DateTime2 = date('Y-m-d H:i:s');

	    //格式化时间
	    $aTmp = array();
	    $DateTimeTemp = strftime('%Y-%m-%d %H:%M:%S',strtotime($DateTime1));//格式化字符串
		$aTmp['Y'] = intval(substr($DateTimeTemp,0,4));
		$aTmp['M'] = intval(substr($DateTimeTemp,5,2));
		$aTmp['D'] = intval(substr($DateTimeTemp,8,2));
		$aTmp['H'] = intval(substr($DateTimeTemp,11,2));
		$aTmp['I'] = intval(substr($DateTimeTemp,14,2));
		$aTmp['S'] = intval(substr($DateTimeTemp,17,2));
		$aDT[] = $aTmp;
		unset($aTmp);
		$DateTimeTemp = strftime('%Y-%m-%d %H:%M:%S',strtotime($DateTime2));//格式化字符串
		$aTmp['Y'] = intval(substr($DateTimeTemp,0,4));
		$aTmp['M'] = intval(substr($DateTimeTemp,5,2));
		$aTmp['D'] = intval(substr($DateTimeTemp,8,2));
		$aTmp['H'] = intval(substr($DateTimeTemp,11,2));
		$aTmp['I'] = intval(substr($DateTimeTemp,14,2));
		$aTmp['S'] = intval(substr($DateTimeTemp,17,2));
		$aDT[] = $aTmp;
        //$DateTime2 - $DateTime1的时间差值，单位秒
		$iSec = mktime($aDT[1]['H'], $aDT[1]['I'], $aDT[1]['S'],
		               $aDT[1]['M'], $aDT[1]['D'], $aDT[1]['Y']) -
		        mktime($aDT[0]['H'], $aDT[0]['I'], $aDT[0]['S'],
		               $aDT[0]['M'], $aDT[0]['D'], $aDT[0]['Y']);

        //格式化输出值
        if ($RetFormat == 'S')
            return $iSec;
        elseif ($RetFormat == 'I')
            return $iSec / 60;
        elseif ($RetFormat == 'H')
            return $iSec / (60 * 60);
        elseif ($RetFormat == 'D')
            return $iSec / (60 * 60 * 24);
        else
            return 0;
	}

	/**
	 * 给定一天时间，计算出这天所在周的周日与周六的日期
	 *     备注：一周的开始时间是星期日开始，星期六结束
	 * 返回 array('星期日日期','星期六日期');
	 *
	 * @param string $sDate 格式:YYYY-MM-DD，可以为NULL
	 * @return array array('星期日日期','星期六日期');
     * @access public
     * @static
	 */
	static public function WeekStartEnd($sDate = null)
	{
		//如果$sDate为空，设置为当前的时间
	    if ($sDate == '') $sDate = date('Y-m-d');

	    //格式化时间
	    $sTemp = strftime('%Y-%m-%d %H:%M:%S',strtotime($sDate));//格式化字符串
		$iYear  = intval(substr($sTemp,0,4));
		$iMonth = intval(substr($sTemp,5,2));
		$iDay   = intval(substr($sTemp,8,2));
		/*获得输入时间的周号*/
		$iWeek = date ('w', mktime (0, 0, 0, $iMonth, $iDay, $iYear));
		if ($iWeek == 0)
		{ //当前时间为星期日
		    $aOutDate[] = $sDate;
		    $aOutDate[] = date ('Y-m-d', mktime (0, 0, 0, $iMonth, $iDay + 6, $iYear));
		}
		else if ($iWeek == 6)
		{ //当前为星期六
		    $aOutDate[] = date ('Y-m-d', mktime (0, 0, 0, $iMonth, $iDay - 6, $iYear));
		    $aOutDate[] = $sDate;
		}
		else
		{
		    $aOutDate[] = date ('Y-m-d', mktime (0, 0, 0, $iMonth, $iDay - $iWeek, $iYear));
		    $aOutDate[] = date ('Y-m-d', mktime (0, 0, 0, $iMonth, $iDay + (6 - $iWeek), $iYear));;
		}
		return $aOutDate;
	}

	/**
	 * 检查日期是否有效
	 * @param string $sDate 日期(格式: yyyy-mm-dd)
	 * @return boolean
	 * @access public
	 */
	static public function checkDate($sDate)
	{
		list($iYear, $iMon, $iDay) = explode('-', trim($sDate));
		return checkdate(intval($iMon), intval($iDay), intval($iYear));
	}

	/**
	 * 检查时间是否有效
	 * @param string $sDate 日期(格式: hh:ii:ss)
	 * @return boolean
	 * @access public
	 */
	static public function checkTime($sTime)
	{
		list($iH, $iI, $iS) = explode(':', trim($sTime));
		$iH = intval($iH);
		$iI = intval($iI);
		$iS = intval($iS);
		return ($iH >=0 && $iH <=23) && ($iI >=0 && $iI <=59) && ($iS >=0 && $iS <=59);
	}

	/**
	 * 检查日期时间的有效性
	 * @param string $sDateTime 日期时间格式(yyyy-mm-dd hh:ii:ss)
	 * @return boolean
	 * @access public
	 */
	static public function checkDateTime($sDateTime)
	{
		list($sDate, $sTime) = explode(' ', trim($sDateTime));
		return self::checkDate($sDate) && self::checkTime($sTime);
	}
}
?>