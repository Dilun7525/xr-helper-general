<?php
/**
 * The main functions of the engine and services
 *
 * @author Admin <support@1000.menu>
 * @author Anatoli <freeworknet@yandex.ru>
 */


// *********
// *  URL  *
// *********

/**
 * Фильтр для пути скрипта ($_GET['go'] по умолчанию).
 * Запрещены все знаки кроме цифр, латинских букв и знаков "_-". Слэши в самом конце отрезаются
 *
 * @param  string $go_url Raw URL path
 *
 * @return string         Filtered URL path
 */
function go_url_sanitize($go_url = NULL){
	
	if($go_url === NULL){
		
		if(empty($_GET['go'])){
			return '';
		}
		
		$go_url = $_GET['go'];
	}
	
	if(empty($go_url)){
		return '';
	}
	
	$max_go_url_length = 1000;
	
	$go_filtered = preg_replace('/[^a-z0-9\/\-_]/', '', strtolower(substr($go_url, 0, $max_go_url_length)));
	
	// если в УРЛ в конце слэши
	while ($go_filtered[strlen($go_filtered)-1]=='/')
	{
		$go_filtered = substr($go_filtered, 0, strlen($go_filtered)-1);
	}
	
	return $go_filtered;
}


// **********
// *  TEXT  *
// **********

/**
 * Экранирование текста (shortcut for htmlspecialchars($str, ENT_QUOTES))
 * @param  string $str String
 * @return string      Filtered string
 */
function ival($str){
	if(empty($str)){
		return '';
	}
	else {
		return htmlspecialchars($str, ENT_QUOTES);
	}
}

/**
 * Быстрая проверка чисел (больше или равно 0)
 * @param  mixed   $str      Checking value if it is a non-negative number (&gt;=0)
 * @param  boolean $positive Demand number to be greater than 0
 * @return boolean           Status result
 */
function is_num($str, $positive = false)
{
	return $str == '0' . $str && (!$positive || $str > 0);
}

/**
 * Форматированное выписывание информации о переменной (в основном для массивов)
 * @param  mixed  $arr    	Variable to print
 * @param  integer $format 	Format:
 *                           <ul>
 *                           	<li> <strong> 0 </strong> (default)
 *                           	 - format via <u>pre</u> tag
 *                           	 <li> <strong> 1 </strong>
 *                           	 - format via nl2br()
 *                            </ul>
 * @return string          Formatted HTML string
 */
function print_arr($arr, $format = 0){
	
	// форматирование через nl2br
	if($format === 1){
		return nl2br(ival(print_r($arr, true)));
	}
	
	// дефолт
	return '<pre>'.print_r($arr,true).'</pre>';
}

/**
 * Создает массив из текста, разбитого по строкам
 * @param  string $str 	String
 * @return array      	Exploded string by lines
 */
function explode_by_lines($str = ''){
	return !empty($str) ? explode("\n", str_replace(array("\r\n", "\r"), "\n", $str)) : array();
}


// ***********
// *  ARRAY  *
// ***********

/**
 * Выдача данных из массива по названию ключа
 * @param  array  	$arr     	Data array
 * @param  string  	$key     	Key name
 * @param  boolean 	$def_val 	Default value if data were not found
 * @return mixed           		Array key value
 */
function arr_key($arr, $key, $def_val = false){
	
	if(!$arr || !is_array($arr)){
		return $def_val;
	}
	
	return isset($arr[$key]) ? $arr[$key] : $def_val;
}

/**
 * Группировка массива по выбранному индексу с возможностью фильтра
 * @param  array   $arr       Array
 * @param  string  $index     Selected key name to group array by
 * @param  array   $selective Selective mode. Filter result array by selected keys
 * @param  array   $params    Settings:
 *                             <ul>
 *                             		<li> <strong> direct_value </strong> bool (false)
 *                             		 - Works only in Selective mode!
 *                             		 Use direct value instead of array as item in the result array (e.g. when only one key in $selective).
 *                             </ul>
 * @return array             Groupped array
 */
