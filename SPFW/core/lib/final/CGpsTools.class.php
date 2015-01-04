<?php
/**
 * Gps坐标运算工具类库
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20141230
 * @package SPFW.core.lib.final
 * @final
 * @static
 * */
final class CGpsTools{
 	/**
 	 * 地球的弧度（单位:km）
 	 * @var float
 	 */
 	const EARTH_RADIUS = 6378.137;
 	/**
 	 * 计算两个坐标之间的距离(km)
 	 * @param array $aPS 起点array('lng'=>'经度', 'lat'=>'纬度')
 	 * @param array $aPE 终点array('lng'=>'经度', 'lat'=>'纬度')
 	 * @return float 单位km
 	 */
	static public function distanceBetween($aPS, $aPE){
		//角度换算成弧度
		$fRadLng1 = deg2rad(floatval($aPS['lng']));
		$fRadLng2 = deg2rad(floatval($aPE['lng']));
		$fRadLat1 = deg2rad(floatval($aPS['lat']));
		$fRadLat2 = deg2rad(floatval($aPS['lat']));
		//计算经纬度的差值
		$fD1 = abs($fRadLat1 - $fRadLat2);
		$fD2 = abs($fRadLng1 - $fRadLng2);
		//距离计算
		$fP = pow(sin($fD1/2), 2) +
			  cos($fRadLat1) * cos($fRadLat2) * pow(sin($fD2/2), 2);
		return round(self::EARTH_RADIUS * 2 * asin(sqrt($fP)), 2);
	}
	/**
	 * 坐标系转换 GCJ02 -&gt; BD09
	 * <li>GCJ02:中国正常坐标系/腾讯地图标系</li>
	 * <li>BD09:百度地图坐标系</li>
	 * @param array $aPS array('lng'=>'经度', 'lat'=>'纬度')
	 * @return array('lng'=>'经度', 'lat'=>'纬度')
	 * @see http://www.cnblogs.com/yushang/archive/2013/07/03/3169685.html
	 */
	static public function Convert_GCJ02_To_BD09($aPS){
		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
		$fLng = floatval($aPS['lng']);
		$fLat = floatval($aPS['lat']);
		$fP =sqrt(pow($fLng, 2) + pow($fLat, 2)) + 0.00002 * sin($fLat * $x_pi);
		$theta = atan2($fLat, $fLng) + 0.000003 * cos($fLng * $x_pi);
		$lng = $fP * cos($theta) + 0.0065;
		$lat = $fP * sin($theta) + 0.006;
		return array('lng'=>round($lng, 6),'lat'=>round($lat, 6));
	}
	/**
	 * 坐标系转换 BD09 -&gt; GCJ02
	 * <li>GCJ02:中国正常坐标系/腾讯地图标系</li>
	 * <li>BD09:百度地图坐标系</li>
	 * @param array $aPS array('lng'=>'经度', 'lat'=>'纬度')
	 * @return array('lng'=>'经度', 'lat'=>'纬度')
	 * @see http://lbs.qq.com/webservice_v1/guide-convert.html
	 * @see http://mapi.map.qq.com/translate/?type=3&points=116.396135,39.811082;116.386135,39.811082&output=jsonp
	 */
	static function Convert_BD09_To_GCJ02($aPS){
		$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
		$fLng = floatval($aPS['lng']) - 0.0065;
		$fLat = floatval($aPS['lat']) - 0.006;
		$fP = sqrt(pow($fLng, 2) + pow($fLat, 2)) - 0.00002 * sin($fLat * $x_pi);
		$theta = atan2($fLat, $fLng) - 0.000003 * cos($fLng * $x_pi);
		$lng = $fP * cos($theta);
		$lat = $fP * sin($theta);
		return array('lng'=>round($lng, 6),'lat'=>round($lat, 6));
	}
	/**
	 * 计算正切圆的正方形坐标
	 * <li>以$aPS为圆心，计算其外切正方形的对角坐标</li>
	 * @param array $aPS array('lng'=>'经度', 'lat'=>'纬度')
	 * @param float $fDistance 正切半径长度(单位km)
	 * @return array(
	 *  'left-top'		=>array('lng'=>'经度', 'lat'=>'纬度'),
	 * 	'right-top'	=>array('lng'=>'经度', 'lat'=>'纬度')
	 * 	'left-bottom'	=>array('lng'=>'经度', 'lat'=>'纬度')
	 * 	'right-bottom'	=>array('lng'=>'经度', 'lat'=>'纬度')
	 * )
	 * @see http://digdeeply.org/archives/06152067.html
	 */
	static function getTangentSquarePoint($aPS, $fDistance=1){
		$fDistance = floatval($fDistance);
		//圆心
		$fcLng = floatval($aPS['lng']);
		$fcLat = floatval($aPS['lat']);
		//偏移坐标
		$dlng = rad2deg(2 * asin(sin($fDistance / (2 * self::EARTH_RADIUS)) / cos(deg2rad($aPS['lat']))));
		$dlat = rad2deg($fDistance / self::EARTH_RADIUS);
		return array(
			'left-top'=>array('lat'=>round($fcLat + $dlat,6),'lng'=>round($fcLng - $dlng,6)),
			'right-top'=>array('lat'=>round($fcLat + $dlat,6), 'lng'=>round($fcLng + $dlng,6)),
			'left-bottom'=>array('lat'=>round($fcLat - $dlat,6), 'lng'=>round($fcLng - $dlng,6)),
			'right-bottom'=>array('lat'=>round($fcLat - $dlat,6), 'lng'=>round($fcLng + $dlng,6))
		);
	}
	/**
	 * 以基点坐标为基础，对GPS坐标点记录集进行距离排序
	 * <li>注意数组的输入结构，区分大小写array('lat'=>0.00, 'lng'=>0.00)</li>
	 * <li>函数会对$aData内容进行直接排序，并在每个节点数组中加入distance数据位，存在基点到坐标点的距离(单位km)</li>
	 * @param array $aBase 基点坐标array('lng'=>'经度', 'lat'=>'纬度')
	 * @param array $aData 待排序的数据array(array('lng'=>'经度', 'lat'=>'纬度', ...其他客户数据))
	 * @param array $aPointFieldName array('lng'=>'精度字段名', 'lat'=>'纬度')
	 * @return int 返回处理时间ms
	 * @example<pre>
		$aBase = array('lat'=>30.259512, 'lng'=>120.099363); //基点坐标
		$aData = array(); //待排序的坐标数组
		$aData[] = array('lat'=>30.272791, 'lng'=>120.153683, 'title'=>'坐标1');
		$aData[] = array('lat'=>30.270956, 'lng'=>120.147289, 'title'=>'坐标2');
		$aData[] = array('lat'=>30.271392, 'lng'=>120.125059, 'title'=>'坐标3');
		$aData[] = array('lat'=>30.268482, 'lng'=>120.142311, 'title'=>'坐标4');
		_dbg(CGpsTools::sortDistance($aBase, $aData, array('lat'=>'lat', 'lng'=>'lng')), '耗时(ms):'); //排序
		_dbg($aData);//打印数组的排序结果
		</pre>
	 */
	static function sortDistance($aBase, & $aData, $aPointFieldName){
		$fStart = microtime(true);
		$lat = $aPointFieldName['lat'];
		$lng = $aPointFieldName['lng'];

		/*步骤1：计算基点到数据集每个坐标之间的距离*/
		foreach ($aData as & $aNode){
			$aNode['distance'] = self::distanceBetween($aBase, array('lng'=>$aNode[$lng], 'lat'=>$aNode[$lat]));
		}
		/*步骤2：对距离进行排序*/
		usort($aData, function($a, $b){
			if ($a['distance'] === $b['distance'])
				return 0;
			else
				return ($a['distance'] > $b['distance'])? 1 : -1;
		});
		return intval((microtime(true) - $fStart)* 1000);
	}
}
?>