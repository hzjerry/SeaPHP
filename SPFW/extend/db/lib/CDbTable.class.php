<?php
/**
 * 数据库表对象操作基类<br/>
 * 备注: 此类是表对象操作类的基类，表对象类必须继承此类。<br />
 * 注意: 对表对象进行链式操作时，不设置表名from(...)时，就会使用默认表名。<br />
 * 介绍: 表对象是最小的数据库操作逻辑类，它被存放于workgroup.db.table_object目录下(在environment.cfg.php中的table_object_path参数可配置)，以表名为单位建立类文件；【注意】表对象的类名与文件名必须大写。<br />
 * 如果在数据库层框架内同时管理一个以上的项目，可使用构造函数中的$sProject参数，指定项目名。指定了$sProject名称后，表对象的类名与文件名的生成规则是$sProject +'_'+ $sTable，文件名与类名必须大写。这样以便于防止不同项目可能存相同表名而导致类名重名的情况。
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130509
 * @package SPFW.extend.db.lib
 */
class CDbTable extends CDbCURD
{
	/**
	 * 默认表名(不带表前缀)
	 * @var string
	 */
	protected $msTable = null;

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		//不在这里执行父类的构造，通过init()来构造父类
	}

	/**
	 * 初始化（必须执行这个后才能使用表对象）
	 * @param CDbDriver $oDBL 数据库链接驱动层对象
	 * @param string $sPrefix 表前缀
	 * @param string $sTableUpperLower 表名强制大小写 [upper:强制大写|lower:强制小写|intact:保持原样]
	 * @param string $sTableObjectPackage 表对象类的根包路径<br />
	 * @param string $sTable 表名
	 * @return void
	 */
	public function init($oDBL, $sPrefix, $sTableUpperLower, $sTable)
	{
		parent::__construct($oDBL, $sPrefix, $sTableUpperLower);
		$this->msTable = $sTable; //保存默认表名
		$this->maFrom[] = $this->TabN($sTable); //设置默认表名
	}

	/**
	 * 清除链式操作生成的子句缓存
	 * @return void
	 * @access public
	 */
	public function clear()
	{
		parent::clear(); //调用父类清除链式缓存方法
		$this->maFrom[] = $this->TabN($this->msTable); //置入默认表明
	}

	/**
	 * 获取表名(包含表前缀)
	 * @return string
	 * @access public
	 */
	public function getTableName()
	{
		return $this->TabN($this->msTable);
	}
}

?>