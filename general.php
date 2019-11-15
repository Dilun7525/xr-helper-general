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
 * Фильтр для пути скрипта.
 * Запрещены все знаки кроме цифр, латинских букв и знаков "_-". Слэши в самом конце отрезаются
 *
 * @param  string $go_url Raw URL path
 *
 * @return string         Filtered URL path
 */
function go_url_sanitize($go_url){
	
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

/**
 * Укорачивание текста
 * @param  string  $str        String to be shorten
 * @param  integer $max_length Max length of the string (chars)
 * @param  array   $sys        Settings array with options:
 *                             <ul>
 *                             		<li> <strong> stop_char </strong> string
 *                             		- Set the cutting point closest to this char. Default: " " (space)
 *                             		<li> <strong> more_char </strong> string
 *                             		- Char placed after truncated string. Default: "…"
 *                             </ul>
 * @return string              Shorted string
 */
function str_shorten($str, $max_length = 200, $sys = array()){
	
	if(!isset($sys['stop_char'])){
		$sys['stop_char'] = ' ';
	}
	if(!isset($sys['more_char'])){
		$sys['more_char'] = '…';
	}
	
	$return = $str;
	
	$str_l = mb_strlen($str);
	$nchar_l = $sys['more_char'] == '…' ? 1 : mb_strlen($sys['more_char']);
	
	if($str_l > $max_length){
		
		$pos = $sys['stop_char'] !== '' ? mb_strpos($str, $sys['stop_char'], $max_length) : false;
		
		if($pos !== false && $pos + $nchar_l <= $max_length){
			$return = mb_substr($str, 0, $pos) . $sys['more_char'];
		} else {
			$return = mb_substr($str, 0, $max_length - $nchar_l) . $sys['more_char'];
		}
	}
	
	return $return;
}

/**
 * Multibyte version of substr_replace()
 * @param  mixed  $string      The input string
 * @param  mixed  $replacement The replacement string
 * @param  mixed  $start       If start is non-negative, the replacing will begin at the start'th offset into string.
 *                             If start is negative, the replacing will begin at the start'th character from the end of string.
 * @param  mixed  $length      If given and is positive, it represents the length of the portion of string which is to be replaced.
 *                             If it is negative, it represents the number of characters from the end of string at which to stop replacing.
 *                             If it is not given, then it will default to strlen( string ); i.e. end the replacing at the end of string.
 *                             Of course, if length is zero then this function will have the effect of inserting replacement into string at the given start offset.
 * @return string              The result string is returned. If string is an array then array is returned.
 */
function mb_substr_replace($string, $replacement, $start, $length = NULL){
	if ($length === NULL){
		return mb_substr($string,0,$start).$replacement;
	}
	else{
		return mb_substr($string,0,$start).$replacement.mb_substr($string,$start + $length);
	}
}

/**
 * Replace the first occurrence of the given needle in subject string
 * @param  mixed  $search  Searched needle
 * @param  mixed  $replace The replacement string
 * @param  string $subject Subject string (haystack)
 * @return string          The result string
 */
function str_replace_first($search, $replace, $subject){
	$pos = mb_strpos($subject, $search);
	if ($pos !== false) {
		return mb_substr_replace($subject, $replace, $pos, mb_strlen($search));
	}
	else {
		return $subject;
	}
}

/**
 * Обработка текста в зависимости от заданного формата
 * @param  string  $str  Text
 * @param  integer $type Text format type (default: 0). Options:
 *                       <ul>
 *                       	<li> <strong> 0 </strong>
 *                       		- Text without HTML (special chars are escaped, new lines are converted to &lt;br&gt;)
 *                       	<li> <strong> 1 </strong>
 *                       		- Text with HTML tags (new lines are converted to &lt;br&gt;)
 *                       	<li> <strong> 2 </strong>
 *                       		- HTML code (leave as is)
 *                       </ul>
 * @param  array   $sys  Settings array with options:
 *                       <ul>
 *                       	<li> <strong> shorten </strong> integer
 *                       		- Use str_shorten() on string. Only if $type == 0. Default: NULL
 *                       	<li> <strong> smile </strong> boolean
 *                       		- Parse string with emoticons (place images). Default: FALSE
 *                       	<li> <strong> wrap </strong> string
 *                       		- Wrap the result with some tag (i.e."div"). Default: NULL
 *                       </ul>
 * @return string        Formatted text
 */
function get_strtype($str, $type = 0, $sys = array()){
	// результат
	$return = '';
	
	$xhtml_br = !isset($sys['xhtml_br']) || !empty($sys['xhtml_br']);
	
	// производим обработку строки
	switch ($type){
		// текст
		case 0: {
			if(!empty($sys['shorten'])){
				$return = nl2br(ival(str_shorten($str, $sys['shorten'])), $xhtml_br);
			} else {
				$return = nl2br(ival($str), $xhtml_br);
			}
			break;
		}
		// текст с разметкой
		case 1: {
			$return = nl2br($str, $xhtml_br);
			break;
		}
		// HTML
		case 2: {
			$return = $str;
			break;
		}
	}
	
	// parse emoticons
	if(!empty($sys['smile'])){
		$return = parse_smile_emoticons($return);
	}
	
	// wrap tag
	if(!empty($sys['wrap'])){
		$return = '<'.$sys['wrap'].'>'.$return.'</'.$sys['wrap'].'>';
	}
	
	return $return;
}

/**
 * Перевод числа в байты, килобайты, мегабайты, гигабайты
 * @param  integer  $num     	Number
 * @param  integer $decimals  	Sets the number of decimal points. Default: 0 (auto)
 * @param  integer $precision 	Precision:
 *                             	<ul>
 *                             		<li> <strong> 0 </strong> - Automatic precision by the number length (default)
 *                             		<li> <strong> 1 </strong> - Bytes
 *                             		<li> <strong> 2 </strong> - Kilobytes
 *                             		<li> <strong> 3 </strong> - Megabytes
 *                             		<li> <strong> 4 </strong> - Gigabytes
 *                             	</ul>
 * @return string           	Converted number to bytes
 */
function name_to_bit($num, $decimals = 0, $precision = 0){
	
	// выясняем точность: 1 - байты, 2 - килобайты, 3 - мегабайты, 4 - Гигобайты
	$precision = $precision ? $precision : ceil(strlen($num) / 3);
	
	switch ($precision){
		case 1: $return = number_format($num, 0, '.', ' ') . " B";
			break;
		case 2: $num = $num / 1024;
			$return = number_format($num, $decimals ? $decimals : 1, '.', ' ') . " KB";
			break;
		case 3: $num = $num / 1024 / 1024;
			$return = number_format($num, $decimals ? $decimals : 2, '.', ' ') . " MB";
			break;
		case 4:
		default: $num = $num / 1024 / 1024 / 1024;
			$return = number_format($num, $decimals ? $decimals : 3, '.', ' ') . " GB";
			break;
	}
	
	return $return;
}

/**
 * Транслитерация текста
 *
 * @param  string  $str     	  String for transliteration (see conversion array $arr_translit)
 * @param  boolean $url_name	  Prepare string for using in URL. Default: false (url non-friendly chars are kept)
 * @param array    $arr_translit
 *
 * @return string            	  Transliterated string
 */
function translit($str,
				  $url_name = false,
				  array $arr_translit = ['а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
										 'ё' => 'yo', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k',
										 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
										 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'x', 'ц' => 'c',
										 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'э' => 'e', 'ы' => 'y', 'ю' => 'u',
										 'я' => 'ya', 'ь' => '', 'ъ' => '']
){
	
	// удаляем лишние строки
	$str = trim($str);
	// в нижний регистр
	$str = mb_strtolower($str);
	// транслитерация
	$str = str_replace(array_keys($arr_translit), array_values($arr_translit), $str);
	// если это для URL
	if($url_name){
		// оставляем только цифры, латинские буквы и знак "-" (заменяем все на "-")
		$str = preg_replace('/[^a-z0-9-]/u', '-', $str);
		// отрезаем лишние "-"
		$str = trim($str, '-');
		// переводим все "-" в одиночные
		$str = preg_replace('/--+/u', '-', $str);
	}
	return $str;
}

/**
 * Проверка равенства транслитирированных текстов (смешанные тексты, где есть и кириллица латиница)
 * @param  string  $str_1         String 1
 * @param  string  $str_2         String 2
 * @param  boolean $search_partly Exact match or search partly. Default: false (exact match)
 * @return boolean|integer        If $search_partly is true, then position is returned in case of $str_1 is found in $str_1.
 *                                Otherwise boolean result status is returned (FALSE means not found / not equal)
 */
function str_equality_translit($str_1, $str_2, $search_partly = false){
	// в нижний регистр
	$str_1 = mb_strtolower($str_1);
	$str_2 = mb_strtolower($str_2);
	
	// транслитирируем
	$str_1 = translit($str_1);
	$str_2 = translit($str_2);
	
	// если ищем влючение
	if($search_partly){
		$return = mb_strpos($str_1, $str_2);
	}
	// если сравниваем значения
	elseif($str_1 == $str_2){
		$return = true;
	}
	// тексты не равны
	else {
		$return = false;
	}
	
	return $return;
}

/**
 * Перевод первого знака в верхний реестр
 * @param  string $str    String
 * @param  array $params  Settings array with options:
 *                        <ul>
 *                        	<li> <strong> rest_to_lower </strong> boolean
 *                        		- Convert rest of the string to lower case. Default: false (leave as is)
 *                        </ul>
 * @return string         String with upper cased first letter
 */
function str_ucfirst($str, $params=array()){
	// check empty
	if(empty($str)){
		return '';
	}
	
	if(mb_strlen($str) > 1){
		
		$part_1 = mb_strtoupper(mb_substr($str, 0, 1));
		$part_2 = mb_substr($str, 1);
		
		if(!empty($params['rest_to_lower'])){
			$part_2 = mb_strtolower($part_2);
		}
		
		$return = $part_1 . $part_2;
	} else{
		$return = mb_strtoupper($str);
	}
	
	return $return;
}

/**
 * Генератор рандомного текста
 * @param  integer $length     Length of the output random text
 * @param  string  $characters Used characters in the output random text. Default: "abcdefghijklmnopqrstuvwxyz0123456789"
 * @return string              Random text
 */
function get_random_string($length, $characters = 'abcdefghijklmnopqrstuvwxyz0123456789'){
	$num_characters = strlen($characters) - 1;
	$return = '';
	while (strlen($return) < $length){
		$return .= $characters[mt_rand(0, $num_characters)];
	}
	return $return;
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
 *    $original_array = [ 0 => ['id'=>1, 'name'=>'test 1'], 1 => ['id'=>2, 'name'=>'test 2'] ] <br>
 *    $result_array = arr_index( $original_array, 'id' ) <br>
 *    => [ <b>1</b> => ['id'=>1, 'name'=>'test 1'], <b>2</b> => ['id'=>2, 'name'=>'test 2'] ]
 *
 * @param  array  $arr    Array to index
 * @param  string $by_key Items array key name to index the $arr by
 *
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

/**
 * Генерация ключей для запросов к базе с оператором WHERE {col} IN (?,?,…).
 * Могут генерироваться либо знаки вопроса (?), либо наименования.
 * Пример:
 * <pre>
 * 		// default (question marks):
 * 		$data = array('foo', 'bar');
 * 		$result = mysql_do('INSERT INTO some_table (col_name) VALUES ('.arr_in($data).')', $data);
 * 		// Query: INSERT INTO some_table (col_name) VALUES (?,?)
 *
 * 		// named indexes
 * 		$data = array(':foo' => 'bar', ':not_foo' => 'not_bar');
 * 		$result = mysql_do('INSERT INTO some_table (col_name) VALUES ('.arr_in($data).')', $data);
 * 		// Query: INSERT INTO some_table (col_name) VALUES (:foo, :not_foo)
 * </pre>
 *
 * @param  array          $arr            Data array
 * @param  boolean|string $prefix         If FALSE (default), then question marks (?) are generated if data array is
 *                                        numerically indexed [0=>…, 1=>…], otherwise array keys names are used.<br> IF
 *                                        STRING is passed, then it is used as prefix to every array key number (not
 *                                        key name itself, but it's numerical position)
 * @param  boolean        $force_q        Force function to generate question marks (?) even if data array is not
 *                                        numerically indexed (see $prefix = FALSE)
 *
 * @return string                    Generated string
 */
function arr_in($arr = array(), $prefix = false, $force_q = false){
	$return = '';
	
	if(is_array($arr) && !empty($arr)){
		// если мы используем наименования и нужен префикс к ключам
		if($prefix !== false){
			for ($i = 0, $c = count($arr); $i < $c; $i++){
				$return .= ($i ? ',' : '') . $prefix . $i;
			}
		}
		// если мы используем пронумерованное поле
		elseif(isset($arr[0]) || $force_q){
			$return = implode(',', array_fill(1, count($arr), '?'));
		}
		// если мы используем наименования
		else
			$return = implode(',', array_keys($arr));
	}
	return $return;
}

/**
 * Ищет в массиве заданный элемент и возвращает ключ или FALSE если ничего не найдено.
 * Массив должен иметь следующий формат: $arr [ item => [ '$index' => '$element', … ], … ].
 * Обычно это используется для поиска в массивах, которые возвращает ф-ция mysql_get_arr()
 * @param  array  $arr     Array to search element in
 * @param  mixed  $element Needle
 * @param  string $index   Items array key name. Default: "id"
 * @return mixed           Found item key name or FALSE if nothing is found
 */
function get_id_array($arr, $element, $index = 'id'){
	
	if(!$arr || !is_array($arr)){
		return false;
	}
	
	$return = false;
	
	foreach ($arr as $a => $a_val){
		if(!isset($a_val[$index])){
			return false;
		}
		if($a_val[$index] == $element){
			$return = $a;
			break;
		}
	}
	
	return $return;
}

/**
 * Выводит JSON
 * @param $array
 * @return bool
 */
function echo_json($array)
{
	echo json_encode($array, JSON_UNESCAPED_UNICODE);
	return true;
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
 *
 * @param  string $dir [description]
 *
 * @return bool   [description]
 */
function dir_remove($dir){
	
	if(!is_dir($dir)){
		return false;
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
 *
 * @param  string $path File path. Folders will be created if not exist
 * @param  string $str  File content
 *
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
 *
 * @param  string $path     File to copy
 * @param  string $new_path New file path (directories are created on fly if they don't exist)
 *
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
 *
 * @param  string $name File path or name
 *
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
 *
 * @param integer $dayOfYear Day of the current year
 * @param int     $time_now
 * @param string  $format    Desired format. Default: "d.m"
 *
 * @return string            Translated day of the year to date string
 */
function getDateFromDay($dayOfYear, int $time_now = 0, $format = 'd.m'){
	$time_now = $time_now ?: time();

	$date = DateTime::createFromFormat('z Y', $dayOfYear . ' ' . date("Y", $time_now));

	return $date->format($format);
}

/**
 * Конвертация периодичной даты из MySQL в указанный формат
 *
 * @param  string $str    Periodic date (every date with year <u>1004</u>) in format "1004-MM-DD"
 * @param  string $format Desired output format. Default: "d.m"
 *
 * @return string           Translated periodic date to date string
 */
function convert_mysql_date_wo_year($str, $format = null){
	$y = $m = $d = 0;
	list($y, $m, $d) = explode('-', $str);
	// faster default way
	if(!$format){
		return $d . '.' . $m;
	}// via mktime
	else{
		return date($format, mktime(0, 0, 0, $m, $d));
	}
}

/**
 * Перевод даты из MySQL формата в любой формат
 *
 * @param  string $date   Date in MySQL format "YYYY-MM-DD"
 * @param  string $format Desired format (see date())
 *
 * @return string         Converted date string from MySQL to desired format
 */
function convert_mysql_date($date, $format = 'd.m.y'){
	$y = $m = $d = 0;
	list($y, $m, $d) = explode('-', $date);
	
	return date($format, mktime(0, 0, 0, $m, $d, $y));
}

/**
 * Перевод даты СО ВРЕМЕНЕМ из MySQL формата в любой формат
 *
 * @param  string $mysqldatetime Date in MySQL format "YYYY-MM-DD HH:II:SS"
 * @param  string $format        Desired format (see date())
 *
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
 *
 * @param  string $date        Input date
 * @param  string $inputformat Input date format. Default: "d.m.Y"
 *
 * @return string              Converted input date in input format to MySQL format "YYYY-MM-DD"
 */
function convert_to_mysql_date($date, $inputformat = 'd.m.Y'){
	$date = DateTime::createFromFormat($inputformat, $date);
	
	return $date->format('Y-m-d');
}

/**
 * Проверка валидности даты
 *
 * @param        $date
 * @param string $format
 *
 * @return bool
 */
function is_valid_date($date, $format = 'Y-m-d H:i:s'){
	$d = DateTime::createFromFormat($format, $date);
	
	return $d && $d->format($format) == $date;
}