function array_groupby($arr = array(), $index = '', $selective = array(), $params = array()){
	
	$result = array();
	
	if(empty($arr)){
		return $result;
	}
	
	// если нужно сохранить только выбранные ключи
	$save_full_row = empty($selective) || !is_array($selective);
	
	// если не нужен массив, а просто величина в selective
	$direct_value = !empty($params['direct_value']);
	
	// проходим по массиву и группируем
	foreach ($arr as $row){
		if(!isset($row[$index])){
			break;
		}
		
		if($save_full_row){
			$result[$row[$index]][] = $row;
		}
		// Если сохраняем только выбранные колонки
		else {
			$tmp = array();
			foreach ($selective as $col){
				if(!isset($row[$col]))
					continue;
				
				// Если настроена прямая запись (без-массивная)
				if($direct_value){
					$tmp = $row[$col];
					break;
				}
				
				$tmp[$col] = $row[$col];
			}
			$result[$row[$index]][] = $tmp;
		}
	}
	
	return $result;
}

/**
 * Индексирует массив по заданному ключу элемента в данном массиве.
 * Пример: <br>
 * 	$original_array = [ 0 => ['id'=>1, 'name'=>'test 1'], 1 => ['id'=>2, 'name'=>'test 2'] ] <br>
 * 	$result_array = arr_index( $original_array, 'id' ) <br>
 *  => [ <b>1</b> => ['id'=>1, 'name'=>'test 1'], <b>2</b> => ['id'=>2, 'name'=>'test 2'] ]
 * @param  array   $arr    Array to index
 * @param  string  $by_key Items array key name to index the $arr by
 * @return array           Indexed array
 */
function arr_index($arr, $by_key){
	if(!$by_key || !is_array($arr)){
		return $arr;
	}
	
	$ret = array();
	
	foreach ($arr as $item){
		if(!isset($item[$by_key])){
			return $arr;
		}
		$ret[$item[$by_key]] = $item;
	}
	
	return $ret;
}


// *******************
// *  FILES AND DIR  *
// *******************

/**
 * Создание директории (создает путь, если его нет)
 * @param  string $new_path Directory to create (folders in path are created on fly if they don't exist)
 * @return boolean          Result status
 */
function dir_create($new_path){
	// если директории еще нет
	if(!is_dir($new_path)){
		// разбиваем на массив и проходим от первого элемента до последнего, создавая путь
		$arr_path = explode(DIRECTORY_SEPARATOR, $new_path);
		
		// если путь больше 1 элемента (то есть файл создаеться в папке)
		if($arr_path){
			$tmp = '.';
			foreach ($arr_path as $a_val){
				// filter
				$a_val = trim($a_val);
				if(!strlen($a_val)){
					continue;
				}
				$tmp .= '/' . $a_val;
				if(!is_dir($tmp) && !mkdir($tmp)){
					return false;
				}
			}
		}
	}
	return true;
}

/**
 * Удаление папки
 * @param  [type] $dir [description]
 * @return [type]      [description]
 */
function dir_remove($dir){
	
	if(!is_dir($dir)){
		return;
	}
	
	foreach(scandir($dir) as $file) {
		if ('.' === $file || '..' === $file) continue;
		if (is_dir("$dir/$file")) dir_remove("$dir/$file");
		else unlink("$dir/$file");
	}
	
	return rmdir($dir);
}

/**
 * Шардинг путей
 *
 * @param $id
 * @return string
 */
function sharding_path($id)
{
	$hash = md5($id);
	$patch = [
		substr($hash, -4, 2),
		substr($hash, -2),
		$id,
	];
	
	return implode('/', $patch);
}

