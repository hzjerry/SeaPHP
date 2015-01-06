<?php
/**
 * 数据库操作层（CURD创建更新读取删除）
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20100104
 * <li>2013-05-07 jerryli 创立</li>
 * <li>2013-12-19 jerryli left_join(),from() 增加了强制指定索引</li>
 * <li>2014-06-22 jerryli 增加了UNID()函数，生成唯一识别号用于insert时的id字段(用于bigint字段)</li>
 * <li>2014-09-23 jerryli 修改了UNID()函数，末尾改为4位随机数，杜绝高频插入时出现主键相同的问题</li>
 * <li>2014-10-09 jerryli 修改了from(),left_join()函数；增加了inner_join()函数；此3个函数中加入了表名可以使用子查询的功能</li>
 * <li>2014-10-14 jerryli 增加了selectCallback()函数</li>
 * <li>2015-01-04 jerryli 增加了procedure()函数，对存储过程进行支持</li>

 * @package SPFW.extend.db.lib
 */
class CDbCURD{
	/**
	 * 数据库操作驱动
	 * @var CDbDriver
	 */
	private $moDBO = null;
	/**
	 * 表前缀
	 * @var string
	 */
	private $msPrefix = null;
	/**
	 * 表名强制大小写<br />
	 * [upper:强制大写|lower:强制小写|intact:保持原样]
	 * @var string
	 * @access protected
	 */
	protected $msTableUpperLower = 'intact';
	/**
	 * 链式操作Fields子句缓存(一维数组)
	 * @var array
	 */
	private $maFields = array();
	/**
	 * 链式操作FROM子句缓存(一维数组)
	 * @var array
	 */
	protected $maFrom = array();
	/**
	 * 链式操作WHERE子句缓存
	 * @var string
	 */
	private $msWhere = null;
	/**
	 * 链式操作GROUP BY子句缓存
	 * @var string
	 */
	private $msGroup = null;
	/**
	 * 链式操作HAVING子句缓存
	 * @var string
	 */
	private $msHaving = null;
	/**
	 * 链式操作ORDER BY子句缓存
	 * @var string
	 */
	private $msOrder = null;
	/**
	 * 链式操作UPDATE的SET子句参数与INSERT参数缓存(一维数组)<br />
	 * 格式:array('field1'=>'value1', 'field2'=>'value2')
	 * @var string
	 */
	private $maParam = array();
	/**
	 * 链式操作LIMIT子句缓存<br />
	 * 备注: 字符串已经含LIMIT关键字
	 * @var string
	 */
	private $msLimit = null;
	/**
	 * 构造函数
	 * @param CDbDriver $oDBL 数据库链接驱动层对象
	 * @param string $sPrefix 表前缀
	 * @param string $sTableUpperLower 表名强制大小写 [upper:强制大写|lower:强制小写|intact:保持原样]
	 */
	public function __construct($oDBL, $sPrefix, $sTableUpperLower){
		$this->moDBO = $oDBL;
		$this->msPrefix = $sPrefix;
		$this->msTableUpperLower = $sTableUpperLower;
	}
	/**
	 * 安全的参数过滤处理（尽可能大量使用这个函数）<br />
	 * 备注:本函数能够抵御SQL注入攻击的处理；如输入值为数字，则保持原样，如果输入的为字符串则输出时会加入''
	 * @param mixed $Val 需要做替换处理的任何值[string|int|float]
	 * @return string
	 * @access public
	 * @static
	 * @example
	 * 'WHERE username='. $this->S('xyz') .'AND age='. $this->S(32);
	 */
	static public function S($Val){
		static $aRep = array('\''=> '\'\'', '\\'=>'\\\\');
		if (is_int($Val) || is_float($Val))
			return $Val .' ';
		elseif (is_string($Val))
			return ' \''. strtr(trim($Val), $aRep) .'\' ';
		else
			return '\'\' ';
	}
	/**
	 * 生成唯一识别号序列<br />
	 * 备注:时间从2014-01-01日开始计算的unix时间戳<br/>
	 * 精度：100ns，并在末尾加4位随机数
	 * @return string bigint型字符串，最大长度2^64
	 * @access public
	 * @static
	 */
	static public function UNID(){
		return join(array(floor((microtime(true)-1387584000)*10000), rand(1000,9999)));
	}
	/**
	 * 清除链式操作生成的子句缓存
	 * @return void
	 * @access protected
	 */
	protected function clear(){
		$this->maFields = array();
		$this->maFrom = array();
		$this->msWhere = null;
		$this->msGroup = null;
		$this->msHaving = null;
		$this->msOrder = null;
		$this->maParam = array();
		$this->msLimit = null;
	}
	/**
	 * 打印SQL执行的历史信息(必须在SQL执行以后使用)
	 * @return void
	 * @access private
	 */
	public function showHistory(){
		$this->moDBO->showSqlHistory();
	}
	/**
	 * 抛出错误信息，并终止程序
	 * @param unknown_type $sMsg
	 * @return void
	 * @access protected
	 */
	protected function throwErr($sMsg){
		echo '<blockquote>',
			'<font face="arial" size="2" color="ff0000">',
			'<strong>SQL Parse error: </strong>',
			'[<font color="000077">', $sMsg, '</font>]<br />',
			'</font>',
			'<hr noshade color="dddddd" size="1" /></blockquote>',
			implode('<br />', dbg::TRACE());
		exit(0);
	}
	/**
	 * 返回带表前缀的表名（尽可能大量使用这个函数）<br />
	 * 备注：内置表名强制大小写的处理
	 * @param string $sTableName
	 * @return string
	 * @access public
	 */
	public function TabN($sTableName){
		if (!empty($this->msPrefix))
			$sTableName = $this->msPrefix . $sTableName;

		/*强制大小写转换处理*/
		if ('upper' === $this->msTableUpperLower)
			$sTableName = trim(strtoupper($sTableName));
		elseif ('lower' === $this->msTableUpperLower)
			$sTableName = trim(strtolower($sTableName));

		return $sTableName;
	}
	/**
	 * 设置检索字段<br />
	 * 用于: SELECT | INSERT(批量插入) 操作
	 * @param array|string $f 格式:array('field1', 'field2',...) 或 'field1,field2,...'
	 * @return CDbCURD
	 * @access public
	 */
	public function fields($f){
		if (is_array($f))
			$this->maFields = $f;
		elseif (is_string($f))
			$this->maFields = array($f);
		return $this;
	}
	/**
	 * 操作的表名
	 * <li>用于: SELECT | DELECT | UPDATE 操作</li>
	 * @param string $sTableName	表名(会自动添加表前缀)
	 * <li>如果是子查询请用'(...)'包裹子查询的SQL串，此时系统不会添加表前缀；但此时必须包含$sByname，否则会抛出错误</li>
	 * @param string $sByname		表别名(默认为null;在left_join()或inner_join子句中会使用)
	 * @param string $sIndexName	强制使用指定的索引(默认null)
	 * @return CDbCURD
	 * @access public
	 */
	public function from($sTableName, $sByname=null, $sIndexName=null){
		unset($this->maFrom);
		$this->maFrom = array();

		$sTableName = trim($sTableName);
		if ($sTableName{0} === '(' && substr($sTableName, -1) === ')' ){ //$sTableName为子查询不加表前缀
			if (empty($sByname)){	//子查询必须设置$sByname
				echo '<br />Subquery must set $$sByname.',
				implode('<br/>', dbg::TRACE());
				exit(0);
			}else{ //加入别名
				$this->maFrom[] = $sTableName . ' '. $sByname;
			}
		}else{//正常表名
			if (empty($sByname)) //不存在别名
				$this->maFrom[] = $this->TabN($sTableName);
			else	//加入别名的处理
				$this->maFrom[] = $this->TabN($sTableName) . ' '. $sByname;

			if (!is_null($sIndexName))//是否强制指定索引
				$this->maFrom[] = 'FORCE INDEX('. $sIndexName .')';
		}
		return $this;
	}
	/**
	 * 表左关联关系添加
	 * <li>注意:必须在本函数前执行from($sTableName, $sByname),且$sByname参数必须设置。</li>
	 * <li>因为$sByname就是left_join()中$sLeftByname需要使用的参数</li>
	 * <li>用于: SELECT | DELECT | UPDATE 操作</li>
	 * @param string $sLeftByname 主表别名
	 * @param string $sJoinTable 被关联表(系统会自动将表名添加表前缀)
	 * <li>如果是子查询请用'(...)'包裹子查询的SQL串，此时系统不会添加表前缀；但此时必须包含$sJoinByname，否则会抛出错误</li>
	 * @param string $sJoinByname 被关联表别名(''|null|别名)
	 * @param string|array $aJoinId 关联字段 'field' 或 array('left_field', 'join_field')
	 * @param string $sIndexName 强制使用指定的索引(默认null)
	 * @return CDbCURD
	 * @access public
	 */
	public function left_join($sLeftByname, $sJoinTable, $sJoinByname, $aJoinId, $sIndexName=null){
		return $this->join('LEFT', $sLeftByname, $sJoinTable, $sJoinByname, $aJoinId, $sIndexName);
	}
	/**
	 * 表内关联关系添加
	 * <li>注意:必须在本函数前执行from($sTableName, $sByname),且$sByname参数必须设置。</li>
	 * <li>因为$sByname就是inner_join()中$sInnerByname需要使用的参数</li>
	 * <li>用于: SELECT | DELECT | UPDATE 操作</li>
	 * @param string $sInnerByname 主表别名
	 * @param string $sJoinTable 被关联表(系统会自动将表名添加表前缀)
	 * <li>如果是子查询请用'(...)'包裹子查询的SQL串，此时系统不会添加表前缀；但此时必须包含$sJoinByname，否则会抛出错误</li>
	 * @param string $sJoinByname 被关联表别名(''|null|别名)
	 * @param string|array $aJoinId 关联字段 'field' 或 array('inner_field', 'join_field')
	 * @param string $sIndexName 强制使用指定的索引(默认null)
	 * @return CDbCURD
	 * @access public
	 */
	public function inner_join($sInnerByname, $sJoinTable, $sJoinByname, $aJoinId, $sIndexName=null){
		return $this->join('INNER', $sInnerByname, $sJoinTable, $sJoinByname, $aJoinId, $sIndexName);
	}
	/**
	 * 表关联关系添加
	 * @param string $sJoinType 连接操作[LEFT|INNER]
	 * @param string $sMainByname 主表别名
	 * @param string $sJoinTable 被关联表(系统会自动将表名添加表前缀)
	 * <li>如果是子查询请用'(...)'包裹子查询的SQL串，此时系统不会添加表前缀；但此时必须包含$sJoinByname，否则会抛出错误</li>
	 * @param string $sJoinByname 被关联表别名(''|null|别名)
	 * @param string|array $aJoinId 关联字段
	 * @param string $sIndexName 强制使用指定的索引(默认null)
	 * @return CDbCURD
	 * @access private
	 */
	private function join($sJoinType, $sMainByname, $sJoinTable, $sJoinByname, $aJoinId, $sIndexName=null){
		if (empty($sMainByname)){	//别名不能为空
			echo '<br />The left_join() $sLeftByname is missing.',
				 implode('<br/>', dbg::TRACE());
			exit(0);
		}elseif (count($this->maFrom) == 0){	//必须使用from()并设置别名
			echo '<br />From () function must be used, and set the $sByname.',
			implode('<br/>', dbg::TRACE());
			exit(0);
		}
		$sJoinTable = trim($sJoinTable);
		if ($sJoinTable{0} === '(' && substr($sJoinTable, -1) === ')' ){ //$sJoinTable为子查询不加表前缀
			if (empty($sJoinByname)){	//子查询必须设置$sJoinByname
				echo '<br />Subquery must set $sJoinByname.',
				implode('<br/>', dbg::TRACE());
				exit(0);
			}
		}else{
			$sJoinTable = $this->TabN($sJoinTable);
		}

		$aBuf = array();
		//加入右表关联表名
		$aBuf[] = $sJoinType .' JOIN';
		if (!empty($sJoinByname))
			array_push($aBuf, $sJoinTable, $sJoinByname);
		else
			$aBuf[] = $sJoinTable;
		//是否强制指定索引
		if (!is_null($sIndexName))
			$aBuf[] = 'FORCE INDEX('. $sIndexName .')';

		if (is_string($aJoinId))
			$aJoinId = array($aJoinId); //如果传入的是字符串，则转换成数组
		$sJoinTable = (empty($sJoinByname)) ? $sJoinTable : $sJoinByname; //如果右表有别名，使用别名
		//加入两个关联表的链接字段
		if (count($aJoinId) == 1)
			array_push($aBuf, 'ON(', $sMainByname .'.'. $aJoinId[0], '=', $sJoinTable .'.'. $aJoinId[0], ')');
		else
			array_push($aBuf, 'ON(', $sMainByname .'.'. $aJoinId[0], '=', $sJoinTable .'.'. $aJoinId[1], ')');

		array_push($this->maFrom, implode(' ', $aBuf));
		return $this;
	}
	/**
	 * 设置WHERE条件<br/>
	 * 用于: SELECT | DELECT | UPDATE 操作
	 * 注意：请尽可能使用模板替换参数，这样系统会自动为字符串加上''，并且可自动抵御SQL注入攻击
	 * @param string $sTemplate WHERE部分的SQL字符串(也可以是模板)
	 * @param array $aSafeReplace 模板参数安全替换(自动对字符串进行SQL防注入攻击处理)<br />
	 * (null:表示保持$sTemplate原样输出)<br />
	 * array('template key'=>'replace value', ...);
	 * @return CDbCURD
	 * @access public
	 * @example
	 * where('id={@id} AND username={@uname}', array('{@id}'=>1, '{@uname}'=>'test'))
	 */
	public function where($sTemplate, $aSafeReplace=null){
		if (is_null($aSafeReplace)){
			$this->msWhere = ' WHERE '. $sTemplate;
		}else{
			//对$aReplace中的replace value值做转换
			foreach ($aSafeReplace as $sKey => &$sVal)
				$sVal = self::S($sVal);
			$this->msWhere = ' WHERE '. trim(strtr($sTemplate, $aSafeReplace));
		}
		return $this;
	}
	/**
	 * 获取当前链式存储区中的Where子句内容<br />
	 * 注意: 不包含WHERE前缀操作符，只含有条件内容
	 * return string | null
	 */
	public function getWhere(){
		if (!is_null($this->msWhere))
			return substr($this->msWhere, 7);
		else
			return null;
	}
	/**
	 * 获取当前链式存储区中SELECT子句的Fields字段数组<br />
	 * return array | null
	 */
	public function getFields(){
		if (!is_null($this->maFields) && count($this->maFields) > 0)
			return $this->maFields;
		else
			return null;
	}
	/**
	 * 设定SQL参数<br />
	 * <strong>注意:</strong> 不会对'value1'做安全处理，'value1'请自行使用CDbCURD::S()做处理<br />
	 * 用于: UPDATE的Set子句 | INSERT的插入参数集 操作
	 * @param array $aArr 一维数组 array('field1'=>'value1', 'field2'=>'value2')<br />
	 * 'value1'这类参数的值，需自行使用CDbCURD::S()对字符串进行安全转换,本函数不会做任何处理
	 * @return CDbCURD
	 * @access public
	 * @example
	 * param(array('ip'=>'INET_ATON('. CDbCURD::S('129.168.1.23') .')'));
	 */
	public function param($aArr){
		$this->maParam = $aArr;
		return $this;
	}
	/**
	 * 设置ORDER BY排序子句<br/>
	 * 用于: SELECT | UPDATE操作<br/>
	 * 备注:DESC 降序 | ASC 升序
	 * @param string $sOrderBy 排序字符串
	 * @return CDbCURD
	 * @access public
	 * @example
	 * orderby('field1 DESC, field2 DESC')
	 */
	public function order($sOrderBy){
		$this->msOrder = ' ORDER BY '. $sOrderBy;
		return $this;
	}
	/**
	 * 设置GROUP BY子句<br/>
	 * 用于: SELECT 操作
	 * @param string $sFields 需要分组的字段(多个字段用','分割)
	 * @return CDbCURD
	 * @access public
	 */
	public function group($sFields){
		$this->msGroup = ' GROUP BY '. $sFields;
		return $this;
	}
	/**
	 * 设置HAVING子句<br/>
	 * 用于: SELECT 操作
	 * 注意：请尽可能使用模板替换参数，这样系统会自动为字符串加上''，并且可自动抵御SQL注入攻击
	 * @param string $sTemplate HAVING部分的SQL字符串(也可以是模板)
	 * @param array $aParam 模板替换参数 (null:表示保持$sTemplate原样输出)<br />
	 * array('template key'=>'replace value', ...);
	 * @return CDbCURD
	 * @access public
	 * @example
	 * having('id={@id} AND username={@uname}', array('{@id}'=>1, '{@uname}'=>'test'))
	 */
	public function having($sTemplate, $aParam=null){
		if (is_null($aParam)){
			$this->msHaving = ' HAVING '. $sTemplate;
		}else{
			//对param中的replace value值做转换
			foreach ($aParam as $sKey => $sVal)
				$aParam[$sKey] = self::S($sVal);
			$this->having = ' HAVING '. strtr($sTemplate, $aParam);
		}
		return $this;
	}
	/**
	 * LIMIT子句设定返回集数量
	 * @param int $iStart 起始位置 &gt;=0
	 * @param int $iCnt 取N条记录
	 * @return CDbCURD
	 * @example
	 * limit(0, 10); //从0条开始取10条记录<br />
	 * limit(10); //取前10条记录
	 */
	public function limit($iStart, $iCnt=null){
		if (is_null($iCnt))
			$this->msLimit = ' LIMIT '. intval($iStart);
		else
			$this->msLimit = ' LIMIT '. intval($iStart) .', '. intval($iCnt);

		return $this;
	}
	/**
	 * 删除操作（必须通过链式操作设置where()与from()）<br/>
	 * 返回: 受影响的记录条数
	 * @return int
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->from('test')<br />
	 *  	->where('id={@id}AND name={@name}', array('{@id}'=>1, '{@name}'=>'gu\'est'))<br />
	 *  	->delete()
	 */
	public function delete(){
		static $sTemplate = 'DELETE FROM {@tag_table}{@tag_where}{@tag_orderby}{@tag_limit}';

		$aParam = array();
		//加入FROM子句
		if (is_array($this->maFrom) && count($this->maFrom)>0)
			$aParam['{@tag_table}'] = implode(' ', $this->maFrom);
		else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少from()参数项');

		//加入WHERE子句
		$aParam['{@tag_where}'] = (empty($this->msWhere)===true)? '' : $this->msWhere;
		//加入ORDER BY子句
		$aParam['{@tag_orderby}'] = (empty($this->msOrder)===true)? '' : $this->msOrder;
		//加入LIMIT子句
		$aParam['{@tag_limit}'] = (empty($this->msLimit)===true)? '' : $this->msLimit;

		$sSql = strtr($sTemplate, $aParam);
		unset($aParam);
		$this->clear(); //清除链式缓存
		return $this->moDBO->exec($sSql);;
	}
	/**
	 * 删除操作（必须通过链式操作设置from()与param()）<br/>
	 * 返回: 受影响的记录条数
	 * @return int
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->from('test')<br />
	 *  	->param(array('val_text'=>'update() \'access\''))<br />
	 *  	->where('id={@id}AND name={@name}', array('{@id}'=>1, '{@name}'=>'gu\'est'))<br />
	 *		->order('id_num DESC')<br />
	 *		->limit(1)<br />
	 *  	->update()
	 */
	public function update(){
		static $sTemplate = 'UPDATE {@tag_table}{@tag_set}{@tag_where}{@tag_orderby}{@tag_limit}';

		$aParam = array();
		if (is_array($this->maFrom) && count($this->maFrom)>0)
			$aParam['{@tag_table}'] = implode(' ', $this->maFrom);
		else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少from()参数项');

		//加入UPDATE的SET子句
		if (is_array($this->maParam) && count($this->maParam) > 0){
			$aTmp = array();
			foreach ($this->maParam as $sField => $Val)
				$aTmp[] = $sField .'='. $Val; //不做安全处理
			$aParam['{@tag_set}'] = ' SET '. implode(', ', $aTmp);
			unset($aTmp);
		}
		else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少param()参数项');

		//加入UPDATE的WHERE子句
		$aParam['{@tag_where}'] = (empty($this->msWhere)===true)? '' : $this->msWhere;
		//加入ORDER BY子句
		$aParam['{@tag_orderby}'] = (empty($this->msOrder)===true)? '' : $this->msOrder;
		//加入LIMIT子句
		$aParam['{@tag_limit}'] = (empty($this->msLimit)===true)? '' : $this->msLimit;

		$sSql = strtr($sTemplate, $aParam);
		unset($aParam);
		$this->clear(); //清除链式缓存
		return $this->moDBO->exec($sSql);
	}
	/**
	 * insert单条插入操作（必须通过链式操作设置from()与param()）<br/>
	 * 返回: 受影响的记录条数
	 * @param string $sReturnType 返回类型(默认affect)['affect':影响条数 | 'id':返回自增ID值]
	 * @return int
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->from('test')<br />
	 *  	->param(array('val_text'=>'insert() \'access\''))<br />
	 *  	->insert()
	 */
	public function insert($sReturnType='affect'){
		static $sTemplate = 'INSERT INTO {@tag_table} ({@tag_col}) VALUES ({@tag_value})';

		$aParam = array();
		if (is_array($this->maFrom) && count($this->maFrom) == 1)
			$aParam['{@tag_table}'] = $this->maFrom[0];
		else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少from()或 设置的表数量超过1个');

		//加入字段与值的对应关系
		if (is_array($this->maParam) && count($this->maParam) > 0){
			$aCol = array();
			$aVal = array();
			foreach ($this->maParam as $sField => $Val)
			{
				$aCol[] = $sField;
				$aVal[] = $Val; //不做安全处理
			}
			$aParam['{@tag_col}'] = implode(', ', $aCol);
			$aParam['{@tag_value}'] = trim(implode(', ', $aVal));
			unset($aCol, $aVal);
		}else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少param()参数项');

		$sSql = strtr($sTemplate, $aParam);
		unset($aParam);
		$this->clear(); //清除链式缓存

		if (!empty($sReturnType) && $sReturnType == 'id')
			return $this->moDBO->exec($sSql, true);
		else
			return $this->moDBO->exec($sSql);
	}
	/**
	 * insert批量插入操作（必须通过链式操作设置from()与fields()）<br/>
	 * 返回: 受影响的记录条数
	 * @param array $aData 二维数组<strong>(自行使用CDbCURD::S()做安全处理)</strong><br />
	 * [按照fields()中的顺序生成需要批量插入的数据包]
	 * array( array(value1,value2, value3,....), ...)
	 * @return int
	 * @access public
	 * @example
	 *  $aData = array();<br />
	 *  $aData[] = array(CDbCURD::S('aaaa') );<br />
	 *  $aData[] = array(CDbCURD::S('bbbb') );<br />
	 *  $aData[] = array(CDbCURD::S('cccc') );<br />
	 *  $odb->db() <br />
	 *  	->from('test')<br />
	 *  	->fields(array('val_text'))<br />
	 *  	->insertMulti($aData);
	 */
	public function insertMulti($aInData){
		static $sTemplate = 'INSERT INTO {@tag_table} ({@tag_col}) VALUES {@tag_values}';

		$aParam = array();
		if (is_array($this->maFrom) && count($this->maFrom) == 1)
			$aParam['{@tag_table}'] = $this->maFrom[0];
		else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少from()或 设置的表数量超过1个');

		//加入字段与值的对应关系
		if (is_array($this->maFields) && count($this->maFields) > 0){
			$aCol = array();
			foreach ($this->maFields as $sField)
				$aCol[] = $sField;
			$aParam['{@tag_col}'] = implode(', ', $aCol);
			unset($aCol);
		}else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少fields()参数项');

		//加入批量插入的值
		if (count($aInData) > 0){
			$iRow = count($aInData);
			$iFieldCnt = count($this->maFields);
			$aRow = array();
			for($i=0; $i<$iRow; $i++){
				$aTmp = array();
				for($if=0; $if<$iFieldCnt; $if++)
					$aTmp[] = $aInData[$i][$if];//不做安全处理

				$aRow[] = '('. trim(implode(', ', $aTmp)) .')';
			}
			unset($aTmp);
			$aParam['{@tag_values}'] = implode(', ', $aRow) .';';
			unset($aRow);
		}else
			$this->throwErr('$aData至少包含一条数据');

		$sSql = strtr($sTemplate, $aParam);
		unset($aParam);
		$this->clear(); //清除链式缓存
		return $this->moDBO->exec($sSql);
	}
	/**
	 * SELECT操作（使用链式操作时必须设置from()与fields()）<br/>
	 * 使用: 如果使用链式操作，请不要设置$sSql参数。当设置了$sSql参数将以此SQL语句为准，链式操作无效。<br/>
	 * 返回: 二维数组 array(array('field1'=>'val1', 'field2'=>'val2', ...), ...)
	 * @param bool $bDBWR 选择主库与只读库<br />
	 * （null:表示使用dsn中的默认配置; | true:强制操作主库 | false:强制操作只读库）
	 * @param string $sSql SELECT的SQL语句（null:表示使用链式操作;）
	 * @return array | null
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->fields(array('id_num', 'val_text')) <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->group('val_text') <br />
	 *		->having('...') <br />
	 *  	->order('id_num ASC') <br />
	 *  	->limit(5) <br />
	 *  	->select();
	 */
	public function select($bDBWR=null, $sSql=null){
		static $sTemplate = 'SELECT {@tag_fields} FROM {@tag_table}{@tag_where}{@tag_groupby}{@tag_having}{@tag_orderby}{@tag_limit}';

		if (empty($sSql)){
			$aParam = array();
			//加入SELECT输出字段
			if (is_array($this->maFields) && count($this->maFields) >= 1)
				$aParam['{@tag_fields}'] = implode(', ', $this->maFields);
			else//SQL语句未执行，缺少必要项
				$this->throwErr('链式访问时缺少fields()或 设置的表数量超过1个');

			//加入FROM子句
			if (is_array($this->maFrom) && count($this->maFrom)>0)
				$aParam['{@tag_table}'] = implode(' ', $this->maFrom);
			else//SQL语句未执行，缺少必要项
				$this->throwErr('链式访问时缺少from()参数项');

			//加入WHERE子句
			$aParam['{@tag_where}'] = (empty($this->msWhere)===true)? '' : $this->msWhere;
			//加入GROUP BY子句
			$aParam['{@tag_groupby}'] = (empty($this->msGroup)===true)? '' : $this->msGroup;
			//加入HAVING子句
			$aParam['{@tag_having}'] = (empty($this->msHaving)===true)? '' : $this->msHaving;
			//加入ORDER BY子句
			$aParam['{@tag_orderby}'] = (empty($this->msOrder)===true)? '' : $this->msOrder;
			//加入LIMIT子句
			$aParam['{@tag_limit}'] = (empty($this->msLimit)===true)? '' : $this->msLimit;

			$sSql = strtr($sTemplate, $aParam);
			unset($aParam);
		}
		$this->clear(); //清除链式缓存
		return $this->moDBO->query($sSql, $bDBWR);
	}
	/**
	 * 根据链式操作，生成SQL语句SELECT专用（使用链式操作时必须设置from()）<br />
	 * (专用于高效的SELECT语句) 无视limit()操作
	 * @return string
	 * @access private
	 */
	private function efficientSelect(){
		static $sTemplate = '{@tag_fields}FROM {@tag_table}{@tag_where}{@tag_groupby}{@tag_having}{@tag_orderby}';

		$aParam = array();
		//加入SELECT输出字段
		if (is_array($this->maFields) && count($this->maFields) >= 1)
			$aParam['{@tag_fields}'] = 'SELECT '. implode(', ', $this->maFields) .' ';
		else//SQL语句未执行，缺少必要项
			$aParam['{@tag_fields}'] = '';
		//加入FROM子句
		if (is_array($this->maFrom) && count($this->maFrom)>0)
			$aParam['{@tag_table}'] = implode(' ', $this->maFrom);
		else//SQL语句未执行，缺少必要项
			$this->throwErr('链式访问时缺少from()参数项');

		//加入WHERE子句
		$aParam['{@tag_where}'] = (empty($this->msWhere)===true)? '' : $this->msWhere;
		//加入GROUP BY子句
		$aParam['{@tag_groupby}'] = (empty($this->msGroup)===true)? '' : $this->msGroup;
		//加入HAVING子句
		$aParam['{@tag_having}'] = (empty($this->msHaving)===true)? '' : $this->msHaving;
		//加入ORDER BY子句
		$aParam['{@tag_orderby}'] = (empty($this->msOrder)===true)? '' : $this->msOrder;

		$sSql = strtr($sTemplate, $aParam);
		unset($aParam);
		return $sSql;
	}
	/**
	 * SELECT操作，获取第一个字段的第一个值[高效能操作]（使用链式操作时必须设置from()与fields()）<br/>
	 * 使用: 如果使用链式操作，请不要设置$sSql参数。当设置了$sSql参数将以此SQL语句为准，链式操作无效。<br/>
	 * 优化: 如果SQL只是统计记录集条数，可以不设置$sSql中的SELECT Fields子句(或不用fields()子句),返回为int<br/>
	 * 注意: 请不要使用LIMIT操作，函数会自动加上这个参数<br />
	 * @param bool $bDBWR 选择主库与只读库<br />
	 * （null:表示使用dsn中的默认配置; | true:强制操作主库 | false:强制操作只读库）
	 * @param string $sSql SELECT的SQL语句（null:表示使用链式操作;）<br />
	 * @return string | int | null
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->fields(array('id_num', 'val_text')) <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->group('val_text') <br />
	 *		->having('...') <br />
	 *  	->order('id_num ASC') <br />
	 *  	->selectOne(); <br />
	 *  -----------------<br />
	 *  $odb->db() <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->selectOne(); <br />
	 *  -----------------<br />
	 *  $odb->db()->selectOne(null, 'FROM '.$odb->db()->TabN('test'). ' WHERE id_num=2')
	 */
	public function selectOne($bDBWR=null, $sSql=null){
		$bGetCount = false;
		if (empty($sSql)) //$sSql不存在，使用链式处理
			$sSql = $this->efficientSelect();
		//如果不存在SELECT Fields内容，则自动加入SELECT COUNT(0)
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0){
			$sSql = 'SELECT COUNT(0) '. trim($sSql);
			$bGetCount = true;
		}
		$this->clear(); //清除链式缓存
		if ($bGetCount)
			return intval($this->moDBO->queryOne($sSql, $bDBWR));
		else
			return $this->moDBO->queryOne($sSql, $bDBWR);
	}
	/**
	 * SELECT操作，统计本条件的记录集条数（使用链式操作时必须设置from()）<br/>
	 * 使用: 如果使用链式操作，请不要设置$sSql参数。当设置了$sSql参数将以此SQL语句为准，链式操作无效。<br/>
	 * 优化: 如果SQL只是统计记录集条数，可以不设置$sSql中的SELECT Fields子句(或不用fields()子句)<br/>
	 * 注意: 请不要使用LIMIT操作，函数会自动加上这个参数<br />
	 * @param bool $bDBWR 选择主库与只读库<br />
	 * （null:表示使用dsn中的默认配置; | true:强制操作主库 | false:强制操作只读库）
	 * @param string $sSql SELECT的SQL语句（null:表示使用链式操作;）<br />
	 * @return string | null
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->group('val_text') <br />
	 *		->having('...') <br />
	 *  	->order('id_num ASC') <br />
	 *  	->selectRowCnt(); <br />
	 *  -----------------<br />
	 *  $odb->db() <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->selectRowCnt(); <br />
	 *  -----------------<br />
	 *  $odb->db()->selectRowCnt(null, 'FROM '.$odb->db()->TabN('test'). ' WHERE id_num=2')
	 */
	public function selectRowCnt($bDBWR=null, $sSql=null){
		if (empty($sSql)) //$sSql不存在，使用链式处理
			$sSql = $this->efficientSelect();
		//如果不存在SELECT Fields内容，则自动加入SELECT COUNT(0)
		if (preg_match('/^(select)\s+\S*/i', $sSql) == 0)
			$sSql = 'SELECT COUNT(0) '. trim($sSql);

		$this->clear(); //清除链式缓存
		return $this->moDBO->queryOne($sSql, $bDBWR);
	}
	/**
	 * SELECT操作，获取第一个字段的整个记录集[高效能操作]（使用链式操作时必须设置from()与fields()）<br/>
	 * 使用: 如果使用链式操作，请不要设置$sSql参数。当设置了$sSql参数将以此SQL语句为准，链式操作无效。<br/>
	 * 返回: 一维数组array(value1, value2,...)
	 * @param bool $bDBWR 选择主库与只读库<br />
	 * （null:表示使用dsn中的默认配置; | true:强制操作主库 | false:强制操作只读库）
	 * @param string $sSql SELECT的SQL语句（null:表示使用链式操作;）<br />
	 * @return array | null
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->fields(array('id_num', 'val_text')) <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->group('val_text') <br />
	 *		->having('...') <br />
	 *  	->order('id_num ASC') <br />
	 *  	->limit(20) <br />
	 *  	->selectFirstCol(); <br />
	 *  -----------------<br />
	 *  $odb->db()->selectFirstCol(null, 'SELECT id_num, val_text FROM '.$odb->db()->TabN('test'). ' WHERE id_num=3')
	 */
	public function selectFirstCol($bDBWR=null, $sSql=null){
		if (empty($sSql)){ //$sSql不存在，使用链式处理
			if (is_array($this->maFields) && count($this->maFields) == 0)
				$this->throwErr('链式访问时缺少fields()');
			$sSql = $this->efficientSelect();
			if (!empty($this->msLimit)) //加入记录集限制
				$sSql .= $this->msLimit;
		}
		$this->clear(); //清除链式缓存
		return $this->moDBO->queryFirstCol($sSql, $bDBWR);
	}
	/**
	 * SELECT操作，获取第一行记录[高效能操作]（使用链式操作时必须设置from()与fields()）<br/>
	 * 使用: 如果使用链式操作，请不要设置$sSql参数。当设置了$sSql参数将以此SQL语句为准，链式操作无效。<br/>
	 * 返回: 一维数组array(value1, value2,...)
	 * @param bool $bDBWR 选择主库与只读库<br />
	 * （null:表示使用dsn中的默认配置; | true:强制操作主库 | false:强制操作只读库）
	 * @param string $sSql SELECT的SQL语句（null:表示使用链式操作;）<br />
	 * @return array | null
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->fields(array('id_num', 'val_text')) <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->group('val_text') <br />
	 *		->having('...') <br />
	 *  	->order('id_num ASC') <br />
	 *  	->selectFirstRow(); <br />
	 *  -----------------<br />
	 *  $odb->db()->selectFirstRow(null, 'SELECT id_num, val_text FROM '.$odb->db()->TabN('test'). ' WHERE id_num=3')
	 */
	public function selectFirstRow($bDBWR=null, $sSql=null){
		if (empty($sSql)){ //$sSql不存在，使用链式处理
			if (is_array($this->maFields) && count($this->maFields) == 0)
				$this->throwErr('链式访问时缺少fields()');

			$sSql = $this->efficientSelect();
		}

		$this->clear(); //清除链式缓存
		return $this->moDBO->queryFirstRow($sSql, $bDBWR);
	}
	/**
	 * SELECT操作（对每个ROW执行一次回调函数）
	 * @param closure $callFunc 闭包函数
	 * <li>闭包函数$callFunc的入口参数array:array('field1'=>'val1', 'field2'=>'val2', ...)</li>
	 * <li>如果$callFunc不是一个闭包函数，将直接报错</li>
	 * @param bool $bDBWR 选择主库与只读库<br />
	 * （null:表示使用dsn中的默认配置; | true:强制操作主库 | false:强制操作只读库）
	 * @param string $sSql SELECT的SQL语句（null:表示使用链式操作;）<br />
	 * @return void
	 * @access public
	 * @example<pre>
		class export2File{ //将数据导出到csv文件类
			private $oFile = null;
			public function __construct($sPath){
				$this->oFile = fopen($sPath, 'w');
				if (($this->oFile = fopen($sPath, 'w')) === false)
					$this->oFile = null;
			}
			public function __destruct(){
				if (!is_null($this->oFile))
					fclose($this->oFile);
			}
			function wirte2file(){ //闭包函数
				return function($aRow){
					$aVals = array_values($aRow);
					foreach ($aVals as & $sVal)
						$sVal = iconv("utf-8", "gbk", $sVal);
					fputcsv($this->oFile, $aVals);
				};
			}
		}

		$op = new export2File('h:/temp.csv');
		$odb->db()
	   	->fields(array('id_num', 'val_text'))
	   	->from('test')
	   	->where('id_num={@id}', array('{@id}'=>1))
	   	->group('val_text')
	 		->having('...')
	   	->order('id_num ASC')
		->queryRowCallback($op->wirte2file());//对每条记录调用一次闭包函数
		</pre>
	 */
	public function selectCallback($callFunc, $bDBWR=null, $sSql=null){
		if (empty($sSql)){ //$sSql不存在，使用链式处理
			if (is_array($this->maFields) && count($this->maFields) == 0)
				$this->throwErr('链式访问时缺少fields()');

			$sSql = $this->efficientSelect();
		}

		$this->clear(); //清除链式缓存
		return $this->moDBO->queryRowCallback($callFunc, $sSql, $bDBWR);
	}
	/**
	 * SELECT操作，分页处理[高效能操作]（使用链式操作时必须设置from()与fields()）<br/>
	 * 使用: 如果使用链式操作，请不要设置$sSql参数。当设置了$sSql参数将以此SQL语句为准，链式操作无效。<br/>
	 * 返回: 二维数组 array(array('field1'=>'val1', 'field2'=>'val2',...),...)
	 * 注意: 如果使用 $sSql 值，那么内容不可包含LIMIT...否则会报错
	 * @param int $iPage 页码
	 * @param int $iPageSize 页大小
	 * @param bool $bDBWR 选择主库与只读库<br />
	 * （null:表示使用dsn中的默认配置; | true:强制操作主库 | false:强制操作只读库）
	 * @param string $sSql SELECT的SQL语句（null:表示使用链式操作;）
	 * @return array | null
	 * @access public
	 * @example
	 *  $odb->db() <br />
	 *  	->fields(array('id_num', 'val_text')) <br />
	 *  	->from('test') <br />
	 *  	->where('id_num={@id}', array('{@id}'=>1)) <br />
	 *  	->group('val_text') <br />
	 *		->having('...') <br />
	 *  	->order('id_num ASC') <br />
	 *  	->selectPage(1, 10); <br />
	 *  -----------------<br />
	 *  $odb->db()->selectPage(null, 'SELECT id_num, val_text FROM '.$odb->db()->TabN('test'))
	 */
	public function selectPage($iPage, $iPageSize, $bDBWR=null, $sSql=null){
		if (empty($sSql)){ //$sSql不存在，使用链式处理
			if (is_array($this->maFields) && count($this->maFields) == 0)
				$this->throwErr('链式访问时缺少fields()');
			$sSql = $this->efficientSelect();
		}

		$this->clear(); //清除链式缓存
		return $this->moDBO->queryPage($sSql, $iPage, $iPageSize, $bDBWR);
	}
	/**
	 * 截断表
	 * @param string $sTable 需要截断的表名
	 * @return bool
	 */
	public function truncate($sTable){
		return ($this->moDBO->exec('TRUNCATE TABLE '. $sTable) > 0);
	}
	/**
	 * 执行无返回的SQL指令<br />
	 * 返回: 操作影响的记录条数
	 * @param string $sSql
	 * @return int
	 */
	public function exec($sSql){
		return $this->moDBO->exec($sSql);
	}
	/**
	 * 执行存储过程
	 *  <li>只能执行含有输入参数IN类型的存储过程，不支持入口参数含有OUT类型的参数</li>
	 *  <li>存储过程默认只能在主库上执行（因为可能存在更新操作）</li>
	 * @param string $sProcName 存储过程名
	 * @param array $aParam 输入参数
	 * <li>注意:字符串型参数需要传入引号</li>
	 * @param bool $bOutResult 是否含有输出结果集(默认为false,没有输出结果集)
	 * @return null|array(array(array()))
	 * <li>返回结果是一个三维数组；存储过程执后可能会返回多个数据集，每个数组为一个结果集</li>
	 * @access public
	 */
	public function procedure($sProcName, $aParam, $bOutResult=false){
		return $this->moDBO->procedure($sProcName, $aParam, $bOutResult);
	}
}
?>