<?php
/**
 * db测试
 * @author Jerryli(hzjerry@gmail.com)
 * @version V20130501
 * @package SPFW
 */
header('Content-Type: text/html; charset=UTF-8');
define('SEA_PHP_ROOT', '/');
require '../SPFW/sea_php_init.php';
$odb = new CDB('CDbCfgLocalTest');

// _dbg($odb->db()->query('SELECT COUNT(0) as CNT FROM sac_client_visit', false));
// _dbg($odb->db()->queryOne('SELECT * FROM sac_client_visit'));
// _dbg($odb->db()->queryFirstRow('SELECT * FROM sac_client_visit'));
// _dbg($odb->db()->queryFirstCol('SELECT * FROM sac_client_visit LIMIT 1, 10'));
// _dbg($odb->db()->showApiVer());
// _dbg($odb->db()->exec('Insert into test(val_text)VALUE(\'eer\')', true));
// $aSqls = array();
// $aSqls[] = 'Insert into test(val_text)VALUE(\'erqer\')';
// $aSqls[] = 'Insert into test(val_text)VALUE(\'中古热凝\')';
// $aSqls[] = 'Insert into test(val_text)VALUE(\'kut\')';
// $aSqls[] = 'delete from test';
// _dbg($odb->db()->transaction($aSqls));
// $odb->db()->showSqlHistory();

//数据库操作对象
//delete处理
// _dbg
// (
// $odb->db()
// 	->from('test')
// 	->where('id_num > {@id}', array('{@id}'=>6))
// // 	->limit(1)
// 	->delete()
// );
//update处理
// _dbg
// (
// $odb->db()
// 	->from('test')
// 	->param(array('val_text'=>'update() \'a\' '. date('Y-m-d H:i:s')))
// 	->where('id_num={@id}', array('{@id}'=>3))
// 	->order('id_num DESC')
// 	->limit(1)
// 	->update()
// );
//insert处理
// _dbg
// (
// $odb->db()
// ->from('test')
// ->param(array('val_text'=>CDbCURD::S('insert():'.date('Y-m-d H:i:s'))))
// ->insert()
// );
//insertMulti处理
// $aData = array();
// $aData[] = array(CDbCURD::S('abcd'));
// $aData[] = array(CDbCURD::S('bbbb'));
// $aData[] = array(CDbCURD::S('WXYZ'));
// _dbg
// (
// $odb->db()
// ->from('test')
// ->fields(array('val_text'))
// ->insertMulti($aData)
// );
//SELECT处理
_dbg
(
$odb->db()
->fields(array('TITLE', 'URL', 'UPDT'))
->from('fun_url')
->where('SACID={@id}', array('{@id}'=>'SATlFCb$ElW'))
->order('UPDT ASC')
->limit(3)
->select()
);
//SELECT ONE处理
// _dbg
// (
// $odb->db()
// ->from('client_visit')
// ->selectOne()
// );
// _dbg
// (
// $odb->db()
// ->from('client_visit')
// ->where('INDT > {@date}', array('{@date}'=>'2012-11-01 00:00:00'))
// ->selectOne()
// );
// $sSql = 'FROM '.$odb->db()->TabN('client_visit'). ' WHERE INDT > \'2012-11-01 00:00:00\'';
// _dbg($odb->db()->selectOne(null, $sSql));
//SELECT FirstCol处理
// _dbg
// (
// $odb->db()
// ->fields(array('INDT', 'CVID'))
// ->from('client_visit')
// ->order('INDT DESC')
// ->limit(20)
// ->selectFirstCol()
// );
// $sSql = 'SELECT INDT, CVID FROM '.$odb->db()->TabN('client_visit'). ' WHERE INDT > \'2012-11-01 00:00:00\' LIMIT 5';
// _dbg($odb->db()->selectFirstCol(null, $sSql));
//SELECT FirstRow处理
// _dbg
// (
// $odb->db()
// ->fields(array('CVID', 'INDT', 'MCID'))
// ->from('client_visit')
// ->order('INDT DESC')
// ->selectFirstRow()
// );
//SELECT关联处理
// _dbg
// (
// $odb->db()
// ->fields(array('TITLE', 'URL', 'UPDT'))
// ->from('fun_url', 'A')
// ->left_join('A', 'fun', 'B', array('ariid'))
// ->where('SACID={@id}', array('{@id}'=>'SATlFCb$ElW'))
// ->order('UPDT ASC')
// ->limit(3)
// ->select()
// );
// Truncate处理
// $aSql = array();
// $aSql[] = 'INSERT INTO test(val_text)VALUES(\'test.tttt\')';
// $aSql[] = 'DELETE FROM test WHERE id_num >7';
// $aSql[] = 'INSERT INTO test(val_text)VALUES(\'test.tttt\')';
// _dbg($odb->db()->truncate($aSql));

// $oTable_test = $odb->tableObj('test');
// _dbg($oTable_test->getInfo());
$odb->db()->showHistory();

_dbg(memory_get_peak_usage(), 'memory');
_dbg(CENV::getRuntime(), 'run time');
?>