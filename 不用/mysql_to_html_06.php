<?php
/*
Create date:2014-01-01
Last update date:2017-04-19
Plugin Name: phpmysqli_dictionary
Plugin URI: http://www.bkk.tw
Description: PHP生成mysql數據庫字典工具 
Version: 1.0.1
Author: david
*/
/*
白底黑字
適用PHP5.4~PHP7.0
*/

//載入設定檔
require('config.inc.php');//載入設定檔

//配置數據庫 載入設定檔
require('config.inc.mysql.php');//載入資料庫帳號設定檔

//mysql配置
$dblink = @mysqli_connect("$dbserver", "$dbusername", "$dbpassword") or die("Mysql connect is error.");
mysqli_select_db($dblink, $database);//選表
mysqli_query($dblink, 'SET NAMES utf8');
$table_result = mysqli_query($dblink, 'show tables');
/*********************************************/
$no_show_databases = array('information_schema','mysql','performance_schema','sys');   //不需要顯示的數據庫
$no_show_table = array();    //不需要顯示的數據表
$no_show_field = array('oc_');   //不需要顯示的字段 oc_就是 OPENCART購物車系統的預設
$_GET['prefix']='oc_';//替換所有的表前綴
if(isset($_GET['prefix'])){
    $prefix=$_GET['prefix'];
}else{
    $prefix=""; 
}
/*********************************************/
//mysql_list_dbs --- 列出 MySQL 伺服器上可用的資料庫
$sql='SHOW DATABASES';
$result = mysqli_query($dblink, $sql);
while ($row = mysqli_fetch_array($result)) {
    //過濾不需要顯示的數據庫
    if (!in_array($row[0], $no_show_databases)) {
        $DATANAME[] = $row[0];
    }
}
$result->close(); //釋放記憶體

echo "<div class=\"warp\"><h3>\n";
echo "服務器上所有數據庫名稱: <br />";
print_r($DATANAME);
echo "</h3></div>\n";


//取得所有的表名
while ($row = mysqli_fetch_array($table_result)) {
    if (!in_array($row[0], $no_show_table)) {
        $tables[]['TABLE_NAME'] = $row[0];
    }
}
//print_r($tables);//所有的表名

//如果有參數
//替換所有表的表前綴
if ($prefix) {
    for($i=0;$i<count($tables);$i++){
            $a=$tables[$i];
            $tables2[]['TABLE_NAME'] = str_replace($prefix,"", $a['TABLE_NAME']);//替換字串
    }
    echo "替換表前綴".$prefix."替換成功！";
}

//循環取得所有表的備註及表中列消息
foreach ($tables as $k => $v) {
    $sql  = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.TABLES ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
    $table_result = mysqli_query($dblink, $sql);
    while ($t = mysqli_fetch_array($table_result)) {
        $tables[$k]['TABLE_COMMENT'] = $t['TABLE_COMMENT'];
    }
    //print_r($sql);

    $sql  = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";

    $fields = array();
    $field_result = mysqli_query($dblink, $sql);
    while ($t = mysqli_fetch_array($field_result)) {
        $fields[] = $t;
    }
    $tables[$k]['COLUMN'] = $fields;
}
mysqli_close($dblink);//關閉資料庫

//print_r($tables);
$html = '';//循環所有表
foreach ($tables as $k => $v) {
//如果有替換所有表的表前綴的行為 
if($prefix)
$TABLE_NAME=$tables2[$k]['TABLE_NAME'];//表名
else
$TABLE_NAME=$v['TABLE_NAME'];
    $html .= '	<h2>' . ($k + 1) . '、' . $v['TABLE_COMMENT'] .'  （<span class="cr">'. $TABLE_NAME.'</span>）</h2>'."\n";//標題
    $html.='<table  border="1" cellspacing="0" cellpadding="0" align="center">';
    $html .= '	<table border="1" cellspacing="0" cellpadding="0" width="100%">'."\n";
    $html .= '		<tbody>'."\n";
    $html .= '			<tr>'."\n";
    $html .= '				<th>字段名</th>'."\n";
    $html .= '				<th>數據類型</th>'."\n";
    $html .= '				<th>數據類型</th>'."\n";
    $html .= '				<th>長度</th>'."\n";
    $html .= '				<th>默認值</th>'."\n";
    $html .= '				<th>允許非空</th>'."\n";
    $html .= '				<th>主鍵</th>'."\n";
    $html .= '				<th>備註</th>'."\n";
    $html .= '			</tr>'."\n";
    //表的值COLUMN
    foreach ($v['COLUMN'] as $f) {
        if (@!is_array($no_show_field[$v['TABLE_NAME']])) {
            $no_show_field[$v['TABLE_NAME']] = array();
        }
        if (!in_array($f['COLUMN_NAME'], $no_show_field[$v['TABLE_NAME']])) {
            $html .= '			<tr>'."\n";
            $html .= '				<td class="c1">' . $f['COLUMN_NAME'] . '</td>'."\n";
            $html .= '				<td class="c2">' . _lang($f['DATA_TYPE'],$idx_i_COLUMN_TYPE). '</td>'."\n";
            $html .= '				<td class="c0c">' . $f['DATA_TYPE'] . '</td>'."\n";
            //長度
            $LENGTH="";
            if ($f['NUMERIC_PRECISION']) {
                $LENGTH=$f['NUMERIC_PRECISION'];
            } else {              
                if($f['CHARACTER_OCTET_LENGTH']){
                $LENGTH=$f['CHARACTER_OCTET_LENGTH'];
                }
            }
            $html .= '				<td class="c0c">' . $LENGTH . '</td>'."\n";
            $html .= '				<td class="c0c">' . $f['COLUMN_DEFAULT'] . '</td>'."\n";
            $html .= '				<td class="c0c">' . $f['IS_NULLABLE'] . '</td>'."\n";
            $html .= '				<td class="c5">' . $f['COLUMN_KEY'].($f['EXTRA']=='auto_increment'?'+自增':'&nbsp;') . '</td>'."\n";
            $html .= '				<td class="c6">' . $f['COLUMN_COMMENT'] . '</td>'."\n";
            $html .= '			</tr>'."\n";
        }
    }
    $html .= '		</tbody>'."\n";
    $html .= '	</table>'."\n";
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>生成數據庫字典</title>
<meta name="generator" content="ThinkDb V1.0" />
<meta name="author" content="生成數據庫字典" />
<meta name="copyright" content="2008-2014 Tensent Inc." />
<style>
body, td, th { font-family: "微軟雅黑"; font-size: 14px; }
.warp{margin:auto; width:1000px;}
.warp h2{margin:0px; padding:0px; line-height:40px; margin-top:10px;}
.warp h3{margin:0px; padding:0px; line-height:30px; margin-top:10px;}
table { border-collapse: collapse; border: 1px solid #CCC; background: #efefef; }
table th { text-align: left; font-weight: bold; height: 26px; line-height: 26px; font-size: 14px; text-align:center; border: 1px solid #CCC; padding:5px;}
table td { height: 20px; font-size: 14px; border: 1px solid #CCC; background-color: #fff; padding:5px;}
.c0 {}
.c0c {text-align:center;}
.c1 { width: 120px; }
/*c2中文類型*/
.c2 { width: 100px;text-align:center; }
.c3 { width: 150px; }
.c4 { width: 80px; text-align:center;}
.c5 { width: 80px; text-align:center;}
.c6 { min-width: 370px; }
.cr{color:#ff0033;}
</style>
</head>
<body>
<div class="warp">
    <h1 style="text-align:center;">生成數據庫字典</h1>
<?php echo $html; ?>
</div>
</body>
</html>