/**
 * Запись контента в файл. Папка создается на лету, если ее нет.
 * @param  string $path File path. Folders will be created if not exist
 * @param  string $str  File content
 * @return boolean      Operation status
 */
function strtofile($path, $str = ''){
	// если не задан путь
	if(!$path)
		return false;
	
	// пытаемся создать файл
	if(!is_file($path) && !dir_create(pathinfo($path, PATHINFO_DIRNAME)))
		return false;
	
	//пишем в файл
	return file_put_contents($path, $str);
}

/**
 * Копирование файла (создает путь, если его нет)
 * @param  string $path     File to copy
 * @param  string $new_path New file path (directories are created on fly if they don't exist)
 * @return boolean          Result status
 */
function file_copy($path, $new_path){
	if(!$path || !is_file($path)){
		return false;
	}
	$dirname = pathinfo($new_path, PATHINFO_DIRNAME);
	
	if($dirname && !dir_create($dirname)){
		return false;
	}
	return copy($path, $new_path);
}

/**
 * Определение типа файла (расширение)
 * @param  string $name File path or name
 * @return string       File extension
 */
function file_get_type($name){
	// разбиваем на массив
	$name = explode('.', $name);
	
	// выделяем расширения
	$name = array_pop($name);
	
	// возвращаем
	return strtolower($name);
}


// **********
// *  DATE  *
// **********

/**
 * Перевод дня года на дату в указанном формате
 * @param  integer $dayOfYear Day of the current year
 * @param  string  $format    Desired format. Default: "d.m"
 * @return string             Translated day of the year to date string
 */
function getDateFromDay($dayOfYear, $format = 'd.m'){
	$date = DateTime::createFromFormat('z Y', $dayOfYear . ' ' . date("Y", $GLOBALS['now_time']));
	return $date->format($format);
}

/**
 * Конвертация периодичной даты из MySQL в указанный формат
 * @param  string  $str     Periodic date (every date with year <u>1004</u>) in format "1004-MM-DD"
 * @param  string  $format  Desired output format. Default: "d.m"
 * @return string           Translated periodic date to date string
 */
function convert_mysql_date_wo_year($str, $format = NULL){
	$y = $m = $d = 0;
	list($y, $m, $d) = explode('-', $str);
	// faster default way
	if(!$format){
		return $d . '.' . $m;
	}
	// via mktime
	else {
		return date($format, mktime(0, 0, 0, $m, $d));
	}
}

/**
 * Перевод даты из MySQL формата в любой формат
 * @param  string $date   Date in MySQL format "YYYY-MM-DD"
 * @param  string $format Desired format (see date())
 * @return string         Converted date string from MySQL to desired format
 */
function convert_mysql_date($date, $format = 'd.m.y'){
	$y = $m = $d = 0;
	list($y, $m, $d) = explode('-', $date);
	return date($format, mktime(0, 0, 0, $m, $d, $y));
}

/**
 * Перевод даты СО ВРЕМЕНЕМ из MySQL формата в любой формат
 * @param  string $mysqldatetime Date in MySQL format "YYYY-MM-DD HH:II:SS"
 * @param  string $format        Desired format (see date())
 * @return string                Converted date <u>with time</u> string from MySQL to desired format
 */
function convert_mysql_datetime($mysqldatetime, $format){
	$date = $time = $year = $month = $day = $hour = $minute = $second = 0;
	
	list($date, $time) = explode(' ', $mysqldatetime);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);
	
	return date($format, mktime($hour, $minute, $second, $month, $day, $year));
}

/**
 * Перевод даты в заданном формате в дату в MySQL формате
 * @param  string $date        Input date
 * @param  string $inputformat Input date format. Default: "d.m.Y"
 * @return string              Converted input date in input format to MySQL format "YYYY-MM-DD"
 */
function convert_to_mysql_date($date, $inputformat = 'd.m.Y'){
	$date = DateTime::createFromFormat($inputformat, $date);
	return $date->format('Y-m-d');
}



