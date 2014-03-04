<?php
/**
 * 数据库表对象业务逻辑类<br/>
 * @author Jerryli(hzjerry@gmail.com)
 * @version V0.20130509
 * @package SPFW.workgroup.db.table_object
 */
class TEST extends CDbTable
{
	public function addInfo()
	{
		return $this
			->param(array('val_text'=>'insert():'.date('Y-m-d H:i:s')))
			->insert();
	}

	public function getInfo()
	{
		return $this
			->fields(array('id_num', 'val_text'))
			->selectPage(2, 3);
	}
}

?>