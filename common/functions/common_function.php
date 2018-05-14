<?php
use yii\helpers\Url;
use yii\base\View;
include_once __DIR__ . '/jsonld.php';
function view($param = '',$exit = false){
	//$_SESSION['configs']['adLogin']['ID']; exit;
	if(Yii::$app->user->can([ROOT_USER]) && !Yii::$app->request->isAjax){
	echo '<pre>'; var_dump($param); echo '</pre>';
	if($exit) exit; 
	}
}
function view2($param = '',$exit = false){
	//$_SESSION['configs']['adLogin']['ID']; exit;
	if(YII_DEBUG && !Yii::$app->request->isAjax ){ 
	echo '<pre>'; var_dump($param); echo '</pre>';
	if($exit) exit; 
	}
}

function cu($param =[],$ab = false,$o = []){
	
	//$suffix = isset($o['suffix']) ? $o['suffix'] : URL_SUFFIX;
	
	if($param === false){
		$category_id = isset($o['category_id']) ? $o['category_id'] : 0;
		if($category_id>0){
			$item = \app\models\Slugs::getItem('',$category_id);
			$param = DS . $item['url'];
		}
	}else{	
		//$string = is_array($param) ? $param[0] : $param;
		if(!is_array($param)){
			$string = $param;
			$param = [$string];
		}else{
			$string = $param[0];
		}
		if(__DOMAIN_ADMIN__ && substr($string, 0,1) != '/'){
			$string = '/' . $string;			
			$param[0] = $string;
		}
	}
	/*
	if( $suffix != "" && isset($param[0])){
	switch ($param[0]){		
		case '/':
		case '/index':
		case 'site/index':
		case 'default/index':
			$suffix = '';
			break;
		default:
			//$param[0] .= $suffix;
			break;
	}}
	*/
	return Url::to($param,$ab) ;
}

function getScheme($domain){
	$s = get_site_value('seo');
	if(isset($s['ssl'][$domain]) && $s['ssl'][$domain] == 'on'){
		return 'https';
	}else{
		return 'http';
	}
}

function getAbsoluteUrl($url){	
	if(strpos($url,'http://') === 0 || strpos($url,'https://') === 0 || substr($url, 0,2) == '//'){
		if(substr($url, 0,2) == '//'){
			return SCHEME . ':' .$url;
		}
		return $url;
	}
	if(!(substr($url, 0,1) == '/')){
		$url = '/' . $url;
	}
	return Url::to($url,true);
}

function getAction($_getParam = 'view'){
	$x = isset(Yii::$app->request->queryParams[$_getParam]) ? Yii::$app->request->queryParams[$_getParam] : '';
	switch ($_getParam){
		case 'id':
			$x = $x > 0 ? $x : 0;
			break;
		case 'view':
			$x = strlen($x) > 0 ? $x : 'index';
			break;
	}
	return $x;
}
function get_script($t,$c = 0,$d ='/'){
	 
	$pos = $c == -1 ? strripos($t, $d) : strpos($t, $d);
	if($pos === false){
		return $t;
	}else{
		return $c == -1 ? substr($t, $pos+1) : substr($t, 0, $pos);
	}
}


function showPrice($prices, $currency_id = -1,$showDefaultText = true, $controller = ''){
	$text_translate_id = 2;
	
	if(is_array($prices)){
		$price = $prices['price'];
	}else{
		$price = $prices;
	}
	
	if($currency_id > 0){		
		$currency = \app\modules\admin\models\UserCurrency::getItem($currency_id);
	}else{
		$currency = Yii::$app->settings['currency']['default'];
	}
	if(!empty($currency)){
		$preText = $afterText = '';
		 
		$priceText = getCurrencyText($price, $currency);
		
		if(!($price>0) && $showDefaultText){
			$preText = $afterText = '';
			$controller = $controller != "" ? $controller : (defined('CONTROLLER_CODE') ? CONTROLLER_CODE : false);
			if(isset(Yii::$site[$controller]['prices']['zero'][__LANG__]) && Yii::$site[$controller]['prices']['zero'][__LANG__] != ""){
				$priceText = uh(Yii::$site[$controller]['prices']['zero'][__LANG__]);
			}else{
				$priceText = getTextTranslate($text_translate_id);
			}
			
			return '<span class="contact-price">' . $priceText . '</span>';
			
		}				
		return  '<span class="price bold">' . $priceText  . '</span>';
	}else{
		return number_format($price);
	}
}

function getCurrencyText($number, $currency,$o=[]){
	if(is_numeric($currency)){
		$currency = \app\modules\admin\models\UserCurrency::getItem($currency);
	}
	//
	$preText = $afterText = '';
	
	$priceText = number_format($number, $currency['decimal_number']);
	
	switch ($currency['display_type']){
		case 1: $preText = ''; $afterText = $currency['symbol']; break;
		case 2: $preText = ''; $afterText = $currency['code']; break;
		case 3: $preText = $currency['symbol']; $afterText = ''; break;
		case 4: $preText = $currency['code']; $afterText = ''; break;
		case 5: $preText = ''; $afterText = $currency['symbol2']; break;
		case 6: $preText = $currency['symbol2']; $afterText = ''; break;
		case 7: $preText = ''; $afterText = ' ' . $currency['symbol2']; break;
		case 8: $preText = ''; $afterText = ' ' . $currency['code']; break;
	}
	
	if(isset($o['show_symbol']) && !$o['show_symbol']){
		$preText = $afterText = '';
	}
	
	return $preText . $priceText . $afterText;
}

function showPrice3($price = 0,$o = array()){
	$type = isset($o['type']) ? $o['type'] : 0;
	$decimal = isset($o['decimal']) ? $o['decimal'] : 0;
	$currency = isset($o['currency']) ? Yii::$app->zii->getCurrency($o['currency']) : Yii::$app->zii->getDefaultCurrency();	
	$decimal = $currency['decimal_number'];
	switch ($type){
		case 1:
			if($price>0){
				$r = (isset($currency['display']) && $currency['display'] == -1 ? $currency['symbol'] : '') . number_format($price,$decimal) .
				(isset($currency['display']) && $currency['display'] == 1 ? $currency['symbol'] : '');
			}else {
				$r = getTextTranslate(2);
			}
			break;
		default:
			if($price>0){
				$r = '<span class="price">'.(isset($currency['display']) 
						&& $currency['display'] == -1 ? 
						'<ins>'.$currency['symbol'].'</ins>' : '') .'<b itemprop="price">' . number_format($price,$decimal).'</b>'.
						(isset($currency['display']) && $currency['display'] == 1 ? 
								'<ins>'.$currency['symbol'].'</ins>' : '') .'</span>';
			}else{
				$r = '<span class="hide" itemprop="price">0</span><span class="contact-price">'.getTextTranslate(2).'</span>';
			}
			break;
	}
	return $r;
}
function validate_domain($domain)
{
	if(stripos($domain, 'http://') === 0)
	{
		$domain = substr($domain, 7);
	}

	///Not even a single . this will eliminate things like abcd, since http://abcd is reported valid
	if(!substr_count($domain, '.'))
	{
		return false;
	}
	if(stripos($domain, 'www.') === 0)
	{
		$domain = substr($domain, 4);
	}
	$again = 'http://' . $domain;
	return filter_var ($again, FILTER_VALIDATE_URL);
}
 
function showTourTime($day = 0, $night = 0){
	$text = $day > 0 ? $day .' '.getTextTranslate(19).' ' : '';
	$text .= $night > 0 ? $night .' '.getTextTranslate(20).'' : '';
	return $text;
}
function getTextTranslate($id = 0, $lang = __LANG__){
	if(0>1 && isset($_SESSION['text'][$id][$lang]) && $_SESSION['text'][$id][$lang] != ""){
		return $_SESSION['text'][$id][$lang];
	}else {
		$c = Yii::$app->db->createCommand("select a.value from text_translate as a where a.id=$id and a.lang='$lang'");
		$r = $c->queryOne();
		if(!empty($r)){
			$_SESSION['text'][$id][$lang] = $r['value'];
			return $r['value'];
		}
	}
}
function jsonify($var){
	return str_ireplace(array("'function:",'"function:',"}'",'}"','"%f%','%f%"',"'%f%","%f%'"),array("function:",'function:',"}",'}','','','',''),json_encode($var,JSON_UNESCAPED_UNICODE));
}
function dijkstra($from =0, $to = 0,$dArray = array(),$pArray = array()){
	//view($pArray);
	$_distArr = $cArray = $node = $place = array();
	foreach($dArray as $key=>$val){
		$cArray[$val['dFrom']][$val['dTo']] = $cArray[$val['dTo']][$val['dFrom']] = $val['distance'];
	}
	// view([62][61]);
	for($i = 1; $i < count($pArray);$i++){

		$_distArr[$i][$i] = 0;
		$v = $pArray[$i-1];
		$node[$v['id']] = $i;
		$place[$i] = $v;
		for($j = $i+1; $j < count($pArray)+1;$j ++){
			$v1 = $pArray[$j-1];
			$node[$v1['id']] = $j;
			$place[$j] = $v1;
			$_distArr[$i][$j] = $_distArr[$j][$i] = isset( $cArray[$v['id']][$v1['id']]) ?  $cArray[$v['id']][$v1['id']] : 99999999;
		}

	}

	//the start and the end
	$a = $node[$from];
	$b = $node[$to];
	//initialize the array for storing
	$S = array();//the nearest path with its parent and weight
	$Q = array();//the left nodes without the nearest path
	foreach(array_keys($_distArr) as $val) $Q[$val] = 99999;
	$Q[$a] = 0;
	//start calculating
	while(!empty($Q)){
		$min = array_search(min($Q), $Q);//the most min weight
		//view($min);
		if($min == $b) break;
		foreach($_distArr[$min] as $key=>$val) if(!empty($Q[$key]) && $Q[$min] + $val < $Q[$key]) {
			$Q[$key] = $Q[$min] + $val;
			$S[$key] = array($min, $Q[$key]);
		}
		unset($Q[$min]);
	}
	//list the path
	$path = array();
	$pos = $b;
	while($pos != $a){
		$path[] = $place[$pos]['name'];
		$pos = $S[$pos][0];
	}
	$path[] = $place[$a]['name'];
	$path = array_reverse($path);
	 
	echo "<br />From ".$place[$a]['name']." to ".$place[$b]['name'];
	echo "<br />The length is ".$S[$b][1];
	echo "<br />Path is ".implode('->', $path);
}

function djson($a = '', $t = 1){
	return json_decode(str_replace('&quot;','"',$a),$t);
}
function cjson($a = array(),$t = JSON_UNESCAPED_UNICODE){
	/*
	if(!empty($a)){
		foreach ($a as $k=>$v){
			if(!is_array($v) && strpos($v, '"')){
				$a[$k] = str_replace('"', '&quot;', $v);
			}
		}
	}
	*/
	
	return json_encode($a,$t);
}
 
function cjson2($a = [],$t = JSON_UNESCAPED_UNICODE){
	$v = json_encode($a,$t);
	return  str_replace('"', '&quot;', $v);
}

function cbool($t = 0){
	return  ($t === 'on' || $t == 1 || $t == true) ? 1 : 0;
}
function isConfirm($value){
	switch (strtolower($value)){
		case 1: case true: case 'on': case 'yes': case 'ok': 
			return true;
			break;
		default: return false; break;
	}
}
function post($element = "",$default = '',$o = array()){
	 $post = Yii::$app->request->post($element,$default);
	 return $post;
}
function createUrl($rt = ''){
	switch($rt){
		case '#': break;
		case '[HOME_URL]':
		case '/':
			$rt = (__IS_ADMIN__ ? ADMIN_ADDRESS : SITE_ADDRESS);
			break;
		default:
			$rt = (__IS_ADMIN__ ? ADMIN_ADDRESS : SITE_ADDRESS).DS.$rt;
			break;
	}
	return $rt;
}
function uhs($t = ''){
	return str_replace(' ', '', $t);
}
function uh($text,$i = 1){
	if(!is_string($text)) return $text; 
	$h = htmlspecialchars_decode(stripslashes($text),ENT_QUOTES );
	switch ($i){
		case 'quot': $h = str_replace('"', '&quot;', $h);break;
		case 'nobr': $h = str_replace(array('<br/>','<br>','</br>'), array(' ',' ',' '), $h);break;

	}
	if(is_numeric($i) && $i > 1){    while ($i > 1){	$i--;   	return uh($h);    }    }
	return $h;
}
function unicode_escape_sequences($str){
	$working = json_encode($str);
	$working = preg_replace('/\\\u([0-9a-z]{4})/', '&#x$1;', $working);
	return json_decode($working);
}
function set_cookie($name='',$value='',$time=0){
	//if($name==null) return false;
	@$_COOKIE[$name]  = $value;
	return setcookie($name,$value,$time);
	///return true;
}
function unset_cookie($name=''){
	//if($name==null) return false;
	@$_COOKIE[$name]  = null;
	return setcookie($name,null,-1,'/');
	//return true;
}
function set_session($name=null,$value=null){
	if($name==null) return false;
	$session=null;
	$name = explode("|",$name);
	$length = count($name);
	switch($length){
		case 1:$_SESSION[$name[0]]=$value; break;
		case 2:$_SESSION[$name[0]][$name[1]]=$value; break;
		case 3:$_SESSION[$name[0]][$name[1]][$name[2]]=$value; break;
		case 4:$_SESSION[$name[0]][$name[1]][$name[2]][$name[3]]=$value; break;
		default: return false;
	}return true;
}
function checkLogged(){
	if(isset($_SESSION['adLogin']) && count($_SESSION['adLogin'])>0) return true;
	return false;
}

function setRoot(){
	$a = array('template','manager');
	if(isset($_SESSION['config']['adLogin']) && MEMBER_LOGIN_TYPE == 'root'){
		define("__IS_ROOT__",true);
		define("ROOT_LOGIN",true);
	}else{
		define("__IS_ROOT__",false);
		define("ROOT_LOGIN",false);
	}
	//view($_SESSION['adLogin']);
	return __IS_ROOT__;
}

function checkRootlogged($r = true){
	if(!__IS_ROOT__ && $r){
		header("Location:".ADMIN_ADDRESS);
	}
	return __IS_ROOT__;
}
function unset_session($name=null){
	if($name==null) return false;
	$value=null;
	$name = explode("|",$name);
	$length = count($name);
	switch($length){
		case 1:$_SESSION[$name[0]]=$value; break;
		case 2:$_SESSION[$name[0]][$name[1]]=$value; break;
		case 3:$_SESSION[$name[0]][$name[1]][$name[2]]=$value; break;
		case 4:$_SESSION[$name[0]][$name[1]][$name[2]][$name[3]]=$value; break;
		default: return false;
	}return true;
}
function remove_accent($str) {
	$a = array('<br>','<br/>', "à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ","è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ","ì","í","ị","ỉ","ĩ","ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ","ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ","ỳ","ý","ỵ","ỷ","ỹ","đ","À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ","È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ","Ì","Í","Ị","Ỉ","Ĩ","Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ","Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ","Ỳ","Ý","Ỵ","Ỷ","Ỹ","Đ",'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ','&quot;');
	$b = array('-','-',"a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a",	"e","e","e","e","e","e","e","e","e","e","e",	"i","i","i","i","i",	"o","o","o","o","o","o","o","o","o","o","o","o"	,"o","o","o","o","o","u","u","u","u","u","u","u","u","u","u","u","y","y","y","y","y","d","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","E","E","E","E","E","E","E","E","E","E","E","I","I","I","I","I","O","O","O","O","O","O","O","O","O","O","O","O"		,"O","O","O","O","O",		"U","U","U","U","U","U","U","U","U","U","U","Y","Y","Y","Y","Y","D",'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o','');
	return str_replace($a, $b, $str);
} 
function unMark($str,$r='-',$lower = true){
	return $lower ? strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'),
			array('', $r, ''), remove_accent($str))) : 
			(preg_replace(array('/[^a-zA-Z0-9 .-]/', '/[ -]+/', '/^-|-$/'),
					array('', $r, ''), remove_accent($str)))
	;
}
function optionListButton($id=0,$a = array()){
	$edit = isset($a['edit']) ? $a['edit'] : true;
	$del = isset($a['del']) ? $a['del'] : true;
	$uid = isset($a['uid']) ? $a['uid'] : __UID;
	$table = isset($a['table']) ? $a['table'] : 'false';
	$mn = '
	<input  type="button" '.($edit === false ? 'disabled="disabled"' : '').' name="update" class="button2 ajax_btn_button_edit" value="Sửa" role="edit"  data-id="'.$id.'" data-uid="'.$uid.'"  data-table="'.$table.'"  />&nbsp;|&nbsp;
	<input  type="button" '.($del === false ? 'disabled="disabled"' : '').' name="delete" class="button2 ajax_btn_button_edit" value="Xóa" data-id="'.$id.'"  data-uid="'.$uid.'"  role="del" data-table="'.$table.'"  /> &nbsp;';
	return $mn;
}
function showListOptionSearch($option = array()){
	$filter_text  = isset($_GET['filter_text']) ? $_GET['filter_text'] : "";
	$filter_option  = isset($_GET['filter_option']) ? $_GET['filter_option'] : "";
	$filter_cate  = isset($_GET['filter_cate']) ? intval($_GET['filter_cate']) : 0;
	$sl = ' selected="selected"';

	$l .= '<input type="text" name="filter_text"  class="fl" id="filter_text" placeholder="'.App::getText(140).'" style="width:250px;vertical-align:middle; padding-left:10px; height:21px;color:#000; font-weight:bold; font-style:italic;  " value="'.$filter_text.'"  />';
	$l .= '<input type="button" name="filter_button" class="button22 filter_button bold" id="" value="'.App::getText(33).'" style="margin-left:5px; vertical-align:middle; " />';
	return $l;
}
function showListOptionButton($option = array()){
	$multi = isset($option['multiple']) && $option['multiple'] == true ? true : false;
	if($multi){
		$l = '<input type="button" name="addnew_multi" class="button2 add_new_record_1 bold" value="'.$reg->f->get_text(37).'" style="color:#F60 ;  "/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	$add = isset($option['add']) && $option['add'] == true ? true : false;
	$add_name = isset($option['add_name']) ? $option['add_name']  : App::getText(9);
	if($add){
		$l .= ' <input type="button" name="addnew" class="button2 btn_button_edit bold" data-action="add"  data-href="'.getUrl(array('act','id','quick_upload','sid')).'act=add&sid='.session_id().'" value="'.$add_name.'"  />';
	}
	$multi_delete = isset($option['multi_delete']) && $option['multi_delete'] == true ? true : false;
	if($multi_delete){
		$l .= '&nbsp;|&nbsp;
            <input type="submit" name="deleteall" class="button2 delete_multi_item bold" value="'.App::getText(15).'" width="100"  />';
	}
	return $l;
}

function getUrl($igr=array(),$o = array()){

	if(isset($igr['check_option'])){
		$o = $igr['check_option'];
		unset($igr['check_option']);
		$cget = isset($o['cget']) && $o['cget'] == true ? true : false;
		//view($_GET);
		if($cget && empty($_GET)){
			return '';
		}
	}
	//if(!$cget && empty($_GET)){
	//	return '';
	//}
	$link = '?';
	if(isset($_GET) && !empty($_GET)){
		foreach($_GET as $k=>$v){
			if(!in_array($k,$igr)){
				$link .= $k . '=' .	$v . '&';
			}
		}
			

	}
//	if($link == '?') return '';
	return $link;
}
function setPagi($o = []){
	$style = isset($o['style']) ? ($o['style']) : ' default ';
	$key = isset($o['key']) ?  ($o['key']) : false;
	$class = isset($o['class']) ?  ($o['class']) : ' ';
	$rewrite = isset($o['rewrite']) && ($o['rewrite']) == true ? true : false;
	$id = isset($o['id']) ?  ($o['id']) : false;
	$total_records = isset($o['total_records']) && $o['total_records'] > 0 ? $o['total_records'] : 0;
	$limit = isset($o['limit']) && $o['limit'] > 0 ? $o['limit'] : 30;

	$first_letter = isset($o['first']) ?  ($o['first']) : '<<';
	$prev_letter = isset($o['prev']) ?  ($o['prev']) : '<';
	$next_letter = isset($o['next']) ?  ($o['next']) : '>';
	$last_letter = isset($o['last']) ?  ($o['last']) : '>>';
	
	$first_class = isset($o['first_class']) ?  ($o['first_class']) : '';
	$prev_class= isset($o['prev_class']) ?  ($o['prev_class']) : '';
	$next_class= isset($o['next_class']) ?  ($o['next_class']) : '';
	$last_class= isset($o['last_class']) ?  ($o['last_class']) : '';
	$page_class= isset($o['page_class']) ?  ($o['page_class']) : '';

	$p =  isset($o['p']) && $o['p'] > 0 ? (int)$o['p'] : 1;
	$t = '<ul class="center system-pagi '.$style .$class.' inline" ';
	$t .= $id != false ? ' id="'.$id.'" ' : '';
	$t .= '>';
	$total_page = ceil($total_records/$limit);
	$start = 1;
	$end = $total_page;
	$first = 1;
	$last = $total_page;
	$prev = $p - 1 > 1 ? $p - 1 : 1;
	$next = $p + 1 > $total_page ? $total_page : $p + 1;
	if($key > 0){
		$link = Category::getLink($key, array('suffix'=>false,));
	}else{
		$link = $key;
	}
	$page = 'page';
	//
	if($total_page > 1){
		if($p > 1){
			$link_first = $rewrite === false ? getUrl(array('p')).'p='.$first : $link.DS.$page.$first.'.html';
			$link_prev = $rewrite === false ? getUrl(array('p')).'p='.$prev : $link.DS.$page.$prev.'.html';
			$t .= '<li class="pagi-li p-li-first" role="'.$first.'"><a class="'.$first_class.'" rel="nofollow" href="'.$link_first.'">'.$first_letter.'</a></li>';
			$t .= '<li class="pagi-li p-li-prev" role="'.$prev.'"><a class="'.$prev_class.'" rel="nofollow" href="'.$link_prev.'">'.$prev_letter.'</a></li>';
		}
		for($i=$start; $i<$total_page+1;$i++){
			$link_page = $rewrite === false ? getUrl(array('p')).'p='.$i : $link.DS.$page.$i.'.html';
			$t .= '<li class="pagi-li p-li-'.$i.' '.($i == $p ? 'active' : '').'" role="'.$i.'"><a class="'.$page_class.' pagi-link '.($i == $p ? 'active' : '').'" role="'.$i.'" rel="nofollow" href="'.$link_page.'">'.$i.'</a></li>';
		}
		if($p < $total_page ){
			$link_next = $rewrite === false ? getUrl(array('p')).'p='.$next : $link.DS.$page.$next.'.html';
			$link_last = $rewrite === false ? getUrl(array('p')).'p='.$last : $link.DS.$page.$last.'.html';
			$t .= '<li class="pagi-li p-li-next" role="'.$next.'"><a class="'.$next_class.' rel="nofollow" href="'.$link_next.'">'.$next_letter.'</a></li>';
			$t .= '<li class="pagi-li p-li-last" role="'.$last.'"><a class="'.$last_class.' rel="nofollow" href="'.$link_last.'">'.$last_letter.'</a></li>';
		}
		$t .= '</ul>';
		return $t;
	}
}

function getParam($param = '',$default = null){
	//if(is_array($default)){ 
		//$default = null; 
	//}
	return Yii::$app->request->get($param,$default);
}

function convertGetToArray(){
	if(isset($_GET)){
		$rs = array();
		$i = 0; $ig = 0;
		foreach($_GET as $k=>$v){
			if(__DOMAIN__ == DOMAIN_NOT_WWW && $ig == 0){
				$ig++;
			}else{
				$rs[$i++] = array('key'=>$k,'value'=>$v);
			}
		}
		return $rs;
	}else return false;
}

function getParamByIndex($index = 0){
	if(isset($_GET) && count($_GET) > 0){
		$i = 0;
		foreach($_GET as $k=>$v){
			if($i == $index) return $v;
			$i++;
		}
	}else{
		return false;
	}
}

function readDate($date = '',$o = array()){
	$spc = isset($o['spc']) ? $o['spc'] : ' ';
	$add_date = isset($o['add_date']) ? $o['add_date'] : 0;
	$lang = isset($o['lang']) ? $o['lang'] : __LANG__;
	$dateOnly = strtotime(str_replace('/', '-', $date));
	
	if($add_date>0){
		$t = ($dateOnly);
		$dateOnly= mktime(0,0,0,date('m',$t),date('d',$t)+$add_date,date('Y',$t));
		//$dateOnly = date('Y-m-d',$n);
	}
	
	$day = date("D",$dateOnly);
	$format = isset($o['format']) ? $o['format'] : 'd/m/Y'; 
	
	$return_option = isset($o['return_option']) ? $o['return_option'] : '';
	
	switch($day){
		case "Mon":	$day = Yii::$app->t->translate('label_monday',$lang);break;
		case "Tue":	$day = Yii::$app->t->translate('label_tuesday',$lang);break;
		case "Wed":	$day = Yii::$app->t->translate('label_wednesday',$lang);break;
		case "Thu":	$day = Yii::$app->t->translate('label_thursday',$lang);break;
		case "Fri":	$day = Yii::$app->t->translate('label_friday',$lang);break;
		case "Sat":	$day = Yii::$app->t->translate('label_saturday',$lang);break;
		case "Sun":	$day = Yii::$app->t->translate('label_sunday',$lang);break;
	}
	
	switch ($return_option){
		case 'day_letter':
			return $day; 
			break;
	}
	return $day .$spc . date($format,$dateOnly);
}

function ampify($html='') {
	# Replace img, audio, and video elements with amp custom elements
	$html = preg_replace( '/style=(["\'])[^\1]*?\1/i', '', $html, -1 );
	//$html = preg_replace( '/alt=(["\'])[^\1]*?\1/i', '', $html, -1 );
	$html = preg_replace( '/"\/\//i', '"'. SCHEME.'://', $html, -1 );
	$html = str_ireplace(
			['<img','<video','/video>','<audio','/audio>'],
			['<amp-img','<amp-video','/amp-video>','<amp-audio','/amp-audio>'],
			$html
			);
	# Add closing tags to amp-img custom element
	$html = preg_replace('/<amp-img(.*?)\/>/', '<amp-img$1 width="800" height="600" layout="responsive"></amp-img>',$html);
	# Whitelist of HTML tags allowed by AMP
	$html = strip_tags($html,'<h1><h2><h3><h4><h5><h6><a><p><ul><ol><li><blockquote><q><cite><ins><del><strong><em><code><pre><svg><table><thead><tbody><tfoot><th><tr><td><dl><dt><dd><article><section><header><footer><aside><figure><time><abbr><div><span><hr><small><br><amp-img><amp-audio><amp-video><amp-ad><amp-anim><amp-carousel><amp-fit-rext><amp-image-lightbox><amp-instagram><amp-lightbox><amp-twitter><amp-youtube>');
	
	
	///$html = preg_replace("/<([a-z]-[a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $html);
	
	return $html;
}

function cDate($time, $option = array()){
	//if(defined(__LANG)) $lang = __LANG;
	$lang = isset($o['lang']) ? $o['lang'] : __LANG__;
	$day = date("D",$time);
	$d = date("d",$time);
	$m = date("m",$time);
	$y = date("Y",$time);
	$s = isset($option['spc']) ? $option['spc'] : 'default';
	$h = isset($option['hour']) ? $option['hour'] : false;
	switch(__LANG__){
		case 'vi_VN':
			switch($day){
				case "Mon":	$day = Yii::$app->t->translate('label_monday',$lang);break;
				case "Tue":	$day = Yii::$app->t->translate('label_tuesday',$lang);break;
				case "Wed":	$day = Yii::$app->t->translate('label_wednesday',$lang);break;
				case "Thu":	$day = Yii::$app->t->translate('label_thursday',$lang);break;
				case "Fri":	$day = Yii::$app->t->translate('label_friday',$lang);break;
				case "Sat":	$day = Yii::$app->t->translate('label_saturday',$lang);break;
				case "Sun":	$day = Yii::$app->t->translate('label_sunday',$lang);break;
			}
			switch($s){
				case 'default':
					$time_re = $day.", ngày ".$d." tháng ".$m." năm ".$y;
					break;
				default:
					$time_re = $day.", ngày ".$d.$s.$m.$s.$y;
					break;
			}
			/*if($s == null){
				$time_re = $day.", ngày ".$d." tháng ".$m." năm ".$y;
				}else{
				$time_re = $day.", ngày ".$d.$s.$m.$s.$y;
				}*/
			if($h==true){
				$time_re.=", ".date("H:i",$time);
			}
			return $time_re;
			break;
		default:
			$today = date("D, d/m/Y, G:i");
			return $today;
			break;
	}

}

function copy_all( $source, $destination ) {
	if ( is_dir( $source ) ) {
		@mkdir( $destination );
		$directory = dir( $source );
		while ( false !== ( $readdirectory = $directory->read() ) ) {
			if ( $readdirectory == '.' || $readdirectory == '..' ) {
				continue;
			}
			$PathDir = $source . '/' . $readdirectory;
			if ( is_dir( $PathDir ) ) {
				copy_all( $PathDir, $destination . '/' . $readdirectory );
				continue;
			}
			@copy( $PathDir, $destination . '/' . $readdirectory );
		}

		$directory->close();
	}else {
		if(!@copy( $source, $destination )){
			if(!file_exists(dirname($destination))){
				@mkdir(dirname($destination),0755,true);
				@copy( $source, $destination );
			}
		}
	}
}

function writeFile($fp,$content='',$mod="w"){
	$fp=str_replace('\\','/',$fp);
	$foder=str_replace('\\','/',$fp);
	$foder = explode("/",$foder);
	$foder=str_replace('/'.$foder[count($foder)-1],'',$fp);
	if(!file_exists($foder) || !is_dir($foder)){
		@mkdir($foder,0755,true);
	}
	@chmod($fp,0644);
	$fp=@fopen($fp,$mod) ;
	if($fp != false){
		$a = @fwrite($fp,$content);
		fclose($fp);
		return $a;
	}else{
		/*$ftp = new ClsFTP();
		 $ftp->type = 1;
		 $ftp->chmod($foder,0777);
		 $fp=@fopen($fp,$mod) ;
		 @fwrite($fp,$content);fclose($fp);
		 return true;*/
	}
	return false;
}
function onOffCheckbox($array = array(),$ck = 0){

	$mn = '<div class="onoffswitch"><input type="checkbox" ';
	if(intval($ck) == 1) $mn .= ' checked="checked" ';

	if(is_array($array) && count($array) > 0){
		$id = isset($array['id']) ? $array['id'] : 0;
		foreach($array as $key=>$vl){
			if($key == 'class') $vl .= ' onoffswitch-checkbox';
			if($key == 'id') $vl = 'myonoffswitch_'.$id;
			$mn .= $key . ' ="'. $vl .'" ';
		}
	}

	$mn .= '/>
    <label class="onoffswitch-label" for="myonoffswitch_'.$id.'">
        <span class="onoffswitch-inner"></span>
        <span class="onoffswitch-switch"></span>
    </label>
    </div>';
	return $mn;
}
function RemoveDir($dir) {
	//echo $dir .'<br/>';
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") RemoveDir($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}
function editFormInput($option = array()){
	$title = isset($option['title']) ? ($option['title'] > 0 ?  $option['title'] : $option['title'] ) : false ;
	$attr = isset($option['attr']) ? $option['attr'] : false;
	$name = isset($attr['name']) ? $attr['name'] : unMark($title);
	$id = isset($attr['id']) ? $attr['id'] : $name;
	if(!isset($attr['id'])){
		$attr['id'] =  $id;

	}

	if(!empty($attr) && !isset($attr['type'])){
		$attr['type'] = 'text';
	}
	if(isset($option['attr']['type']) && $option['attr']['type'] == 'checkbox'){
		$checked = isset($option['attr']['value']) && $option['attr']['value'] == 1 ? ' checked="checked"' : '';
		$c = 'line0';

	}else{$checked = ''; $c = 'line5';}
	$r = '<tr>

      <td class="col_left" align="right"><label class="bold" for="'.$id.'">'.$title.':</label>&nbsp;&nbsp;</td>

      <td class="col_right">';
	if(!empty($attr)){
		$r .= '<input ';
		foreach($attr as $k=>$v){
			$r .= $k .'="' .$v .'" ' ;
		}
		$r .= $checked;
		$r .= ' />';
	}
	 
	$r .= ' </td>

    </tr>

		<tr class="line5  '.$c.'">
		  <td class=" ">&nbsp;</td>
		  <td class=" ">&nbsp;</td>
    </tr>';
	return $r;
}

$mangso = array('không','một','hai','ba','bốn','năm','sáu','bảy','tám','chín');
function dochangchuc($so,$daydu)
{
	global $mangso;
	$chuoi = "";
	$chuc = floor($so/10);
	$donvi = $so%10;
	if ($chuc>1) {
		$chuoi = " " . $mangso[$chuc] . " mươi";
		if ($donvi==1) {
			$chuoi .= " mốt";
		}
	} else if ($chuc==1) {
		$chuoi = " mười";
		if ($donvi==1) {
			$chuoi .= " một";
		}
	} else if ($daydu && $donvi>0) {
		$chuoi = " lẻ";
	}
	if ($donvi==5 && $chuc>1) {
		$chuoi .= " lăm";
	} else if ($donvi>1||($donvi==1&&$chuc==0)) {
		$chuoi .= " " . $mangso[$donvi];
	}
	return $chuoi;
}
function docblock($so,$daydu)
{
	global $mangso;
	$chuoi = "";
	$tram = floor($so/100);
	$so = $so%100;
	if ($daydu || $tram>0) {
		$chuoi = " " . $mangso[$tram] . " trăm";
		$chuoi .= dochangchuc($so,true);
	} else {
		$chuoi = dochangchuc($so,false);
	}
	return $chuoi;
}
function dochangtrieu($so,$daydu)
{
	$chuoi = "";
	$trieu = floor($so/1000000);
	$so = $so%1000000;
	if ($trieu>0) {
		$chuoi = docblock($trieu,$daydu) . " triệu";
		$daydu = true;
	}
	$nghin = floor($so/1000);
	$so = $so%1000;
	if ($nghin>0) {
		$chuoi .= docblock($nghin,$daydu) . " nghìn";
		$daydu = true;
	}
	if ($so>0) {
		$chuoi .= docblock($so,$daydu);
	}
	return $chuoi;
}
function docso($so, $currency = 1, $lang = __LANG__)
{
	global $mangso;
	if ($so==0) return $mangso[0];
	$chuoi = "";
	$hauto = "";
	$chan = "";
	$c = $currency == 1 ? 'đồng' : Yii::$app->zii->showCurrency($currency);
	if($so % 10000 == 0 && $currency == 1) $chan = ' chẵn';
	do {
		$ty = $so%1000000000;
		$so = floor($so/1000000000);
		if ($so>0) {
			$chuoi = dochangtrieu($ty,true) . $hauto . $chuoi;
		} else {
			$chuoi = dochangtrieu($ty,false) . $hauto . $chuoi;
		}
		$hauto = " tỷ";
	} while ($so>0);
	return ucfirst(trim($chuoi)) .' ' . $c . $chan;
}
 
function readCurrency($so, $currency = 1, $lang = __LANG__){
	global $mangso;
	if ($so==0) return $mangso[0];
	$chuoi = "";
	$hauto = "";
	$chan = "";
	$c = $currency == 1 ? 'đồng' : Yii::$app->zii->showCurrency($currency);
	if($so % 10000 == 0 && $currency == 1) $chan = ' chẵn';
	do {
		$ty = $so%1000000000;
		$so = floor($so/1000000000);
		if ($so>0) {
			$chuoi = dochangtrieu($ty,true) . $hauto . $chuoi;
		} else {
			$chuoi = dochangtrieu($ty,false) . $hauto . $chuoi;
		}
		$hauto = " tỷ";
	} while ($so>0);
	return ucfirst(trim($chuoi)) .' ' . $c ;
}

function adIndexButton($o = array()){

	$id = isset($o['id']) ? $o['id'] : 0;
	$del = isset($o['del']) ? $o['del'] : 1;
	$edit = isset($o['edit']) ? $o['edit'] : 1;
	$table = isset($o['table']) ? $o['table'] : 'none';
	$join_table = isset($o['join_table']) ? $o['join_table'] : 'none';
	$join_field = isset($o['join_field']) ? $o['join_field'] : 'none';
	$field = isset($o['field']) ? $o['field'] : 'none';
	$uid = isset($o['uid']) ? $o['uid'] : 'none';
	$lang = isset($o['lang']) ? $o['lang'] : 'none';



	$m = '<input type="button" name="edit" class="btn_ajax_action normal_button" value="Sá»­a" role="'.($edit == 1 ? 'edit' : 'disable').'" data-table="'.$table.'" data-id="'.$id.'" data-href="'.getUrl(array('id','act')).'act=edit&id='.$id.'" >
    &nbsp;|&nbsp;
    <input type="button" name="delete" class="btn_ajax_action normal_button" value="XÃ³a" role="'.($del == 1 ? 'del' : 'disable').'" data-table="'.$table.'" data-id="'.$id.'" data-uid="'.$uid.'" data-join_table="'.$join_table.'" data-lang="'.$lang.'">';
	 
	return $m;
}

function beginLine($o = array()){
	$id = isset($o['id']) ? $o['id'] : false;
	$class = isset($o['class']) ? $o['class'] : '';
	$class_in = isset($o['class_in']) ? $o['class_in'] : '';
	$key = isset($o['key']) ? trim($o['key']) : false;
	$bgr = isset($o['bgr']) && $o['bgr'] == false ? false  : true;
	$class_cin = isset($o['class_cin']) ? $o['class_cin'] : '';
	// Member::getHeaderBackground(0,array('class'=>'header fl100','id'=>'header'));
	$t = 919;
	switch($key){
		case 'header': $t = 2; break;
		case 'footer': $t = 3; break;
		case 'main': $t = 6; break;

	}
	if($bgr){
		$to = Member::getBackground(array(
				'tag'=>'div',
				't'=>$t,
				'key'=>$key,
				'attr'=>array(
						'id'=>$id !== false ? $id : 'id_'.time().rand(0,999999),
						'class'=>$class . " fl100 $key",

				)
		));
	}else{
		$to = '<div  class="fl100 '.$key.' '.$class .' " ' .($id !== false ? 'id="'.$id.'"' : '') .'>';
	}
	if($bgr){
		$ti = Member::getBackground(array(
				'tag'=>'div',
				't'=>$t + 15,

				'attr'=>array(
						// 'id'=>$id !== false ? $id : 'id_'.time().rand(0,999999),
						'class'=>$class_in . " pr fl100 $key" .'_in',

				)
		));
	}else{
		$ti = '<div  class="fl100 pr '.$key.'_in '.$class_in .' ">';
	}
	 
	$t = $to ;
	$t .= "<div class=\"c_in $class_cin\">";
	$t .= $ti;
	echo $t;
}
function endLine(){
	echo '</div></div></div><!--endline-->';
}
function strip_tags_content($text, $tags = '', $invert = FALSE) {

	preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
	$tags = array_unique($tags[1]);

	if(is_array($tags) AND count($tags) > 0) {
		if($invert == FALSE) {
			return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
		}
		else {
			return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
		}
	}
	elseif($invert == FALSE) {
		return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
	}
	return $text;
}

function getPaging($o=array()){
	$style = isset($o['style']) ? $o['style'] : 'default';
	$btnText = isset($o['btnText']) ? $o['btnText'] : '';
	$key = isset($o['key']) ? $o['key'] : '';
	$type = isset($o['type']) ? $o['type'] : '';
	$p = isset($o['p']) ? $o['p'] : 1;
	$totalPage = isset($o['totalPage']) ? $o['totalPage'] : 1;
	$controllerID = isset($o['controllerID']) ? $o['controllerID'] : 0;
	$limit = isset($o['limit']) ? $o['limit'] : 0;
	$str = '';
	if($totalPage < 2) return '';
	$str .= '<div class="systemPaging center">';
	switch($style){
		case 'ajax_load':
			$data = '{&quot;p&quot;:'.$p.',&quot;limit&quot;:'.$limit.',&quot;totalPage&quot;:'.$totalPage.',&quot;controllerID&quot;:'.$controllerID.',&quot;key&quot;:&quot;'.$key.'&quot;,&quot;type&quot;:&quot;'.$type.'&quot;}';
			$str .= '<p class="center img_loading hide"><img  src="'.__LIBS_DIR.'/img/loading.gif'.'" alt="" /></p><span role="'.$data.'" class="system center  btnPaging">'.$btnText.'</span> ';
			break;
	}
	$str .= '</div>';
	return $str;
}

function returnUrl($igr = array(),$id = 0){

	$igr += array('id','act');
	$id = isset($_GET['id']) && $_GET['id'] > 0 ? (int)$_GET['id'] : $id;
	if(isset($_POST['temp_save']) || (isset($_GET['bt_submit']) && $_GET['bt_submit'] == 'temp_save')){
		$l = "id=".$id.'&act=edit';
	}elseif(isset($_POST['save_and_close']) || (isset($_GET['bt_submit']) && $_GET['bt_submit'] == 'save_and_close')){
		$l = '&act=index';
	}elseif(isset($_POST['save_and_addnew']) || (isset($_GET['bt_submit']) && $_GET['bt_submit'] == 'save_and_addnew')){
		$l = '&act=add';
	}
	$link = ADMIN_ADDRESS.DS.'?';
	if(isset($_GET)){
		foreach($_GET as $k=>$v){
			if(!in_array($k,$igr)){
				$link .= $k . '=' .	$v . '&';
			}
		}
	}
	$link = str_replace('&&','&',$link.$l);
	header("Location:". $link );

}

function ckeditor($id,$o = array()){
	$input = isset($o['input']) ? trim($o['input']) : 'textarea';
	$attr =  isset($o['attr']) ? $o['attr'] : false;
	$value = isset($o['value']) ? $o['value'] : '';
	$toolbar = isset($o['toolbar']) ? $o['toolbar'] : "Basic";
	$class = isset($o['class']) ? $o['class'] : "ckeditor_full";
	$upload = isset($o['upload']) &&  $o['upload'] == true ? true : '';
	$w = isset($o['w']) &&  $o['w'] > 0  ? $o['w'] : '';
	$h = isset($o['h']) &&  $o['h'] > 0  ? $o['h'] : '';
	$d = ($w > 0 ? "width:$w," : '') . ($h > 0 ? "height:$h," : '');


	if($upload === true){
		$f1 = '/libs/ckeditor/ckfinder/ckfinder.html';
		$f2 = '/libs/ckeditor/ckfinder/core/connector/php/connector.php';
		$upload = "filebrowserBrowseUrl : '$f1',
		filebrowserImageBrowseUrl : '$f1?type=Images',
		filebrowserFlashBrowseUrl : '$f1?type=Flash',
		filebrowserUploadUrl : '$f2?command=QuickUpload&type=Files',
		filebrowserImageUploadUrl : '$f2?command=QuickUpload&type=Images',
		filebrowserFlashUploadUrl : '$f2?command=QuickUpload&type=Flash'";
	}
	/////////////////////////////////////////////////////////
	$t = "<$input class=\"$class form-control\" id=\"ckeditor_$id\"";
	$name = $id;
	if(!empty($attr)){
		foreach($attr as $k=>$v){
			$k = $k == 'h' ? 'data-height' : $k;
			if($k == 'name')
				$name = $v;
				elseif($k != "class")
				$t .= " $k=\"$v\"";
		}
	}
	$t .= $h > 0 ? ' data-height="'.$h.'"' : '';
	$t .= " name=\"$name\">$value</$input>";
	switch($toolbar){
		case 'Basic':
			$toolbar = "toolbar: [
		{ name: 'document', items: [ 'Source', '-', 'NewPage', 'Preview', '-', 'Templates' ] },	// Defines toolbar group with name (used to create voice label) and items in 3 subgroups.
		[ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],			// Defines toolbar group without name.
																							// / Line break - next group will be placed in new line.
		{ name: 'basicstyles', items: [ 'Bold', 'Italic' ] }
	],";
			break;
		default:
			if(is_array($toolbar)){
				$toolbar ='toolbar:' . json_encode($toolbar) .',';
			}else{$toolbar = '';}
			break;
	}
	$script = "<script defer>CKEDITOR.replace( 'ckeditor_$id',{
	$d $toolbar $upload
});</script>";
	return $t;
}

function filesize_formatted($size = 0){
	//$size = filesize($path);
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$power = $size > 0 ? floor(log($size, 1024)) : 0;
	return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

function get_file_size($url){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	$data = curl_exec($ch);
	$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
	curl_close($ch);
	return $size;
}
function check_file_existed($url){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	$data = curl_exec($ch);
	$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
	curl_close($ch);
	return $size > 0 ? true : false;
	/*

	$result = -1;
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_NOBODY, true );
	curl_setopt( $curl, CURLOPT_HEADER, true );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
	$data = curl_exec( $curl );
	curl_close($curl);
	if($data) {
	$content_length =0;
	$status = "unknown";
	if(preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)){
	$status = (int)$matches[1];
	}
	if(preg_match( "/Content-Length: (\d+)/", $data, $matches)){
	$content_length = (int)$matches[1];
	}
	if($status == 200 || ($status > 300 && $status <= 308)){
	$result = $content_length;
	}
	}
	return $result > 0 ? true : false;
	*/
}
function parse_file_name($filename = ''){
	$pos = strrpos($filename,'/',1);
	$filename = substr($filename,$pos+1);
	$pos = strrpos($filename,'.',1);
	$file_type = substr($filename,$pos+1);
	$file_name = substr($filename, 0,$pos);
	return array(
			'type'=>$file_type,
			'name'=>$file_name,
			'full_name'=>$filename,
			 
	);
}
function getImage($o = array(),$absolute = false){
	$w = isset($o['w']) && $o['w'] > 0 ? $o['w'] : (isset($o['width']) && $o['width'] > 0 ? $o['width'] : 0);
	$h = isset($o['h']) && $o['h'] > 0 ? $o['h'] : (isset($o['height']) && $o['height'] > 0 ? $o['height'] : 0);
	$maxWidth = isset($o['max-width']) && $o['max-width'] > 0 ? $o['max-width'] : 0;
	$maxHeight = isset($o['max-height']) && $o['max-height'] > 0 ? $o['max-height'] : 0;
	$rename = isset($o['rename']) && $o['rename'] == false ? false : true;
	$w = $w > $maxWidth && $maxWidth > 0 ? $maxWidth : $w;
	$h = $h > $maxHeight && $maxHeight > 0 ? $maxHeight : $h;
	$src = isset($o['src'])  ?   $o['src'] : false;
	$src = str_replace(" ",'%20',$src);
	if($src === false || $src == "") return false;
	//
	if(substr($src, 0,2) == '//'){
		$src = SCHEME . ':' . $src; 
	}
	//
	$op = isset($o['img_attr']) ?  $o['img_attr'] : 
	(isset($o['attr']) ?  $o['attr'] : (isset($o['attrs']) ?  $o['attrs'] : false));
	$output = isset($o['output']) ? $o['output'] : 'img';
	$save = isset($o['save']) && $o['save'] ? true : false;
	$alt = isset($o['alt']) ? $o['alt'] : '';
	$s = explode(';',$src);
	$href = $src;
	$hxx= ($src);
	if(count($s)>0){
		$src = $s[0];
	}
	$sUrl = false;
	if($w==0 && $h==0 ){
		$href = $src;
	}else{
		//
		if(0>1&& !isset(Yii::$site['settings']['medias']['domain'])){
			$l = (new \yii\db\Query())->from(['server_config'])->where(['sid'=>[0,__SID__],'is_active'=>1])->select(['web_address'])->orderBy(['sid'=>SORT_DESC])->one();
			if(!empty($l)){
				$host_url = (dString($l['web_address']) . '/image.php?src='.$src.'&w='.$w.'&h='.$h);
				$href= $host_url;
				 
				$sUrl = true;
			}else{
				$host_url = Url::home($absolute) . 'image.php';
			}
		}
		$host_url = Url::home($absolute) . 'image.php';
		//
		if(!$sUrl){
		$file = parse_file_name($src);
		$hash_file = $rename ? md5($src) : unMark(str_replace('%20', '-', $file['name']));
		$ext = $file['type'];
		if($ext == 'jpeg') $ext = 'jpg';
		//if(0>1 && isset(Yii::$site['other_setting']['thumb_url']) && strlen(Yii::$site['other_setting']['thumb_url']) > 4){
		//	$host_url = Yii::$site['other_setting']['thumb_url'];
		//}else{
		//	$host_url = Url::home($absolute) . 'image.php';
		//}
		$fp =   '/medias/thumbs/'.$w.'x'.$h.DS.$hash_file.'.'.$ext;
		
		if(@file_exists(__ROOT_PATH__.$fp)){
			//}
			//if(check_file_existed($fp)){
			$href = removeLastSlashes(Url::home($absolute)).$fp;
		}else{
			if($save){
				require_once __ROOT_PATH__ .'/libs/thumb/ThumbLib.inc.php';
				$thumb = PhpThumbFactory::create($src);
				if($w > 0 && $h > 0){ $fn = 'adaptiveResize';}else $fn = 'resize';
				$thumb->$fn($w,$h);
				if(in_array($ext,array('jpg','png','jpeg'))){
					if(!file_exists(dirname(__ROOT_PATH__.$fp))){
						@mkdir(dirname(__ROOT_PATH__.$fp),0755,true);
					}
					@$thumb->save(__ROOT_PATH__.$fp, $ext);
					$href = removeLastSlashes(Url::home($absolute)) . $fp;
				}
			}else
				$href = $host_url.'?src='.$src.'&w='.$w.'&h='.$h.'&rename='.(!$rename ? 'false' : 'true').'&hash_file='.$hash_file;
		}
		}
	}
	$hxx .= ' / '. ($href);
	//view($href);
	if($output == 'src'){
		return $href;
	}

	$img = '<img src="'.$href.'" ';
	if(!empty($op)){
		foreach($op as $k=>$v){
			if($k == 'alt'){
				$alt = $v;
			}else $img .= $k .'="'.$v.'" ';
		}
	}
	$img .= ' alt="'.$alt.'" />';
	return $img;
}
function getImagex($option = array()){
	$w = isset($option['w']) && $option['w'] > 0 ? $option['w'] : (isset($option['width']) && $option['width'] > 0 ? $option['width'] : 0);
	$h = isset($option['h']) && $option['h'] > 0 ? $option['h'] : (isset($option['height']) && $option['height'] > 0 ? $option['height'] : 0);
	$maxWidth = isset($option['max-width']) && $option['max-width'] > 0 ? $option['max-width'] : 0;
	$maxHeight = isset($option['max-height']) && $option['max-height'] > 0 ? $option['max-height'] : 0;
	$w = $w > $maxWidth && $maxWidth > 0 ? $maxWidth : $w;
	$h = $h > $maxHeight && $maxHeight > 0 ? $maxHeight : $h;
	$src = isset($option['src'])  ?   $option['src'] : false;
	$src = str_replace(" ",'%20',$src);
	if($src === false || $src == "") return false;
	$op = isset($option['img_attr']) ?  $option['img_attr'] : (isset($option['attr']) ?  $option['attr'] : false);
	$output = isset($option['output']) ? $option['output'] : 'img';
	$alt = isset($option['alt']) ? $option['alt'] : '';
	$s = explode(';',$src);
	if(count($s)>0){
		$src = $s[0];
	}
	if($w==0 && $h==0 ){
		$href = $src;
	}else{
		//$hash_file = @hash_file('md5',$src);
		//$hash_file = $hash_file !== false ? $hash_file : md5($src);
		$hash_file = md5($src);
		$ext = explode('.',$src);
		$ext = strtolower($ext[count($ext)-1]);
		if($ext == 'jpeg') $ext = 'jpg';
		$fp = '/medias/thumbs/'.$w.'x'.$h.DS.$hash_file.'.'.$ext;
		if(isset(Yii::$site['other_setting']['thumb_url']) && strlen(Yii::$site['other_setting']['thumb_url']) > 4){
			$host_url = Yii::$site['other_setting']['thumb_url'];
		}else{
			$host_url = Yii::getBaseUrl();
		}
		 
		if(@file_exists(__ROOT_PATH__.$fp)){
			$href = Yii::getBaseUrl().$fp;
		}else{
			$href = Yii::getBaseUrl().'/image.php?src='.$src.'&w='.$w.'&h='.$h.'&hash_file='.$hash_file;
		}
	}
	if($output == 'src') return $href;
	$img = '<img src="'.$href.'" ';
	if(!empty($op)){
		foreach($op as $k=>$v){
			if($k == 'alt'){
				$alt = $v;
			}else $img .= $k .'="'.$v.'" ';
		}
	}
	$img .= ' alt="'.$alt.'" />';
	return $img;
}
function checkPermission($moduleID = 0, $type = 0){
	if(!defined('MEMBER_LOGIN_TYPE')) define('MEMBER_LOGIN_TYPE', $_SESSION['config']['adLogin']['type']);
	if(in_array(MEMBER_LOGIN_TYPE,array('admin','root'))) return true;
	//$type = $type == 0 ? 'inside' : 'outside';
	switch ($type){
		case 1: $type = 'outside'; break;
		case 2: $type = 'forms'; break;
		default: $type = 'inside'; break;
	}
	return isset($_SESSION['permission'][$type]) ? in_array($moduleID, $_SESSION['permission'][$type]) : false;
	return false;
}
function checkPPermission($module = '', $action = 'view', $post_by = 0){
	if(!defined('MEMBER_LOGIN_TYPE')) define('MEMBER_LOGIN_TYPE', $_SESSION['config']['adLogin']['type']);
	if(in_array(MEMBER_LOGIN_TYPE,array('admin','root'))) return true;
	if(isset($_SESSION['permission']['module'][$module])){
		$t = false;
		if($post_by > 0){
			$t = $post_by == MEMBER_LOGIN_ID ? true : false;
		}
		return $_SESSION['permission']['module'][$module][$action] == 1 ? true : $t;

	}
	return false;
}
function validateEmail($email = ''){
	if(filter_var($email, FILTER_VALIDATE_EMAIL) !==false){
		return true;
	}
	return false;
}
function getMongoID($mongoColection = array()){
	if(is_object($mongoColection)){
		return ($mongoColection->{'$id'});
	}
	elseif(isset($mongoColection['_id'])){
		return ($mongoColection['_id']->{'$id'});
	}
	return '';
}
function uhx($str = ''){
	return str_replace(array("\n","\t","\r",'<br/>','<br>','</br>'),array(' ',' ',' ',' ',' ',' '), $str);
}
function randString($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
	// Length of character list
	$chars_length = (strlen($chars) - 1);

	// Start our string
	$string = $chars{rand(0, $chars_length)};

	// Generate random string
	for ($i = 1; $i < $length; $i = strlen($string))
	{
		// Grab a random character from our list
		$r = $chars{rand(0, $chars_length)};

		// Make sure the same two characters don't appear next to each other
		if ($r != $string{$i - 1}) $string .=  $r;
	}

	// Return the string
	return $string;
}
function danhso($so = 0,$lenght = 6,$o = array()){
	$allowNull = isset($o['allowNull']) && $o['allowNull'] == false ? false : true;
	$before = isset($o['before']) ? $o['before'] : '';
	$after = isset($o['after']) ? $o['after'] : '';
	if($so == 0 && $allowNull) return '';
	$max = $a = '';
	for($i = 0;$i<$lenght;$i++){
		$max .= '9';
	}
	$s = strlen($max) - strlen($so);
	for($i = 0;$i<$s;$i++){
		$a .= '0';
	}
	return $before . $a . $so . $after;
}
function showMP3Player($o = []){
	$id = isset($o['id']) ? $o['id'] : randString(6);
	$mp3_file = isset($o['file']) ? $o['file'] : '';
	$title = isset($o['title']) ? $o['title'] : '';
	$style = isset($o['style']) ? $o['style'] : '';
	$script = '';
	$script .= '<div id="jquery_jplayer_'.$id.'" class="jp-jplayer"></div>
<div id="jp_container_'.$id.'" class="jp-audio w100 '.$style.'" role="application" aria-label="media player">
	<div class="jp-type-single">
		<div class="jp-gui jp-interface">
			<div class="jp-controls">
				<button class="jp-play" role="button" tabindex="0">play</button>
				<button class="jp-stop" role="button" tabindex="0">stop</button>
			</div>
			<div class="jp-progress">
				<div class="jp-seek-bar">
					<div class="jp-play-bar"></div>
				</div>
			</div>
			<div class="jp-volume-controls">
				<button class="jp-mute" role="button" tabindex="0">mute</button>
				<button class="jp-volume-max" role="button" tabindex="0">max volume</button>
				<div class="jp-volume-bar">
					<div class="jp-volume-bar-value"></div>
				</div>
			</div>
			<div class="jp-time-holder">
				<div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
				<div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
				<div class="jp-toggles">
					<button class="jp-repeat" role="button" tabindex="0">repeat</button>
				</div>
			</div>
		</div>
		<div class="jp-details">
			<div class="jp-title" aria-label="title">&nbsp;</div>
		</div>
		<div class="jp-no-solution">
			<span>Update Required</span>
			To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
		</div>
	</div>
</div>';
	return $script;
}
function ctime($o = []){
	$string = isset($o['string']) ? $o['string'] : '';
	$format = isset($o['format']) ? $o['format'] : 'Y-m-d H:i:s';
	$return_type = isset($o['return_type']) ? $o['return_type'] : 0;
	switch ($return_type){
		case 1:
			return strtotime(str_replace('/', '-', $string));
			break;
		default:
			return date($format,strtotime(str_replace('/', '-', $string)));
			break;
	}
}
function convertTime($timeString = '', $format = "Y-m-d H:i:s",$r = 0){
	switch ($r){
		case 1:
			return strtotime(str_replace('/', '-', $timeString));
			break;
		default:
			return date($format,strtotime(str_replace('/', '-', $timeString)));
			break;
	}
}
function roundMoney($val = 0){
	$r = $val > 1000000 ? -4 : -3;
	return round($val,$r);
}
function cprice($val = 0){
	
	if(!(strlen($val) > 0)){
		return 0;
	}
	
	if(is_string($val)){
		if(strlen($val) == 0) return $val;	
		$n = str_replace(',', '', $val);
		return is_numeric($n) ? $n : 0;
	}elseif (is_numeric($val)){
		return $val;
	}
	return 0;
}
function getHeaderExportBill($o = array()){
	//$cp = isset($o['colspan']) ? $o['colspan'] : 15;
	$field = isset($o['field']) ? $o['field'] : array();
	$title = isset($o['title']) ? $o['title'] : '';
	$tax = isset($o['tax']) && $o['tax'] == true ? $o['tax'] : false;
	$file_name = isset($o['file_name']) ? $o['file_name'] : '';
	$bg_color = isset($o['bg_color']) ? $o['bg_color'] : 'yellow';
	$customerInfo = isset($o['customerInfo']) ? $o['customerInfo'] : false;
	$cp = count($field);

	$address =  '';


	$html = '<tr class="sui-row">';
	$html .= '<td class="sui-cell" colspan="'.$cp.'" style="text-align:center;font-weight:bold;font-size:14pt">'.$title.'</td>';
	$html .= '</tr>';

	$html .= '<tr class="sui-row">';
	$html .= '<td class="sui-cell" colspan="'.$cp.'" style="text-align:center;font-size:10pt"></td>';
	$html .= '</tr>';


	$html .= '<tr class="sui-row">';
	$html .= '<td class="sui-cell" colspan="'.$cp.'"></td>';
	$html .= '</tr>';

	if(!empty($field)){
		$html .='<tr class="sui-columnheader">';
		foreach ($field as $f){
			$name = $f; $width='auto';
			if(is_array($f)){
				$name = $f['name'];
				$width = isset($f['width']) ? $f['width'] : $width;
			}
			$html .= '<th class="sui-headercell" style="background-color:'.$bg_color.'; border:0.5pt solid black;width:'.$width.'"><b class="sui-link">'.$name.'</b></th>';
		}
		$html .='</tr>';
	}

	return $html;
}
function showTourType($type = 1){
	$m = '';
	switch ($type){
		case 1: $m = 'Inbound'; break;
		case 2: $m = 'Outbound'; break;
		case 3: $m = 'NÃƒÂ¡Ã‚Â»Ã¢â€žÂ¢i Ãƒâ€žÃ¢â‚¬ËœÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¹a'; break;
	}
	return $m;
}
function generatorCaptcha($o = array()){
	$h = isset($o['h']) && $o['h'] > 0 ? $o['h'] : 0;
	$html = '<img src="'.__LIBS_DIR__.'/captcha/" alt="captcha" '.($h > 0 ? 'height="'.$h.'"' : '').' id="captcha_image" class="pointer" onclick="refreshCaptcha(this);" title="Click Ãƒâ€žÃ¢â‚¬ËœÃƒÂ¡Ã‚Â»Ã†â€™ thay Ãƒâ€žÃ¢â‚¬ËœÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¢i mÃƒÆ’Ã‚Â£ captcha" />';
	return $html;
}
function logout($m = 1){
	if($m == 1){
		set_cookie('adAutoLogin',0,0);
		unset_cookie("adUserLogin");
		unset_cookie("adUserPassword");
		$_SESSION['config']['adLogin'] = array();
		unset($_SESSION['config']);
		session_destroy();
		header("Location:".createUrl('/'));
	}else{
		set_cookie('memAutoLogin',0,0);
		unset_cookie("memberLogin");
		unset_cookie("memberPassword");
		$_SESSION['config']['memLogin'] = array();
		unset($_SESSION['config']);
		session_destroy();
		header("Refresh:0");
	}
}
function validateDate($date, $format = 'Y-m-d H:i:s')
{
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}
function check_date_string($date_string = '',$min = 2010){
	if(strlen($date_string) < 4){
		return false;
	}
	if(date("Y",strtotime(str_replace('/', '-',  $date_string))) > $min){
		return true;
	}
	return false;

}
function get_folder_upload_file($file_name = ''){
	$folder = 'files';
	$pos = strrpos($file_name,'.',1);
	$file_type = $pos > 0 ? substr($file_name,$pos+1) : '';
	switch (strtolower($file_type)){
		case 'jpeg':case 'png':case 'gif':case 'jpg':case 'ico':case 'bmp':case 'svg':
			$folder = 'images';
			break;
			//case 'jpeg':case 'png':case 'gif':case 'jpg':case 'ico':case 'bmp':case 'svg':
			//	$folder = 'images';
			//	break;
		case 'mp4':case '3gp':case '3gpp2':case 'avi':case 'flv':
			$folder = 'videos';
			break;
	}
	return $folder;
}
function file_extension_upload($type = 'files') {
	switch ($type){
		case 'image':case 'images':
			$file_extensions = array('jpeg', 'png', 'gif','jpg','ico','bmp','svg');
			break;
		case 'doc':case 'docs':case 'document':case 'documents':
			$file_extensions = array('xls', 'xlsx', 'doc','docx','dot','txt','pdf','ppt','pptx');
			break;
		case 'video':case 'videos':
			$file_extensions = array('mp4', '3gp', '3gpp2', 'avi','flv');
			break;
		case 'audio':
			$file_extensions = array('3gpp', 'mp3', 'acc', 'ac3', 'amr', 'm4a', 'wma', 'wav',);
			break;
		case 'text':
			$file_extensions = array('txt','srt','ass','sub');
		default:
			$file_extensions = array(
			'txt','srt','ass','sub','zip','rar','7z','tar','gz',
			'jpeg', 'png', 'gif','jpg','ico','bmp','svg',
			'xls', 'xlsx', 'doc','docx','dot','pdf','ppt','pptx',
			'mp4', '3gp', '3gpp2', 'avi',
			'3gpp', 'mp3', 'acc', 'ac3', 'amr', 'm4a', 'wma', 'wav',

			);
			break;
	}
	return $file_extensions;
}
function count_time_post($time_string = '', $lang = __LANG__){
	$time2 = strtotime(str_replace('/', '-', $time_string));
	$sub_time = time() - $time2; $r = '';
	if($sub_time <11){
		$r = 'Vừa xong';
	}elseif($sub_time>10 && $sub_time < 60){
		$r = $sub_time .'s trước' ;
	}elseif ($sub_time < 3600){
		$m = (int)($sub_time/60);
		$r = $m .' phút trước' ;
	}elseif ($sub_time < 86400){
		$m = (int)($sub_time/3600);
		$r = $m .' giờ trước' ;
	}elseif ($sub_time < 604800){
		$m = (int)($sub_time/86400);
		$r = $m .' ngày trước' ;
	}elseif ($sub_time < 2592000){
		$m = (int)($sub_time/604800);
		$r = $m .' tuần trước' ;
	}elseif ($sub_time < 31536000){
		$m = (int)($sub_time/2592000);
		$r = $m .' tháng trước' ;
	}else{
		$m = (int)($sub_time/31536000);
		$r = $m .' năm trước' ;
	}
		
	return $r;
}
function get_domain_with_scheme($domain){
	$d = parse_url($domain);
	$d['scheme'] = isset($d['scheme']) ? $d['scheme'] : 'http';
	$d['host'] = isset($d['host']) ? $d['host'] : $d['path'];
	return $d['scheme'] . '://' . $d['host'];
}
function get_site_value($string = '',$d = 1,$trim_all = false){
	$r = '';
	 
	if(strlen($string)>0){
		$x = explode('/', $string);
		switch (count($x)){
			case 2:
				$r = isset(Yii::$site[$x[0]][$x[1]]) ? uh(Yii::$site[$x[0]][$x[1]],$d) : '';
				break;
			case 3:
				$r = isset(Yii::$site[$x[0]][$x[1]][$x[2]]) ? uh(Yii::$site[$x[0]][$x[1]][$x[2]],$d) : '';
				break;
			case 4:
				$r = isset(Yii::$site[$x[0]][$x[1]][$x[2]][$x[3]]) ? uh(Yii::$site[$x[0]][$x[1]][$x[2]][$x[3]],$d) : '';
				break;
			default:
				$r = isset(Yii::$site[$x[0]]) ? uh(Yii::$site[$x[0]],$d) : '';
				break;
		}
	}
	return $trim_all ? trim_all($r) : ($r);
}
function price_range(){
	switch (__LANG__){
		case 'vi_VN':
			return array(
			'default'=>'1000;1000',
			'from'=>1000,
			'to'=>20000,
			'scale_from'=>'1000k',
			'scale_to'=>'20000k',
			'step'=>200,
			'dimension'=>'k'
					);
			break;
		default:
			return array(
			'default'=>'100;100',
			'from'=>100,
			'to'=>10000,
			'scale_from'=>'$100',
			'scale_to'=>'$10000',
			'step'=>50,
			'dimension'=>'$'
		
					);
			break;
	}
}

function getBrowser()
{
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$bname = $ub = 'Unknown';
	$platform = 'Unknown';
	$version= "";

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	elseif (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}

	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	}
	elseif(preg_match('/Firefox/i',$u_agent))
	{
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	elseif(preg_match('/Chrome/i',$u_agent))
	{
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}
	elseif(preg_match('/Safari/i',$u_agent))
	{
		$bname = 'Apple Safari';
		$ub = "Safari";
	}
	elseif(preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Opera';
		$ub = "Opera";
	}
	elseif(preg_match('/Netscape/i',$u_agent))
	{
		$bname = 'Netscape';
		$ub = "Netscape";
	}

	// finally get the correct version number
	$known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .
	')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}

	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
			$version= $matches['version'][0];
		}
		else {
			$version= isset($matches['version'][1]) ? $matches['version'][1] : '';
		}
	}
	else {
		$version= $matches['version'][0];
	}

	// check if we have a number
	if ($version==null || $version=="") {$version="?";}
	$version = str_replace('.', '_',  $version);
	$pos = strpos($version,'_');
	$v = $pos !== false ? substr($version,0, $pos) : $version;
	return array(
			//'userAgent' => $u_agent,
			'name'      => strtolower($bname),
			'short_name'=> strtolower($ub),
			'browser' => strtolower($ub),
			'full_version'   => $version,
			'version'   => $v,
			'platform'  => $platform, // window - linux - ios - android
			'platform_version'  => $platform ,// win10, win8 ...,
			'device_type' => '', // Desktop or Mobile
			'device_pointing_method' => '', // Touch or mouse
			
			
			
	);
}
function get_file_type($file_src = ''){
	$pos = strrpos($file_src,'.',1);
	if($pos === false) return 'unknown';
	$file_ext = strtolower(substr($file_src,$pos+1));
	switch ($file_ext){
		case 'png':case 'ico':case 'bmp':case 'jpg':case 'jpeg':case 'tif':case 'gif':case 'tiff':case 'svg':case 'exif':
			$file_type = 'image';
			break;
		case 'flv':case 'f4v':case 'mp4':case 'mpg4':case 'mkv':case 'rmvb':case 'avi':case 'm4v':case 'mpeg':case 'mpg':
		case '3gp':case '3gp2':case 'amv':case 'wmv':case 'mov':case 'qt':case 'mng':case 'ogg':case 'ogv':case 'webm':
			$file_type = 'video';
			break;
		case 'aac':case 'amr':case 'flac':case 'gsm':case 'm4a':case 'm4b':case 'mp3':case 'mpc':case 'wma':case 'wam':
			$file_type = 'audio';
			break;
		default: $file_type = $file_ext; break;
	}
	return $file_type;
}
function parse_url_post($url=''){
	//validate_domain($domain)
	if(strpos($url, 'http') === 0){
		while (strlen($url) > 0 && substr($url	,-1) == '/'){
			$url = substr($url,0, -1);
		}
		$pos = strrpos($url,'/',1);
		$url = $pos === false ? $url : substr($url,$pos+1);
	}
	return unMark($url);
} 
function controller_non_privileges(){
	return array('ajax','systemAjax','chat','seo','robots.txt','sitemap.xml','tags');
}
function load_model($m = '',$c = false){
	if(strpos($m, '\\') !== false){
		return new $m(); 
	}
	else{
		$models = explode('_', $m);
		$model_name = '';
		foreach ($models as $m){
			$model_name .= strtoupper(substr($m, 0,1)) . strtolower(substr($m, 1));
		}
		switch (Yii::$app->controller->module->id){
			case 'app-frontend':
				$model_name = '\app\models\\'.$model_name;
				break;
			default:
				$model_name = '\app\modules\\'.(Yii::$app->controller->module->id)
				.'\models\\'.$model_name; 
				break;
		}
		
		return new $model_name;
	}
}

function filter_phone_number($number = ''){
	if(strpos($number, '+') === false){

	}else{
		$number = str_replace('+84', '', $number);
	}
	if(substr($number, 0,1) != 0){
		$number = '0' . $number;
	}
	$number = unMark($number,'');
	return $number;
}
function replace_text_form($con = array(), $subject = ''){
	$search = $replace = array();
	if(!empty($con)){
		foreach ($con as $k=>$v){
			$search[] = $k;
			$replace[] = $v;
		}
	}
	return str_replace($search, $replace, $subject);
}
function get_social($o = array()){
	return array(
			'facebook'=>array('name'=>'Facebook','hint_link'=>'facebook.com/username'),
			'twitter'=>array('name'=>'Twitter','hint_link'=>'twitter.com/username'),
			'gplus'=>array('name'=>'Google Plus','hint_link'=>'plus.google.com/username'),
			'instagram'=>array('name'=>'Instagram','hint_link'=>'instagram.com/username'),
			'pinterest'=>array('name'=>'Pinterest','hint_link'=>'pinterest.com/username'),
			'linkedin_user'=>array('name'=>'Linkedin','hint_link'=>'linkedin.com/in/username'),
			'linkedin_group'=>array('name'=>'Linkedin groups','hint_link'=>'linkedin.com/groups/groupname'),
			'linkedin_company'=>array('name'=>'Linkedin company','hint_link'=>'linkedin.com/company/companyname'),
			'linkedin_education'=>array('name'=>'Linkedin education','hint_link'=>'linkedin.com/edu/educationname'),
			'flickr'=>array('name'=>'Flickr','hint_link'=>'flickr.com/photos/username'),
			'youtube_user'=>array('name'=>'Youtube','hint_link'=>'youtube.com/user/username'),
			'youtube_channel'=>array('name'=>'Youtube channel','hint_link'=>'youtube.com/channel/channelname'),
			'youtube_custom'=>array('name'=>'Youtube custom','hint_link'=>'youtube.com/c/customname'),
			'tumblr'=>array('name'=>'Tumblr','hint_link'=>'username.tumblr.com'),
			'foursquare'=>array('name'=>'Foursquare','hint_link'=>'foursquare.com/username'),
			'blogspot'=>array('name'=>'Blogspot','hint_link'=>'blogname.blogspot.com/'),
			'blogger'=>array('name'=>'Blogger','hint_link'=>'blogger.com/profile/username'),
				
	);
}
function redirect($url = ''){
	header('HTTP/1.1 301 Moved Permanently');
	header("Location:". $url);
	exit;
}
function spc($level){

	$a = array(
			'',
			'&nbsp;&nbsp;&nbsp;+&nbsp;',
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;',
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;',
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;+&nbsp;',
	);
	if(is_numeric($level)) return $a[$level];
}
function demo_view_price($xday ='30/09/2016',$day = '17/10/2016'){
	//$day = date("d/m/Y");
	$day = ctime(array('string'=>$day,'format'=>'Y-m-d'));
	$xday = ctime(array('string'=>$xday,'format'=>'Y-m-d'));
	$tday = strtotime($day); $txday = strtotime($xday);
	$cyear = date('Y');
	$year = date('Y',strtotime($day));
	$month = date('m',strtotime($day));
	//
	$xyear = date('Y',strtotime($xday));
	$xmonth = date('m',strtotime($xday));
	//
	//view($xyear);
	$b01_01 = strtotime($year . '-01-01 00:00:00');
	$b30_09 = strtotime($year . '-09-30 23:59:59');
	$b31_12 = strtotime($year . '-12-31 23:59:59');
	echo "<p style=\"font-size: 30px;\">ngày Ä‘áº·t / SDDV: ".date("d/m/Y",strtotime($day))." - ".(date("d/m/Y",strtotime($xday)))."</p>";
	if(strtotime($day) > $b01_01 - 1 && strtotime($day) < $b30_09 +1
			&& strtotime($xday) > $b01_01 - 1 && strtotime($xday) < $b30_09 +1){
				echo "<p style=\"font-size: 30px;\">Báo giá ".($year-1)." - $year</p>";
	}elseif($tday > $b30_09 && $tday<$b31_12+1){
		echo "<p style=\"font-size: 30px;\">Báo giá ".($year)." - ".($year+1)." (1)</p>";
	}elseif ($xyear == $year +1 && $xmonth < 10 && $month > 9){
		echo "<p style=\"font-size: 30px;\">Báo giá ".($year)." - ".($year+1)." (2)</p>";
	}elseif($year == $xyear && $xmonth > 9 && $xmonth < 13) {
		echo "<p style=\"font-size: 30px;\">Báo giá ".($year-1)." - ".($year)." (3)</p>";
	}else{
		echo "<p style=\"font-size: 30px;\">Báo giá ".($year-1)." - $year  * % (phÃ­)</p>";
	}
}
function get_social_button($o=array()){
	$html = '<div class="btn-social-list">';
	$html .= '<div class="fb-like" data-href="'.URL_WITH_PATH.'" data-layout="button" data-action="like" data-size="small" data-show-faces="false" data-share="true"></div>';
	$html .= '<div class="fb-save" data-uri="'.URL_WITH_PATH.'" data-size="small"></div>';
	$html .= '<div class="g-plus" data-action="share" data-annotation="none" data-height="20" data-href="'.URL_WITH_PATH.'"></div>';
	/*$html .= '<a class="twitter-share-button"
	 href="https://twitter.com/share"
	 data-size="large"
	 data-url="https://dev.twitter.com/web/tweet-button"
	 data-via="twitterdev"
	 data-related="twitterapi,twitter"
	 data-hashtags="example,demo"
	 data-text="custom share text">
	 Tweet
	 </a>';
	 */

	$html .= '</div>';
	return $html;
}
function get_currency($id = 1,$o = array()){
	$a = Yii::$app->zii->getUserCurrency()['list']; 
	if(isset($o['return']) && $o['return'] == 'list'){
		return $a;
	}
	$name = isset($o['name']) ? $o['name'] : 'name';
	return $a[$id][$name];
}
function get_currency_select($o = array()){
	$name = isset($o['name']) ? $o['name'] : 'f[currency]';
	$select_tag = isset($o['select_tag']) && $o['select_tag'] == false ? false : true;
	$l = get_currency(0,array('return'=>'list'));
	$id = isset($o['id']) ? $o['id'] : 1;
	$html = $select_tag ? '<select class="form-control select2" data-search="hidden" name="'.$name.'" style="width:100%">' : '';
	foreach ($l as $k=>$v){
		$html .= '<option '.($v['id']==$id ? 'selected' : '').' value="'.$v['id'].'">'.$v['title'].'</option>';
	}
	$html .= $select_tag ? '</select>' : '';
	return $html;
}
function get_params($allow = [], $igrone = []){
	$get = Yii::$app->request->get();
	$r = [];
	if(!empty($get)){
		foreach ($get as $k=>$v){
			if(!empty($allow) && in_array($k, $allow) &&!in_array($k, $igrone) || (empty($allow) &&!in_array($k, $igrone))){
				$r[$k] = $v;	
			}			 
		}
	}
	return $r;
}
 
function buildUrl($data = [], $o = []){
	$igrones = isset($o['igrones']) ? $o['igrones'] : [];
	$params = isset($o['params']) ? $o['params'] : Yii::$app->request->get();
	if(!empty($igrones)){
		foreach ($igrones as $k){
			if(isset($params[$k])){
				unset($params[$k]);
			}
		}
	}
	
	if(!empty($data)){
		foreach (array_keys($data) as $k){
			if(isset($params[$k])){
				unset($params[$k]);
			}
		}
	}
	
	$controller = isset($o['controller']) ? $o['controller'] : CONTROLLER_TEXT;
	$action = isset($o['action']) ? $o['action'] : false;
	//view($o['regex']);
	$regex = isset($o['regex']) && $o['regex'] != null ? $o['regex']  : ($controller === false ? "/" : ($action === false ? "$controller/" : "$controller/$action"));
	
	if(URL_SUFFIX != ""){
		$regex = rtrim($regex,URL_SUFFIX);
	}
	//view($regex);
	
	return \yii\helpers\Url::to(([$regex]+array_merge($params,$data)));
}

function Ad_GetUrl($data = [], $o = []){
	$igrones = isset($o['igrones']) ? $o['igrones'] : [];	
	$params = isset($o['params']) ? $o['params'] : Yii::$app->request->get();	
	if(!empty($igrones)){
		foreach ($igrones as $k){
			if(isset($params[$k])){
				unset($params[$k]);				
			}
		}
	}
	
	if(!empty($data)){
		foreach (array_keys($data) as $k){
			if(isset($params[$k])){
				unset($params[$k]);
			}
		}
	}
	
	//$url = isset($o['url']) ? $o['url'] : \yii\helpers\Url::to([CONTROLLER_TEXT.DS]);
	$controller = isset($o['controller']) ? $o['controller'] : CONTROLLER_TEXT;
	$action = isset($o['action']) ? $o['action'] : Yii::$app->controller->action->id;
	
	$regex = $controller === false ? "/" : ($action === false ? "$controller/" : "$controller/$action"); 
	
	return \yii\helpers\Url::to(([$regex]+array_merge($params,$data)));
}

function afGetUrl($o = [],$igrone = []){
	$link ='?';
	$e = '';
	 
	if(!empty($o) && is_array($o)){
		foreach($o as $k=>$v){
			$e .= "&$k=$v";
			$igrone[] = $k;
		}
	}
	if(isset($_GET) && !empty($_GET)){
		foreach($_GET as $key=>$value){
			$value =  str_replace("'","\'",trim($value));
			if(strlen($value) > 0 && !in_array(strtolower($key),$igrone)){
				$link.=$key.'='.$value.'&';
			}
		}
	}
	if(substr($link,-1) == '&'){
		$link = substr_replace($link,'',strlen($link)-1,1);
	}
	$link .= $e ;
	if($link == '?') $link = '';
	return str_replace('?&','?',$link);
}






  
function btnClickReturn($btn = 1, $id = 0,$tab = "",$o = []){	
	$cid = is_numeric($id) ? ($id > 0 ? true : false) : ($id != "" ? true : false);	
	$edit_action = isset($o['edit_action']) ? $o['edit_action'] : 'edit';
	$add_action = isset($o['add_action']) ? $o['add_action'] : 'add';
	
	switch($btn){
		case 1:// Save temp
			//$href = createUrl(CONTROLLER_TEXT).($id > 0 ? '/edit'.afGetUrl(array('id'=>$id)) : '');
			if($cid){
				$href = cu([CONTROLLER_TEXT.'/'.$edit_action]) . ($cid ? afGetUrl(['id'=>$id]) : '');
			}else{
				$href = cu([CONTROLLER_TEXT.DS]) ;
			}
			break;
		case 2:// Add new
			$href = cu([CONTROLLER_TEXT.'/'.$add_action]).afGetUrl([],['id']) ;
			break;
		case 3: // Save and copy
			$href = cu([CONTROLLER_TEXT.'/'.$add_action]).($cid ? afGetUrl(array('id'=>$id)) : afGetUrl());
			break;
		case 4: // Save && Close
			$href = cu([CONTROLLER_TEXT.DS]).afGetUrl(['p'=>getParam('p',1)],array('id')) ; 
			break;
		case 6: // Save && confirm
			//$href = cu([CONTROLLER_TEXT.DS]).afGetUrl(['p'=>getParam('p',1)],array('id')) ; 
			//$href = cu([CONTROLLER_TEXT.'/'.$edit_action]) . ($cid ? afGetUrl(['id'=>$id]) : '');
			return;
			break;
	}
	
	header("Location:".$href . $tab);
}
 
function afShowThread($o=array(),$i = array()){
	$stt = isset($i['STT']) ? $i['STT'] : getTextTranslate(53,ADMIN_LANG);
	$act = isset($i['ACTION']) ? $i['ACTION'] : getTextTranslate(58,ADMIN_LANG);
	$ckc = isset($i['CHECK']) && $i['CHECK'] == false ? false : true;

	$t = '<thead class="bg-thread">
      <tr>';
	$t .= $ckc ? '<th class="center ccheckitem"> </th>' : '';
	$t .= $stt !== false ? '<th class="center cnumerlist">'.$stt.'</th>' : '';
	$igr = array('sort','field');
	if(!empty($o)){
		foreach($o as $k=>$v){
			if(!empty($v)){ 
			//if(!in_array($k, array('order'))){
			$field = isset($v['field']) ? $v['field'] : false;
			$order = isset($v['order']) ? $v['order'] : false;
			$fo = ''; $fe = '';
			if($order && $field != false){
				$fo = '<i onclick="window.location=\''.getUrl($igr).'sort=asc&field='.$field.'\';" data-href="'.getUrl($igr).'" title="up" class="ft-sort-up '.(getParam('sort') == 'asc' && getParam('field') == $field ? 'active' : '').' sort_icon sort_icon_asc"></i>';
				$fe = '<i onclick="window.location=\''.getUrl($igr).'sort=desc&field='.$field.'\';" title="down" class="ft-sort-down '.(getParam('sort') == 'desc' && getParam('field') == $field ? 'active' : '').' sort_icon sort_icon_desc"></i>';
			}
			$name = isset($v['name']) ? $v['name'] : '';
			$class = isset($v['class']) ? $v['class'] : '';
			$filter = isset($v['filter']) ? $v['filter'] : false;
			$filter_text = '';
			
			if(!empty($filter)){
				$filter_text .= '<i title="Lọc" class="pointer fa fa-filter tb-header-filter"></i>';
			}
			
			$t .= ' <th class="xheader '.$class.' pr">'.$fo.$name.$fe.$filter_text.'</th>';
			}
		}
	}
	$t .= $act  ? '<th class="center coption">'.$act.'</th>' : '';
	$t .= '</tr> </thead>';
	return $t;
}
function afShowPagination($o = array()){
	$p = isset($o['p']) && $o['p'] > 0 ? (int)$o['p'] : getParam('p',array('num'=>true,'min'=>1,'default'=>1));
	$limit = isset($o['limit']) && $o['limit'] > 0 ? (int)$o['limit'] : 30;
	$total_records = isset($o['total_records']) && $o['total_records'] > 0 ? $o['total_records'] : 0;
	$total_pages = isset($o['total_pages']) && $o['total_pages'] > 0 ? (int)$o['total_pages'] : 1;
	$select_option = isset($o['select_option']) && $o['select_option'] == false ? false : true;
	$check_all = isset($o['check_all']) && $o['check_all'] == false ? false : true;
	$check_all_state = isset($o['check_all_state']) && $o['check_all_state'] == false ? false : true;
	$label = isset($o['label']) ? $o['label'] : array();
	// Next 10 pages
	$limit_page = 10;
	$current_r_page = ceil($p/$limit_page); 
	$total_r_page = ceil($total_pages/$limit_page);
	$start =  $current_r_page * $limit_page - ($limit_page - 1);
	$end = $start + $limit_page;

	$end = $end > $start ? $end : $start + 1;
	$end = $end > $total_pages ? $total_pages  : $end;

	//
	$first = 1;	 
	$next = $current_r_page + $limit_page;
	$pc = $next%$limit_page > 0 ? $next%$limit_page : $limit_page;
	$next = $p + $limit_page;
	$next = $next < $total_pages ? $next : $total_pages;
	$prev = $p - $limit_page;
	$prev = $prev > 1 ? $prev : 1;
	$last = $total_pages;

	$ajax = isset($o['ajax']) && $o['ajax'] == true ? true : false;
	$action = isset($o['action'])  ? $o['action'] : 'delete';
	$table = isset($o['table'])  ? $o['table'] : false;
	$href = $ajax ? ADMIN_ADDRESS .'/ajax' :cu(CONTROLLER_TEXT).'' ;
	$btn = isset($o['btn']) ? $o['btn'] : [];
	//$link = afGetUrl(array('p'));
	///////////////////////////////////
	$t = '<div class="highlight fl100 clear">';
	if($select_option && $check_all){
		$t .= '<div class="width_select_option fl">
    <span class="selectallarrow">&nbsp;</span>
    
    <label class="" for="resultsForm_checkall">
<input '.(!$check_all_state ? 'disabled' : '').' id="resultsForm_checkall" type="checkbox" class="checkall_box uniform" title="'.(isset($label['choose_all']) ? $label['choose_all'] : getTextTranslate(59,ADMIN_LANG)).'"> 
'.(isset($label['choose_all']) ? $label['choose_all'] : getTextTranslate(59,ADMIN_LANG)).'</label>
    <i class="with_select">'.(isset($label['action_selected']) ? $label['action_selected'] : getTextTranslate(60,ADMIN_LANG)).': </i>
    <button data-type="multiple" class="mult_submit btn-checkAll-delete" type="button" name="submit_mult"'; 
		if(isset($btn['del']['attr']) && !empty($btn['del']['attr'])){
			foreach ($btn['del']['attr'] as $k=>$v)
			{
				$t .= " data-$k=\"$v\"";
			}
		}
    $t .= ' title="'.(isset($btn['del']['label']) ? $btn['del']['label'] : getTextTranslate(64,ADMIN_LANG)).'"  data-toggle="confirmation-popout" data-placement="top" data-btnOkClass="btn-primary btn-checkAll-delete">
    <span class="nowrap"><img src="'. __RSDIR__.'/images/dot.gif" title="Delete" alt="Delete" class="icon ic_b_drop delete_item"> '.(isset($label['delete']) ? $label['delete'] : getTextTranslate(49,ADMIN_LANG)).'</span></button>
	<button class="mult_submit" type="button" onclick="showModal(\'Thông báo\',\'Chức năng đang xây dựng.\');" name="submit_mult" value="export" title="'.(isset($label['export']) ? $label['export'] : getTextTranslate(61,ADMIN_LANG)).' file">
    <span class="nowrap"><img src="'. __RSDIR__.'/images/dot.gif" title="Export" alt="Export" class="icon ic_b_tblexport"> '.(isset($label['export']) ? $label['export'] : getTextTranslate(61,ADMIN_LANG)).'</span></button>

</div>';
	}
	$t .= '<nav class="fr"><ul class="pagination fr pagination-sm spagination">'; 
	if($p > 1){

		$t.='<li><a title="Quay lại trang '.$prev.'" href="'.afGetUrl(array('p'=>$prev)).'"><i class="fa fa-long-arrow-left"></i></a></li>';
	}

	for($k=$start;$k<$end+1;$k++){
		$t .= '<li><a title="Đi đến trang '.$p.'" class="'.($k==$p ? 'active':'').'" '.($k == $p ? '' : 'href="'.afGetUrl(array('p'=>$k)).'"').' >'.$k.'</a></li>';
	}

	if($p < $total_pages){
		$t .= '<li><a title="Đi đến trang '.$next.'" href="'.afGetUrl(array('p'=>$next)).'"><i class="fa fa-long-arrow-right"></i></a></li>';
	}

	$t .=   '</ul> 
    </nav>
    <span class="fr pagi-total-record">'.(isset($label['total_records']) ? $label['total_records'] : getTextTranslate(62,ADMIN_LANG)).': <b>'. number_format($total_records).'/'.number_format($total_pages).'</b> '.(isset($label['pages']) ? strtolower($label['pages']) : getTextTranslate(63,ADMIN_LANG)).' &nbsp;&nbsp; -|-&nbsp;&nbsp;</span>
</div>';


	return $t;
}
/*     form edit    */
function postCheck($name){
	return isset($_POST[$name]) && ($_POST[$name] == 'on' || $_POST[$name] == 1) ? 1 : 0;
}
function getCheckValue($val = 1){
	return $val == 'on' || $val == 1 ? 1 : 0;
}
function getCheckBox($o = array()){
	$name = isset($o['name']) ? $o['name'] : '';
	$label = isset($o['label']) ? $o['label'] : '';
	$glabel = isset($o['glabel']) ? $o['glabel'] : '';
	$value = isset($o['value']) ? $o['value'] : 0;
	$cvalue = isset($o['cvalue']) && $o['cvalue'] == true ? true : false;
	$checked = isset($o['checked']) ? $o['checked'] : -1;
	$type =  isset($o['type']) ? $o['type'] : 'default';
	$class = isset($o['class']) ? $o['class'] : 'input-checkbox-active-'.$type;
	$attr =  isset($o['attr']) ? $o['attr'] : false;
	$attrs =  isset($o['attrs']) ? $o['attrs'] : $attr;
	$col_lb = isset($o['col_lb']) ? $o['col_lb'] : 2;
	$col_right = 12 - $col_lb;
	$active_from_date =  isset($o['active_from_date']) ? $o['active_from_date'] : date("d/m/Y H:i");
	$active_to_date =  isset($o['active_to_date']) ? $o['active_to_date'] : '';
	$offset =  isset($o['offset']) ? $o['offset'] : 0;
	$m = false;
	$input = '';
	if(is_array($name) && !empty($name)){ 	
		$m = true;
	foreach($name as $i=>$n){
		if($n != ""){
			$input .= '<label style="margin-right:15px"><input '.($cvalue ? 'value="'.cbool($value[$i]).'"' : '').' type="checkbox" name="'.$n.'" '.(cbool($value[$i]) == 1 ? 'checked="checked"' : '').' ';
			if(!empty($attrs)){
				foreach($attrs as $k=>$v){
					if($k == 'class') $class .= ' ' . $v;
					$input .= $k .'="'.$v.'" ';
				}
			}
			$input .= 'class="'.$class.'" /> '.$label[$i].'</label>';
		}
	}
	}else{
		if($checked !== -1){
			$input .= '<input type="checkbox" '.($cvalue ? 'value="'.($value).'"' : '').' name="'.$name.'" '.(($checked === true || $checked === 1) ? 'checked="checked"' : '').' ';
		}else{
			$input .= '<input type="checkbox" '.($cvalue ? 'value="'.($value).'"' : '').' name="'.$name.'" '.(cbool($value) == 1 ? 'checked="checked"' : '').' ';
		}
		if(!empty($attrs)){
			foreach($attrs as $k=>$v){
				if($k == 'class') $class .= ' ' . $v;
				$input .= $k .'="'.$v.'" ';
			}
		}
		$input .= 'class="'.$class.'" />';
		$sinput = $input;
		$input = '<label>'.$input.' '.$label.'</label>';
	}
	switch($type){
		case 'singer':
			$t = '<div class="onoff-button-div">'. (isset($sinput) ? $sinput : $input ) .'</div>';
			break;
		case 'n01':
			$t = '<div class="form-group"><label class="col-sm-2 control-label ptop0">'.$glabel.'</label><div class="col-sm-offset-'.$offset.' col-sm-'.(10-$offset).'"><div class="checkbox '.($m ? 'multilple' : 'single').'">';
			$t .= '<div class="input-group group-mt5 group-sm30 group-vtop ">';
			$t.= '<span class="input-group-addon radius0" id="basic-addon-input01">Từ</span>';
			//$t.= $input;
			//$t .= '<span class="input-group-btn">TÃ¡Â»Â« ngÃƒÆ’Ã‚Â y</span>';
			//$t .= '<span class="input-group-addon" id="basic-addon1">@</span>
			$t .= '<input name="task[active_from_date]" value="'.$active_from_date.'" type="text" class="form-control datetimepicker2" placeholder="Từ" aria-describedby="basic-addon-input01">';
			//$t .= ''
			$t .= '<span class="input-group-addon radius0 border1010" id="basic-addon-input02">đến</span>';
			$t .= '<input name="task[active_to_date]" value="'.$active_to_date.'" type="text" class="form-control datetimepicker" placeholder="Để trống kích hoạt vô thời hạn" aria-describedby="basic-addon-input02">';
			//$t .= '<span class="input-group-btn">Ãƒâ€žÃ¯Â¿Â½ÃƒÂ¡Ã‚ÂºÃ‚Â¿n ngÃƒÆ’Ã‚Â y</span>';
			$t.='</div></div>';
			$t .= '</div></div>';

			break;
		case 'n02':
			$t = '<div class="form-group"><label class="col-sm-12 aleft control-label ptop0">'.$glabel.'</label>
          <div class="col-sm-12">
              <div class="checkbox '.($m ? 'multilple' : 'single').'">'.$input .'</div>
            </div>
        </div>';
			break;
		default:
			$t = '<div class="form-group"><label class="col-sm-'.($col_lb).' control-label ptop0">'.$glabel.'</label>
          <div class="col-sm-offset-'.$offset.' col-sm-'.($col_right-$offset).'">
              <div class="checkbox '.($m ? 'multilple' : 'single').'">'.$input .'</div>
            </div>
        </div>';
			break;
	}

	return $t;
}
function afGetEditLink($id = 0,$o = array()){
	$edit = isset($o['edit']) && $o['edit'] == false ? false : true;
	$ajax = isset($o['ajax']) && $o['ajax'] == true ? true : false;
	$action = isset($o['action'])  ? $o['action'] : 'edit';
	$params = isset($o['params'])  ? $o['params'] : [];
	//$html = $edit ? cu(CONTROLLER_TEXT).'/'.$action .'?' .http_build_query(array('id'=>$id)) : '';  
	if($edit){
		$_params = [CONTROLLER_TEXT . "/$action"]; 
		//$html = cu(CONTROLLER_TEXT).'/'.$action;
		
		if($id !== 0){
			//$html .= '?' .http_build_query(['id'=>$id]);
			$params['id'] = $id;
		}
		$_params += $params;
		$html = \yii\helpers\Url::to($_params);
	}else{
		return '';
	}
	return $html;
}
function get_link_edit($id = 0, $o = array()){
	$edit = isset($o['edit']) && $o['edit'] == false ? false : true;
	$param = isset($o['param']) ? $o['param'] : [];
	$action = isset($o['action']) ? $o['action'] : 'edit';
	$identity_field = isset($o['identity_field']) ? $o['identity_field'] : 'id';
	
	$igrone = array_merge(is_array($identity_field) ? $identity_field : [$identity_field] ,isset($o['igrone']) ? $o['igrone'] : []) ;
	
	
	$html = $edit ? cu([__RCONTROLLER__ .'/'.$action,$identity_field=>$id]+$param+get_params([],$igrone)) : '#'; 
	return $html;
}
function checkAuthByUser($authItem=[], $o = []){
	$post_by = isset($o['post_by']) ? $o['post_by'] : 0;
	$per1 = true;
	if($post_by>0){		
		
		$per1 = $post_by == Yii::$app->user->id ||  Yii::$app->user->can([ROOT_USER,ADMIN_USER,'form-'.getParam('type').'-edit-all']);
		 
	} 
	//var_dump(array_merge($authItem , [ROOT_USER,ADMIN_USER])); 
	return $per1 &&  Yii::$app->user->can(array_merge([ROOT_USER,ADMIN_USER],$authItem)) ? true : false;
}
function afShowListButton($id = 0 ,$o = array()){
	$edit = isset($o['edit']) && $o['edit'] == false ? false : true;
	$role = isset($o['role']) ? $o['role'] : '';
	$del = isset($o['del']) && $o['del'] == false ? false : true;
	$ajax = isset($o['ajax']) && $o['ajax'] == true ? true : false;
	$setting = isset($o['setting']) && $o['setting'] == true ? true : false;
	$label = isset($o['label']) ? $o['label'] : array();
	$btn = isset($o['btn']) ? $o['btn'] : [];
	$post_by = isset($o['post_by']) ? $o['post_by'] : 0;
	$identity_field = isset($o['identity_field']) ? $o['identity_field'] : 'id';
	$tab_click = isset($o['tab_click']) ? $o['tab_click'] : '';
	//
	if($edit !== false){
		switch (Yii::$app->controller->id){
			case 'menu':
			case 'content':
				
				//$per1 = Yii::$app->user->can([ROOT_USER,ADMIN_USER,Yii::$app->controller->id . '-' . (defined('CONTROLLER_CODE') && CONTROLLER_CODE != "" ? CONTROLLER_CODE . '-' : '') .'edit','site-'.$id.'-edit']); 
				$per2 = Yii::$app->user->can([ROOT_USER,ADMIN_USER,'form-'.getParam('type').'-edit']);
				$per3 = true;
				if($post_by>0){
					
					$per3 = $post_by == Yii::$app->user->id ||  Yii::$app->user->can([ROOT_USER,ADMIN_USER,'form-'.getParam('type').'-edit-all']);
					//var_dump($id . $per3);
				}
				
				$edit = $per2 && $per3 ? true : false;				 
				break;
			default:
				$edit = Yii::$app->user->can([ROOT_USER,ADMIN_USER,Yii::$app->controller->id . '-' . (defined('CONTROLLER_CODE') && CONTROLLER_CODE != "" ? CONTROLLER_CODE . '-' : '') .'edit']);
				break;
				
		}
	}
	//view($btn);
	//$edit = $edit === false ? false : Yii::$app->user->can([ROOT_USER,ADMIN_USER,Yii::$app->controller->id . '-' . (defined('CONTROLLER_CODE') && CONTROLLER_CODE != "" ? CONTROLLER_CODE . '-' : '') .'edit','site-'.$id.'-edit']); 
	$del = $del === false ? false : Yii::$app->user->can([ROOT_USER,ADMIN_USER,Yii::$app->controller->id . '-' . (defined('CONTROLLER_CODE') && CONTROLLER_CODE != "" ? CONTROLLER_CODE . '-' : '') .'delete','site-'.$id.'-delete','form-'.getParam('type').'-delete']); 
	
	$edit_action = isset($o['btn']['edit']['action']) ? $o['btn']['edit']['action'] : 'edit';
	
	$edit_href = isset($o['btn']['edit']['href']) ? 
	$o['btn']['edit']['href'] : cu([__RCONTROLLER__.DS.$edit_action,$identity_field=>$id]+get_params([],[$identity_field])).$tab_click;
	$setting_href = isset($o['btn']['setting']['href']) ?
	$o['btn']['setting']['href'] : cu([__RCONTROLLER__.DS.'setting',$identity_field=>$id]+get_params([],[$identity_field])).$tab_click;
	//
	$html = '';
	if($setting){
		$html .= '<a '.($edit ? '' : 'disabled').' href="'.($setting ? $setting_href: '#').'"';
		if(isset($btn['setting']['attr']) && !empty($btn['setting']['attr'])){
			foreach ($btn['setting']['attr'] as $k=>$v)
			{
				$html .= " data-$k=\"$v\"";
			}
		}
		if(isset($btn['setting']['event']) && !empty($btn['setting']['event'])){
			foreach ($btn['setting']['event'] as $k=>$v)
			{
				$html .= " $k=\"$v\"";
			}
		}
		$html .=  ' class="btn btn-link '.(isset($btn['setting']['class']) ? $btn['setting']['class'] : 'edit_item').'"><i class="fa fa-cogs"></i> '.(isset($btn['setting']['label']) ? $btn['setting']['label'] : 'Cài đặt').'</a>';
		
	}
	
	$html .= '<a '.($edit ? '' : 'disabled').' href="'.($edit ? $edit_href : '#').'"';
	if(isset($btn['edit']['attr']) && !empty($btn['edit']['attr'])){
		foreach ($btn['edit']['attr'] as $k=>$v)
		{
			$html .= " data-$k=\"$v\"";
		}
	}
	if(isset($btn['edit']['event']) && !empty($btn['edit']['event'])){
		foreach ($btn['edit']['event'] as $k=>$v)
		{
			$html .= " $k=\"$v\"";
		}
	}
	$html .=  ' class="btn btn-sm btn-link btn-index-item-action hover-red '.(isset($btn['edit']['class']) ? $btn['edit']['class'] : '').'">'.(isset($btn['edit']['icon_class']) ? '<i class="'.$btn['edit']['icon_class'].'"></i>' : '<i class="fa fa-pencil"></i>').(isset($btn['edit']['label']) ? $btn['edit']['label'] : getTextTranslate(48,ADMIN_LANG)).'</a>';
	$html .= '<a '.($del ? 'data-toggle="confirmation-popout"' : '').' href="#" '.($del ? '' : 'disabled').' 
class="btn btn-sm btn-link btn-index-item-action text-danger hover-red '.(isset($btn['del']['class']) ? $btn['del']['class'] : '').'"';
	if(isset($btn['del']['attr']) && !empty($btn['del']['attr'])){
		foreach ($btn['del']['attr'] as $k=>$v)
		{
			$html .= " data-$k=\"$v\" ";
		}
	}
	$html .= ' data-title="'.(isset($btn['del']['title']) ? $btn['del']['title'] : getTextTranslate(50,ADMIN_LANG)).'" data-placement="left" data-btnOkClass="btn-primary">'.(isset($btn['del']['icon_class']) ? '<i class="'.$btn['del']['icon_class'].'"></i>' : '<i class="fa fa-trash"></i>').(isset($btn['del']['label']) ? $btn['del']['label'] : getTextTranslate(49,ADMIN_LANG)).'</a>';
	return $html;
}
function escape($str = null){

	return $str;
}
function showNotfoundItem(){
	echo '<p style="font-size:20px;margin:50px">Không tìm thấy dữ liệu. Vui lòng liên hệ administrator.<br/><i class="pointer btn-link" onclick="goBack();">Quay láº¡i</i></p>';
	echo '<script>jQuery(\'.list-btn\').remove();</script>';
}
function eString($string = null){
	$salt = randString(4);
	return base64_encode($salt.base64_encode($salt . $string));

}
function dString($string = null){
	return substr(base64_decode(substr(base64_decode($string),4)),4);

}
function showJqueryAttr($a = [],$array = false){
	$r = $array ? [] : '';
	if(is_array($a) && !empty($a)){
		foreach ($a as $k=>$v){
			if($array) $r["data-$k"] = $v;
			else $r .= "data-$k=\"$v\" ";
		}
	}
	return $r;
}
function parsePost(){
	$post = Yii::$app->request->post();
	if(isset($post['checkbox']) && !empty($post['checkbox'])){
		//foreach ($post)
	}
}

function db($sql=false){
	return $sql === false ? Yii::$app->db->createCommand() : Yii::$app->db->createCommand($sql);
}
function Ad_list_show_check($v=[],$o=[]){
	$identity_field = isset($o['identity_field']) ? $o['identity_field'] : 'id';
	$identity_value = isset($o['identity_value']) ? $o['identity_value'] : $v[$identity_field];
	$disabled = isset($o['disabled']) ? $o['disabled'] : false;
	//$disabled = $disabled === false ? false : Yii::$app->user->can([ROOT_USER,ADMIN_USER,Yii::$app->controller->id . '-' . (defined('CONTROLLER_CODE') && CONTROLLER_CODE != "" ? CONTROLLER_CODE . '-' : '') .'delete','site-'.$id.'-delete','form-'.getParam('type').'-delete']);
	$html = '<td class="center"><input '.($disabled ? 'disabled' : '').' type="checkbox" class="checked_item uniform" name="check_item[]" value="'.$identity_value.'" /></td>';
	return $html;
}
function Ad_list_show_icon($v = []){
	$html = '<td>';
	$html .= isset($v['icon']) ? '<a href="#" rel="popover" tabindex="0" class="center block" role="button"  data-img="'.$v['icon'].'" data-trigger="focus" title="Xem ảnh lớn" 
        data-content="">'
        .getImage(array(
                'src'=>$v['icon'],
                'h'=>30,'w'=>30,
                'attr'=>array(
                    'class'=>'img-rounded img-small-icon',
                    'alt'=>'' ,
                    'data-holder-rendered'=>"true"  ,
                 ),
              )).
        '</a>' : '';
        
    $html .= '</td>';
	return $html;                
}
function Ad_list_show_link_field($v=[],$o=[]){
	$after = isset($o['after']) ? $o['after'] : '';
	$target = isset($o['target']) ? $o['target'] : '_self';
	$html = '<td class=" pr"><a target="'.$target.'" href="'.$o['link'].'">'.(isset($v[$o['field']]) ? uh($v[$o['field']]) : '').$after.'</a></td>';
	return $html;
}
function Ad_list_show_plain_text_field($v=[],$o=[]){
	 
	$td_class = isset($o['td_class']) ? $o['td_class'] : 'center';
	$html = '<td class="'.$td_class.' pr"><p class="mgb0">'.(isset($o['field']) && isset($v[$o['field']]) ? uh($v[$o['field']]) :
			(is_array($v) ? implode(' | ', $v) : $v)).'</p></td>';
	return $html;
}
function Ad_list_show_qtext_field($v=[],$o=[]){	
	$role = isset($o['role']) ? $o['role'] : [];
	$field = isset($o['field']) ? $o['field'] : '';
	$v[$field] = isset($o['value']) ? $o['value'] : (isset($v[$field]) ? $v[$field] : '');
	$class = isset($o['class']) ? $o['class'] : '';
	$decimal = isset($o['decimal']) ? $o['decimal'] : false;
	$disabled = isset($o['disabled']) ? $o['disabled'] : false;
	$readonly = isset($o['readonly']) ? $o['readonly'] : false;
	
	$html = '<td class="center pr">'.($disabled ? '<span>'.(is_numeric($v[$field]) ? number_format($v[$field],is_numeric($decimal) ? $decimal : 0) : $v[$field]).'</span>' : 
			'<input '.($disabled ? 'disabled' : '').' '.($readonly ? 'readonly' : '').' '.showJqueryAttr($role).' data-field="'.$field.'" onblur="Ad_quick_change_item(this);" ondblclick="open_edit_mode(this);" title="Click đúp để bật chế độ sửa nhanh (nếu ô text đã bị lock)" class="w100 center sui-input sui-input-focus '.$class.'" data-old="'.$v[$field].'" '.(is_numeric($decimal) ? 'data-decimal="'.$decimal.'"' : '').' value="'.$v[$field].'"/>').'</td>';
	return $html;
}
function Ad_list_show_checkbox_field($v=[],$o=[]){
	$role = isset($o['role']) ? $o['role'] : [];
	$field = isset($o['field']) ? $o['field'] : '';
	$class = isset($o['class']) ? $o['class'] : '';
	$value = isset($o['value']) ? $o['value'] : (isset($v[$field]) ? $v[$field] : 0);
	$decimal = isset($o['decimal']) ? $o['decimal'] : false;
	$action = isset($o['action']) ? $o['action'] : false;
	$onchange = isset($o['onchange']) ? $o['onchange'] : 'Ad_quick_change_item(this);';
	if($action !== false) $role['action'] = $action;
	
	$table = isset($o['table']) ? $o['table'] : false;
	if($table !== false) $role['table'] = $table;
	$disabled = isset($o['disabled']) ? $o['disabled'] : false;
	$html = '<td class="center pr">'.($disabled ? '<span>' . ($value == 1 ? 'ON' : 'OFF') . '</span>' :getCheckBox(array(
            'name'=>$field,
            'value'=>$value,
            'type'=>'singer',
            'class'=>'switchBtn ',
            'attr'=>showJqueryAttr($role,true)+array(
            		
            	'data-old'=>isset($v[$field]) ? $v[$field] : '',
                'data-boolean'=>1,
                'data-field'=>$field,
                'onchange'=>$onchange                                
            ),
        ))).'</td>';
	return $html;
}

function Ad_list_show_radio_field($v=[],$o=[]){
	$role = isset($o['role']) ? $o['role'] : [];
	$field = isset($o['field']) ? $o['field'] : '';
	$class = isset($o['class']) ? $o['class'] : '';
	$value = isset($o['value']) ? $o['value'] : (isset($v[$field]) ? $v[$field] : 0);
	$decimal = isset($o['decimal']) ? $o['decimal'] : false;
	$action = isset($o['action']) ? $o['action'] : false;
	$field_name = isset($o['field_name']) ? $o['field_name'] : ($field != "" ? 'f['.$field.']' : '');
	$attrs = isset($o['attrs']) ? $o['attrs'] : [];
	
	$onchange = isset($o['onchange']) ? $o['onchange'] : 'Ad_quick_change_item(this);';
	if($action !== false) $role['action'] = $action;
	
	$table = isset($o['table']) ? $o['table'] : false;
	if($table !== false) $role['table'] = $table;
	$disabled = isset($o['disabled']) ? $o['disabled'] : false;
	$html = '<td class="center pr"><input type="radio" '.($value == 1 ? 'checked' : '').' name="'.$field_name.'" value="'.$value.'" ';
	if(!empty($attrs)){
		foreach ($attrs as $key=>$value){
			$html .= $key . '="'.$value.'" ';
		}
	}
	$html .= '/>
</td>';
	return $html;
}

function Ad_list_show_option_field($v=[],$o=[]){
	$role = isset($o['role']) ? $o['role'] : [];
	//view($role);
	$action = isset($o['action']) ? $o['action'] : false;
	//view($action);
	if($action !== false) $role['action'] = $action;
	$table = isset($o['table']) ? $o['table'] : false;
	if($table !== false) $role['table'] = $table;
	$field = isset($o['field']) ? $o['field'] : '';
	$class = isset($o['class']) ? $o['class'] : '';
	$value = isset($o['value']) ? $o['value'] : (isset($v[$field]) ? $v[$field] : 0);
	$decimal = isset($o['decimal']) ? $o['decimal'] : false;
	$post_by = isset($o['post_by']) ? $o['post_by'] : 0;
	$check_permision = isset($o['check_permision']) ? $o['check_permision'] : false;
	$edit = (isset($o['edit']) && $o['edit'] == false) || (isset($o['btn']['edit']) && $o['btn']['edit'] == false) ? false : true;
	$del = (isset($o['del']) && $o['del'] == false) || (isset($o['btn']['del']) && $o['btn']['del'] == false) ? false : true;
	$setting = (isset($o['setting']) && $o['setting'] == true) || (isset($o['btn']['setting']) && $o['btn']['setting'] == true) ? true : false;
	$tab_click = isset($o['tab_click']) ? $o['tab_click'] : '';
	
	$identity_field = isset($o['identity_field']) ? $o['identity_field'] : 'id';
	$identity_value = isset($o['identity_value']) ? $o['identity_value'] : (isset($v[$identity_field]) ? $v[$identity_field] : $v['id']);
	
	
	
	$html = '<td class="center pr">'.afShowListButton($identity_value,
        array(
        		'identity_field'=>$identity_field,
        		'edit'=>$edit,
        		'del'=>$del,
        		'setting'=>$setting,
        		'post_by'=>$post_by,
        		'tab_click'=>$tab_click,
        'btn'=>[
        	'edit'=>isset($o['btn']['edit']) ? $o['btn']['edit'] : [],
        	'del'=>['attr'=>$role]
        ]
            		)).'</td>';
	return $html;
}

function checkAvailableEditField($crkey, $field){
	if(isset(Yii::$site['settings'][$crkey]['fields'][$field]) && Yii::$site['settings'][$crkey]['fields'][$field] == 'on'){
		return true;
	}else{
		return false;
	}
}


function Ad_edit_show_text_field($v=[],$o=[]){
	//
	$field = isset($o['field']) ? $o['field'] : false;
	$crkey = isset($o['crkey']) ? $o['crkey'] : false;
	$group = isset($o['group']) ? $o['group'] : false;
	if(!($crkey === true) && $crkey !== false){
		if(isset(Yii::$site['settings'][$crkey]['fields'][$field]) && Yii::$site['settings'][$crkey]['fields'][$field] == 'on'){
			
		}else{
			return '';
		}
	}
	//
	$label = isset($o['label']) ? $o['label'] : '';
	
	$field_name = isset($o['field_name']) ? $o['field_name'] : "f[$field]";
	$class = isset($o['class']) ? $o['class'] : '';
	$title = isset($o['title']) ? $o['title'] : '';
	$input = isset($o['input']) ? $o['input'] : 'input';
	
	$value = isset($o['value']) ? $o['value'] : (isset($v[$field]) ? $v[$field] : (isset($o['default_value']) ? $o['default_value'] : ''));
	
	//$value = str_replace('"', '&quot;', $value); 
	$value = yii\helpers\Html::encode(uh($value)); 
	//view(yii\helpers\Html::encode($value));
	$group_class = isset($o['group_class']) ? $o['group_class'] : 'form-group';
	$placeholder = isset($o['placeholder']) ? $o['placeholder'] : $title;
	$attrs = isset($o['attrs']) ? $o['attrs'] : []; 
	$input_type = isset($o['input_type']) ? $o['input_type'] : 'text';
	$required = isset($o['required']) && $o['required'] ? true : false;
	
	$html= '<div class="'.$group_class.'"><label class="col-sm-12 control-label aleft">'.$label .($required ? ' <i class="red font-normal">(*)</i>' : '').'</label><div class="col-sm-12">';
	if($group){
		$html .= '<div class="input-group group-sm34">';  
	}
	$html .= '<'.$input;
	if(!empty($attrs)){
		foreach ($attrs as $k=>$v){
			$html .= " $k=\"$v\"";
		}
	}
	//
	$group_attrs = isset($o['group_attrs']) ? $o['group_attrs'] : []; 
	$rand = randString(8);
	if(isset($o['disabled']) && $o['disabled'] == 'on'){
		$html .= ' disabled';
	}
	//
	$html .= ' type="'.$input_type.'" name="'.$field_name.'" class="form-control '.$class.' '.$rand.'" placeholder="'.$placeholder.'" '.($input == 'input' ? 'value="'.($value).'" /' : "").'>'.($input == 'input' ? '' : ($value)."</$input>");
	if($group){
		$html .= '<span data-toggle="tooltip" title="'.(isset($group_attrs['title']) ? $group_attrs['title'] : '').'" class="input-group-addon "><label class="mgb0"> <input name="'.(isset($group_attrs['name']) ? $group_attrs['name'] : '').'" '.(isset($group_attrs['checked']) && $group_attrs['checked'] == 'on' ? 'checked' : '').' data-function="reverse" onchange="enabledInput(this);" data-target=".'.$rand.'" type="checkbox" aria-label="'.(isset($group_attrs['label']) ? $group_attrs['label'] : '').'">
      '.(isset($group_attrs['label']) ? $group_attrs['label'] : '').'</label>
      </span>';
	}
	if($group){
		$html.= '</div>';
	}
	$html .= '</div>';
	
	$html .= '</div>';
	
	return $html;
}


function Ad_edit_show_text_field_group($v=[],$o=[]){
	$html = '<div class="form-group">';
	//$group_crkey = isset($o['group_crkey']) ? $o['group_crkey'] : false;
	$c = 0;
	foreach ($o as $field => $f){
		
		$crkey = isset($f['crkey']) ? $f['crkey'] : false;
		//view($crkey);
		if(($crkey === true) || ($crkey !== false 
				&& isset(Yii::$site['settings'][$crkey]['fields'][$field]) 
				&& Yii::$site['settings'][$crkey]['fields'][$field] == 'on')){
			$c++;		
		}
	}
	//view($c);
	if($c==0) return '';
	
	$csm = (int)(12 / $c);
	
	foreach ($o as $fields){
		$html .= '<div class="col-sm-'.$csm.' col-xxs-6 col-xxxs-12">';
		$html .= Ad_edit_show_text_field($v, $fields);
		$html .= '</div>';
	}
	
	$html .= '</div>';
	
	return $html;
}

function Ad_edit_show_select_field($v=[],$o=[]){
	$label = isset($o['label']) ? $o['label'] : '';
	$field = isset($o['field']) ? $o['field'] : false;
	$field_name = isset($o['field_name']) ? $o['field_name'] : "f[$field]";
	$class = isset($o['class']) ? $o['class'] : '';
	$title = isset($o['title']) ? $o['title'] : '';
	$multiple = isset($o['multiple']) ? $o['multiple'] : false;
	$default_value = isset($o['default_value']) ? $o['default_value'] : false;
	$data = isset($o['data']) ? $o['data'] : [];
	$disabled = isset($o['data-disabled']) ? $o['data-disabled'] : [];
	$selected = isset($o['data-selected']) ? $o['data-selected'] : [];
	$option_value = isset($o['option-value-field']) ? $o['option-value-field'] : 'id';
	$option_title = isset($o['option-title-field']) ? $o['option-title-field'] : 'title';
	$attrs = isset($o['attrs']) ? $o['attrs'] : [];
	$html= '<div class="form-group group-sm34"><label class="col-sm-12 control-label">'.$label.'</label><div class="col-sm-12"><select';
	if(!empty($attrs)){
		foreach ($attrs as $k=>$v){
			$html .= " $k=\"$v\"";
		}
	}
	$html .= ' data-type="select" data-select="select2" name="'.$field_name.'" class="form-control input-sm '.$class.'" style="width: 100%" '.($multiple ? 'multiple="multiple"' : '').'>'; 	
    if(!empty($data)){
    	$html .= $default_value !== false ? '<option value="'.$default_value.'"> -- </option>' : '';  	
    	foreach($data as $k1=>$v1){                
    		$html .= '<option '.(isset($v1[$option_value]) && in_array($v1[$option_value],$disabled) ? 'disabled="disabled"' : '').' '.(isset($v1[$option_value]) && in_array($v1[$option_value] ,$selected) ? 'selected="selected"' : '').' value="'.(isset($v1[$option_value]) ? $v1[$option_value] : '').'">'.(isset($v1['level']) ? spc($v1['level']) : '').(isset($v1[$option_title]) ? uh($v1[$option_title]) : '').'</option> ';
    	}
    }    
    $html .= '</select></div></div>';
	return $html;
}

function Ad_edit_show_select_field_group($v=[],$o=[]){
	$label = isset($o['label']) ? $o['label'] : '';
	$field = isset($o['field']) ? $o['field'] : false;
	$field_name = isset($o['field_name']) ? $o['field_name'] : "f[$field]";
	$class = isset($o['class']) ? $o['class'] : '';
	$title = isset($o['title']) ? $o['title'] : '';
	$multiple = isset($o['multiple']) ? $o['multiple'] : false;
	$default_value = isset($o['default_value']) ? $o['default_value'] : false;
	$data = isset($o['data']) ? $o['data'] : [];
	$disabled = isset($o['data-disabled']) ? $o['data-disabled'] : [];
	$selected = isset($o['data-selected']) ? $o['data-selected'] : [];
	$option_value = isset($o['option-value-field']) ? $o['option-value-field'] : 'id';
	$option_title = isset($o['option-title-field']) ? $o['option-title-field'] : 'title';
	$attrs = isset($o['attrs']) ? $o['attrs'] : [];
	$html= '<div class="form-group group-sm34"><label class="col-sm-12 control-label">'.$label.'</label>
			
			<div class="col-sm-12"><div class="input-group"><select';
	if(!empty($attrs)){
		foreach ($attrs as $k=>$v){
			$html .= " $k=\"$v\"";
		}
	}
	$html .= ' data-type="select" data-select="select2" name="'.$field_name.'" class="form-control input-sm '.$class.'" style="width: 100%" '.($multiple ? 'multiple="multiple"' : '').'>';
	if(!empty($data)){
		$html .= $default_value !== false ? '<option value="'.$default_value.'"> -- </option>' : '';
		foreach($data as $k1=>$v1){
			$html .= '<option '.(isset($v1[$option_value]) && in_array($v1[$option_value],$disabled) ? 'disabled="disabled"' : '').' '.(isset($v1[$option_value]) && in_array($v1[$option_value] ,$selected) ? 'selected="selected"' : '').' value="'.(isset($v1[$option_value]) ? $v1[$option_value] : '').'">'.(isset($v1['level']) ? spc($v1['level']) : '').(isset($v1[$option_title]) ? uh($v1[$option_title]) : '').'</option> ';
		}
	}
	$html .= '</select><span class="input-group-btn"><button ';
    $class = isset($o['groups']['class']) ? uh($o['groups']['class']) : '';
    if(isset($o['groups']['attrs']) && !empty($o['groups']['attrs'])){
    	foreach ($o['groups']['attrs'] as $k=>$v){
    		if($k == 'class'){
    			$class .= ' '.$v;
    		}else {
    			$html .= $k . '="'.$v .'" ';
    		}
    	}
    }
    $html .= 'class="btn btn-default '.$class.'" type="button">'.(isset($o['groups']['label']) ? uh($o['groups']['label']) : '').'</button></span></div></div></div>';
	return $html;
}
function Ad_edit_show_image_field($v=[],$o=[]){
	$label = isset($o['label']) ? $o['label'] : '';
	$field = isset($o['field']) ? $o['field'] : false;
	$field_name = isset($o['field_name']) ? $o['field_name'] : "f[$field]";
	$class = isset($o['class']) ? $o['class'] : '';
	$title = isset($o['title']) ? $o['title'] : '';
	$multiple = isset($o['multiple']) ? $o['multiple'] : false;
	$data = isset($o['data']) ? $o['data'] : [];
	$disabled = isset($o['data-disabled']) ? $o['data-disabled'] : [];
	$selected = isset($o['data-selected']) ? $o['data-selected'] : [];
	$option_value = isset($o['option-value-field']) ? $o['option-value-field'] : 'id';
	$placeholder = isset($o['placeholder']) ? $o['placeholder'] : '';
 
	$html= '<div class="form-group"><label class="col-sm-12 control-label">'.$label.'</label><div class="col-sm-12">';
    if(isset($v[$field]) && $v[$field] != ""){
    	$html .= '<p style="margin-bottom:5px;">'.getImage([
                'src'=>$v[$field],
                'h'=>100,
                'attr'=>[
                    'class'=>'img-thumbnail img-icon',
                    'alt'=>'' ,
                    'data-holder-rendered'=>"true"
                 ],
              ]).'</p>';
    }
    $html .= '<input type="text" placeholder="'.$placeholder.'" class="form-control input-sm '.$class.'" name="old_'.$field_name.'" value="'.(isset($v[$field]) ? $v[$field] : '').'" />
            <input type="file" id="inputicon" name="'.$field_name.'" class="input-file f12e" accept="image/*" />
            <p class="help-block"></p></div></div>';
	return $html;
}

function Ad_edit_show_check_field($o = []){
	$label = isset($o['label']) ? $o['label'] : '';
	$field = isset($o['field']) ? $o['field'] : false;
	$field_name = isset($o['field_name']) ? $o['field_name'] : (!is_array($field) ? "f[$field]" : '' );
	$value = isset($o['value']) ? $o['value'] : 0;
	$class = isset($o['class']) ? $o['class'] : 'checkbox multilple';
	$title = isset($o['title']) ? $o['title'] : '';
	$multiple = isset($o['multiple']) ? $o['multiple'] : false;
	$type = isset($o['type']) ? $o['type'] : false;
	$data = isset($o['data']) ? $o['data'] : [];
	$disabled = isset($o['data-disabled']) ? $o['data-disabled'] : [];
	$selected = isset($o['data-selected']) ? $o['data-selected'] : [];
	$option_value = isset($o['option-value-field']) ? $o['option-value-field'] : 'id';
	$placeholder = isset($o['placeholder']) ? $o['placeholder'] : '';
	$boolean = isset($o['boolean']) ? $o['boolean'] : false;
	
	$html = '<div class="form-group"><label class="col-sm-12 aleft control-label ">'.$label.'</label>
<div class="col-sm-12"><div class="'.$class.'">';
	switch ($type){
		case 'time':
			$active_from_date = isset($o['active_from_date']) ? $o['active_from_date'] : '';
			$active_to_date = isset($o['active_to_date']) ? $o['active_to_date'] : '';
			if(check_date_string($active_to_date) && !check_date_string($active_from_date)){
				$active_from_date = ctime(['string'=>$active_to_date,'return_type'=>1]) > time() ? date("d/m/Y H:i:s") : ctime(['string'=>$active_to_date,'format'=>'d/m/Y H:i:s']);
			}
			
			$active_from_date_field = isset($o['active_from_date_field']) ? $o['active_from_date_field'] : 'task[active_from_date]';
			$active_to_date_field = isset($o['active_to_date_field']) ? $o['active_to_date_field'] : 'task[active_to_date]';
			$value = check_date_string($active_from_date) ? -1 : $value;
			$html .= '<div class="col-sm-2 col-xs-4 group-sm34"><div class="row"><select onchange="Ad_change_select_active(this)" name="'.$field_name.'" class="select2 form-control " data-search="hidden">					
					<option '.($value == 0 ? 'selected' : '').' value="0">Tắt</option>
					<option '.($value == 1 ? 'selected' : '').' value="1">Bật</option>
					<option '.($value == -1 ? 'selected' : '').' value="-1">Tùy chỉnh</option> 
					</select></div></div>'; 
			$html .= '<div class="col-sm-10 col-xs-8 mgl-1"><div class="row">
					<div class="input-group group-sm34 group-vtop group-sm">
					<span class="input-group-addon radius0" id="basic-addon-input01">Từ</span>
					<input '.($value == -1 ? '' : 'disabled').' name="'.$active_from_date_field.'" value="'.$active_from_date.'" type="text" class="from-date form-control datetimepicker2" data-time="1" placeholder="Kích hoạt từ" aria-describedby="basic-addon-input01"><span class="input-group-addon radius0 border1010" id="basic-addon-input02">đến</span>
					<input '.($value == -1 ? '' : 'disabled').' name="'.$active_to_date_field.'" value="'.$active_to_date.'" type="text" class="to-date form-control datetimepicker2" data-time="1" placeholder="Để trống nếu chỉ kích hoạt 1 lần" aria-describedby="basic-addon-input02"></div>
					</div></div>';
			break;
		default:
			if(!empty($field)){
				foreach ($field as $k=>$v){
					$fclass = isset($v['class']) ? $v['class'] : '';				
					$html .= '<label style="margin-right:15px"><input ';
					if(isset($v['attrs']) && !empty($v['attrs'])){
						foreach($v['attrs'] as $k1=>$v1){
							$html .= "$k1=\"$v1\" ";
						}
					}
			
					$html .= 'type="checkbox" name="'.$k.'" '.((isset($v['checked']) && $v['checked'] == true) || (isset($v['boolean']) && $v['boolean'] && cbool($v['value']) == 1) ? 'checked="checked"' : '' ).'
class="input-checkbox-active-n02 '.$fclass.'"/> '.(isset($v['label']) ? $v['label'] : '').'</label>';
				}
			}
			break;
	}
	$html .= '</div></div></div>';	
	return $html;
}
function Ad_edit_show_date_field($o = []){
	$label = isset($o['label']) ? $o['label'] : '';
	$field = isset($o['field']) ? $o['field'] : false;
	$field_name = isset($o['field_name']) ? $o['field_name'] : (!is_array($field) ? "f[$field]" : '' );
	$checked = isset($o['checked']) ? $o['checked'] : false;
	$class = isset($o['class']) ? $o['class'] : '';
	$value = isset($o['value']) ? $o['value'] : '';
	$title = isset($o['title']) ? $o['title'] : '';
	$multiple = isset($o['multiple']) ? $o['multiple'] : false;
	$data = isset($o['data']) ? $o['data'] : [];
	$disabled = isset($o['data-disabled']) ? $o['data-disabled'] : [];
	$selected = isset($o['data-selected']) ? $o['data-selected'] : [];
	$option_value = isset($o['option-value-field']) ? $o['option-value-field'] : 'id';
	$placeholder = isset($o['placeholder']) ? $o['placeholder'] : '';
	$html = '<div class="form-group"><label class="col-sm-12 control-label">'.getTextTranslate(66,ADMIN_LANG).'</label><div class="col-sm-10">';
	$html .= '<input data-min="0" data-max="255" '.($checked ? '' : 'disabled').' type="text" name="'.$field_name.'" class="form-control finput-time input-sm datetimepicker2" value="'.$value.'" />';
	$html .= '</div><div class="col-sm-2"><div class="row"><div class="checkbox "><label><input onchange="enabledInput(this);" data-target=".finput-time" class="" '.($checked ? 'checked' : '').' name="biz[manual_'.$field.']" type="checkbox"> '.getTextTranslate(177,ADMIN_LANG).'</label></div></div></div></div>';

	return $html;
}
function Ad_edit_show_dropdown_currency($v, $o = []){
	
	$label = isset($o['label']) ? $o['label'] : '';
	$field = isset($o['field']) ? $o['field'] : false;
	$field_name = isset($o['field_name']) ? $o['field_name'] : (!is_array($field) ? "f[$field]" : '' );
	$checked = isset($o['checked']) ? $o['checked'] : false;
	$class = isset($o['class']) ? $o['class'] : '';
	$value = isset($o['value']) ? $o['value'] : '';
	$title = isset($o['title']) ? $o['title'] : '';
	$multiple = isset($o['multiple']) ? $o['multiple'] : false;
	$data = isset($o['data']) ? $o['data'] : [];
	$disabled = isset($o['data-disabled']) ? $o['data-disabled'] : [];
	$selected = isset($o['data-selected']) ? $o['data-selected'] : [];
	$option_value = isset($o['option-value-field']) ? $o['option-value-field'] : 'id';
	$placeholder = isset($o['placeholder']) ? $o['placeholder'] : '';
	$show_group = isset($o['show_group']) && $o['show_group'] == true ? true : false;
	$currency = isset($o['currency']) ? $o['currency'] : [];
	$currency_name = isset($o['currency_name']) ? $o['currency_name'] : 'f[currency_name]';
	
	$c = Yii::$app->zii->getCurrency(isset($v['currency']) ? $v['currency'] : 1);	 
	$html = '';
	$html .= !$show_group ? '<div class="form-group f12e"><label class="col-sm-12 control-label">'.$label.'</label><div class="col-sm-12">' : '';
	$html .= '<div class="input-group '.(isset($o['input-group-class']) ? $o['input-group-class'] : '').'" >';
	if(isset($o['input0']) && !empty($o['input0'])){
		$input0 = $o['input0'];
		$html .= '<input 
		data-decimal="'.(isset($c['decimal_number']) ? $c['decimal_number'] : 1).'"';
		if(isset($input0['attrs']) && !empty($input0['attrs'])){
			foreach ($input0['attrs'] as $key=>$value){
				$html .= $key . '="'.$value.'" ';
			}
		}
		$html .= '/>';
		
	}
	$html .= '<input data-decimal="'.(isset($c['decimal_number']) ? $c['decimal_number'] : 1).'" type="text" name="'.$field_name.'" class="input-price-decimal-field form-control number-format  '.$class.'" value="'.(isset($v[$field]) ? $v[$field] : '') .'" placeholder="'.$placeholder.'">';


    $html .= '<span class="input-group-btn"><div class="btn-group">
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
  <b class="input-currency-symbol f12px">'.(Yii::$app->zii->showCurrency(isset($v['currency']) ? $v['currency'] : 1)).'</b> <span class="caret"></span>
  </button><input name="'.$currency_name.'" value="'.(isset($v['currency']) ? $v['currency'] : 1).'" class="input-currency" type="hidden"/>';
	$html .= '<ul class="dropdown-menu dropdown-menu-right">';
	$c = Yii::$app->zii->getUserCurrency();
	if(!empty($c['list'])){
		
		foreach ($c['list'] as $k1=>$v1){
			$html .= '<li><a data-decimal="'.$v1['decimal_number'].'" data-target="input.input-price-decimal-field" onclick="changeDropdownCurrency(this);" data-id="'.$v1['id'].'" data-symbol="'.Yii::$app->zii->showCurrency($v1['id']).'" >'.$v1['code'] . ' - '. $v1['symbol'].'</a></li>';
		}
 
  		
	}
	$html .= '</ul>';
	$html .= '</div></span></div>';
	
	$html .= !$show_group ? '</div></div>' : ''; 
	
	return $html;
}

function showState($state = 0, $type_id = 0){
	$r = '';
	if(!isset($_SESSION['states'][$type_id][$state])){		
		$v = (new yii\db\Query())->select(['title'])->from(['a'=>'{{%states}}'])->where(['a.id'=>$state,'a.type_id'=>$type_id])->one();
		if(!empty($v)){
			$r = $_SESSION['states'][$type_id][$state] = $v['title'];
		}
	}else{
		$r = $_SESSION['states'][$type_id][$state]; 
	}
	return $r;
}

function read_date($number = 0, $lang = __LANG__){
	$date = '';
	switch ($number){
		case 0: case 7:
			$date = 'Chủ nhật';
			break;
		default:
			$date = 'Thứ ' . ($number+1);
			break;
	}
	return $date;
} 
 
function get_nearest_date($date_number = 0, $o = []){
	$string = false;
	$min_date = isset($o['min_date']) ? ctime(['string'=> $o['min_date'], 'format'=>'Y-m-d']) : date('Y-m-d');
	$date_before = isset($o['date_before']) ? $o['date_before'] : 0;
	$date_igrone = isset($o['date_igrone']) ? $o['date_igrone'] : [];
	
	if(!is_array($date_igrone)){
		$date_igrone = explode(',', $date_igrone);
	}
	
	if(!empty($date_igrone)){
		foreach ($date_igrone as $k1=>$d1){
			$date_igrone[$k1] = ctime(['string'=>$d1,'format'=>'d/m/Y']);
		}
	}
	 
	$time_min_date = strtotime($min_date);
	$today = date('w',strtotime($min_date));
	$today = mktime(0,0,0,date('m',$time_min_date),date('d',$time_min_date)+$date_before,date('Y',$time_min_date));
	
	$max_date = isset($o['max_date']) ? $o['max_date'] : date('Y-m-d',mktime(0,0,0,date('m',$today),date('d',$today),date('Y',$today)+10));
	
	
	
	if(is_array($date_number)){
		$rs = [];
		foreach ($date_number as $dn){ 
			 
			$td = $today; $i = 0;
			$date_string = date('d/m/Y',$td);
			$result_time = $today;
			//view($dn);
			while($td<strtotime($max_date) && (date('w',$td) != $dn || in_array($date_string, $date_igrone))){
				//view($dn);view(date('D d/m/Y',$td));
				$td = $result_time = mktime(0,0,0,date('m',$td),date('d',$td)+1,date('Y',$td));
				$date_string = date('d/m/Y',$td);
			//	view(date('D d/m/Y',$result_time));
			}
			if($result_time>0 && !in_array($date_string, $date_igrone)){
				$rs[] = $result_time;
			}
			
		}

		return !empty($rs) ? date('d/m/Y', min($rs)) : false;
	}else{
		$td = $today;
		while(date('w',$td) !== $date_number){
			$td = mktime(0,0,0,date('m',$td),date('d',$td)+1,date('Y',$td));
		}
		return (date('d/m/Y',$td));
	}
	 
	 
	
	
	return $string;
}


function removeLastSlashes($string){
	while(strlen($string)>0 && substr($string, -1) == '/'){
		$string = substr($string, 0,-1);
	}
	return $string;
}

function checkAuthorization($parent, $child = 'index'){
	return Yii::$app->authManager->hasChild(Yii::$app->authManager->createRole($parent),Yii::$app->authManager->createPermission($child));
}


function get_data_from_url($url)
{
	$ch = curl_init();
	$timeout = 10;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function get_exchangerates(){
	$url = 'http://www.vietcombank.com.vn/ExchangeRates/ExrateXML.aspx';
	return simplexml_load_string(get_data_from_url($url));
}

function xmlObject2Array($object){
	$r = [];
	if(!empty($object)){
		foreach($object->Attributes() as $key=>$val){
			$r[$key] = (string)$val;
		}
	}
	return $r;
}

function object2array($object) { return @json_decode(@json_encode($object),1); }

function showPartDay($part = 0, $lang = __LANG__){
	switch ($part){
		case 0: $day = Yii::$app->t->translate('label_morning',$lang); break;
		case 1: $day = Yii::$app->t->translate('label_noon',$lang); break;
		case 2: $day = Yii::$app->t->translate('label_afternoon',$lang); break;
		case 3: $day = Yii::$app->t->translate('label_evening',$lang); break;
		default: $day = '--'; break;
	}
	return $day;
}

function showListChooseService($lang=__LANG__){
	return [
			['id'=>TYPE_ID_HOTEL,'title'=>Yii::$app->t->translate('label_hotel',$lang)],
			['id'=>TYPE_ID_SHIP_HOTEL,'title'=>Yii::$app->t->translate('label_ship_hotel',$lang)],
			['id'=>TYPE_ID_SHIP,'title'=>Yii::$app->t->translate('label_ship_transport',$lang)],
			['id'=>TYPE_ID_TRANSPORT_TICKET,'title'=>Yii::$app->t->translate('label_transport_ticket',$lang)],
			['id'=>TYPE_ID_REST,'title'=>Yii::$app->t->translate('label_restaurant',$lang)],
			['id'=>TYPE_CODE_DISTANCE,'title'=>Yii::$app->t->translate('label_transport',$lang)],
			['id'=>TYPE_ID_SCEN,'title'=>Yii::$app->t->translate('label_visit_ticket',$lang)],
			['id'=>TYPE_ID_GUIDES,'title'=>Yii::$app->t->translate('label_guide',$lang)],
			['id'=>TYPE_ID_TRAIN,'title'=> Yii::$app->t->translate('label_train',$lang)],
			['id'=>TYPE_ID_AIR,'title'=>Yii::$app->t->translate('label_airplane',$lang)],
			['id'=>TYPE_ID_TEXT,'title'=> Yii::$app->t->translate('label_text_instruction',$lang)],
			//['id'=>TYPE_ID_HOTEL,'title'=>'Khách sạn'],
	];
}


function getServiceType($type_id = 0,$lang=__LANG__){
	switch ($type_id){ 
		case TYPE_ID_HOTEL: return Yii::$app->t->translate('label_hotel',$lang); break;
		case TYPE_ID_SHIP_HOTEL: return Yii::$app->t->translate('label_ship_hotel',$lang); break;
		case TYPE_ID_REST: return Yii::$app->t->translate('label_restaurant',$lang); break;
		case TYPE_CODE_DISTANCE: return Yii::$app->t->translate('label_transport',$lang); break;
		case TYPE_ID_SCEN: return Yii::$app->t->translate('label_visit_ticket',$lang); break;
		case TYPE_ID_GUIDES: return Yii::$app->t->translate('label_guide',$lang); break;
		case TYPE_ID_SHIP: return Yii::$app->t->translate('label_ship_transport',$lang); break;
		case TYPE_ID_TRAIN : return Yii::$app->t->translate('label_train',$lang); break;
		case TYPE_ID_AIR: return Yii::$app->t->translate('label_airplane',$lang); break;
		default : return ''; break;
	}
	 
}

function getServiceUnitPrice($type_id = 0,$lang=__LANG__){
	switch ($type_id){
		case TYPE_ID_HOTEL: return Yii::$app->t->translate('label_unit_room',$lang); break;
		case TYPE_ID_REST: return Yii::$app->t->translate('label_unit_pax',$lang); break;
		case TYPE_ID_TRAIN : return Yii::$app->t->translate('label_unit_ticket',$lang); break;
		case TYPE_ID_AIR: return Yii::$app->t->translate('label_unit_pax',$lang); break;
		case TYPE_ID_SHIP_HOTEL: return 'Cabin'; break;
		case TYPE_CODE_DISTANCE: return '-'; break;
		case TYPE_ID_SCEN: return Yii::$app->t->translate('label_unit_pax',$lang); break;
		case TYPE_ID_SHIP: return Yii::$app->t->translate('label_unit_ship',$lang); break;
		case TYPE_ID_GUIDES: return '-'; break;

		default : return ''; break;
	}

}

function getPriceHeaderButton($supplier_id = 0,$o = []){
	 
	$html = '<div class="col-sm-12 mgb10 btn-list-mglr1"><p class="aright mgt15">';
	$price_type = $type_id = '';
	if(!empty($o)){
		foreach ($o as $k=>$v){
			if($v === true) {
				switch ($k){					
					case 'controller_code':
						$controller_code = $v; 
						break;
					case 'quotation':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Có thể lập nhiều báo giá cho từng thời điểm khác nhau. Một báo giá có thể bao gồm nhiều gói dịch vụ." data-required-save="true" data-class="w80" data-action="add-more-quotation-price-to-supplier" data-title="Thêm báo giá" type="button" data-supplier_id="'.$supplier_id.'" onclick="open_ajax_modal(this);" class="btn btn-sm btn-danger btn-add-more btn-data-required-save"><i class="fa fa-gg"></i>&nbsp;Báo giá</button>';
						break;
					case 'package':
						//$package_title = isset($o['package_title']) ? $o['package_title'] : 'Gói dịch vụ';
						$pg = isset($o['package_attrs']) ? $o['package_attrs'] : [];
						$btn_title = isset($pg['btn-title']) ? $pg['btn-title'] : 'Gói dịch vụ';
						$data_title = isset($pg['data-title']) ? $pg['data-title'] : 'Thêm gói dịch vụ';
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' 
								data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' 
								data-toggle="tooltip" data-placement="top" 
								title="Một gói dịch vụ có thể bao gồm nhiều nhóm quốc tịch." 
								data-required-save="true" data-class="w80" 
								data-action="sadd-more-package-price-to-supplier" 
								data-title="'.$data_title.'" type="button" 
								data-supplier_id="'.$supplier_id.'" 
								onclick="open_ajax_modal(this);" class="btn btn-sm btn-warning btn-add-more btn-data-required-save">
								<i class="fa fa-cogs"></i>&nbsp;'.$btn_title.'</button>';
						break;
					case 'nationality':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Một nhóm quốc tịch có thể bao gồm nhiều thực đơn khác nhau." data-required-save="true" data-class="w60" data-action="add-more-nationality-group-to-supplier" data-title="Thêm nhóm quốc tịch" type="button" data-supplier_id="'.$supplier_id.'" data-target=".ajax-result-nationality-group" onclick="open_ajax_modal(this);" class="btn btn-sm btn-primary btn-add-more btn-data-required-save"><i class="fa fa-users"></i> Nhóm quốc tịch</button>';
						break;
					case 'group':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Thiết lập nhóm khách để tính giá. VD: FIT & GIT " data-required-save="true" data-class="w60" data-action="add-more-room-group" data-title="Thêm nhóm khách" type="button" data-supplier_id="'.$supplier_id.'" data-target=".ajax-result-nationality-group" onclick="open_ajax_modal(this);" class="btn btn-sm btn-info btn-add-more btn-data-required-save"><i class="fa fa-user-circle-o"></i> Nhóm khách</button>';
						break;
					case 'menu':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Thực đơn sẽ bao gồm nhiều món ăn khác nhau. Thực đơn có thể được áp dụng riêng cho từng gói dịch vụ, nhóm quốc tịch và nhóm khách." data-required-save="true" data-load="new" data-supplier_id="'.$supplier_id.'" data-title="Thêm thực đơn" type="button" onclick="open_ajax_modal(this);" data-class="w90" data-action="add-more-menu-supplier" class="btn btn-sm btn-success btn-data-required-save"><i class="fa fa-book"></i> Thực đơn</button>';
						break;
					case 'room':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Thêm danh sách phòng" data-required-save="true" data-load="new" data-supplier_id="'.$supplier_id.'" data-title="Thêm phòng" type="button" onclick="open_ajax_modal(this);" data-action="add-more-room-to-hotel" class="btn btn-sm btn-success btn-data-required-save"><i class="fa fa-bed"></i> Phòng</button>';
						break;	
					case 'vehicle':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Thêm phương tiện vào bảng giá, một phương tiện có thể có nhiều mức giá (áp dụng khi tính theo km) tùy theo độ dài quãng đường." data-required-save="true" data-load="new" data-supplier_id="'.$supplier_id.'" data-title="Thêm phương tiện" type="button" onclick="open_ajax_modal(this);" data-class="w60" data-action="add-more-vehicle-to-supplier-price" class="btn btn-sm btn-success btn-data-required-save"><i class="fa fa-cab"></i> Phương tiện</button>';
						break;
					case 'distance':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Thêm chặng xe vào bảng giá, mỗi chặng có thể có nhiều mức giá tùy theo thời điểm (xem thêm mục cài đặt mùa)" data-required-save="true" data-load="new" data-supplier_id="'.$supplier_id.'" data-title="Thêm chặng di chuyển" type="button" onclick="open_ajax_modal(this);" data-class="w60" data-action="add-more-distance-to-supplier-price" class="btn btn-sm btn-success btn-data-required-save"><i class="fa fa-cab"></i> Chặng vận chuyển</button>';
						break;
					case 'guide':
						$html .= '<button '.($price_type != "" ? 'data-price_type="'.$price_type.'"' : '').' data-controller_code="'.($controller_code).'" '.($type_id != "" ? 'data-type_id="'.$type_id.'"' : '').' data-toggle="tooltip" data-placement="top" title="Thêm hướng dẫn" data-required-save="true" data-load="new" data-supplier_id="'.$supplier_id.'" data-title="Thêm hướng dẫn viên" type="button" onclick="open_ajax_modal(this);" data-class="w60" data-action="add-more-guides" class="btn btn-sm btn-success btn-data-required-save"><i class="fa fa-cab"></i> Hướng dẫn viên</button>';
						break;
					case 'train_ticket'	:
						$html .= '<button  data-required-save="true" data-type_id="'.TYPE_ID_TRAIN.'" data-supplier_id="'.$supplier_id.'" data-title="Thêm vé" type="button" onclick="open_ajax_modal(this);" data-class="w80" data-action="add-more-station-to-distance" class="btn btn-warning btn-data-required-save btn-sm"><i class="fa fa-plus"></i> Thêm vé</button>';
						break; 
				}				
			}else{
				switch ($k){
					case 'price_type':
						$price_type = $v;
						break;
					case 'type_id':
						$type_id = $v;
						break;	
					case 'controller_code':
						$controller_code = $v;
						break;
				}
			}
		}
	}
 
	
	
	 				
	$html .= '</p></div>';
	return $html;
}


function getSupplierPricesList($supplier_id = 0, $o = []){
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$supplier = \app\modules\admin\models\Customers::getItem($supplier_id);
	if(empty($supplier)) return false;
	$supplier_type = $supplier['type_id'];
	// Lấy ds báo giá
	$quotations = \app\modules\admin\models\Customers::getSupplierQuotations($supplier_id,[
			'order_by'=>['a.to_date'=>SORT_DESC,'a.title'=>SORT_ASC],
			'is_active'=>1
	]);
	// Lay package
	$packages = \app\modules\admin\models\PackagePrices::getPackages($supplier_id);
	// Lay nhom quoc tich
	$nationalitys = \app\modules\admin\models\NationalityGroups::get_supplier_group($supplier_id);
	// Lay mua co tinh gia truc tiep
	$incurred_prices_list = \app\modules\admin\models\Customers::getCustomerSeasons($supplier_id,[
			'price_type'=>[0],'type_id'=>2
	]);
	$ckc_incurred = true;
	// Lay danh sach cuoi tuan ngay thuong tinh gia truc tiep
	$incurred_prices_weekend_list = \app\modules\admin\models\Customers::getCustomerWeekend([
			'price_type'=>[0],
			'supplier_id'=>$supplier_id,
			'return_type'=>'for_price',
	]);	
	// Lay nhom phong
	$room_groups = \app\modules\admin\models\Seasons::get_rooms_groups($supplier_id);
	// Lay danh sach buổi tinh gia truc tiep
	$incurred_prices_weekend_list_time = \app\modules\admin\models\Customers::getCustomerWeekendTime([
			'price_type'=>[0],
			'supplier_id'=>$supplier_id,
			'return_type'=>'for_price',
	]);
	//
	
	$html = ''; $h = [
			'controller_code'=>$supplier_type,
			'type_id'=>$supplier_type,
			'quotation'=>true,'package'=>true,
			'nationality'=>true,'group'=>true,
	];
	switch ($supplier_type){
		case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
			$h['room'] = true;		
			$l = \app\modules\admin\models\Hotels::getListRooms($supplier_id);
			break;
		case TYPE_ID_REST:
			$h['menu'] = true;
			
			$l = \app\modules\admin\models\Menus::getMenus(['supplier_id'=>$supplier_id]);
			break;
		case TYPE_ID_GUIDES:
			$h['guide'] = true;$h['package'] = false;
			$l = \app\modules\admin\models\Guides::getGuides(['supplier_id'=>$supplier_id]);
			break;
	}
	$html .= getPriceHeaderButton($supplier_id,$h);
	if(!empty($quotations)){
		foreach ($quotations as $q=>$quotation){
			$html .= '<div class="col-sm-12 mgt15 quotation-block" style=""><div class="row pr"><p class="grid-sui-pheader bold aleft">
				'.$quotation['title'].'<i> - Áp dụng từ <span class="  underline">'.date('d/m/Y H:i:s',strtotime($quotation['from_date'])).' - '.date('d/m/Y H:i:s',strtotime($quotation['to_date'])).'</span></i></p></div>';
				
	
			$html .= '<div class="row-10">';
	
			foreach ($packages as $package){
				if(!empty($nationalitys)){
	
					foreach ($nationalitys as $kb=>$vb){
						$existed_nationality[] = $vb['id'];
						$html .= '<div class="col-sm-12 mgt15"><div class="row pr"><p class="grid-sui-pheader bold aleft"><i style="font-weight: normal;">';
	
	
						if($package['id']>0){
							$html .= 'Gói dịch vụ ';
							$html .= '<b class="italic green underline">' .$package['title'] .'</b> ';
						}else{
							$html .= 'Bảng giá ';
						}
						$html .= ' - áp dụng cho <b class="italic underline">' .$vb['title'] .'</b> ';
	
						$html .= '</i><i data-name="remove_nationality" data-id="'.$vb['id'].'" onclick="addToRemove(this);" class="fa fa-trash pointer hide btn-remove btn-delete-item"></i></p></div></div>';
						$colspan = count($room_groups) * (count($incurred_prices_weekend_list)>0 ? count($incurred_prices_weekend_list) : 1);
						$html .= '<div class="col-sm-12"><div class="row"><div class="fl100 pr auto_height_price_list"><table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered vmiddle ">
<thead>
<tr><th rowspan="5" class="center w50p"></th>
<th rowspan="5" class="center" style="min-width:200px">Tiêu đề</th>
<th colspan="'.($colspan*count($incurred_prices_list) *count($incurred_prices_weekend_list_time) ).'" class="center underline ">Bảng giá</th>
<th rowspan="5" class="w100p center" title="Chuyển đổi nhanh loại tiền tệ">Tiền tệ <hr><select
		data-target=".select-currency-'.$quotation['id'].'-'.$package['id'].'-'.$vb['id'].'"
		data-decimal="0" onchange="get_decimal_number(this);change_multi_currency_price(this);" class="sl-cost-price-currency form-control select2 input-sm" data-search="hidden" >';
						
						
						
						foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
							$html .= '<option value="'.$v2['id'].'">'.$v2['code'].'</option>';
						}
						//}
						//return json_encode($ckc_incurred);
						$html .= '</select></th><th rowspan="5" class="w100p center">Mặc định</th><th rowspan="5" class="w100p"></th>
</tr>
							<tr class="'.(count($incurred_prices_list) > 1 && $ckc_incurred ? '' : 'hide').'">';
						if(!empty($incurred_prices_list)){
							foreach ($incurred_prices_list as $in){
								$html .= '<th colspan="'.($colspan * count($incurred_prices_weekend_list_time)).'" class="center w200p">'.$in['title'].'</th>';
							}
						}
						
						$html .= '
</tr><tr class="'.(count($room_groups) > 1 ? ' ' : 'hide').'">';
						if(!empty($incurred_prices_list)){
							foreach ($incurred_prices_list as $in){
								if(!empty($room_groups)){
									foreach ($room_groups as $room){
										$html .= '<th colspan="'.(count($incurred_prices_weekend_list) * count($incurred_prices_weekend_list_time)).'" class="center w200p"><a data-class="w70" data-supplier_id="'.$supplier_id.'" data-parent_id="'.(isset($v['id']) ? $v['id'] : 0).'" data-id="'.$room['id'].'" data-action="add-more-room-group" data-title="Thiết lập nhóm phòng" onclick="open_ajax_modal(this);" class="pointer hover_underline">'.$room['title'].($room['note'] != "" ? '<p><i class="f11p font-normal">('.$room['note'].')</i></p>' : '').'</a></th>';
									}
								}
							}}
							$html .= '</tr>';
							$html .= '<tr class="'.(count($incurred_prices_weekend_list) > 1 ? '' : 'hide').'">';
							if(!empty($incurred_prices_list)){
								foreach ($incurred_prices_list as $in){
									if(!empty($room_groups)){
										foreach ($room_groups as $room){
											if(!empty($incurred_prices_weekend_list)){
												foreach ($incurred_prices_weekend_list as $weekend){
													$html .= '<th colspan="'.(count($incurred_prices_weekend_list_time)).'" class="center w200p"><i>'.$weekend['title'].'</i></th>';
												}
											}
										}
									}
								}}
	
								$html .='</tr>';
								$html .= '<tr class="'.(count($incurred_prices_weekend_list_time) > 1 ? '' : 'hide').'">';
								if(!empty($incurred_prices_list)){
									foreach ($incurred_prices_list as $in){
										if(!empty($room_groups)){
											foreach ($room_groups as $room){
												if(!empty($incurred_prices_weekend_list)){
													foreach ($incurred_prices_weekend_list as $weekend){
														if(!empty($incurred_prices_weekend_list_time)){
															foreach ($incurred_prices_weekend_list_time as $weekend_time){
																$html .= '<th class="center w150p"><i>'.$weekend_time['title'].'</i></th>';
															}
														}
													}
												}
											}
										}
									}}
	
									$html .='</tr>';
	
									$html .= '</thead><tbody >';
										
									if(!empty($l)){
										foreach ($l as $k1=>$v1){
											$existed[] = $v1['id'];
											//$p = $menus->get_price($v1['id'],$id,$vb['id'],$package['id']);
											$currency = 1; $isDefault = 0;
											$tr = [
													$supplier_id,
													$quotation['id'],
													$package['id'],
													$vb['id'],
													$v1['id']
											];
											$cates = \app\modules\admin\models\Menus::getItemCategorys($v1['id'],0);
											$html .= '<tr class="tr-price-'.implode('-', $tr).'">
<td class="center">'.($k1+1).'</td>
<td><a class="pointer" data-supplier_id="'.$supplier_id.'" data-menu_id="'.$v1['id'].'" data-item_id="'.$v1['id'].'" data-title="Chỉnh sửa" onclick="open_ajax_modal(this);" data-class="w90" data-action="add-more-menu-supplier">'.uh($v1['title']). ( !empty($cates) ? ' <i class="font-normal red">('.implode(' | ', $cates).')</i>' : '') .'</a></td>';
											if(!empty($incurred_prices_list)){
												foreach ($incurred_prices_list as $in){
													if(!empty($room_groups)){
														foreach ($room_groups as $room){
															if(!empty($incurred_prices_weekend_list)){
																foreach ($incurred_prices_weekend_list as $w){
																	if(!empty($incurred_prices_weekend_list_time)){
																		foreach ($incurred_prices_weekend_list_time as $weekend_time){
																			 
																			$price = \app\modules\admin\models\Customers::getSupplierDetailPrice([
																					'item_id'=>$v1['id'],
																					'season_id'=>$in['id'],
																					'weekend_id'=>$w['id'],
																					'group_id'=>$room['id'],
																					'supplier_id'=>$supplier_id,
																					'package_id'=>$package['id'],
																					'quotation_id'=>$quotation['id'],
																					'time_id'=>$weekend_time['id'],
																					'nationality_id'=>$vb['id']
																			]);
																			//return json_encode($incurred_prices_list); 
																			if(!empty($price)){
																				$currency = $price['currency'];
																				$isDefault = $price['is_default'];
																			}
																			$html .= '<td class="center"><input
											data-supplier_id="'.$supplier_id.'"
											data-quotation_id="'.$quotation['id'].'"
											data-package_id="'.$package['id'].'"
											data-nationality_id="'.$vb['id'].'"
											data-item_id="'.$v1['id'].'"
											data-season_id="'.$in['id'].'"
											data-group_id="'.$room['id'].'"
											data-weekend_id="'.$w['id'].'"
											data-time_id="'.$weekend_time['id'].'"
													data-controller_code="'.$supplier_type.'"
											data-supplier_type="'.$supplier['type_id'].'"		
											onblur="quick_change_supplier_service_price(this);"
											type="text" name="prices['.$package['id'].']['.$vb['id'].']['.$v1['id'].'][list_child]['.$in['id'].']['.$room['id'].']['.$w['id'].'][price1]"
											value="'.(isset($price['price1']) ? $price['price1'] : '').'"
											data-old="'.(isset($price['price1']) ? $price['price1'] : '').'"
											class="form-control input-sm aright number-format w100 min-width-80px inline-block input-currency-price-'.$v1['id'].'" data-decimal="'.Yii::$app->zii->showCurrency($currency,3).'"/></td>';
																		}
																	}
																}
															}
														}
													}
												}}
												$html .= '<td class="center">';
												$html .= '<select
					data-supplier_id="'.$supplier_id.'" 
					data-quotation_id="'.$quotation['id'].'"
					data-package_id="'.$package['id'].'"
					data-nationality_id="'.$vb['id'].'"
					data-item_id="'.$v1['id'].'"
					data-controller_code="'.$supplier_type.'"
					data-decimal="'.Yii::$app->zii->showCurrency($currency,3).'" 
					data-target-input=".input-currency-price-'.$v1['id'].'" 
					onchange="get_decimal_number(this);quick_change_menu_price_currency(this);" 
					class="ajax-select2-no-search sl-cost-price-currency form-control ajax-select2 input-sm select-currency-'.$quotation['id'].'-'.$package['id'].'-'.$vb['id'].'" 
					data-search="hidden" name="prices['.$package['id'].']['.$vb['id'].']['.$v1['id'].'][currency]">';
												//if(isset($v['currency']['list']) && !empty($v['currency']['list'])){
												foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
													$html .= '<option value="'.$v2['id'].'" '.($currency == $v2['id'] ? 'selected' : '').'>'.$v2['code'].'</option>';
												}
												//}
	
												$html .= '</select>';
												$html .= '</td>';
$html .= '<td class="center"><input
		data-supplier_id="'.$supplier_id.'" 
		data-quotation_id="'.$quotation['id'].'"
		data-package_id="'.$package['id'].'"
		data-nationality_id="'.$vb['id'].'"
		data-controller_code="'.$supplier_type.'"		
		data-item_id="'.$v1['id'].'"
		onchange="quick_change_menus_price_default(this);" 
		type="radio" name="set_default['.$quotation['id'].']['.$package['id'].']['.$vb['id'].']" value="'.$v1['id'].'"
				'.($isDefault == 1 ? ' checked' : '').'
				/></td>';
												$html .= '<td class="center">
<i data-supplier_id="'.$supplier_id.'"
					data-quotation_id="'.$quotation['id'].'"
					data-package_id="'.$package['id'].'"
					data-nationality_id="'.$vb['id'].'"
					data-item_id="'.$v1['id'].'"
					data-confirm-text="<span class=red>Lưu ý: Bản ghi <b class=underline>'.$v1['title'].'</b> sẽ bị xóa khỏi toàn bộ các báo giá.</span>"
					class="pointer glyphicon glyphicon-trash btn-delete-item" data-id="'.$v1['id'].'" data-name="remove_menu" data-confirm-action="quick_change_menu_price_remove" data-action="open-confirm-dialog" data-class="modal-sm" data-title="Xác nhận xóa." onclick="open_ajax_modal(this);"></i>
</td>
</tr> ';
										}
									}
										
									$html .= '</tbody></table></div></div></div>';
					}
				}
			}
				
			//
	
	
			$html .= '</div></div>';
		}
	
	} else{
		$html .= '<div class="col-sm-12"><p class="help-block red ">Bạn cần tạo báo giá trước khi nhập giá.</p></div>';
	}
	
	return $html;
}




function getSupplierVehiclePrices($supplier_id = 0, $o = []){

	$supplier = \app\modules\admin\models\Customers::getItem($supplier_id);
	if(empty($supplier)) return false;
	$supplier_type = $supplier['type_id'];
	// Lấy ds báo giá
	$quotations = \app\modules\admin\models\Customers::getSupplierQuotations($supplier_id,[
			'order_by'=>['a.to_date'=>SORT_DESC,'a.title'=>SORT_ASC],
			'is_active'=>1
	]);
	// Lay package
	$packages = \app\modules\admin\models\PackagePrices::getPackages($supplier_id);
	// Lay nhom quoc tich
	$nationalitys = \app\modules\admin\models\NationalityGroups::get_supplier_group($supplier_id,true);
	//view($nationalitys);
	// Lay mua co tinh gia truc tiep
	$incurred_prices_list = \app\modules\admin\models\Customers::getCustomerSeasons($supplier_id,[
			'price_type'=>[0],'type_id'=>2,'default'=>true
	]);
	//view($incurred_prices_list);
	$ckc_incurred = true;
	// Lay danh sach cuoi tuan ngay thuong tinh gia truc tiep
	$incurred_prices_weekend_list = \app\modules\admin\models\Customers::getCustomerWeekend([
			'price_type'=>[0],
			'supplier_id'=>$supplier_id,
			'return_type'=>'for_price',
	]);
	$incurred_prices_weekend_list[] = [
		'id'=>0,
			'title'=>'Lưu đêm',
			'type'=>'price2'
	]; 
	//view($incurred_prices_weekend_list,true);
	// Lay nhom phong
	$room_groups = \app\modules\admin\models\Seasons::get_rooms_groups($supplier_id);
	// Lay danh sach buổi tinh gia truc tiep
	$incurred_prices_weekend_list_time = \app\modules\admin\models\Customers::getCustomerWeekendTime([
			'price_type'=>[0],
			'supplier_id'=>$supplier_id,
			'return_type'=>'for_price',
	]);
	//
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$html = ''; $h = [
			'price_type'=>1,
			'controller_code'=>$supplier_type,
			'type_id'=>$supplier_type,
			'quotation'=>true,'package'=>true,
			'nationality'=>true,'group'=>true,
	]; 
	switch ($supplier_type){
		case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
			$h['room'] = true;
			$l = \app\modules\admin\models\Hotels::getListRooms($supplier_id);
			break;
		case TYPE_ID_REST:
			$h['menu'] = true;
			$l = \app\modules\admin\models\Menus::getMenus(['supplier_id'=>$supplier_id]);
			break;
		case TYPE_ID_VECL:
			$l = \app\modules\admin\models\Cars::getPrices(['supplier_id'=>$supplier_id]);
			$h['vehicle'] = true;
			break;
	}
	
	$html .= getPriceHeaderButton($supplier_id,$h);
	if(!empty($quotations)){
		foreach ($quotations as $q=>$quotation){
			$html .= '<div class="col-sm-12 mgt15 quotation-block" style=""><div class="row pr"><p class="grid-sui-pheader bold aleft">
				'.$quotation['title'].'<i> - Áp dụng từ <span class="  underline">'.date('d/m/Y H:i:s',strtotime($quotation['from_date'])).' - '.date('d/m/Y H:i:s',strtotime($quotation['to_date'])).'</span></i></p></div>';


			$html .= '<div class="row-10">';

			foreach ($packages as $package){
				if(!empty($nationalitys)){

					foreach ($nationalitys as $kb=>$vb){
						$existed_nationality[] = $vb['id'];
						$html .= '<div class="col-sm-12 mgt15"><div class="row pr"><p class="grid-sui-pheader bold aleft"><i style="font-weight: normal;">';


						if($package['id']>0){
							$html .= 'Gói dịch vụ ';
							$html .= '<b class="italic green underline">' .$package['title'] .'</b> ';
						}else{
							$html .= 'Bảng giá ';
						}
						$html .= $vb['id'] > 0 ? ' - áp dụng cho <b class="italic underline">' .$vb['title'] .'</b> ' : ' ';

						$html .= '</i><i data-name="remove_nationality" data-id="'.$vb['id'].'" onclick="addToRemove(this);" class="fa fa-trash pointer hide btn-remove btn-delete-item"></i></p></div></div>';
						$colspan = count($room_groups) * (count($incurred_prices_weekend_list)>0 ? count($incurred_prices_weekend_list) : 1);
						$html .= '<div class="col-sm-12"><div class="row"><div class="fl100 pr auto_height_price_list"><table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered vmiddle ">
<thead>
<tr><th rowspan="5" class="center w50p"></th>
<th rowspan="5" class="center" style="min-width:200px">Phương tiện</th>
<th rowspan="5" class="center" style="min-width:100px">Từ (km)</th>
<th rowspan="5" class="center" style="min-width:100px">Đến (km)</th>
<th colspan="'.($colspan*count($incurred_prices_list) *count($incurred_prices_weekend_list_time) ).'" class="center underline ">Bảng giá</th>
<th rowspan="5" class="w100p center" title="Chuyển đổi nhanh loại tiền tệ">Tiền tệ <hr><select
		data-target=".select-currency-'.$quotation['id'].'-'.$package['id'].'-'.$vb['id'].'"
		data-decimal="0" onchange="get_decimal_number(this);change_multi_currency_price(this);" class="sl-cost-price-currency form-control select2 input-sm" data-search="hidden" >';



						foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
							$html .= '<option value="'.$v2['id'].'">'.$v2['code'].'</option>';
						}
						//}
						//return json_encode($ckc_incurred);
						$html .= '</select></th><th rowspan="5" class="w100p"></th>
</tr>
							<tr class="'.(count($incurred_prices_list) > 1 && $ckc_incurred ? '' : 'hide').'">';
						if(!empty($incurred_prices_list)){
							foreach ($incurred_prices_list as $in){
								$html .= '<th colspan="'.($colspan * count($incurred_prices_weekend_list_time)).'" class="center w200p">'.$in['title'].'</th>';
							}
						}

						$html .= '
</tr><tr class="'.(count($room_groups) > 1 ? ' ' : 'hide').'">';
						if(!empty($incurred_prices_list)){
							foreach ($incurred_prices_list as $in){
								if(!empty($room_groups)){
									foreach ($room_groups as $room){
										$html .= '<th colspan="'.(count($incurred_prices_weekend_list) * count($incurred_prices_weekend_list_time)).'" class="center w200p"><a data-class="w70" data-supplier_id="'.$supplier_id.'" data-parent_id="'.(isset($v['id']) ? $v['id'] : 0).'" data-id="'.$room['id'].'" data-action="add-more-room-group" data-title="Thiết lập nhóm phòng" onclick="open_ajax_modal(this);" class="pointer hover_underline">'.$room['title'].($room['note'] != "" ? '<p><i class="f11p font-normal">('.$room['note'].')</i></p>' : '').'</a></th>';
									}
								}
							}}
							$html .= '</tr>';
							$html .= '<tr class="'.(count($incurred_prices_weekend_list) > 0 ? '' : 'hide').'">';
							if(!empty($incurred_prices_list)){
								foreach ($incurred_prices_list as $in){
									if(!empty($room_groups)){
										foreach ($room_groups as $room){
											if(!empty($incurred_prices_weekend_list)){
												foreach ($incurred_prices_weekend_list as $weekend){
													$html .= '<th colspan="'.(count($incurred_prices_weekend_list_time)).'" class="center w200p"><i>'.$weekend['title'].'</i></th>';
												}
												//$html .= '<th colspan="'.(count($incurred_prices_weekend_list_time)).'" class="center w200p"><i>'.$weekend['title'].'</i></th>';
											}
										}
									}
								}}

								$html .='</tr>';
								$html .= '<tr class="'.(count($incurred_prices_weekend_list_time) > 1 ? '' : 'hide').'">';
								if(!empty($incurred_prices_list)){
									foreach ($incurred_prices_list as $in){
										if(!empty($room_groups)){
											foreach ($room_groups as $room){
												if(!empty($incurred_prices_weekend_list)){
													foreach ($incurred_prices_weekend_list as $weekend){
														if(!empty($incurred_prices_weekend_list_time)){
															foreach ($incurred_prices_weekend_list_time as $weekend_time){
																$html .= '<th class="center w150p"><i>'.$weekend_time['title'].'</i></th>';
															}
														}
													}
												}
											}
										}
									}}

									$html .='</tr>';

									$html .= '</thead><tbody >';

									if(!empty($l)){
										foreach ($l as $k1=>$v1){
											$existed[] = $v1['id'];
											//$p = $menus->get_price($v1['id'],$id,$vb['id'],$package['id']);
											$currency = 1;
											$tr = [
													$supplier_id,
													$quotation['id'],
													$package['id'],
													$vb['id'],
													$v1['id']
											];
											$html .= '<tr class="tr-price-'.implode('-', $tr).'">
<td class="center">'.($k1+1).'</td>
<td><a class="pointer" data-supplier_id="'.$supplier_id.'" data-menu_id="'.$v1['id'].'" data-title="Chỉnh sửa" data-class="w90" data-action="add-more-menu-supplier">'.uh($v1['title']).'</a></td>
<td class="center"><input
		data-field="pmin" 
		data-group_id="-1"
		data-group_id="-1"
		data-weekend_id="-1"
		data-season_id="-1"
		data-time_id="-1"
											data-parent_group_id="'.$v1['parent_group_id'].'"
											data-field="pmin"
											data-supplier_id="'.$supplier_id.'"
											data-quotation_id="'.$quotation['id'].'"
											data-package_id="'.$package['id'].'"
											data-nationality_id="'.$vb['id'].'"
											data-item_id="'.$v1['id'].'"											 
											data-supplier_type="'.$supplier['type_id'].'"
											onblur="quick_change_supplier_service_price(this);"
											type="text" name="pricesxxx"
											value="'.($v1['pmin']).'"
											data-old="'.($v1['pmin']).'"
											class="form-control input-sm center number-format w100 min-width-80px inline-block" 
											/></td> <td>
		<input data-field="pmax"
		data-group_id="-1"
		data-group_id="-1"
		data-weekend_id="-1"
		data-season_id="-1"
		data-time_id="-1"
													
											data-parent_group_id="'.$v1['parent_group_id'].'"
											data-field="pmax"
											data-supplier_id="'.$supplier_id.'"
											data-quotation_id="'.$quotation['id'].'"
											data-package_id="'.$package['id'].'"
											data-nationality_id="'.$vb['id'].'"
											data-item_id="'.$v1['id'].'"
											data-supplier_type="'.$supplier['type_id'].'"
											onblur="quick_change_supplier_service_price(this);"
											type="text" name="pricesxxx"
											value="'.($v1['pmax']).'"
											data-old="'.($v1['pmax']).'"
											class="form-control input-sm center number-format w100 min-width-80px inline-block" 
											/></td>';
											if(!empty($incurred_prices_list)){
												foreach ($incurred_prices_list as $in){
													if(!empty($room_groups)){
														foreach ($room_groups as $room){
															if(!empty($incurred_prices_weekend_list)){
																foreach ($incurred_prices_weekend_list as $w){
																	if(!empty($incurred_prices_weekend_list_time)){
																		foreach ($incurred_prices_weekend_list_time as $weekend_time){

																			$price = \app\modules\admin\models\Customers::getSupplierDetailPrice([
																					'item_id'=>$v1['id'],
																					'season_id'=>$in['id'],
																					'weekend_id'=>$w['id'],
																					'group_id'=>$room['id'],
																					'supplier_id'=>$supplier_id,
																					'package_id'=>$package['id'],
																					'quotation_id'=>$quotation['id'],
																					'time_id'=>$weekend_time['id'],
																					'nationality_id'=>$vb['id'],
																					'parent_group_id'=>$v1['parent_group_id']
																			]);
																			//return json_encode($incurred_prices_list);
																			if(!empty($price)) $currency = $price['currency'];
																			$field = isset($w['type']) && $w['type']=='price2' ? 'price2' : 'price1';
																			$html .= '<td class="center"><input
											
																					data-field="'.($field).'"
											data-parent_group_id="'.$v1['parent_group_id'].'"
											data-supplier_id="'.$supplier_id.'"
											data-quotation_id="'.$quotation['id'].'"
											data-package_id="'.$package['id'].'"
											data-nationality_id="'.$vb['id'].'"
											data-item_id="'.$v1['id'].'"
											data-season_id="'.$in['id'].'"
											data-group_id="'.$room['id'].'"
											data-weekend_id="'.$w['id'].'"
											data-time_id="'.$weekend_time['id'].'"
											data-supplier_type="'.$supplier['type_id'].'"
											onblur="quick_change_supplier_service_price(this);"
											type="text" name="pricesxxx['.$package['id'].']['.$vb['id'].']['.$v1['id'].'][list_child]['.$in['id'].']['.$room['id'].']['.$w['id'].'][price1]"
											value="'.(isset($price[$field]) ? $price[$field] : '').'"
											data-old="'.(isset($price[$field]) ? $price[$field] : '').'"
											class="form-control input-sm aright number-format w100 min-width-80px inline-block input-currency-price-'.$v1['id'].'" data-decimal="'.Yii::$app->zii->showCurrency($currency,3).'"/></td>';
																		}
																	}
																}
															}
														}
													}
												}}
												$html .= '<td class="center">';
												$html .= '<select
					data-supplier_id="'.$supplier_id.'"
					data-controller_code="'.$supplier_type.'"		
					data-quotation_id="'.$quotation['id'].'"
					data-package_id="'.$package['id'].'"
					data-nationality_id="'.$vb['id'].'"
					data-item_id="'.$v1['id'].'"
					data-parent_group_id="'.$v1['parent_group_id'].'" 
					data-decimal="'.Yii::$app->zii->showCurrency($currency,3).'" data-target-input=".input-currency-price-'.$v1['id'].'" onchange="get_decimal_number(this);quick_change_menu_price_currency(this);" class="ajax-select2-no-search sl-cost-price-currency form-control ajax-select2 input-sm select-currency-'.$quotation['id'].'-'.$package['id'].'-'.$vb['id'].'" data-search="hidden" name="pricesxxx['.$package['id'].']['.$vb['id'].']['.$v1['id'].'][currency]">';
												//if(isset($v['currency']['list']) && !empty($v['currency']['list'])){
												foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
													$html .= '<option value="'.$v2['id'].'" '.($currency == $v2['id'] ? 'selected' : '').'>'.$v2['code'].'</option>';
												}
												//} 

												$html .= '</select>';
												$html .= '</td>';
												$html .= '<td class="center">
<i data-supplier_id="'.$supplier_id.'"
		data-parent_group_id="'.$v1['parent_group_id'].'"
					data-quotation_id="'.$quotation['id'].'"
					data-package_id="'.$package['id'].'"
					data-nationality_id="'.$vb['id'].'"
					data-item_id="'.$v1['id'].'"
					data-confirm-text="<span class=red>Lưu ý: Bản ghi <b class=underline>'.$v1['title'].'</b> sẽ bị xóa khỏi toàn bộ các báo giá.</span>"
					class="pointer glyphicon glyphicon-trash btn-delete-item" data-id="'.$v1['id'].'" data-name="remove_menu" data-confirm-action="quick_change_menu_price_remove" data-action="open-confirm-dialog" data-class="modal-sm" data-title="Xác nhận xóa." onclick="open_ajax_modal(this);"></i>
</td>
</tr> ';
										}
									}

									$html .= '</tbody></table></div></div></div>';
					}
				}
			}

			//


			$html .= '</div></div>';
		}

	} else{
		$html .= '<div class="col-sm-12"><p class="help-block red ">Bạn cần tạo báo giá trước khi nhập giá.</p></div>';
	}

	return $html;
} 


function getSupplierVehiclePrices2($supplier_id = 0, $o = []){ 
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$supplier = \app\modules\admin\models\Customers::getItem($supplier_id);
	if(empty($supplier)) return false;
	$supplier_type = $supplier['type_id'];
	// Lấy ds báo giá
	$quotations = \app\modules\admin\models\Customers::getSupplierQuotations($supplier_id,[
			'order_by'=>['a.to_date'=>SORT_DESC,'a.title'=>SORT_ASC],
			'is_active'=>1
	]);
	// Lay package
	$packages = \app\modules\admin\models\PackagePrices::getPackages($supplier_id);
	// Lay nhom quoc tich
	$nationalitys = \app\modules\admin\models\NationalityGroups::get_supplier_group($supplier_id,true);
	//view($nationalitys);
	// Lay mua co tinh gia truc tiep
	$incurred_prices_list = \app\modules\admin\models\Customers::getCustomerSeasons($supplier_id,[
			'price_type'=>[0],'type_id'=>2,'default'=>true
	]);
	//view($incurred_prices_list);
	$ckc_incurred = true;
	// Lay danh sach cuoi tuan ngay thuong tinh gia truc tiep
	$incurred_prices_weekend_list = \app\modules\admin\models\Customers::getCustomerWeekend([
			'price_type'=>[0],
			'supplier_id'=>$supplier_id,
			'return_type'=>'for_price',
	]);
	 
	// Lay nhom phong
	$room_groups = \app\modules\admin\models\Seasons::get_rooms_groups($supplier_id);
	// Lay danh sach buổi tinh gia truc tiep
	$incurred_prices_weekend_list_time = \app\modules\admin\models\Customers::getCustomerWeekendTime([
			'price_type'=>[0],
			'supplier_id'=>$supplier_id,
			'return_type'=>'for_price',
	]);
	// Danh sách phương tiện theo chỗ ngồi
	$listCarBySeat = \app\modules\admin\models\Cars::get_list_cars_by_seats($supplier_id,array('is_active'=>1));
	
	// Danh sách chặng
	$distances = \app\modules\admin\models\Cars::get_list_distance_from_price($supplier_id);
	
	//view($distances);
	$html = ''; $h = [
			'price_type'=>2,
			'controller_code'=>$supplier_type,'type_id'=>$supplier_type,
			'quotation'=>true,'package'=>true,
			'nationality'=>true,'group'=>true, 
	];
	switch ($supplier_type){
		case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
			$h['room'] = true;
			$l = \app\modules\admin\models\Hotels::getListRooms($supplier_id);
			break;
		case TYPE_ID_REST:
			$h['menu'] = true;
			$l = \app\modules\admin\models\Menus::getMenus(['supplier_id'=>$supplier_id]);
			break;
		case TYPE_ID_VECL: case TYPE_ID_SHIP:
			//$l = \app\modules\admin\models\Cars::getPrices(['supplier_id'=>$supplier_id]);
			$h['distance'] = true;
			break;
			
	}
	switch ($supplier_type){
		case 8: $ptname = 'Tàu'; break;
		default: $ptname = 'Xe'; break;
	}
	$html .= getPriceHeaderButton($supplier_id,$h);
	if(!empty($quotations)){
		foreach ($quotations as $q=>$quotation){
			$html .= '<div class="col-sm-12 mgt15 quotation-block ovh" style=""><div class="row pr"><p class="grid-sui-pheader bold aleft">
				'.$quotation['title'].'<i> - Áp dụng từ <span class="  underline">'.date('d/m/Y H:i:s',strtotime($quotation['from_date'])).' - '.date('d/m/Y H:i:s',strtotime($quotation['to_date'])).'</span></i></p></div>';


			$html .= '<div class="row-10">';

			foreach ($packages as $package){
				if(!empty($nationalitys)){

					foreach ($nationalitys as $kb=>$vb){
						$existed_nationality[] = $vb['id'];
						$html .= '<div class="col-sm-12 mgt15"><div class="row pr"><p class="grid-sui-pheader bold aleft"><i style="font-weight: normal;">';


						if($package['id']>0){
							$html .= 'Gói dịch vụ ';
							$html .= '<b class="italic green underline">' .$package['title'] .'</b> ';
						}else{
							$html .= 'Bảng giá ';
						}
						$html .= $vb['id'] > 0 ? ' - áp dụng cho <b class="italic underline">' .$vb['title'] .'</b> ' : ' ';

						$html .= '</i><i data-name="remove_nationality" data-id="'.$vb['id'].'" onclick="addToRemove(this);" class="fa fa-trash pointer hide btn-remove btn-delete-item"></i></p></div></div>';
						$colspan = count($room_groups) * (count($incurred_prices_weekend_list)>0 ? count($incurred_prices_weekend_list) : 1);
						$html .= '<div class="col-sm-12"><div class="row"><div class="fl100 pr ">';
						
						$html .= '<ul class="nav border-left border-right form-edit-tab-level2 nav-tabs" role="tablist">';
						$i3 = 0;
						foreach ($listCarBySeat as $k3=>$v3){
							 
							$html .= '<li role="presentation" class="'.($i3++ == 0 ? 'active' : '').'"><a href="#tab-price-distance1-'.$k3.'" role="tab" data-toggle="tab"><b>'.$ptname.' '.($k3).' chỗ</b></a></li>';
						}
						$html .= '</ul>';
						$i3 = 0;
						$html .= '<div class="tab-content">';
						foreach ($listCarBySeat as $k3=>$v3){
							$html .= '<div role="tabpanel" class="auto_height_price_list tab-panel tab2-panel '.($i3++ == 0 ? 'active' : '').'" id="tab-price-distance1-'.$k3.'">';
							$html .= '<div class="mg5">';
							$html .= '<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered vmiddle ">
<thead>
<tr class="hide"><th rowspan="5" class="center w50p"></th>
<th rowspan="5" class="center" style="min-width:200px">Chặng vận chuyển</th>
 
 
<th colspan="'.($colspan*count($incurred_prices_list) *count($incurred_prices_weekend_list_time) * count($v3)).'" class="center underline ">Bảng giá '.$ptname .' '. $k3 .' chỗ</th>
<th rowspan="5" class="w100p center" title="Chuyển đổi nhanh loại tiền tệ">Tiền tệ <hr><select
		data-target=".select-currency-'.$quotation['id'].'-'.$package['id'].'-'.$vb['id'].'"
		data-decimal="0" onchange="get_decimal_number(this);change_multi_currency_price(this);" class="sl-cost-price-currency form-control select2 input-sm" data-search="hidden" >';



						foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
							$html .= '<option value="'.$v2['id'].'">'.$v2['code'].'</option>';
						}
						//}
						//return json_encode($ckc_incurred);
						$html .= '</select></th><th rowspan="5" class="w100p"></th>
</tr>';
						if(!empty($v3)){
							$html .= '<tr>
									<th rowspan="5" class="center" style="min-width:200px">Chặng vận chuyển</th>
									';
							foreach ($v3 as $vehicle){
								$html .= '<th colspan="'.($colspan*count($incurred_prices_list) *count($incurred_prices_weekend_list_time) ).'" class="center">'.uh($vehicle['title']).'</th>';
							}
							$html .= '<th rowspan="5" class="w100p center" title="Chuyển đổi nhanh loại tiền tệ">Tiền tệ <hr><select
							data-target=".select-currency-'.$quotation['id'].'-'.$package['id'].'-'.$vb['id'].'"
									data-decimal="0" onchange="get_decimal_number(this);change_multi_currency_price(this);" class="sl-cost-price-currency form-control select2 input-sm" data-search="hidden" >';
							
							
							
						foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
							$html .= '<option value="'.$v2['id'].'">'.$v2['code'].'</option>';
						}
						//}
						//return json_encode($ckc_incurred);
						$html .= '</select></th><th rowspan="5" class="w100p"></th>';
							$html .= '</tr>';
						}
						$html .= '<tr class="'.(count($incurred_prices_list) > 1 && $ckc_incurred ? '' : 'hide').'">';
						if(!empty($v3)){foreach ($v3 as $vehicle){
						if(!empty($incurred_prices_list)){
							foreach ($incurred_prices_list as $in){
								$html .= '<th colspan="'.($colspan * count($incurred_prices_weekend_list_time)).'" class="center w200p">'.$in['title'].'</th>';
							}
						}}}

						$html .= '
</tr><tr class="'.(count($room_groups) > 1 ? ' ' : 'hide').'">';
						if(!empty($v3)){foreach ($v3 as $vehicle){
						if(!empty($incurred_prices_list)){
							foreach ($incurred_prices_list as $in){
								if(!empty($room_groups)){
									foreach ($room_groups as $room){
										$html .= '<th colspan="'.(count($incurred_prices_weekend_list) * count($incurred_prices_weekend_list_time)).'" class="center w200p"><a data-class="w70" data-supplier_id="'.$supplier_id.'" data-parent_id="'.(isset($v['id']) ? $v['id'] : 0).'" data-id="'.$room['id'].'" data-action="add-more-room-group" data-title="Thiết lập nhóm phòng" onclick="open_ajax_modal(this);" class="pointer hover_underline">'.$room['title'].($room['note'] != "" ? '<p><i class="f11p font-normal">('.$room['note'].')</i></p>' : '').'</a></th>';
									}
								}
							}}}}
							$html .= '</tr>';
							$html .= '<tr class="'.(count($incurred_prices_weekend_list) > 0 ? '' : 'hide').'">';
							if(!empty($v3)){foreach ($v3 as $vehicle){
							if(!empty($incurred_prices_list)){
								foreach ($incurred_prices_list as $in){
									if(!empty($room_groups)){
										foreach ($room_groups as $room){
											if(!empty($incurred_prices_weekend_list)){
												foreach ($incurred_prices_weekend_list as $weekend){
													$html .= '<th colspan="'.(count($incurred_prices_weekend_list_time)).'" class="center w200p"><i>'.$weekend['title'].'</i></th>';
												}
												//$html .= '<th colspan="'.(count($incurred_prices_weekend_list_time)).'" class="center w200p"><i>'.$weekend['title'].'</i></th>';
											}
										}
									}
								}}
							}}
								$html .='</tr>';
								$html .= '<tr class="'.(count($incurred_prices_weekend_list_time) > 1 ? '' : 'hide').'">';
								if(!empty($v3)){foreach ($v3 as $vehicle){
								if(!empty($incurred_prices_list)){
									foreach ($incurred_prices_list as $in){
										if(!empty($room_groups)){
											foreach ($room_groups as $room){
												if(!empty($incurred_prices_weekend_list)){
													foreach ($incurred_prices_weekend_list as $weekend){
														if(!empty($incurred_prices_weekend_list_time)){
															foreach ($incurred_prices_weekend_list_time as $weekend_time){
																$html .= '<th class="center w150p"><i>'.$weekend_time['title'].'</i></th>';
															}
														}
													}
												}
											}
										}
									}}
								}}
									$html .='</tr>';

									$html .= '</thead><tbody >';

									if(!empty($distances)){
										foreach ($distances as $k1=>$v1){
											$existed[] = $v1['id'];
											//$p = $menus->get_price($v1['id'],$id,$vb['id'],$package['id']);
											$currency = 1;
											$tr = [
													$supplier_id,
													$quotation['id'],
													$package['id'],
													$vb['id'],
													$v1['id']
											];
											$html .= '<tr class="tr-price-'.implode('-', $tr).'">
 
<td><a class="pointer" data-supplier_id="'.$supplier_id.'" data-menu_id="'.$v1['id'].'" data-title="Chỉnh sửa" data-class="w90" data-action="add-more-menu-supplier">'.uh($v1['title']).'</a></td>
'; 
											if(!empty($v3)){foreach ($v3 as $vehicle){
											if(!empty($incurred_prices_list)){
												foreach ($incurred_prices_list as $in){
													if(!empty($room_groups)){
														foreach ($room_groups as $room){
															if(!empty($incurred_prices_weekend_list)){
																foreach ($incurred_prices_weekend_list as $w){
																	if(!empty($incurred_prices_weekend_list_time)){
																		foreach ($incurred_prices_weekend_list_time as $weekend_time){

																			$price = \app\modules\admin\models\Customers::getSupplierDetailPrice([
																					'item_id'=>$v1['id'],
																					'season_id'=>$in['id'],
																					'weekend_id'=>$w['id'],
																					'group_id'=>$room['id'],
																					'supplier_id'=>$supplier_id,
																					'package_id'=>$package['id'],
																					'quotation_id'=>$quotation['id'],
																					'time_id'=>$weekend_time['id'],
																					'nationality_id'=>$vb['id'],
																					//'parent_group_id'=>$v1['parent_group_id']
																					'vehicle_id'=>$vehicle['id'],
																					'price_type'=>2
																			]);
																			//return json_encode($incurred_prices_list);
																			if(!empty($price)) $currency = $price['currency'];
																			$field = isset($w['type']) && $w['type']=='price2' ? 'price2' : 'price1';
																			$html .= '<td class="center"><input
											
																					data-field="'.($field).'"
											
											data-supplier_id="'.$supplier_id.'" data-price_type="2"
											data-quotation_id="'.$quotation['id'].'"
											data-package_id="'.$package['id'].'"
											data-nationality_id="'.$vb['id'].'"
											data-item_id="'.$v1['id'].'"
											data-vehicle_id="'.$vehicle['id'].'"
											data-season_id="'.$in['id'].'"
											data-group_id="'.$room['id'].'"
											data-weekend_id="'.$w['id'].'"
											data-time_id="'.$weekend_time['id'].'"
											data-supplier_type="'.$supplier['type_id'].'"
											onblur="quick_change_supplier_service_price(this);"
											type="text" name="pricesxxx['.$package['id'].']['.$vb['id'].']['.$v1['id'].'][list_child]['.$in['id'].']['.$room['id'].']['.$w['id'].'][price1]"
											value="'.(isset($price[$field]) ? $price[$field] : '').'"
											data-old="'.(isset($price[$field]) ? $price[$field] : '').'"
											class="form-control input-sm aright number-format w100 min-width-80px inline-block input-currency-price-'.$v1['id'].'" data-decimal="'.Yii::$app->zii->showCurrency($currency,3).'"/></td>';
																		}
																	}
																}
															}
														}
													}
												}}
											}}
												$html .= '<td class="center">';
												$html .= '<select
														data-controller_code="'.TYPE_ID_VECL.'"
														data-season_id="-1"
														data-group_id="-1"
														data-time_id="-1"
														data-weekend_id="-1"
														data-group_id="-1"
					data-supplier_id="'.$supplier_id.'"
					data-quotation_id="'.$quotation['id'].'"
					data-field="currency"
					data-package_id="'.$package['id'].'"
					data-nationality_id="'.$vb['id'].'"
					data-vehicle_id="-1"
					data-item_id="'.$v1['id'].'"
					data-price_type="2"
					data-decimal="'.Yii::$app->zii->showCurrency($currency,3).'" data-target-input=".input-currency-price-'.$v1['id'].'" onchange="get_decimal_number(this);quick_change_menu_price_currency(this);" class="ajax-select2-no-search sl-cost-price-currency form-control ajax-select2 input-sm select-currency-'.$quotation['id'].'-'.$package['id'].'-'.$vb['id'].'" data-search="hidden" name="pricesxxx['.$package['id'].']['.$vb['id'].']['.$v1['id'].'][currency]">';
												//if(isset($v['currency']['list']) && !empty($v['currency']['list'])){
												foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
													$html .= '<option value="'.$v2['id'].'" '.($currency == $v2['id'] ? 'selected' : '').'>'.$v2['code'].'</option>';
												}
												//} 

												$html .= '</select>';
												$html .= '</td>';
												$html .= '<td class="center">
<i data-supplier_id="'.$supplier_id.'"
				data-price_type="2"
					data-quotation_id="'.$quotation['id'].'"
					data-package_id="'.$package['id'].'"
					data-nationality_id="'.$vb['id'].'"
					data-item_id="'.$v1['id'].'"
					data-vehicle_id="'.$vehicle['id'].'"
					 
					data-confirm-text="<span class=red>Lưu ý: Bản ghi <b class=underline>'.$v1['title'].'</b> sẽ bị xóa khỏi toàn bộ các báo giá.</span>"
					class="pointer glyphicon glyphicon-trash btn-delete-item" data-id="'.$v1['id'].'" data-name="remove_menu" data-confirm-action="quick_change_menu_price_remove" data-action="open-confirm-dialog" data-class="modal-sm" data-title="Xác nhận xóa." onclick="open_ajax_modal(this);"></i>
</td>
</tr> ';
										}
									}

									$html .= '</tbody></table></div></div>';
						}
									$html .= '</div></div>';$html .= '</div></div>';
					}
				}
			}

			//


			$html .= '</div></div>';
		}

	} else{
		$html .= '<div class="col-sm-12"><p class="help-block red ">Bạn cần tạo báo giá trước khi nhập giá.</p></div>';
	}

	return $html;
}
function showFirstTitle($title = '', $length = 1){
	$titles = explode(' ', trim($title));
	$c = $b = [];
	foreach ($titles as $k=>$v){
		if($k<$length){
			$c[] = $v;
		}else {
			$b[] = $v;
		}
	}
	return '<span class="first-character">'.implode(' ',$c).'</span> ' . implode(' ', $b);
} 

function loadTourProgramDetail($o = []){
	$fields = isset($o['fields']) ? $o['fields'] : [];
	
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$print = isset($o['print']) && $o['print']== true ? true : false;
	$cols = 13; $scols= 0;
	$lang = isset($o['lang']) ? $o['lang'] : ADMIN_LANG;
	
	if($print){
		$cols --;
	}
	
	if((isset($fields['price']) && !$fields['price'])){
		--$cols;
	}
	if((isset($fields['amount']) && !$fields['amount'])){
		--$cols;
	}
	$scols = 12 - $cols;
	
	
	$html = '<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered table-sm vmiddle table-striped"> 
 
<colgroup class="dcols-'.$cols.'">';
	$w = 100/$cols;
	for($i = 0; $i< $cols;$i++){
		$html .= '<col style="width:'.$w.'%">'; 
	}
	$html .= '</colgroup>
<thead class="clear">


</thead> <tbody >

<tr class="col-middle bold">
 <th class="bold center">'.Yii::$app->t->translate('label_day',$lang).'</th>
 <th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="1" class="center col-ws-1">'.Yii::$app->t->translate('label_time',$lang).'</th>
 <th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="4" class="center col-ws-3">'.Yii::$app->t->translate('label_services',$lang).'</th>
 <th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="2" class="center col-ws-2">'.Yii::$app->t->translate('label_service_detail',$lang).'</th>
 <th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="1" class="center col-ws-1">'.Yii::$app->t->translate('label_service_type',$lang).'</th>
 <th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="1" class="center col-ws-1">'.Yii::$app->t->translate('label_short_unit',$lang).'</th>
 <th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="1" class="center col-ws-1">'.Yii::$app->t->translate('label_quantity',$lang).'</th>';
	if(!(isset($fields['price']) && !$fields['price'])){
		$html .= '<th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="1" class="center col-ws-1">'.Yii::$app->t->translate('label_unit_price',$lang).'</th>';
	}
	if(!(isset($fields['amount']) && !$fields['amount'])){
		$html .= '<th '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="1" class="center col-ws-1">'.Yii::$app->t->translate('label_amount',$lang).' (VND)</th>';
	}
  
if(!$print) $html .= '<th></th>';
$html .= '</tr>';
	
	$total_price = 0;
	$loadDefault= isset($o['loadDefault']) ? $o['loadDefault'] : false; 
	$updateDatabase= isset($o['updateDatabase']) && $o['updateDatabase']== false ? false : true; 
	
	
	
	
	$id = isset($o['id']) ? $o['id'] : 0;
	$day =isset($o['day']) ? $o['day'] : 0;
	
	$v = \app\modules\admin\models\ToursPrograms::getItem($id);
	$day = max($v['day'],$v['night']);
	//
	$v['from_date'] = check_date_string($v['from_date']) ? $v['from_date'] : date('Y-m-d');
	
	for($i = 0; $i<$day;$i++){
		$colspan2 = \app\modules\admin\models\ToursPrograms::countProgramServicesPerDay([
				'item_id'=>$id,
				'day_id'=>$i
		]);
		$_c = 0;
		for($j=0;$j<4;$j++){
			$servicesx[$j] = \app\modules\admin\models\ToursPrograms::getProgramServices($id,$i,$j);
			$_c += max(count($servicesx[$j]),1);
		}
			//$date = date('Y-m-d',strtotime($v['from_date']) + ($i * 86400) );
			$date =  date('Y-m-d', mktime(0, 0, 0, date("m",strtotime($v['from_date']))  , date("d",strtotime($v['from_date']))+$i, date("Y",strtotime($v['from_date']))));
		$colspan2  = ($_c + ($print ? 5 : 9)); 
		$html .= '<tr class="bgr-input-odd-event-'.($i%2).'">
<td rowspan="'.$colspan2.'" class="center" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>'.(check_date_string($v['from_date']) ? '<p>
<b class="green underline"> '.Yii::$app->t->translate('label_day',$lang).' '.($i+1).'</b></p>' : '').'
'.($print ? readDate($date,['spc'=>' <br> ','lang'=>$lang]): '<div class="label inline-block label-danger f12p">
<span>'.(check_date_string($v['from_date']) ?  readDate($date,['spc'=>' <br> ','lang'=>$lang]) : ''.Yii::$app->t->translate('label_day',$lang).' '.($i+1)).'</span>
</div>').'



</td></tr>';
		for($j=0;$j<4;$j++){
			$services = $servicesx[$j];
	
			$rowspan1 = max(count($services),1) + 1;
			switch ($j){
				case 1: $class='btn-success';break;
				case 2: $class='btn-info';break;
				case 3: $class='btn-warning';break;
				default: $class='btn-primary';break;
			}
			$html .= '<tr class="bgr-input-odd-event-'.($i%2).'">
<td rowspan="'.$rowspan1.'" class="center" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>
'.($print ? (showPartDay($j,$lang)) : '<button data-class="w95" data-action="add-tours-services" 
data-title="Chọn thêm dịch vụ / Hành trình" data-id="'.$id.'" data-day="'.$i.'" data-time="'.$j.'" 
onclick="open_ajax_modal(this);" title="Chọn thêm / xóa dịch vụ" class="w50p btn '.$class.' btn-label btn-sm first-letter-upper" 
type="button">'.(showPartDay($j,$lang)).'</button>').'


</td></tr>'; 
				 
			if(!empty($services)){
				foreach ($services as $kv=>$sv){
					$price = [];  
					//view($sv);
					$prices = Yii::$app->zii->getServiceDetailPrices([
							'item_id'=>$id,
							'day_id'=>$i,
							'time_id'=>$j,
							'service_id'=>$sv['id'],
							'package_id'=>$sv['package_id'],
							'type_id'=>$sv['type_id'],
							'nationality'=>$v['nationality'],
							'total_pax'=>$v['guest'],
							'from_date'=>$date,
							'sub_item_id'=>(isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0),
							'loadDefault'=>$loadDefault,
							'updateDatabase'=>$updateDatabase,
							'quantity'=>isset($sv['quantity']) ? $sv['quantity'] : 0,
					]);
					 
					
					 
					if(!empty($prices) && isset($prices['price1'])){
						$price = Yii::$app->zii->getServicePrice($prices['price1'],[
								'item_id'=>$id,
								//'price'=>$prices['price1'],
								'from'=>(isset($prices['currency']) ? $prices['currency'] : 1),
								'to'=>$v['currency']
						]);
						//view($prices);
					}
					$sub_item = Yii::$app->zii->getSupplierServiceDetail(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : 0,$sv['type_id']);

					if($sv['type_id'] == TYPE_ID_REST){

					}
					
					$package = \app\modules\admin\models\PackagePrices::getItem($sv['package_id']);
					$a = '<a href="#" '.(!in_array($sv['type_id'], [TYPE_ID_TEXT])).' 
									
'.(!$print ? ' onclick="open_ajax_modal(this);return false;" ' : '').'							
data-action="qedit-service-detail-day" 
									data-title="Chỉnh sửa dịch vụ" 
									data-class="w80"
									data-service_id="'.$sv['id'].'" 
									data-id="'.$v['id'].'"
									data-type_id="'.$sv['type_id'].'"	
									data-package_id="'.$sv['package_id'].'"		
									data-day_id="'.$i.'"	 
									data-time_id="'.$j.'"
									data-item_id="'.(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : (isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0)).'" 		 
									data-supplier_id="'.(isset($sv['supplier_id']) ? $sv['supplier_id'] : 0).'" 			
											>';
					$html .= '<tr class="bgr-input-odd-event-'.($i%2).'">
<td colspan="4" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>
<a 
							href="#" '.(!in_array($sv['type_id'], [TYPE_ID_TEXT])).'							
'.(!$print ? ' onclick="open_ajax_modal(this);return false;" ' : '').'
									data-class="w95" data-action="add-tours-services" 
data-title="Thay đổi dịch vụ" data-id="'.$id.'" data-day="'.$i.'" data-time="'.$j.'" 
onclick="open_ajax_modal(this);"			
											>';
switch ($sv['type_id']){
	case TYPE_ID_TRAIN:
		$ticket = \app\modules\admin\models\Tickets::getTrainTicketDetail($sv['id']);
		$html .= Yii::$app->t->translate($sv['lang_code'],$lang,['default'=>$ticket['title']]);
		break;
	case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
		$html .= (isset($prices['supplier']['title']) ?
		(isset($prices['supplier']['lang_code']) && $prices['supplier']['lang_code'] != "" ? Yii::$app->t->translate($prices['supplier']['lang_code'],$lang,['default'=> $prices['supplier']['title']]) : $prices['supplier']['title'])
		. (!empty($package) ? '&nbsp;<i class="green"> - '.uh($package['title']).'</i>&nbsp;' : ''):
		
		((isset($sv['lang_code']) && $sv['lang_code'] != "" ? Yii::$app->t->translate($sv['lang_code'],$lang,['default'=>(isset($sv['title']) ?
		uh($sv['title'] ) : uh($sv['name']))]) .$sv['id']: (isset($sv['title']) ?
		uh($sv['title']  ) : uh($sv['name'] .'')))
		).(isset($sv['supplier_name']) ?
		' <i class="underline font-normal green">['.uh($sv['supplier_name']).']</i>' : ''));
		break;	
	default:
		$html .= (isset($prices['supplier']['title']) ?
		(isset($prices['supplier']['lang_code']) && $prices['supplier']['lang_code'] != "" ? Yii::$app->t->translate($prices['supplier']['lang_code'],$lang,['default'=> $prices['supplier']['title']]) : $prices['supplier']['title'])
		:
		(!empty($package) ?
		'<i class="underline green">['.uh($package['title']).']</i>&nbsp;' : '')
		.((isset($sv['lang_code']) && $sv['lang_code'] != "" ? Yii::$app->t->translate($sv['lang_code'],$lang,['default'=>(isset($sv['title']) ?
		uh($sv['title'] ) : uh($sv['name']))]) : (isset($sv['title']) ?
		uh($sv['title'] ) : uh($sv['name'] .'')))
		).(isset($sv['supplier_name']) ?
		' <i class="underline font-normal green">['.uh($sv['supplier_name']).']</i>' : ''));
		break;
}


$html .= '</a> 
</td>
										<td class="'.(isset($sv['services']) && !empty($sv['services']) && count($sv['services'])>1 ? 'aleft' : 'center').' " colspan="2" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>';

$a2 = '<a href="#"
'.(!$print ? ' onclick="call_ajax_function(this);return false;" ' : '').'
									data-action="qedit-detail-service-detail-day" 
									data-title="Chỉnh sửa dịch vụ" 
									data-class="w80"
									data-service_id="'.$sv['id'].'" 
									data-id="'.$v['id'].'"
									data-type_id="'.$sv['type_id'].'"	
									data-package_id="'.$sv['package_id'].'"		
									data-day_id="'.$i.'"	 
									data-time_id="'.$j.'"
									data-item_id="'.(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : (isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0)).'" 		 
									data-supplier_id="'.(isset($sv['supplier_id']) ? $sv['supplier_id'] : 0).'" 
>';
					switch ($sv['type_id']){
						case TYPE_ID_SCEN:  
							$html .= $a2;
							if(!isset($prices['supplier']['title'])){
								$html .= (isset($sub_item['title']) ? $sub_item['title'] :  (!empty($package) ? '<i class="underline green">'.uh($package['title']).'</i>&nbsp;' : ''));
							}
							$html .= '</a>';
							break;
						case TYPE_ID_HOTEL: case TYPE_ID_SHIP_HOTEL:
							$html .= '<a href="javascript:void(0);"
'.(!$print ? ' onclick="call_ajax_function(this);return false;" ' : '').'
data-action="quick-change-service-day-detail-'.$sv['type_id'].'"
data-title="Chỉnh sửa dịch vụ"
data-class="w80"
data-service_id="'.$sv['id'].'"
data-id="'.$v['id'].'"
data-type_id="'.$sv['type_id'].'"
data-package_id="'.$sv['package_id'].'"
data-from_date="'.$date.'"
data-nationality_id="'.$v['nationality'].'"


data-quantity="'.$prices['quantity'].'"
data-currency="'.$prices['currency'].'"
data-day_id="'.$i.'"
data-time_id="'.$j.'"
data-item_id="'.(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : (isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0)).'"
data-supplier_id="'.(isset($sv['supplier_id']) ? $sv['supplier_id'] : 0).'"
		
title="Click để thay đổi hạng phòng" class="hover-pointer hover-underline aleft" >' . (isset($sub_item['title']) ? $sub_item['title'] : '' ). '';
							
							 
							$html .= '</a>';
							//if(isset($ticket['room']['title'])){
							
							if(isset($sv['services']) && !empty($sv['services']) && count($sv['services'])>1){
								foreach ($sv['services'] as $s){
									$html .= '<p class="pm0 aleft">+ <span class="underline">'.$s['title'].':</span> <span>'.$s['quantity'].' x '.getCurrencyText($s['price1'],$s['currency']).'</span></p>';
								}
							}
							break;
						case TYPE_ID_TRAIN:
							$html .= '<a href="javascript:void(0);"
'.(!$print ? ' onclick="call_ajax_function(this);return false;" ' : '').'
data-action="quick-change-service-day-detail-'.$sv['type_id'].'" 
data-title="Chỉnh sửa dịch vụ" 
data-class="w80"
data-service_id="'.$sv['id'].'" 
data-id="'.$v['id'].'"
data-type_id="'.$sv['type_id'].'"	
data-package_id="'.$sv['package_id'].'"
data-from_date="'.$date.'"
data-nationality_id="'.$v['nationality'].'"
data-station_from="'.$ticket['station_from'].'"
data-station_to="'.$ticket['station_to'].'"
data-quantity="'.$prices['quantity'].'"
data-currency="'.$prices['currency'].'"
data-day_id="'.$i.'"	 
data-time_id="'.$j.'"
data-item_id="'.(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : (isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0)).'" 		 
data-supplier_id="'.(isset($sv['supplier_id']) ? $sv['supplier_id'] : 0).'"

title="Click để thay đổi hạng phòng / ghế" class="hover-pointer hover-underline" >' . $ticket['supplier']['name'] . '';
							
							if(isset($ticket['room']['title'])){
								$html .= ' - ' . $ticket['room']['title'] .'';
							}
							$html .= '</a>';
							//if(isset($ticket['room']['title'])){
								
if(isset($sv['services']) && !empty($sv['services']) && count($sv['services'])>1){
	foreach ($sv['services'] as $s){
		$html .= '<p class="pm0 aleft">+ <span class="underline">'.$s['title'].':</span> <span>'.$s['quantity'].' x '.getCurrencyText($s['price1'],$s['currency']).'</span></p>';
	}
}
						//	}
							break;
							
						case TYPE_ID_REST:
							$html .= '<a href="javascript:void(0);"
'.(!$print ? ' onclick="call_ajax_function(this);return false;" ' : '').'
data-loading="fb2"
data-action="quick-change-service-day-detail-'.$sv['type_id'].'"
data-title="Chỉnh sửa dịch vụ"
data-class="w80"
data-service_id="'.$sv['id'].'"
data-id="'.$v['id'].'"
data-type_id="'.$sv['type_id'].'"
data-package_id="'.$sv['package_id'].'"
data-from_date="'.$date.'"
data-nationality_id="'.$v['nationality'].'"
		
data-price="'.(isset($prices['price1']) ? $prices['price1'] : 0).'"	
data-quantity="'.$prices['quantity'].'"
data-currency="'.$prices['currency'].'"
data-day_id="'.$i.'"
data-time_id="'.$j.'" 
data-item_id="'.(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : (isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0)).'"
data-supplier_id="'.(isset($sv['supplier_id']) ? $sv['supplier_id'] : 0).'"
		
title="Click để thay đổi thực đơn" class="hover-pointer hover-underline aleft" >' . (isset($sub_item['title']) ? $sub_item['title'] : '' ). '';
							
							
							$html .= '</a>';
							 
							break;
						default:
							
							$html .= $a2;
							$html .= (isset($sub_item['title']) ? $sub_item['title'] : '' ) .  (!empty($package) ? '<i class="underline green">&nbsp;['.uh($package['title']).']</i>&nbsp;' : '');
							
							$html .= '</a>';
							break;
					}
					
					$sub_total = (isset($price['price']) ? $price['price'] * $prices['quantity'] : 0);
					$total_price += $sub_total;
					
					$svx = \app\modules\admin\models\ToursPrograms::getProgramServiceDayDetail($id,$i,$j,$sv['id']); 
					//$html .= '</a>';
					
					
					if(isset($svx['note']) && $svx['note'] != ""){
						$html .= '<p class="pm0 italic text-muted f11px aleft">('.uh($svx['note']).')</p>'; 
					}
					$html .= '</td>			
<td class="center " colspan="1" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>
'.$a.getServiceType($sv['type_id'],$lang).'</a></td>
										<td class="center" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>
'.$a.getServiceUnitPrice($sv['type_id'],$lang).'</a></td>
										<td class="center" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>'.($prices['quantity'] > 0 ? $a.number_format($prices['quantity']) .'</a>' : '-').'</td>
'.(!(isset($fields['price']) && !$fields['price']) ? '<td class="aright" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').' >'.$a.'<span class="'.(isset($price['changed']) && $price['changed'] ? 'red underline' : '').'" title="'.(isset($price['changed']) && $price['changed'] ? $price['old_price'] : '').'">'.(isset($price['price']) && $price['price'] > 0 ? number_format($price['price'],(isset($price['decimal']) ? $price['decimal'] : 0)) : '-').'</span></a></td>' : '').'
'.(!(isset($fields['amount']) && !$fields['amount']) ? '<td class="aright" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').' ><span class="bold underline" >'.(isset($price['price']) && $price['price']>0 ? number_format($sub_total,(isset($price['decimal']) ? $price['decimal'] : 0)): '').'</span></td>' : '');
									
					if(!$print){ 
					$html .= '<td>
<div class="btn-group "> 
<button title="Thao tác với dịch vụ này" class="btn btn-warning btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
Thao tác <span class="caret"></span> </button>
<ul class="dropdown-menu dropdown-menu-right"> 

<li><a href="#"
data-action="qedit-service-detail-day" 
onclick="open_ajax_modal(this);return false;"
data-title="Chỉnh sửa dịch vụ" 
data-class="w80"
data-service_id="'.$sv['id'].'" 
data-id="'.$v['id'].'"
data-type_id="'.$sv['type_id'].'"	
data-package_id="'.$sv['package_id'].'"		
data-day_id="'.$i.'"	 
data-time_id="'.$j.'"
data-item_id="'.(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : (isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0)).'" 		 
data-supplier_id="'.(isset($sv['supplier_id']) ? $sv['supplier_id'] : 0).'"
><i class="fa fa-pencil mgr0i"></i>Thêm ghi chú</a></li> 

<li><a><i class="fa fa-terminal mgr0i"></i>Yêu cầu dịch vụ</a></li>
<li><a data-class="w95" 
data-action="add-tours-services" 
data-title="Chọn thêm dịch vụ / Hành trình" 
data-id="'.$id.'" 
data-day="'.$i.'" 
data-time="'.$j.'" 
onclick="open_ajax_modal(this);"><i class="fa fa-random mgr0i"></i>Thay đổi dịch vụ</a></li>
<li role="separator" class="divider"></li> 
<li><a href="#"
data-action="Tour_service_day_remove" 
onclick="call_ajax_function(this);return false;"
data-title="Chỉnh sửa dịch vụ" 
data-class="w80"
data-service_id="'.$sv['id'].'" 
data-id="'.$v['id'].'"
data-type_id="'.$sv['type_id'].'"	
data-package_id="'.$sv['package_id'].'"		
data-day_id="'.$i.'"	 
data-time_id="'.$j.'"
data-item_id="'.(isset($prices['sub_item_id']) ? $prices['sub_item_id'] : (isset($sv['sub_item_id']) ? $sv['sub_item_id'] : 0)).'" 		 
data-supplier_id="'.(isset($sv['supplier_id']) ? $sv['supplier_id'] : 0).'"
><i class="red fa fa-remove mgr0i"></i>Xóa dịch vụ</a></li>
</ul> 
</div>
</td>';
					}

$html .= '</tr>';
					
				}
				
			}else{
				//if($print) 
					$html .= '<tr><td colspan="'.$cols.'" '.(isset($inline_css) && $inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>-</td></tr>'; 
			}
				
			if(!$print){	
			$html .= '<tr class="bgr-input-odd-event-'.($i%2).'" '.($print ? 'style="height:0px"' : '').'>
<td colspan="'.$cols.'" class="pr vtop" '.($print ? 'style="height:0px; padding:0;border-width:0"' : '').'>'.($print ? '' : '<p class=" aright ">   
<button data-toggle="tooltip" data-placement="left" data-class="w95" data-action="add-tours-services" 
data-title="Chọn thêm dịch vụ / Hành trình" data-id="'.$id.'" data-day="'.$i.'" data-time="'.$j.'" onclick="open_ajax_modal(this);" 
title="Chọn thêm / xóa dịch vụ cho '.(showPartDay($j,$lang)).'" class="btn btn-primary btn-sm" type="button">
<i class="glyphicon glyphicon glyphicon-pencil"></i> Thay đổi dịch vụ <b class="underline">'.(showPartDay($j,$lang)).'</b></button></p>').'
							
</td>';
				 
			$html .= '</tr>';
			}
				
		}
			
	
	} 
	if($updateDatabase){
		Yii::$app->db->createCommand()->update(\app\modules\admin\models\ToursPrograms::tableName(), [
				'total_price1'	=>	$total_price,
		],[
				'id'=>$id,
		])->execute();
		\app\modules\admin\models\ToursPrograms::updatePrice($id);
	}
	
	$html .= '</tbody> </table>';
	
	return ['html'=>$html,'total_price'=>$total_price];
}

function genCustomerCode($regex = [],$i=0){
	// 
	//view($regex,true); 
	$table = isset($regex['table']) ? $regex['table'] : '{{%customers}}'; 
	if(isset($regex['auto_code']) && $regex['auto_code'] == 'on'){
		$length = isset($regex['code_length']) && $regex['code_length'] > 0 ? $regex['code_length'] : 6;
		$before= isset($regex['code_before']) ? $regex['code_before'] : '';
		$after= isset($regex['code_after']) ? $regex['code_after'] : '';
		$code_regex = isset($regex['code_regex']) && strlen($regex['code_regex']) > 0 ? $regex['code_regex'] : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
		$sort_asc = isset($regex['sort_asc']) && $regex['sort_asc'] == 'on' ? true : false;
		$code_length = $length - (strlen($before) + strlen($after));
		if(!$sort_asc){
			$code = randString($code_length,$code_regex);
		}else{
			$controller_code= isset($regex['controller_code']) ? $regex['controller_code'] : 0;
			$sql = "select max(";
			if($before == "" && $after == ""){
				$sql .= "`code`";
			}else{
				//if($before != "" && $after != ""){
					$sql .= "replace(replace(`code`,'$after',''),'$before','')";
				//}
			}//
			$sql .= ") from $table where sid=".__SID__;
			$code = Yii::$app->db->createCommand($sql)->queryScalar();
			$code = danhso(($code > 0 ? $code : 0) + 1,$code_length);
			
		}
		
		$code = $before. $code . $after;
		if((new \yii\db\Query())->from($table)->where(['code'=>$code,'sid'=>__SID__])->count(1) >0){
			if($i > substr('999999999999999999999', 0,$code_length)){
				return false;
			}
			return genCustomerCode($regex,++$i);
		}
		return $code;
	}
	return false;
}
function parsePhoneNumber($phone, $country_code = 84){ 
	$phone = str_replace(' ', '', $phone);
	if(substr($phone, 0,1) != '+'){
		return '+' . $country_code . $phone;
	}
	return $phone;
}
function splitName($f){
	$nameArray = ['full_name','fullname','fullName'];
	$spName = isset($f['fname']) ? $f['fname'] : '' ;
	foreach ($nameArray as $name){
		if(isset($f[$name])){
			$spName = $f[$name]; unset($f[$name]);
		}
	}
	if(isset($f['name'])){
		$spName = $f['name'];
	}
	//
	if(isset($f['lname']) && $f['lname'] != ""){
		//return $f;
	}else{
		$n = explode(' ', trim($spName));
		$f['fname'] = $n[count($n)-1];
		if(count($n) > 1){
			unset($n[count($n)-1]);
			$f['lname'] = implode(' ', $n);
		}else{
			$f['lname'] = '';
		}
	}
	if(!isset($f['name'])){
		$f['name'] = $f['lname'] . ' ' . $f['fname'];
	}
	//
	if(isset($f['birth_year']) && isset($f['birth_month']) && isset($f['birth_day'])){
		$f['birth'] = $f['birth_year'] . '-' . $f['birth_month'] . '-' .$f['birth_day']; 
		unset($f['birth_year']);
		unset($f['birth_month']);
		unset($f['birth_day']);
	}
	if(isset($f['phone'])){
		if(!in_array(substr($f['phone'], 0,1), ['0','+'])){
			$f['phone'] = '0' . $f['phone'];
		}
	}
	return $f;
}

function getCustomerTypeID(){
	$r = [];
	foreach (customersTypeID() as $v){
		if((new yii\db\Query())->from('customer_type_to_templete')->where(['temp_id'=>__TCID__,'customer_type_id'=>$v['id']])->count(1)>0){
			$r[] = $v;
		}
	}
	return $r;
}
function getTypeID(){
	
}
function customersTypeID(){
	return [
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng','route'=>'customers'],
			['id'=>TYPE_ID_PART,'title'=>'Đối tác','route'=>'partners'],
			['id'=>TYPE_ID_TEA,'title'=>'Giáo viên','route'=>'teachers'],
			['id'=>TYPE_ID_AST,'title'=>'Trợ giảng','route'=>'teachers'],
			['id'=>TYPE_ID_COACHES,'title'=>'Huấn luyện viên','route'=>'coaches'],
		//	['id'=>TYPE_ID_STUDENTS,'title'=>'Học viên'],
			['id'=>TYPE_ID_MEMBERS,'title'=>'Thành viên','route'=>'members'],
		/*/	['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			['id'=>TYPE_ID_CUS,'title'=>'Khách hàng'],
			/*/
	];
}

function getLocalType($type_id = -1){
	$r = [
			['id'=>0,'title'=>'Quốc gia'],
			['id'=>1,'title'=>'Tỉnh'],
			['id'=>2,'title'=>'Thành phố'],
			['id'=>3,'title'=>'Huyện'],
			['id'=>4,'title'=>'Quận'],
			['id'=>5,'title'=>'Thị xã'],
			['id'=>6,'title'=>'Xã'],
			['id'=>7,'title'=>'Phường'],
			['id'=>8,'title'=>'Thị trấn'],
	];
	if(is_numeric($type_id) && $type_id > -1){
		foreach ($r as $v){
			if($v['id'] == $type_id){
				return $v['title'] ; break;
			}
		}
	}
	return $r;
}


function showLocalType($type_id = -1){
	$r = [
			['id'=>0,'title'=>''],
			['id'=>1,'title'=>'Tỉnh '],
			['id'=>2,'title'=>'TP '],
			['id'=>3,'title'=>'Huyện '],
			['id'=>4,'title'=>'Quận '],
			['id'=>5,'title'=>'Thị Xã '],
			['id'=>6,'title'=>'Xã '],
			['id'=>7,'title'=>'Phường '],
			['id'=>8,'title'=>'Thị Trấn '],
	];
	if(is_numeric($type_id) && $type_id > -1){
		foreach ($r as $v){
			if($v['id'] == $type_id){
				return $v['title'] ; break;
			}
		}
	}
	return $r;
}

function showLocalName($name = '', $type_id = 0, $show_full = false){
	$r = [
			['id'=>0,'title'=>'','short'=>''],
			['id'=>1,'title'=>'Tỉnh ','short'=>'T'],
			['id'=>2,'title'=>'Thành Phố ','short'=>'TP'],
			['id'=>3,'title'=>'Huyện ','short'=>'H'],
			['id'=>4,'title'=>'Quận ','short'=>'Q'],
			['id'=>5,'title'=>'Thị Xã ','short'=>'TX'],
			['id'=>6,'title'=>'Xã ','short'=>'X'],
			['id'=>7,'title'=>'Phường ','short'=>'P'],
			['id'=>8,'title'=>'Thị Trấn ','short'=>'TT'],
	];
	if(!is_numeric($type_id)){
		return $name;
	}
	if(is_numeric($type_id) && $type_id > -1){
		foreach ($r as $v){
			if($v['id'] == $type_id){
				if($show_full){
					return $v['title'] . $name;
				}else{
					return $v['short'] != "" ? $v['short'] .(
							is_numeric($name) ? $name : ($name != '-' ? '. ' . $name : '')
							) : ($name != '-' ? $name : '');
					
					//return $v['short'] . (is_numeric($name) ? $name : ($name != '-' ? '. ' . $name : ''));
				}
				 
				break;
			}
		}
	}
	return $r;
}
function dirToArray($dir, $r = []) {
	
	if(!is_dir($dir)) return false;
	$result = array();

	$cdir = scandir($dir);
	foreach ($cdir as $key => $value)
	{
		if (!in_array($value,array(".","..")))
		{
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
			{
				//$r[] =$dir . DIRECTORY_SEPARATOR . $value; 
				$r = dirToArray($dir . DIRECTORY_SEPARATOR . $value,$r);
			}
			else
			{
				$r[] = $dir . DIRECTORY_SEPARATOR .$value;
			}
		}
	}
	 
	return $r;
}

function copyToRemoteServer($o = []){
	$dir = isset($o['source']) ? $o['source'] : '';
	$dest = isset($o['dest']) ? $o['dest'] : '/';
	$config = isset($o['config']) ? $o['config'] : [];
	//
	$ftp = new \yii\web\FtpUpload($config);
	//view($config);
	//view($ftp->testConnected()); 
	if($ftp->testConnected()){		 	 
		if(!is_dir($dir)){
			$ftp->nfileupload($dir, $dest);
		}else{
			$cdir = scandir($dir);
			foreach ($cdir as $key => $value)
			{
				if (!in_array($value,array(".","..")))
				{
					if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
					{
						$o['source'] = $dir . DIRECTORY_SEPARATOR . $value;
						$result[$value] = copyToRemoteServer($o);
					}
					else
					{
						$result[] = $value;
						$fp = $value;
					//	view($fp);
					}
				}
			}
		}
	}
}
 
function configPartTime(){
	return [
			0=>['from_time'=>'00:00:00','to_time'=>'09:59:59','title' => 'Sáng'],
			1=>['from_time'=>'10:00:00','to_time'=>'13:59:59','title' => 'Trưa'],
			2=>['from_time'=>'14:00:00','to_time'=>'17:59:59','title' => 'Chiều'],
			3=>['from_time'=>'18:00:00','to_time'=>'23:59:59','title' => 'Tối'],
	];
}
function getClientIP(){
	if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	{
		$ip=$_SERVER['HTTP_CLIENT_IP'];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	{
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else
	{
		$ip=$_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}


function loadTourProgramDistances($id = 0,$o=[]){
	$item_id = $id; $places_id = [];
	$print = isset($o['print']) && $o['print'] == true ? true : false;
	$fields = isset($o['fields']) ? $o['fields'] : [];
	$loadDefault = isset($o['loadDefault']) && cbool($o['loadDefault']) == 1 ? true : false;
	$updateDatabase = isset($o['updateDatabase']) && cbool($o['updateDatabase']) == 1 ? true : false;
	$item = \app\modules\admin\models\ToursPrograms::getItem($id);
	$segment = isset($o['segment']) ? $o['segment'] : [];
	$package_id = isset($o['package_id']) ? $o['package_id'] : 0;
	$total_price = 0;
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$cols = 12; $scols= 0;
	if((isset($fields['distance']) && !$fields['distance'])){
		$cols --; 
	}
	if((isset($fields['price']) && !$fields['price'])){
		$cols --;
	}
	if((isset($fields['amount']) && !$fields['amount'])){
		$cols --;
	}
	$scols = 12 - $cols;
	//$mcol = 
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$html = '<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered mgb0 table-sm vmiddle"> 
 
<colgroup>';
	$w = 100/$cols;
	for($i = 0; $i< $cols;$i++){
		$html .= '<col style="width:'.$w.'%">';
	}
	$html .= '</colgroup>
<thead>

<tr class="col-middle">
 <th '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' class="bold center">Nhà xe</th>
 <th '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' class="bold center " colspan="2" style="width:16.66666%">Loại xe</th>
 <th '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' class="bold center">Số lượng<p class="center font-normal italic">(1)</p></th>
 <th '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' colspan="5" > 
 <p class="bold center">Chặng di chuyển</p>
</th>';
	if(!(isset($fields['distance']) && !$fields['distance'])){
		$html .= '<th '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').' class="bold center">Km<p class="center font-normal italic">(2)</p></th>';
	}

	if(!(isset($fields['price']) && !$fields['price'])){
		$html .= '<th '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').' class="bold aright">Đơn giá ('.Yii::$app->zii->showCurrency(isset($item['currency']) ? $item['currency'] : 1).')<p class="center font-normal italic">(3)</p></th>';
	}
	
	if(!(isset($fields['amount']) && !$fields['amount'])){
		$html .= '<th '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').' class="bold aright">Thành tiền <p class="center font-normal italic">(1) x (2) x (3)</p></th> ';
	}


 
$html .= '</tr>
</thead> <tbody class="ajax-load-distance-detail" data-count="0">';	 
	
	$j=$i=-1;
	//view($segment);
	foreach (Yii::$app->zii->getTourProgramSuppliers($id,['segment_id'=>(isset($segment['id']) ? $segment['id'] : 0)]) as $k=>$v){
		//\\//\\ *.* //\\//\\
		$supplier_id = $v['id'];
		$from_date = $item['from_date'];
		$places_id[] = $v['place_id'];
		$quotation = \app\modules\admin\models\Suppliers::getQuotation([
				'supplier_id'=>$supplier_id,
				'date'=>$from_date
		]);
		//view($quotation);
		//
		$nationality_group = \app\modules\admin\models\Suppliers::getNationalityGroup([
				'supplier_id'=>$supplier_id,
				'nationality_id'=>$item['nationality'],
		]);
		//
		$seasons = \app\modules\admin\models\Suppliers::getSeasons([
				'supplier_id'=>$supplier_id,
		
				'date'=>$from_date,
				//'time_id'=>$time_id
		]);
		$groups = \app\modules\admin\models\Suppliers::getGuestGroup([
				'supplier_id'=>$supplier_id,
				'total_pax'=>$item['guest'],
				'date'=>$from_date,
				//'time_id'=>$time_id
		]);
		
		//
		 
		$selected_car = Yii::$app->zii->getSelectedVehicles([
				'total_pax'=>$item['guest'],
				'nationality_id'=>$item['nationality'],
				'supplier_id'=>$v['id'],
				'item_id'=>$id,
				'segment_id'=>$segment['id'],
				'default'=>true,
				'loadDefault'=>$loadDefault,
				'updateDatabase'=>$updateDatabase
				////'auto'=>true,
				//'update'=>true,
		]);
		//view($selected_car);
		$services = \app\modules\admin\models\ToursPrograms::getProgramDistanceServices($id,$v['id'],[
				'segment_id'=>$segment['id']
		]);
		$colspan1 = count($services)+1;
		$colspan2 = (($colspan1) * count($selected_car)) + 1;
			
			
			
		$html .= '<tr>
<td rowspan="'.$colspan2.'" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>
'.($print ? '<b>'.($v['name']).'</b>' :
		'
<div class="btn-group">
  
  <button type="button" 
title="Click để hiển thị thêm thao tác"
class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
'.($v['name']).'    
<span class="caret"></span>
    <span class="sr-only">Toggle Dropdown</span>
  </button>
  <ul class="dropdown-menu">
<li><a href="#"
data-segment_id="'.$segment['id'].'" 
data-item_id="'.$id.'"
data-nationality="'.$item['nationality'].'" 
data-action="quick-edit-supplier-services" 
data-supplier_id="'.$v['id'].'" 
data-class="w90" 
onclick="open_ajax_modal(this);return false;" 
data-title="Chỉnh sửa thông tin <b class=red>'.($v['name']).'</b>"
><i class="fa fa-car mgr0i"></i> Thay đổi loại xe</a></li>
<li><a href="#"><i class="fa fa-random mgr0i"></i> Thay đổi chặng di chuyển</a></li>
<li><a href="#"><i class="fa fa-refresh mgr0i"></i> Đổi sang nhà xe khác</a></li>
<li><a href="#"><i class="fa fa-terminal mgr0i"></i> Yêu cầu dịch vụ</a></li>
<li><a href="#"><i class="fa fa-pencil mgr0i"></i> Thêm ghi chú</a></li>    
    <li role="separator" class="divider"></li>
<li><a href="#"><i class="fa fa-remove mgr0i red"></i> Xóa nhà xe này</a></li>
  </ul>
</div>

<button 
data-segment_id="'.$segment['id'].'" 
class="btn btn-sm btn-label btn-primary hide" 
type="button" data-item_id="'.$id.'"
data-nationality="'.$item['nationality'].'" 
data-action="quick-edit-supplier-services" 
data-supplier_id="'.$v['id'].'" 
data-class="w90" 
onclick="open_ajax_modal(this);return false;" 
data-title="Chỉnh sửa thông tin <b class=red>'.($v['name']).'</b>"
>'.($v['name']).'</button>').'
';
			
		///$html .= '';
			
		$html .= '</td></tr>';
		//for($j=0;$j<4;$j++){
		foreach ($selected_car as $k3=>$car){
			 
			$html .= '<tr>
<td class="center" rowspan="'.($k3 == count($selected_car)-1 ? ($colspan1) : $colspan1).'" colspan="2" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>
'.($print ? '<span class="f12p">'.$car['title'].'</span>' : 
		'
<div class="btn-group">
  
  <button type="button" 
title="Click để hiển thị thêm thao tác"
class="btn btn-sm dropdown-toggle btn-'.($k3 % 2 == 0 ? 'danger' : 'warning').'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
'.$car['title'].'    
<span class="caret"></span>
    <span class="sr-only">Toggle Dropdown</span>
  </button>
  <ul class="dropdown-menu">
<li><a href="#"
data-segment_id="'.$segment['id'].'" 
data-item_id="'.$id.'"
data-nationality="'.$item['nationality'].'" 
data-action="quick-edit-supplier-services" 
data-supplier_id="'.$v['id'].'" 
data-class="w90" 
onclick="open_ajax_modal(this);return false;" 
data-title="Chỉnh sửa thông tin <b class=red>'.($v['name']).'</b>"
><i class="fa fa-refresh mgr0i"></i> Đổi xe</a></li>
<li><a href="#"><i class="fa fa-random mgr0i"></i> Thay đổi chặng di chuyển</a></li>
 
    
    <li role="separator" class="divider"></li>
<li><a href="#"><i class="fa fa-remove mgr0i red"></i> Xóa xe này</a></li>
  </ul>
</div>


<button class="btn btn-sm btn-label hide btn-'.($k3 % 2 == 0 ? 'danger' : 'warning').'" 
data-segment_id="'.$segment['id'].'" 
data-item_id="'.$id.'" 
data-nationality="'.$item['nationality'].'" 
data-action="quick-edit-supplier-services" 
data-supplier_id="'.$v['id'].'" 
data-class="w90" 
onclick="open_ajax_modal(this);return false;" 
data-title="Chỉnh sửa thông tin <b class=red>'.($v['name']).'</b>"
><span class="f12p">'.$car['title'].'</span></button>').'


</td>';
			$html .= '<td class="center" rowspan="'.($k3 == count($selected_car)-1 ? ($colspan1) : $colspan1).'" colspan="1" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>

'.($print ? '<span class="">'.(isset($car['quantity']) ? $car['quantity'] : 0).'</span>' : '<a data-segment_id="'.$segment['id'].'" data-item_id="'.$id.'" data-nationality="'.$item['nationality'].'" data-action="quick-edit-supplier-services" data-supplier_id="'.$v['id'].'" data-class="w90" href="#" onclick="open_ajax_modal(this);return false;" data-title="Chỉnh sửa thông tin <b class=red>'.($v['name']).'</b>"><span class="badge">'.(isset($car['quantity']) ? $car['quantity'] : 0).'</span></a>').'


</td>';
	
			$html .= '</tr>';
			if(!empty($services)){
				foreach ($services as $kv=>$sv){
					//
					$distance = isset($sv['distance']) && $sv['distance'] > 0 ? $sv['distance'] : -1;
					$prices = Yii::$app->zii->calcDistancePrice([
							//'supplier_id'=>$v['id'],
							'vehicle_id'=>$car['id'],
							'distance_id'=>$sv['id'],
							'item_id'=>$id,
							'quotation_id'=>isset($quotation['id']) ? $quotation['id'] : 0,
							'nationality_id'=>isset($nationality_group['id']) ? $nationality_group['id'] : 0,
							'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
							'supplier_id'=>$supplier_id,
							'total_pax'=>$item['guest'],
							'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
							//'package_id'=>0,
							'group_id'=>isset($groups['id']) ? $groups['id'] : 0, 
							'loadDefault'=>$loadDefault,
							'updateDatabase'=>$updateDatabase,
							'segment_id'=>$segment['id'],
					]);
					//view($prices);
					if(!empty($prices) && isset($prices['price1'])){
						$price = Yii::$app->zii->getServicePrice($prices['price1'],[
								'item_id'=>$id,
								//'price'=>$prices['price1'],
								'from'=>$prices['currency'],
								'to'=>$item['currency']
						]);
					}
					//
					$sub_total = (isset($price['price']) ? $price['price'] * $prices['quantity'] * $car['quantity'] : 0);
					$total_price += $sub_total;
					$html .= '<tr>
<td colspan="5" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'> 
<a data-segment_id="'.$segment['id'].'" data-item_id="'.$id.'" data-vehicle_id="'.$car['id'].'" 
data-supplier_id="'.$v['id'].'" data-service_id="'.$sv['id'].'" 
data-class="w80" data-action="qedit-service-detail" data-title="Chỉnh sửa dịch vụ" href="#" 
'.($print ? '' : 'onclick="open_ajax_modal(this);return false;"').'

>  ' .(isset($sv['title']) ? uh($sv['title']) : uh($sv['name'])).(isset($sv['supplier_name']) ? ' <i class="underline font-normal green">['.uh($sv['supplier_name']).']</i>' : '').'
									<input value="'.$sv['id'].'" type="hidden" class="selected_value_'.$sv['type_id'].' selected_value_'.$sv['type_id'].'_'.$i.'_'.$j.' selected_value_'.$sv['type_id'].'_'.$i.'_'.$j.'_'.$segment['id'].'" name="selected_value[]"/>
									<input value="'.$sv['type_id'].'" type="hidden" class="selected_value_'.$sv['type_id'].'" name="selected_type_id[]"/>
											</a></td>';
					if(!(isset($fields['distance']) && !$fields['distance'])){
					$html .= '<td class="center" colspan="1" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>'.($prices['price_type'] == 1 ? '<a data-segment_id="'.$segment['id'].'" data-item_id="'.$id.'" data-vehicle_id="'.$car['id'].'" data-supplier_id="'.$v['id'].'" data-service_id="'.$sv['id'].'" data-class="w80" data-action="qedit-service-detail" data-title="Chỉnh sửa dịch vụ" href="#" 
'.($print ? '' : 'onclick="open_ajax_modal(this);return false;"').'
>'.(is_numeric($prices['quantity']) ? number_format($prices['quantity']) : '') .'</a>' : '-').'</td>';
					}
					if(!(isset($fields['price']) && !$fields['price'])){
					$html .= '<td class="aright"  '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><span data-decimal="'.(isset($price['decimal']) ? $price['decimal'] : 0).'" class="number-format '.(isset($price['changed']) && $price['changed'] ? 'red underline' : '').'" title="'.(isset($price['changed']) && $price['changed'] ? $price['old_price'] : '').'">'.(isset($price['price']) ? $price['price'] : '-').'</span></td>';
					}
					if(!(isset($fields['amount']) && !$fields['amount'])){
					$html .= '<td class="aright"  '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><span data-decimal="'.(isset($price['decimal']) ? $price['decimal'] : 0).'" class="bold underline number-format " >'.(isset($price['price']) ? $price['price'] * $prices['quantity'] * $car['quantity'] : '-').'</span></td>';
					}
					
					$html .= '</tr>';
				}
			}
		}
	
			
		//}
			
		//	$html .= '<td class="center" colspan="1">'.$selected_car['quantity'].'</td>';
		//	$html .= '<td class="center" colspan="1">'.$selected_car['quantity'].'</td>';
		//	$html .= '<td class="center" colspan="1">'.$selected_car['quantity'].'</td>';
	
	
	if(!$print){
		$html .= '<tr><td colspan="12" class="pr vtop">
						<p class=" aright">
							<button data-segment_id="'.$segment['id'].'" data-place_id="'.$v['place_id'].'" data-class="w90" data-action="add-tours-distance-services" data-title="Chọn thêm dịch vụ / Hành trình - <b class=red>'.$v['name'].'</b>" data-id="'.$id.'" data-supplier_id="'.$v['id'].'" data-time="'.$j.'" onclick="open_ajax_modal(this);" data-toggle="tooltip" data-placement="left" title="Chọn thêm / xóa dịch vụ cho '.$v['name'].'" class="btn btn-primary input-sm" type="button"><i class="glyphicon glyphicon glyphicon-pencil"></i> Thêm/ xóa chặng di chuyển</button></p>
						</td></tr>';
	}
			
	}
	if(!$print){
		$html .= '<tr><td colspan="12" class="pr vtop">
						<p class=" aright ">
			
							<button data-segment_id="'.(isset($segment['id']) ? $segment['id'] : 0).'" data-toggle="tooltip" data-placement="left" data-nationality="'.$item['nationality'].'" data-guest="'.$item['guest'].'" data-class="w90" data-action="add-more-distance-supplier" data-title="Chọn thêm nhà xe" data-id="'.$id.'" onclick="open_ajax_modal(this);" title="Chọn thêm nhà xe'.(!empty($segment) ? ' cho chặng '. uh($segment['title']) : '').'" class="btn btn-success input-sm" type="button"><i class="fa fa-bus"></i> Chọn thêm nhà xe</button>
									</p>
						</td></tr>';
	}
	
	$html .= '</tbody> </table>';
	
	
	return ['html'=>$html,'total_price'=>$total_price];
}

function getGuideTypeName($type = 1){
	switch ($type){
		case 1:
			return 'HDV suốt tuyến';
			break;
		case 2: return 'HDV chặng'; break;
		default: return 'Chưa xác định'; break;
	}
}
function discountPrice($price2 = 0, $price1 = 0, $price_type = 0){ // 0: % 
	if($price1 > $price2 && $price2 > 0){
		$du = $price1 - $price2;
		$pt = $du * 100 / $price1; 
		 
		return '<div class="onsale">' . number_format((-1) * $pt,(strpos($pt, '.') === false ? 0 : 1)) . '% </div>';
	}
	return '';
}

function array_sort($array, $on, $order=SORT_ASC) 
{
	$new_array = array();
	$sortable_array = array();

	if (count($array) > 0) {
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $k2 => $v2) {
					if ($k2 == $on) {
						$sortable_array[$k] = $v2;
					}
				}
			} else {
				$sortable_array[$k] = $v;
			}
		}

		switch ($order) {
			case SORT_ASC:
				asort($sortable_array);
				break;
			case SORT_DESC:
				arsort($sortable_array);
				break;
		}

		foreach ($sortable_array as $k => $v) {
			$new_array[$k] = $array[$k];
		}
	}

	return $new_array;
}

function countDownDayExpired($time){
	if(!is_numeric($time)){
		$time = ctime(['string'=>$time,'return_type'=>1]);
	}
	return ceil(($time - time())/86400);	
}

function getTourProgramSegments($item_id=0, $o = []){
	//
	$print = isset($o['print']) && $o['print'] == true ? true : false;
	$fields = isset($o['fields']) ? $o['fields'] : [];
	$total_price = 0;
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$label= isset($o['label']) ? $o['label'] : 'Chặng vận chuyển';
	$sub_label = isset($o['sub_label']) ? $o['sub_label'] : '';
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$lang = isset($o['lang']) ? $o['lang'] : ADMIN_LANG;
	
	$item_id = is_numeric($item_id) ? $item_id : (isset($o['item_id']) ? $o['item_id'] : 0);	
	//
	$segments = \app\modules\admin\models\ProgramSegments::getAll($item_id,['parent_id'=>0]);
	// 
	$item = \app\modules\admin\models\ToursPrograms::getItem($item_id);
	$html = '<div class="col-sm-12 bang-thong-tin-chung"><div class="row">';
	
	if(!$label){}else{
	$html .= '<div class="" style="margin-top: 10px; "> 
'.($print ? '<p class="upper bold grid-sui-pheader aleft " '.($inline_css ? 'style="font-weight: bold;border: 1px solid #ddd;line-height: 30px;border-bottom: none;background-color: #dedede;padding-left: 15px;"' : '').'>'.$label.'</p>' 
		:'<div class=" bold grid-sui-pheader aleft pr f14px"><span class="upper">Thiết lập chặng tour <i class="font-normal">(tối đa 2 cấp)</i></span>
<div class="btn-group ps r0"> 
<button title="Thao tác với dịch vụ này" class="btn btn-default btn-sm dropdown-toggle " type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
<i class="fa fa-cog"></i> Thao tác <span class="caret"></span> </button>
<ul class="dropdown-menu dropdown-menu-right"> 

<li><a href="#" 
data-class="w60" 
data-index="'.count($segments).'" 
data-placement="top" 
data-action="add-more-tour-segment" 
data-title="Thêm chặng tour" 
data-id="'.$item_id.'" 
data-item_id="'.$item_id.'" 
onclick="open_ajax_modal(this);return false;"><i class="fa fa-pencil mgr0i"></i>Thêm chặng tour</a></li> 

<li>
<a href="#" 
data-class="w60" 
data-index="'.count($segments).'" 
data-placement="top" 
data-action="add-more-tour-segment" 
data-title="Thêm chặng tour" 
data-id="'.$item_id.'" 
data-item_id="'.$item_id.'" 
onclick="open_ajax_modal(this);return false;"><i class="fa fa-terminal mgr0i"></i>Xem danh sách chặng</a>
</li>
</ul> 
</div>

</div>').'

</div>';
	}
	
	
	if(!empty($segments)){ 
		foreach ($segments as $km=>$segment){
			$segments1 = \app\modules\admin\models\ProgramSegments::getAll($item_id,['parent_id'=>$segment['id']]);
			 
			$html .= '<div class="block-examples"> 
'.($print ? ' <p 
class="upper bold block-title"  '.($inline_css ? 'style="font-weight: bold;border: 1px solid #ddd;line-height: 30px;border-bottom: none;background-color: #dedede;padding-left: 15px;margin-bottom:0"' : '').'>
<i class="fa fa-puzzle-piece">'.($sub_label != '' ? ' </i> '.uh($sub_label) : ' </i> '.uh($segment['title']).' <i>('.$segment['number_of_day'].' '.Yii::$app->t->translate('label_day',$lang).')</i>').'</p>' : '<p data-class="w60" data-toggle="tooltip" data-placement="top" data-action="add-more-tour-segment" 
data-title="Chỉnh sửa chặng tour" data-parent_id="'.$segment['parent_id'].'" 
data-segment_id="'.$segment['id'].'" data-id="'.$item_id.'" data-item_id="'.$item_id.'" onclick="open_ajax_modal(this);" 
title="Click để chỉnh sửa chặng: '.uh($segment['title']).'" 
class="pointer upper bold block-title">
<i class="fa fa-puzzle-piece"></i> '.uh($segment['title']).' <i>('.$segment['number_of_day'].' '.Yii::$app->t->translate('label_day',$lang).')</i></p>').'
					
					<div>';
			if(!empty($segments1)){
				foreach ($segments1 as $segment1){
					$html .= '<div class="bc1 block-example bdb0 pdb0 bdl0 bdr0'.($km%2==1 ? ' bg-success' : '').'">
'.($print ? '<span 
title="'.uh($segment1['title']).'" 
class="f12e block-title btn-sm btn btn-default mgl0 bdl1">
<i class="fa fa-hand-o-right"></i> '.uh($segment1['title']).' <i>('.$segment1['number_of_day'].' '.Yii::$app->t->translate('label_day',$lang).')</i></span>' : '<span 
data-class="w60" data-toggle="tooltip" data-placement="right" 
data-action="add-more-tour-segment" data-title="Chỉnh sửa chặng tour" 
data-parent_id="'.$segment1['parent_id'].'" 
data-segment_id="'.$segment1['id'].'" 
data-id="'.$item_id.'" data-item_id="'.$item_id.'" 
onclick="open_ajax_modal(this);" 
title="Click để chỉnh sửa chặng: '.uh($segment1['title']).'" 
class="f12e block-title btn-sm btn btn-default mgl0 bdl1">
<i class="fa fa-hand-o-right"></i> '.uh($segment1['title']).' <i>('.$segment1['number_of_day'].' '.Yii::$app->t->translate('label_day',$lang).')</i></span>');
					$html .= '<div class="form-group mgb0">';
					$c = loadTourProgramDistances($item_id,['inline_css'=>$inline_css, 'segment'=>$segment1,'print'=>$print,'fields'=>$fields]);
					$html .= $c['html'];
					$total_price += $c['total_price'];
					$html .= '</div>'; 
					$html .= '</div>';
				} 
			}else{
				$c = loadTourProgramDistances($item_id,['inline_css'=>$inline_css, 'segment'=>$segment,'print'=>$print,'fields'=>$fields]);
				//$html .= ;
				$total_price += $c['total_price'];
				$html .= '<div class="mg5">' . $c['html'].'</div>';
			}
			$html .= '</div>';
			$html .= '</div>';
				
			 
			 
		}
	}
	if(!$print){
	$html .= '<p class=" aright " style="padding:0 5px 15px 5px;border-bottom:1px solid #ddd">
		<button 
data-class="w60" 
data-toggle="tooltip" 
data-index="'.count($segments).'" 
data-placement="top" 
data-action="add-more-tour-segment" 
data-title="Thêm chặng tour" 
data-id="'.$item_id.'" 
data-item_id="'.$item_id.'" 
onclick="open_ajax_modal(this);" 
title="Thêm chặng tour" 
class="btn btn-warning btn-lg active" type="button" data-original-title="Thêm chặng tour"><i class="fa fa-random"></i> Thêm chặng tour</button></p>';
	}
	$html .= '</div>';
	
	$html .= '<div class="row">
<div class="" style="margin-top: 10px"> 

 
     
</div></div>';

	
	$html .= '</div>';
	if(isset($o['updateDatabase']) && $o['updateDatabase'] == true){
		Yii::$app->db->createCommand()->update(\app\modules\admin\models\ToursPrograms::tableName(), [
				'total_price2'=>$total_price
		],[
				'id'=>$item_id
		])->execute();
		\app\modules\admin\models\ToursPrograms::updatePrice($item_id);
	}
	return ['html'=>$html,'total_price'=>$total_price];
}

function loadTourProgramGuides($item_id=0, $o = []){
	//
	$print = isset($o['print']) && $o['print'] == true ? true : false;
	$fields = isset($o['fields']) ? $o['fields'] : [];
	$sub_label = isset($o['sub_label']) && $o['sub_label'] == false ? false : true;
	$item_id = is_numeric($item_id) ? $item_id : (isset($o['item_id']) ? $o['item_id'] : 0);
	$item = \app\modules\admin\models\ToursPrograms::getItem($item_id);
	//
	$total_price = $total_exprice = 0;
	$segments = \app\modules\admin\models\ProgramSegments::getAll($item_id,['parent_id'=>0]);
	$loadDefault = isset($o['loadDefault']) ? $o['loadDefault'] : false;
	$updateDatabase = isset($o['updateDatabase']) ? $o['updateDatabase'] : true;
	$lang = isset($o['lang']) ? $o['lang'] : ADMIN_LANG;
	if($loadDefault && $updateDatabase){
		\app\modules\admin\models\ToursPrograms::setSegmentsAutoGuides(['item_id'=>$item_id]);
		//$loadDefault = false; 
	}
	//
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	
	
	$html = '<div class="col-sm-12 bang-thong-tin-chung bt-guides block-examples"><div class="row">';
	
	
	if((isset($item['guide_language']) && isset($item['guide_type']))){ 
		$html .= '<input name="biz[guide_language]" type="hidden" value="'.$item['guide_language'].'"/>
<input name="biz[guide_type]" type="hidden" value="'.$item['guide_type'].'"/>';
	}
	
	if(isset($item['guide_language']) && isset($item['guide_type'])){
		$c = \app\modules\admin\models\AdLanguage::getLanguage($item['guide_language']);
		//view2($c);
	}else{
		$c = [];
	}
	
	$html .= '<div class="">
<div class="upper bold grid-sui-pheader aleft f12p" '.($inline_css ? 'style="font-weight: bold;border: 1px solid #ddd;line-height: 30px;border-bottom: none;background-color: #dedede;padding-left: 15px;"' : '').'>
			 Hướng dẫn viên:  '.(
					!(isset($item['guide_language']) && isset($item['guide_type'])) ? '<b class="red underline">Chưa thiết lập</b>' : '
					<b class="red">'.getGuideTypeName($item['guide_type']).'</b>
					<b class="green"> - '.(Yii::$app->t->translate($c['lang_code'],ADMIN_LANG)).'</b>'
					).'
						
'.($print ? '' : '<button data-guide_type="'.(isset($item['guide_type']) ? $item['guide_type'] : 2).'" data-guide_language="'.(isset($item['guide_language']) ? $item['guide_language'] : DEFAULT_LANG).'" data-action="setup-tourprogram-guides" data-toggle="tooltip" title="Thiết lập này sẽ áp dụng cho toàn bộ các chặng của chương trình này." data-placement="right" type="button" data-item_id="'.$item_id.'" data-segment_id="0"  data-title="Chọn hướng dẫn viên [level 1: áp dụng cho tất cả các chặng]" onclick="open_ajax_modal(this);" class="btn btn-default f14px btn-sm mgl30"><i class="fa-gears fa"></i> Thiết lập</button>').'			
				
			</div></div>';
	
	$html .= '<div class="root-chosse-guides block f16px" style="border:none"></div>';
	
	$guide_type = $root_guide_type = isset($item['guide_type']) ? $item['guide_type']  : 2;
	$guide_language = isset($item['guide_language']) ? $item['guide_language'] : DEFAULT_LANG;
	if($root_guide_type == 1){
		 
		$html .= '<div class="mg5">';
		$ex = getTourProgramGuides($item_id,[
				'segment'=>[],
				'item'=>$item,
				'guide_type'=>$guide_type,
				'guide_language'=>$guide_language,
				'updateDatabase'=>$updateDatabase,
				'loadDefault'=>$loadDefault,
				'print'=>$print,
				'fields'=>$fields,
				'sub_label'=>$sub_label,
				'inline_css'=>$inline_css
		]);
		$html .= $ex['html'];
		
		$html .= '</div>';
		$total_price += $ex['total_price'];
		$total_exprice += $ex['total_exprice'];
	}else{
	
	if(!empty($segments) && isset($item['guide_language']) && isset($item['guide_type']) && $guide_type == 2){
		foreach ($segments as $km=>$segment){
			$segments1 = \app\modules\admin\models\ProgramSegments::getAll($item_id,['parent_id'=>$segment['id']]);
			$guide_type = isset($item['guide_type']) ? $item['guide_type'] : 2;
			$guide_language = isset($item['guide_language']) ? $item['guide_language'] : DEFAULT_LANG;
			$html .= '<div class="block-examplesz">';
			if(!($print && count($segments)==1)){
			$html .= '<p data-class="w60" '.(!$print ? '
data-toggle="tooltip" data-placement="top" onclick="open_ajax_modal(this);" 
data-action="add-more-tour-segment" 
data-title="Chỉnh sửa chặng tour" 
data-parent_id="'.$segment['parent_id'].'" data-segment_id="'.$segment['id'].'" data-id="'.$item_id.'" 
data-item_id="'.$item_id.'" title="Click để chỉnh sửa chặng: '.uh($segment['title']).'"' : '') . '
class="pointer upper bold block-title">
<i class="fa fa-puzzle-piece"></i> '.uh($segment['title']).' <i>('.$segment['number_of_day'].' '.Yii::$app->t->translate('label_day',$lang).')</i></p>';
			}
			$html .= '<div>';
			
			if(!empty($segments1)){
				
				$sg = \app\modules\admin\models\ProgramSegments::getSegmentGuideType([
						'item_id'=>$item_id,
						'segment_id'=>$segment['id']
				]);
				$guide_type = isset($sg['type_id']) ? $sg['type_id'] : (
						isset($item['guide_type']) ? $item['guide_type'] : 2
						);
				$guide_language = isset($sg['lang']) ? $sg['lang'] : false;
			$html .= '<div class="root-chosse-guides-2 mg5 block f14px">
			<span>[2] Đã chọn: </span>'.(
								!$guide_language ? '<b class="red underline">Chưa thiết lập</b>' : '
					<b class="red">'.getGuideTypeName($guide_type).'</b>
					<b class="green"> - '.(\app\modules\admin\models\AdLanguage::getLanguage($guide_language)['title']).'</b>'
								).'
			
			<button data-guide_type="'.$guide_type.'" data-guide_language="'.$guide_language.'" data-action="setup-tourprogram-guides" data-toggle="tooltip" title="Thiết lập này sẽ áp dụng cho toàn bộ các chặng con của chặng '.$segment['title'].'." data-placement="right" type="button" data-item_id="'.$item_id.'" data-segment_id="'.$segment['id'].'"  data-title="Chọn hướng dẫn viên [level 2: áp dụng cho tất cả các chặng con của chặng '.$segment['title'].']" onclick="open_ajax_modal(this);" class="btn btn-default f14px btn-sm mgl30"><i class="fa-gears fa"></i> Thiết lập</button>
			
					</div>';			
			}		
			if($guide_type==1){
				$html .= '<div class="mg5">';
				$ex = getTourProgramGuides($item_id,[
						'segment'=>$segment,
						'segment_parent'=>$segment['id'],
						'item'=>$item,
						'guide_type'=>$guide_type,
						'guide_language'=>$guide_language,
						'updateDatabase'=>$updateDatabase,
						'loadDefault'=>$loadDefault,
						'root_guide_type'=>$root_guide_type,
						'print'=>$print,
						'fields'=>$fields,
						'sub_label'=>$sub_label,
						'inline_css'=>$inline_css
				]);
				$html .= $ex['html'];
				$total_price += $ex['total_price'];
				$total_exprice += $ex['total_exprice'];
				$html .= '</div>';
			}else{
				
			
			if(!empty($segments1)){
				foreach ($segments1 as $segment1){
					
					$html .= '<div class="bc1 block-example mgb5 '.($km%2==1 ? ' bg-' : '').'">
					<span data-class="w60" '.(!$print ? ' 
data-toggle="tooltip" 
data-placement="right" data-action="add-more-tour-segment" 
data-title="Chỉnh sửa chặng tour" data-parent_id="'.$segment1['parent_id'].'" 
data-segment_id="'.$segment1['id'].'" data-id="'.$item_id.'" data-item_id="'.$item_id.'" 
onclick="open_ajax_modal(this);" title="Click để chỉnh sửa chặng: '.uh($segment1['title']).'" ' : '') .'
class="f12e block-title btn-sm btn btn-default mgl0 bdl1">
<i class="fa fa-hand-o-right"></i> '.uh($segment1['title']).' <i>('.$segment1['number_of_day'].' '.Yii::$app->t->translate('label_day',$lang).')</i></span>';
					$html .= '<div class="form-group mgb0">';
					 				
					$ex = getTourProgramGuides($item_id,[
							'segment'=>$segment1,
							'item'=>$item,
							'guide_type'=>$guide_type,
							'guide_language'=>$guide_language,
							'updateDatabase'=>$updateDatabase,
							'loadDefault'=>$loadDefault,
							'root_guide_type'=>$root_guide_type,
							'print'=>$print,
							'fields'=>$fields,
							'sub_label'=>$sub_label,
							'inline_css'=>$inline_css
					]);	
					$html .= $ex['html'];
					$total_price += $ex['total_price'];
					$total_exprice += $ex['total_exprice'];
					$html .= '</div>';
					$html .= '</div>'; 
					 
				}
			}else{
				$html .= '<div class="mg5">';
				$ex = getTourProgramGuides($item_id,[
						'segment'=>$segment,
						'item'=>$item,
						'guide_type'=>$guide_type,
						'guide_language'=>$guide_language,
						'updateDatabase'=>$updateDatabase,
						'loadDefault'=>$loadDefault,
						'root_guide_type'=>$root_guide_type,
						'print'=>$print,
						'fields'=>$fields,
						'sub_label'=>$sub_label,
						'inline_css'=>$inline_css
				]);				
				$html .= $ex['html'];
				$total_price += $ex['total_price'];
				$total_exprice += $ex['total_exprice'];
				$html .= '</div>';
			}
			}
			$html .= '</div>';
			$html .= '</div>';



		}
	}}

	
	$html .= '</div>';
	$html .= '</div>';
	if($updateDatabase){
		Yii::$app->db->createCommand()->update(\app\modules\admin\models\ToursPrograms::tableName(), [
				'total_price3'=>$total_exprice+$total_price
		],[
				'id'=>$item_id
		])->execute();
		\app\modules\admin\models\ToursPrograms::updatePrice($item_id);
	}
	return ['html'=>$html,'total_price'=>($total_exprice+$total_price)];
}

function getTourProgramGuides($item_id=0, $o = []){ 
	$segment = isset($o['segment']) ? $o['segment'] : [];
	$print = isset($o['print']) && $o['print'] == true ? true : false;
	$fields = isset($o['fields']) ? $o['fields'] : [];
	$sub_label = isset($o['sub_label']) && $o['sub_label'] == false ? false : true;
	$segment_parent_id = isset($o['segment_parent_id']) ? $o['segment_parent_id'] : -1;
	$item = isset($o['item']) ? $o['item'] : \app\modules\admin\models\ToursPrograms::getItem($item_id);
	$html = '<div class="">';
	$guide_type = isset($o['guide_type']) ? $o['guide_type'] : 2;
	$root_guide_type = isset($o['root_guide_type']) ? $o['root_guide_type'] : (
			isset($item['guide_type']) ? $item['guide_type'] : 2
			);
	$guide_language = isset($o['guide_language']) ? $o['guide_language'] : DEFAULT_LANG;
	$places_id = [];
	$loadDefault = isset($o['loadDefault']) ? $o['loadDefault'] : false;
	$updateDatabase = isset($o['updateDatabase']) ? $o['updateDatabase'] : true;
	$segment_id = isset($segment['id']) ? $segment['id'] : 0;
	//
	$total_price = 0;
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$c = \app\modules\admin\models\AdLanguage::getLanguage($guide_language);
	$cols = 12; $scols= 0;
	if((isset($fields['sguide_type']) && !$fields['sguide_type'])){ 
		--$cols;
	}
	if((isset($fields['price']) && !$fields['price'])){
		--$cols;
	}
	if((isset($fields['amount']) && !$fields['amount'])){
		--$cols;
	}
	$scols = 12 - $cols;
	//
		// Huong dan
		if(!!$sub_label){
		//$html .= '<p class="upper bold grid-sui-pheader aleft ">Hướng dẫn viên <i>('.Yii::$app->t->translate($c['lang_code'],ADMIN_LANG).')</i></p>';
		}
		$html .= '<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered mgb0 table-sm vmiddle">
<colgroup class="dcols-'.$cols.'">';
	$w = 100/$cols;
	for($i = 0; $i< $cols;$i++){
		$html .= '<col style="width:'.$w.'%">'; 
	}
	$html .= '</colgroup>	
				<tbody>
<tr class="grid-sui-pheader">
<th colspan="3" class="" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;text-align:left"' : '').'>Hướng dẫn viên</th>';
	if(!(isset($fields['sguide_type']) && !$fields['sguide_type'])){
		$html .= '<th colspan="5" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;"' : '').'></th>';
	}
	$html .= '
<th class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;"' : '').'>SL</th>
<th class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;"' : '').'>Số ngày</th>';
	if(!(isset($fields['price']) && !$fields['price'])){
		$html .= '<th class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;"' : '').'>Đơn giá</th>';
	}
	if(!(isset($fields['amount']) && !$fields['amount'])){
		$html .= '<th class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;"' : '').'>Thành tiền</th>';
	}
	$html .= '</tr>';
		$from_date = $item['from_date'];
		$package_id = 0;
		if($loadDefault && $updateDatabase){
			//
			
			//
			
			//
		}
		
		 
		 
		foreach ( \app\modules\admin\models\ToursPrograms::getProgramGuides([
				'item_id'=>$item['id'],
				'segment_id'=>$segment_id,
				'guide_type'=>$guide_type
		]) as $kv=>$sv){
			
			$supplier_id = Yii::$app->zii->getSupplierIDFromService($sv['guide_id'],TYPE_ID_GUIDES);
			$quotation = \app\modules\admin\models\Suppliers::getQuotation([
					'supplier_id'=>$supplier_id,
					'date'=>$from_date
			]);
			//view($quotation);
			//
			$nationality_group = \app\modules\admin\models\Suppliers::getNationalityGroup([
					'supplier_id'=>$supplier_id,
					'nationality_id'=>$item['nationality'],
			]);
			//
			$seasons = \app\modules\admin\models\Suppliers::getSeasons([
					'supplier_id'=>$supplier_id,
			
					'date'=>$from_date,
					//'time_id'=>$time_id
			]);
			$groups = \app\modules\admin\models\Suppliers::getGuestGroup([
					'supplier_id'=>$supplier_id,
					'total_pax'=>$item['guest'],
					'date'=>$from_date,
					//'time_id'=>$time_id
			]);
			 
			
			$prices = Yii::$app->zii->getProgramGuidesPrices([
					'controller_code'=>TYPE_ID_GUIDES,
					'quotation_id'=>$quotation['id'],
					'nationality_id'=>$nationality_group['id'],
					'season_id'=>isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0,
					'supplier_id'=>$supplier_id,
					'total_pax'=>$item['guest'],
					'weekend_id'=>isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0,
					'time_id'=>isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1,
					'package_id'=>$package_id,
					'item_id'=>$item_id,
					'service_id'=>$sv['id'],
					//'season_time_id'=>$season_time_id,
					'seasons'=>$seasons,
					'segment_id'=>$segment_id,
					'segment_parent_id'=>$segment_parent_id['id'],
					'loadDefault'=>$loadDefault,
					'updateDatabase'=>$updateDatabase,
					'quantity'=>$sv['quantity'], 
					'number_of_day'=>isset($segment['number_of_day']) ? $segment['number_of_day'] : 0,
					'guide_type'=>$guide_type,
					'root_guide_type'=>$root_guide_type
					
	
			]);
			$prices['number_of_day'] = isset($prices['number_of_day']) ? $prices['number_of_day'] : (isset($segment['number_of_day']) ? $segment['number_of_day'] : 0);
				
			if(!empty($prices) && isset($prices['price1'])){
				$price = Yii::$app->zii->getServicePrice($prices['price1'],[
						'item_id'=>$item_id,
						//'price'=>$prices['price1'],
						'from'=>$prices['currency'], 
						'to'=>$item['currency']
				]); 
			}
			$sub_price = (isset($price['price']) ? $price['price'] * $prices['quantity'] * $prices['number_of_day'] : 0);
			$total_price += $sub_price;
			$html .= '<tr>
<td colspan="3" class="" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;"' : '').'>
<a href="#"
data-action="open-form-guide-note"
onclick="call_ajax_function(this);return false;"
data-controller_code = "'.TYPE_ID_GUIDES.'"
data-quotation_id= "'.$quotation['id'].'"
data-nationality_id = "'.$nationality_group['id'].'"
data-season_id= "'.(isset($seasons['seasons_prices']['id']) ? $seasons['seasons_prices']['id'] : 0).'"
data-supplier_id = "'.$supplier_id.'"
data-total_pax= "'.$item['guest'].'"
data-weekend_id = "'.(isset($seasons['week_day_prices']['id']) ? $seasons['week_day_prices']['id'] : 0).'"
data-time_id= "'.(isset($seasons['time_day_prices']['id']) ? $seasons['time_day_prices']['id'] : -1).'"
data-item_id = "'.$item['id'].'"
data-package_id= "'.$package_id.'"
data-service_id = "'.$sv['id'].'"
data-segment_id = "'.$segment_id.'"
data-segment_parent_id= "'.$segment_parent_id['id'].'"
data-quantity = "'.$sv['quantity'].'"
data-number_of_day= "'.(isset($segment['number_of_day']) ? $segment['number_of_day'] : 0).'" 
data-guide_type = "'.$guide_type.'"
data-root_guide_type= "'.$root_guide_type.'" 					 
data-currency="'.$prices['currency'].'"
>
'.uh($sv['supplier_name']).' - '.uh($sv['title']) .'
</a>
</td>
';
			if(!(isset($fields['sguide_type']) && !$fields['sguide_type'])){
				$html .= '<td colspan="5" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>'.getGuideTypeName($sv['type_id']).'</td>';
			}
			$html .= '<td class=" center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>
<span title="Số lượng" class="">'.(isset($sv['quantity']) ? $sv['quantity'] : 0).'</span></td>
<td title="Số ngày" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'>
'.$prices['number_of_day'].'</td>';
			if(!(isset($fields['price']) && !$fields['price'])){
					$html .= '<td class=" aright" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'>
<span data-decimal="'.(isset($price['decimal']) ? $price['decimal'] : 0).'" 
class="number-format '.(isset($price['changed']) && $price['changed'] ? 'red underline' : '').'" 
title="'.(isset($price['changed']) && $price['changed'] ? 'Đơn giá: ' . $price['old_price'] : 'Đơn giá').'">'.(isset($price['price']) ? $price['price'] : '-').'</span></td>';
			}
			if(!(isset($fields['amount']) && !$fields['amount'])){
				$html .= '<td class="aright" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><span title="Thành tiền" data-decimal="'.(isset($price['decimal']) ? $price['decimal'] : 0).'" class="bold underline number-format " >'.(isset($price['price']) ? $price['price'] * $prices['quantity'] * $prices['number_of_day'] : '-').'</span></td>';
			} 
									
			$html .= '</tr>';
		} 
		$places = \app\modules\admin\models\ProgramSegments::getPlaceIDs($segment_id);
		
		if(!$print){
		$html .= '<tr><td colspan="12" class="pr vtop">';
		$html .= '<p class="aright"><button data-place_id="'.(!empty($places) ? $places[0] : '').'" data-guide_language="'.$guide_language.'" data-root_guide_type="'.$root_guide_type.'" data-guide_type="'.$guide_type.'" data-place_id="'.implode(',', $places_id).'" data-segment_id="'.(isset($segment['id']) ? $segment['id'] : 0).'" data-toggle="tooltip" data-placement="left" data-nationality="'.$item['nationality'].'" data-guest="'.$item['guest'].'" data-class="w90" data-action="add-more-tours-program-guides" data-title="Chọn hướng dẫn viên" data-item_id="'.$item_id.'" onclick="open_ajax_modal(this);" title="Chọn hướng dẫn viên'.(!empty($segment) ? ' cho chặng '. uh($segment['title']) : '').'" class="btn btn-warning input-sm" type="button"><i class="fa fa-universal-access"></i> Thay đổi hướng dẫn viên</button></p>';
		$html .= '</td></tr>';
		}
		$html .= '</tbody></table>';
		//
		//$html .= '</div>';
		$ex = loadProgramSegmentExtendPrices([
				'item_id'=>$item_id,
				'segment_id'=>$segment_id,
				'item'=>$item,
				//'segment'=>$segment,
				'print'=>$print,
				'guide_type'=>$guide_type,
				'fields'=>$fields,
				'sub_label'=>$sub_label,
				'inline_css'=>$inline_css
		]);
		$html .= $ex['html'];		
		//$html ='';
	 return ['html'=>$html,'total_price'=>$total_price,'total_exprice'=>$ex['total_price']];
	 
}


function loadProgramSegmentExtendPrices($o = []){
	$print = isset($o['print']) && $o['print'] == true ? true : false;
	$fields = isset($o['fields']) ? $o['fields'] : [];
	$sub_label = isset($o['sub_label']) && $o['sub_label'] == false ? false : true;
	$html = '<div class="">';  
	$item_id = isset($o['item_id']) ? $o['item_id'] : 0;
	$total_price = 0;
	$segment_id = isset($o['segment_id']) ? $o['segment_id'] : 0;
	$guide_type = isset($o['guide_type']) ? $o['guide_type'] : 2;
	$item = isset($o['item']) && !empty($item) ? $o['item'] : (
			\app\modules\admin\models\ToursPrograms::getItem($item_id)
			);
	$segment = isset($o['segment']) && !empty($segment) ? $o['segment'] : (
			\app\modules\admin\models\ProgramSegments::getXItem($segment_id)
			);
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$l = \app\modules\admin\models\ToursPrograms::getExtendPrices([
			'item_id'=>$item['id'],
			'segment_id'=>$segment_id,
			'type_id'=>$guide_type
	]);
	//
	// Huong dan
	if(!!$sub_label && !$print){
		//$html .= '<p class="upper bg-danger bold grid-sui-pheader aleft ">Chi phí phát sinh cho HDV</p>';
	}elseif($print && !empty($l)){
		//$html .= '<p class="upper bg-danger bold grid-sui-pheader aleft ">Chi phí phát sinh cho HDV</p>';
	}
	
	$cols = 12; $scols= 0; $c = 2;
	
	if((isset($fields['price']) && !$fields['price'])){
		--$cols;
	}else{
		$c--;
	}
	if((isset($fields['amount']) && !$fields['amount'])){
		--$cols;
	}else{
		$c--;
	}
	$scols = 12 - $cols;
	
	$c = $cols == 12 ? $c = 9 : 9; 
	
	$html .= '<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered mgb0 table-sm vmiddle">
	<colgroup class="dcols-'.$cols.'">';
	$w = 100/$cols;
	for($i = 0; $i< $cols;$i++){
		$html .= '<col style="width:'.$w.'%">'; 
	}
	$html .= '</colgroup><tbody>';
	if(!$print || !empty($l)){
	$html .= '<tr class="grid-sui-pheader bg-danger">
	<th colspan="'.($c+1).'" class="aleft" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;text-align:left"' : '').'>Chi phí phát sinh</th>';
	if(!(isset($fields['price']) && !$fields['price'])){
		$html .= '<th class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;"' : '').'>&nbsp;</th>';
	}
	if(!(isset($fields['amount']) && !$fields['amount'])){
		$html .= '<th class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;"' : '').'>&nbsp;</th>';
	}
	$html .= '</tr>';
	 
	}
	foreach ($l as $kv=>$sv){
		$price = Yii::$app->zii->getServicePrice($sv['price1'],[
				'item_id'=>$item_id,
				//'price'=>$prices['price1'],
				'from'=>$sv['currency'],
				'to'=>$item['currency']
		]);
		$sub_total = (isset($price['price']) ? $price['price'] * $sv['quantity']  : 0);
		$total_price += $sub_total;
		$html .= '<tr>
		<td colspan="3" class="aleft" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px ;text-align:left "' : '').'>
		<a class="pointer pdl5"
			data-segment_id="'.(isset($segment['id']) ? $segment['id'] : 0).'" 
			data-guide_type="'.$guide_type.'"			
			data-placement="left"  
			data-class="w60" data-id="'.$sv['id'].'"
			data-action="add-more-tours-program-extend-prices" 
			title="Chi phí phát sinh - '.(uh($segment['title'])).'"  
			data-item_id="'.$item_id.'" onclick="open_ajax_modal(this);" 
			>'.uh($sv['title']) .'</a></td>
		';
		
		if(!(isset($fields['price']) && !$fields['price'])){
			$html .= '<td colspan="5" class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'> - </td>';
		}
		
		$html .= '<td class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'> <span title="Số lượng" class="">'.(isset($sv['quantity']) ? $sv['quantity'] : 0).'</span> </td>
		<td class="center" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:center;"' : '').'> - </td>';
		
		if(!(isset($fields['price']) && !$fields['price'])){			
			$html .= '<td class="aright" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').'><span data-decimal="'.(isset($price['decimal']) ? $price['decimal'] : 0).'" class="number-format '.(isset($price['changed']) && $price['changed'] ? 'red underline' : '').'" title="'.(isset($price['changed']) && $price['changed'] ? 'Đơn giá: ' . $price['old_price'] : 'Đơn giá').'">'.(isset($price['price']) ? $price['price'] : '-').'</span></td>';
		}
		if(!(isset($fields['amount']) && !$fields['amount'])){
			$html .= '<td class="aright" '.($inline_css ? 'style="border: 1px solid #ddd;padding: 8px;line-height: 1.42857143;vertical-align: middle;font-size: 12px;text-align:right;"' : '').' ><span title="Thành tiền" data-decimal="'.(isset($price['decimal']) ? $price['decimal'] : 0).'" class="bold underline number-format " >'.(isset($price['price']) ? $price['price'] * $sv['quantity']  : '-').'</span></td>';
		}


			$html .= '</tr>';
	}
	
	if(!$print){ 
	$html .= '<tr><td colspan="12" class="pr vtop">';
	$html .= '<p class="aright"><button 
			data-segment_id="'.(isset($segment['id']) ? $segment['id'] : 0).'" 
			data-guide_type="'.$guide_type.'"
			data-toggle="tooltip" 
			data-placement="left" 
			data-class="w60" 
			data-action="add-more-tours-program-extend-prices" 
			data-title="Chi phí phát sinh - '.(uh($segment['title'])).'" 
			data-item_id="'.$item_id.'" onclick="open_ajax_modal(this);" 
			title="Chi phí phát sinh'.(!empty($segment) ? ' cho chặng '. uh($segment['title']) : '').'" 
			class="btn btn-danger input-sm" type="button"><i class="fa fa-arrow-circle-o-up"></i> Chi phí phát sinh</button></p>';
	$html .= '</td></tr>';
	}
	$html .= '</tbody></table>';
	//
	$html .= '</div></div>';
	return ['html'=>$html,'total_price'=>$total_price];

}

function Tourprogram_ReloadAllPrice($o = []){
	$id = $item_id = isset($o['item_id']) ? $o['item_id'] : (isset($o['id']) ? $o['id'] : 0);
	$guest = isset($o['guest']) ? $o['guest'] : 0;
	$c = [];
	$v = \app\modules\admin\models\ToursPrograms::getItem($id);
		
	if(!empty($v)){
		//
		if($guest>-1){
			$c = ['guest'=>$guest];
		}
			
		 
		$a = loadTourProgramDetail([
				'id'=>$id,
				'loadDefault'=>true,
				'updateDatabase'=>true,
		]);
		foreach (\app\modules\admin\models\ProgramSegments::getAll($id,['parent_id'=>0]) as $segment){
			$x1 = \app\modules\admin\models\ProgramSegments::getAll($id,['parent_id'=>$segment['id']]);
			if(!empty($x1)){
				foreach ($x1 as $x2) {
					loadTourProgramDistances($id,[
							'loadDefault'=>true,
							'updateDatabase'=>true,
							'segment'=>$x2
					]);
				}
			}else{
				loadTourProgramDistances($id,[
						'loadDefault'=>true,
						'updateDatabase'=>true,
						'segment'=>$segment
				]);
			}
		}
	
		 
		loadTourProgramGuides($id,[
				'loadDefault'=>true,
				'updateDatabase'=>true,
		]);
	}
}


function loadSupplierTrainPrices($supplier_id){
	$id = $supplier_id;
	$existed = [];
	$html = '';
	$m = load_model('distances');
	$packages = $m->getItemBySupplier($supplier_id);
	$packages = \app\modules\admin\models\PackagePrices::getPackages($supplier_id);
	$roomsModel = load_model('rooms_categorys');
	$model = load_model('services_provider');
	$rooms = $roomsModel->getRoomBySupplier($supplier_id);
	//$seasons = \app\modules\admin\models\Seasons::get_incurred_category_for_price(TYPE_ID_TRAIN,[2],['supplier_id'=>$supplier_id]);
	$seasons= \app\modules\admin\models\Customers::getCustomerSeasons($supplier_id,[
			'price_type'=>[0],'type_id'=>2
	]);
	
	
	
	if(empty($seasons)){
		$seasons = [
				['id'=>0,'title'=>''],
		];
	}
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	$existed_seasons = $existed_items = [];
		
	$h = [
			'controller_code'=>TYPE_ID_TRAIN,
			'type_id'=>TYPE_ID_TRAIN,
			'quotation'=>true,
			'package'=>true,
			
			'package_attrs'=>[
					'btn-title'=>'Thêm tuyến',
					'data-title'=>'Thêm tuyến / Chặng vận chuyển'
					
			],
			'nationality'=>false,
			'group'=>false,
			//'train_ticket'=>true,
	];
	$quotations = \app\modules\admin\models\Customers::getSupplierQuotations($supplier_id,[
			'order_by'=>['a.to_date'=>SORT_DESC,'a.title'=>SORT_ASC],
			'is_active'=>1
	]);
	$html .= getPriceHeaderButton($supplier_id, $h);
	if(!empty($quotations)){
		foreach ($quotations as $quotation){
			foreach ($packages as $package){
	
				$html .= '<div class="col-sm-12 mgt15"><div class="row"><p class="grid-sui-pheader bold aleft"><i style="font-weight: normal;">
         ';
				$html .= $quotation['title'];
				 
				$html .=' - áp dụng cho tuyến <b class="italic underline">' .$package['title'] .'</b>
	
	
 </i></p></div></div>';
					
					
					
				$html .= '<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered vmiddle mgt15"><thead>
		<tr>
	
		<th rowspan="3" class="center">Ga đi - Ga đến</th>
		<th rowspan="3" class="center w100p">Cự ly (Km)</th>
		<th colspan="'.(count($rooms) * count($seasons)).'" class="center">Loại ghế</th>
		<th rowspan="3" class="center w80p" style="max-width:80px">Tiền tệ</th>
		<th rowspan="3" class="center w50p"></th>
		</tr>';
					
					
	
				$html .= '<tr class="'.(count($seasons) == 1 ? 'hide' : '').'">';
				if(!empty($seasons)){
					foreach ($seasons as $season){
						if(!in_array($season['id'], $existed_seasons)){
							$existed_seasons[] = $season['id'];
						}
						$html .= '<th colspan="'.(count($rooms)).'" class="center">'.uh($season['title']).'</th>';
					}
				}
				$html .= '<tr>';
				foreach ($seasons as $season){
					if(!empty($rooms)){
						foreach ($rooms as $room){
							if(!in_array($room['id'], $existed_items)){
								$existed_items[] = $room['id'];
							}
							$html .= '<th class="center" title="'.uh($room['note']).'"><a>'.uh($room['title']).'</a></th>';
						}
					}
				}
					
				$html .='</tr>';
	
				$html .= '</thead><tbody>';
				$i = 0;
				foreach ($model->getTrainDistanceBySupplier(['supplier_id'=>$supplier_id,'package_id'=>$package['id']]) as $k1=>$v1){
						
					$p = $model->getTrainPrice($v1[0],$v1[1],$supplier_id,$package['id']);
						
					$html .= '<tr class="tr-item-'.$supplier_id.'-'.$quotation['id'].'-'.$package['id'].'-'.$v1[0].'-'.$v1[1].'">
	
		<td class=""><a class="truncate">'.uh($v1['distance']) .'</a></td>
		<td class="">
				<input type="hidden" value="'.$v1[0].'" name="prices['.$package['id'].']['.$i.'][station_from]" class=""/>
				<input type="hidden" value="'.$v1[1].'" name="prices['.$package['id'].']['.$i.'][station_to]" class=""/>
				<input type="number" 
						onblur="quick_change_supplier_service_price(this);"
						data-old="'.(isset($p['distance']) ? $p['distance'] : 0).'"
						data-supplier_id="'.$supplier_id.'"
						data-quotation_id = "'.$quotation['id'].'"
						data-package_id = "'.$package['id'].'"							 
					 	data-field="distance"
						data-season_id = "'.$season['id'].'"
						data-group_id="0"
						data-station_from="'.$v1[0].'"  
						data-station_to="'.$v1[1].'" 
						data-supplier_type="'.TYPE_ID_TRAIN.'"						
						value="'.(isset($p['distance']) ? $p['distance'] : 0).'" 
						name="prices['.$package['id'].']['.$i.'][distance]" 
						class="form-control input-sm ajax-number-format center"/></td>';
					$currency = 1;
					foreach ($seasons as $season){
						if(!empty($rooms)){
							foreach ($rooms as $room){
								/*	
								$price = \app\modules\admin\models\Customers::getSupplierDetailPrice([
										'item_id'=>$room['id'],
										'season_id'=>$season['id'],
										//'weekend_id'=>0,
										'group_id'=>0,
										'supplier_id'=>$supplier_id,
										'package_id'=>$package['id'],
										'quotation_id'=>$quotation['id'],
										//'time_id'=>-1,
										//'nationality_id'=>0
								]);
									*/
								if(!empty($p)){
									$currency = $p['currency'];								
								}
								$html .= '<td class="center">
									<input onblur="quick_change_supplier_service_price(this);"
									type="text"
									data-supplier_id="'.$supplier_id.'"
									data-quotation_id = "'.$quotation['id'].'"
									data-package_id = "'.$package['id'].'"							 
									data-item_id = "'.$room['id'].'"
									data-season_id = "'.$season['id'].'"
									data-group_id="0"
									data-station_from="'.$v1[0].'"  
									data-station_to="'.$v1[1].'" 
									data-supplier_type="'.TYPE_ID_TRAIN.'"
									data-type_id="'.TYPE_ID_TRAIN.'"
									data-ticket_id="'.(isset($p[$season['id']][$room['id']]['ticket_id']) ? $p[$season['id']][$room['id']]['ticket_id'] : 0).'"								 
									data-decimal="'.Yii::$app->zii->showCurrency($currency,3).'"
									data-old="'.(isset($p[$season['id']][$room['id']]['price1']) ? $p[$season['id']][$room['id']]['price1'] : '').'"
									value="'.(isset($p[$season['id']][$room['id']]['price1']) ? $p[$season['id']][$room['id']]['price1'] : '').'"
									data-decimal="'.Yii::$app->zii->showCurrency((isset($p['currency']) && $p['currency'] ? $p['currency'] : 2),3).'"
									name="prices['.$package['id'].']['.$i.'][list]['.$season['id'].']['.$room['id'].']"									 
									class="input-currency-price-00 form-control input-sm ajax-number-format aright input-price-'.$k1.'"/></td>';
							}
						}
					}
					$html .= '<td class="center">';
					$html .= '<select 
						data-decimal="'.Yii::$app->zii->showCurrency((isset($p['currency']) && $p['currency'] ? $p['currency'] : 0),3).'" 
						data-target-input=".input-price-'.$k1.'" 
						onchange="get_decimal_number(this);quick_change_supplier_service_price(this);"
						data-target-input=".input-currency-price-00" 
						data-supplier_id="'.$supplier_id.'"
						data-quotation_id = "'.$quotation['id'].'"
						data-package_id = "'.$package['id'].'"							 
					 	data-field="currency"
						data-season_id = "'.$season['id'].'"
						data-group_id="0"
						data-station_from="'.$v1[0].'"  
						data-station_to="'.$v1[1].'" 
						data-supplier_type="'.TYPE_ID_TRAIN.'"
						class="ajax-select2-no-search sl-cost-price-currency form-control ajax-select2 input-sm" 
						data-search="hidden" name="prices['.$package['id'].']['.$i.'][currency]">';
	
					foreach(Yii::$app->zii->getUserCurrency()['list'] as $k2=>$v2){
						$html .= '<option value="'.$v2['id'].'" '.(isset($p['currency']) && $p['currency'] == $v2['id'] ? 'selected' : '').'>'.$v2['code'].'</option>';
					}
						
					$html .= '</select>';
					$html .= '</td>';
					$html .= '<td class="center">
							<i data-supplier_id="'.$supplier_id.'" 
							data-quotation_id="'.$quotation['id'].'" 
							data-package_id = "'.$package['id'].'"
							data-group_id="0"
							data-station_from="'.$v1[0].'"  
							data-station_to="'.$v1[1].'" 
							data-ticket_id="'.(isset($p['ticket_id']) ? $p['ticket_id'] : 0).'"		
							data-supplier_type="'.TYPE_ID_TRAIN.'"
							data-confirm-text="<span class=red>Lưu ý: Bản ghi <b class=underline>'.$v1['distance'].'</b> sẽ bị xóa khỏi báo giá này.</span>" 
							class="pointer glyphicon glyphicon-trash btn-delete-item" 
							data-name="remove_menu" 
							data-confirm-action="quick_change_station_price_remove" 
							data-action="open-confirm-dialog" data-class="modal-sm" data-title="Xác nhận xóa." onclick="open_ajax_modal(this);"></i>
							</td>
		</tr>';
					$i++;
				}
					
				$html .= '</tbody></table>';
	
				$html .= '<p class="aright mgt15">';
	
				$html .= '<button data-toggle="tooltip" data-placement="left"  
						title="Thêm vé cho tuyến '.$package['title'].'" 
						data-seasons="'.implode(',', $existed_seasons).'" 
						data-items="'.implode(',', $existed_items).'" 
						data-required-save="true" 
						data-package_id="'.$package['id'].'" 
						data-type_id="'.TYPE_ID_TRAIN.'" 
						data-supplier_id="'.$id.'" 
						data-quotation_id="'.$quotation['id'].'"		
						data-title="Thêm vé" type="button" onclick="open_ajax_modal(this);" data-class="w80" data-action="add-more-station-to-distance" class="btn btn-warning btn-data-required-save"><i class="fa fa-plus"></i> Thêm vé</button></p><hr>';
	
			}}}
			
			return $html;
}


function getClassStatus($status = 0){
	switch ($status){
		case \app\modules\admin\models\ClassManage::$CLASS_STATUS_READY:
			return 'Sắp khai giảng';
			break;
		case \app\modules\admin\models\ClassManage::$CLASS_STATUS_ACTIVE:
			return 'Đang hoạt động';
			break;	
		case \app\modules\admin\models\ClassManage::$CLASS_STATUS_SUCCESS:
			return 'Đã hoàn thành';
			break;	
			
		default: return 'Chưa xác định'; break;
	}
}

function showDateCreated($date = ''){
	if(is_numeric($date) &&  $date > 0){
		return date('d/m/Y H:i:s',($date));
	}
	if(check_date_string($date)){
		return date('d/m/Y H:i:s',strtotime($date));
	}
	return '-';
}

function getIDYoutubeVideo($url){
	$link = $url;
	if(strrpos($url, '?v=') !== false){
		$pos = strrpos($url, '?v=');
		if($pos>0){
			$link = substr($url, $pos+3);
			$pos = strrpos($link, '&');
			if($pos>0){
				$link = substr($link, 0, $pos);
			}
		}
	}else{
		$pos = strrpos($url, '/');
		if($pos>0){
			$link = substr($url, $pos+1);
		}
	}
	//view($link);
	return $link;
}






function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
	$output = NULL;
	if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
		$ip = $_SERVER["REMOTE_ADDR"];
		if ($deep_detect) {
			if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
					$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
	}
	$purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
	$support    = array("country", "countrycode", "state", "region", "city", "location", "address");
	$continents = array(
			"AF" => "Africa",
			"AN" => "Antarctica",
			"AS" => "Asia",
			"EU" => "Europe",
			"OC" => "Australia (Oceania)",
			"NA" => "North America",
			"SA" => "South America"
	);
	if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
		$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
		if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
			switch ($purpose) {
				case "location":
					$output = array(
					"city"           => @$ipdat->geoplugin_city,
					"state"          => @$ipdat->geoplugin_regionName,
					"country"        => @$ipdat->geoplugin_countryName,
					"country_code"   => @$ipdat->geoplugin_countryCode,
					"continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
					"continent_code" => @$ipdat->geoplugin_continentCode
					);
					break;
				case "address":
					$address = array($ipdat->geoplugin_countryName);
					if (@strlen($ipdat->geoplugin_regionName) >= 1)
						$address[] = $ipdat->geoplugin_regionName;
						if (@strlen($ipdat->geoplugin_city) >= 1)
							$address[] = $ipdat->geoplugin_city;
							$output = implode(", ", array_reverse($address));
							break;
				case "city":
					$output = @$ipdat->geoplugin_city;
					break;
				case "state":
					$output = @$ipdat->geoplugin_regionName;
					break;
				case "region":
					$output = @$ipdat->geoplugin_regionName;
					break;
				case "country":
					$output = @$ipdat->geoplugin_countryName;
					break;
				case "countrycode":
					$output = @$ipdat->geoplugin_countryCode;
					break;
			}
		}
	}
	return $output;
}

function find_date( $string ) { 
	$r = [];
	$pattern = "/\d{2}\-\d{2}\-\d{4}/"; 
	if (preg_match_all($pattern, $string, $matches)) {
		$r = array_merge($r,$matches[0]); 
	}
	
	$pattern = "/\d{4}\-\d{2}\-\d{2}/";
	if (preg_match_all($pattern, $string, $matches)) {
		$r = array_merge($r,$matches[0]);
	}
	
	$pattern = "/\d{2}\/\d{2}\/\d{4}/"; 	
	if (preg_match_all($pattern, $string, $matches)) {	
		$r = array_merge($r,$matches[0]);
	}
	if(!empty($r)){
		foreach ($r as $k=>$v){
			$r[$k] = ctime(['string'=>$v,'format'=>'Y-m-d']);
		}
	}
	return $r;
}

function getTourProgramDay($v){
	$r = [];
	if(isset($v['tabs']) && !empty($v['tabs'])){
		foreach ($v['tabs'] as $k1=>$v1){
			switch ($v1['type']){
				case 'program':
					if(!empty($v1['program'])){
						foreach ($v1['program'] as $kp=>$vp){
							if(!empty($vp)){
								foreach ($vp as $vc=>$vx){
									if(isset($vx['is_active']) && $vx['is_active'] == "on"){
										if($kp == 'day'){
											$r[] = $vx;
										}
									}
								}
							}
						} 
					}
					break;
			}
		}
	}
	return $r;
}
 

function trim_all($string,$pattern  = '/(\s\s+)|(\\t|\\r|\\n|\\0\\x0B)/'){
	return preg_replace($pattern, ' ', trim($string));
}


function opLink($link, $o = 1){
	switch ($o){
		case 1:
			return str_replace(['http://','https://'], SCHEME . '://', $link);
			break;
		default:
			return str_replace(['http://','https://'], '//', $link);
			break;
	}
}

function validateAbsoluteUrl($url){
	return strpos($url, '://') !== false;
}

function getImageInfo($url){
	$info = @getimagesize($url);
	if($info !== false){
		return $info;
	}
	return [
		0,0,0	
	];
}


function dispatchLoopShutdown($body) {
	 
	//remove redundant (white-space) characters
	$replace = array(
			//remove tabs before and after HTML tags
			'/\>[^\S ]+/s'   => '>',
			'/[^\S ]+\</s'   => '<',
			//shorten multiple whitespace sequences; keep new-line characters because they matter in JS!!!
			'/([\t ])+/s'  => ' ',
			//remove leading and trailing spaces
			'/^([\t ])+/m' => '',
			'/([\t ])+$/m' => '',
			// remove JS line comments (simple only); do NOT remove lines containing URL (e.g. 'src="http://server.com/"')!!!
			'~//[a-zA-Z0-9 ]+$~m' => '',
			//remove empty lines (sequence of line-end and white-space characters)
			'/[\r\n]+([\t ]?[\r\n]+)+/s'  => "\n",
			//remove empty lines (between HTML tags); cannot remove just any line-end characters because in inline JS they can matter!
			'/\>[\r\n\t ]+\</s'    => '><',
			//remove "empty" lines containing only JS's block end character; join with next line (e.g. "}\n}\n</script>" --> "}}</script>"
			'/}[\r\n\t ]+/s'  => '}',
			'/}[\r\n\t ]+,[\r\n\t ]+/s'  => '},',
			//remove new-line after JS's function or condition start; join with next line
			'/\)[\r\n\t ]?{[\r\n\t ]+/s'  => '){',
			'/,[\r\n\t ]?{[\r\n\t ]+/s'  => ',{',
			//remove new-line after JS's line end (only most obvious and safe cases)
			'/\),[\r\n\t ]+/s'  => '),',
			//remove quotes from HTML attributes that does not contain spaces; keep quotes around URLs!
			'~([\r\n\t ])?([a-zA-Z0-9]+)="([a-zA-Z0-9_/\\-]+)"([\r\n\t ])?~s' => '$1$2=$3$4', //$1 and $4 insert first white-space character found before/after attribute
	);
	$body = preg_replace(array_keys($replace), array_values($replace), $body);
	
	//remove optional ending tags (see http://www.w3.org/TR/html5/syntax.html#syntax-tag-omission )
	$remove = array(
			'</option>', '</li>', '</dt>', '</dd>', '</tr>', '</th>', '</td>'
	);
	$body = str_ireplace($remove, '', $body);
	
	return ($body);
}


function getUrlDetail($url){
	$urls = parse_url($url);
	$path = explode('/', $urls['path']);
	foreach (array_reverse($path) as $url){
		//view($url);
		if($url != ""){			
			return $url;
		}
	}
}


function tourInfoCategoryDetail($o=[]){
	$inline_css = isset($o['inline_css']) ? $o['inline_css'] : false;
	return [
			['code'=>'TRANSPORT','title'=>'Thông tin vận chuyển','icon'=>'fa fa-car',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	<tbody>
		<tr>
			<td>Đang cập nhật</td>
			 
		</tr>
		 
	</tbody>
</table>'
			],
			['code'=>'FLIGHT','title'=>'Thông tin chuyến bay','icon'=>'fa fa-plane',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	<tbody>
		<tr>
			<td>Ngày đi: </td>
			<td>Đến:&nbsp;</td>
			<td>Chuyến bay:&nbsp;</td>
		</tr>
		<tr>
			<td>Ngày về:&nbsp;</td>
			<td>Đến:&nbsp;</td>
			<td>Chuyến bay:</td>
		</tr>
	</tbody>
</table>'
			],
			['code'=>'HOTEL','title'=>'Thông tin khách sạn','icon'=>'fa fa-hotel',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	<thead>
		<tr style="font-weight: bold; font-size: 16px;">
			<td>T&ecirc;n kh&aacute;ch sạn</td>
			<td>Địa chỉ</td>
			<td>Điện thoại</td>
			<td>Website</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>
'
			],
			['code'=>'GUIDE','title'=>'Thông tin hướng dẫn viên','icon'=>'fa fa-user',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	<thead>
		<tr style="font-weight: bold; font-size: 16px;">
			<td>Họ tên</td>
			<td>Địa chỉ</td>
			<td>Điện thoại</td>
			 
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Chờ báo sau</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		 
		</tr>
	</tbody>
</table>
'
					
	],

			['code'=>'AGG','title'=>'Thông tin tập trung','icon'=>'fa fa-info',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	 
	<tbody>
		<tr>
			<td>Ngày giờ tập trung</td>
			<td>Đang cập nhật</td>
		 
			 
		</tr>
<tr>
			 
			<td>Nơi tập trung</td>
			<td> </td>
			 
		</tr>
<tr>
			 
		
			<td>Ngày giờ họp đoàn</td>
			 	<td></td>
		</tr>
	</tbody>
</table>
'],
			['code'=>'NET_PRICE','title'=>'Giá tour cơ bản','icon'=>'fa fa-money',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	<thead>
		<tr style="font-weight: bold; font-size: 16px;">
<td></td>
			<td>Giá người lớn (Từ 12 tuổi trở lên)</td>
			<td>Giá trẻ em (Từ 2 tuổi đến dưới 12 tuổi)</td>
			<td>Giá trẻ nhỏ (Dưới 2 tuổi)</td>
			 
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Giá tour cơ bản</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		 <td>&nbsp;</td>
		</tr>
<tr>
			<td >Phụ thu phòng đơn</td>
			<td colspan=3>&nbsp;</td>
			 
		</tr>
	</tbody>
</table>'
					
			],
			['code'=>'PRICE','title'=>'Giá tour & phụ thu phòng đơn','icon'=>'fa fa-money',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	<thead>
		<tr style="font-weight: bold; font-size: 16px;">
<td>Loại khách</td>
			<td>Việt Nam</td>
			<td>Nước ngoài</td>
			<td>Land tour</td>
					
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Giá người lớn (Từ 12 tuổi trở lên)</td>
			<td></td>
			<td></td>
		 <td></td>
		</tr>
<tr>
 
			<td>Giá trẻ em (Từ 5 tuổi đến dưới 12 tuổi)</td>
			<td></td>
			<td></td>
		 <td></td>
		</tr>
<tr> 
			<td>Giá trẻ nhỏ (Từ 2 tuổi đến dưới 5 tuổi)</td>
		 <td></td>
			<td></td>
		 <td></td>
		</tr>

<tr>
	 
		 <td>Giá em bé (Dưới 2 tuổi)</td>
<td></td>
			<td></td>
		 <td></td>
		</tr>

<tr>
			<td >Phụ thu phòng đơn</td>
			<td></td>
			<td></td>
		 <td></td>
					
		</tr>
	</tbody>
</table>'
					
			],
			['code'=>'LAND','title'=>'Land tour','icon'=>'fa fa-street-view',
					'templete'=>'<table cellpadding="0" cellspacing="0" '.(isset($inline_css) && $inline_css ? 'style="border-collapse: collapse;width: 100%;max-width: 100%;margin-bottom: 20px;border: 1px solid #ddd;"' : '').' class="table table-bordered">
	<thead>
		<tr style="font-weight: bold; font-size: 16px;">
<td></td>
			<td>Giá người lớn (Từ 12 tuổi trở lên)</td>
			<td>Giá trẻ em (Từ 2 tuổi đến dưới 12 tuổi)</td>
			<td>Giá trẻ nhỏ (Dưới 2 tuổi)</td>
					
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Giá tour cơ bản</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		 <td>&nbsp;</td>
		</tr>
<tr>
			<td >Phụ thu phòng đơn</td>
			<td colspan=3>&nbsp;</td>
					
		</tr>
	</tbody>
</table>'
					
	],			
			['code'=>'INCLUDE_AND_NOT','title'=>'Dịch vụ bao gồm & không bao gồm','icon'=>'fa fa-object-group'],
			
			['code'=>'NOTE','title'=>'Ghi chú','icon'=>'fa fa-sticky-note-o'], 
			
		 
			 
	];
}

function getTourInfoCategoryDetail($code){
	foreach (tourInfoCategoryDetail() as $v1){
		if($code == $v1['code']) return $v1;
	}
} 


function downloadImage($image_url, $image_file){
	$fp = fopen ($image_file, 'w+');              // open file handle
	
	$ch = curl_init($image_url);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
	curl_setopt($ch, CURLOPT_FILE, $fp);          // output to file
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1000);      // some large value to allow curl to run for a long time
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
	// curl_setopt($ch, CURLOPT_VERBOSE, true);   // Enable this line to see debug prints
	curl_exec($ch);
	
	curl_close($ch);                              // closing curl handle
	fclose($fp);                                  // closing file handle
} 

function check_base64_image($data) {		  	
	if (strpos($data, 'data:image') !== false) {
		return true;
	}
	return false;
}

function includeFolder($dir){
	$files = scandir($dir);
	if(!empty($files)){
		foreach ($files as $file){
			switch ($file){
				case '.': case '..': break;
				default: include_once $dir . DIRECTORY_SEPARATOR . $file;
			}
		}
	}
}

function showCurrency($number, $o = []){
	$decimal = isset($o['decimal_number']) ? $o['decimal_number'] : 0;
	$number = number_format($number,$decimal);
	$display_type = isset($o['display_type']) ? $o['display_type'] : 1;
	$code = isset($o['code']) ? $o['code'] : '';
	$symbol= isset($o['symbol']) ? $o['symbol'] : '';
	$symbol2= isset($o['symbol2']) ? $o['symbol2'] : $symbol;
	
	switch ($display_type){
		case 1: $number = $number . $symbol; break;
		case 2: $number = $number . ' '. $code; break;
		case 3: $number = $symbol . $number; break;
		case 4: $number = $code .' '. $number; break;
		case 5: $number = $number . $symbol2; break;
		case 6: $number = $symbol2 .'' . $number; break; 
		case 7: $number = $number .' '. $symbol2; break;
	}
	
	return $number; 
}

/*
 * Hàm dịch ngôn ngữ
 */

function t($lang_code, $lang = __LANG__, $params = []){ 
	return Yii::$app->t->translate($lang_code, $lang, $params);
}

function in_array3($needle , $haystack , $field = 'id' ){
	$state = false;
	
	if(!empty($haystack)){
		foreach ($haystack as $v){
			if(isset($v[$field]) && $v[$field] == $needle){
				$state = true;
			}
		}
	}
	
	return $state;
}

function renderDeadlineInput($o = []){
	$label = isset($o['label']) ? $o['label'] : '';
	$lang = isset($o['lang']) ? $o['lang'] : __LANG__;
	
	$html = '';
	$html .= $label != "" ? '<label class="col-sm-12 control-label">'.$label.'</label>' : '';
	
	$html .= '<div class="input-group-date col-sm-12">';
	
	$date = isset($o['date']) ? $o['date'] : [];
	if(!empty($date)){	
		$html .= '<input type="text"  title="Ngày tháng năm"
		class="form-control input-date '.(isset($date['class']) ? $date['class'] : '').'" 
		name="'.(isset($date['name']) ? $date['name'] : 'deadline[date]').'" 
		value="'.(isset($date['value']) ? $date['value'] : '').'" ';
		if(isset($date['attrs']) && !empty($date['attrs'])){
			foreach ($date['attrs'] as $key=>$value){
				$html .= $key . '="'.$value.'" ';
			}
		}
		$html .= '/>';
	}
	$field = 'hour';
	$hour = isset($o['hour']) ? $o['hour'] : [];
	if(!empty($hour)){
		$html .= '<select type="text" title="Giờ"
		class="form-control input-'.$field.' '.(isset($hour['class']) ? $hour['class'] : '').'"
		name="'.(isset($hour['name']) ? $hour['name'] : 'deadline['.$field.']').'" ';
		if(isset($hour['attrs']) && !empty($hour['attrs'])){
			foreach ($hour['attrs'] as $key=>$value){
				$html .= $key . '="'.$value.'" ';
			}
		}
		$html .= '>';
		for($i=0;$i<24;$i++){
			$html .= '<option '.(isset($hour['value']) && $hour['value']==$i ? 'selected' : '').' value="'.danhso($i,2,['allowNull'=>false]).'">'.danhso($i,2,['allowNull'=>false]).'</option>';
		}
		$html .= '</select>';
	}
	$field = 'minute';
	$input = isset($o['minute']) ? $o['minute'] : [];
	if(!empty($input)){
		$html .= '<select type="text" title="Phút"
		class="form-control input-'.$field.' '.(isset($input['class']) ? $input['class'] : '').'"
		name="'.(isset($input['name']) ? $input['name'] : 'deadline['.$field.']').'"
		';
		if(isset($input['attrs']) && !empty($input['attrs'])){
			foreach ($input['attrs'] as $key=>$value){
				$html .= $key . '="'.$value.'" ';
			}
		}
		$html .= '>';
		for($i=0;$i<60;$i++){
			$html .= '<option '.(isset($input['value']) && $input['value']==$i ? 'selected' : '').' value="'.danhso($i,2,['allowNull'=>false]).'">'.danhso($i,2,['allowNull'=>false]).'</option>';
		}
		$html .= '</select>';
	}
	$field = 'second';
	$input = isset($o[$field]) ? $o[$field] : [];
	if(!empty($input)){
		$html .= '<select type="text" title="Giây"
		class="form-control input-'.$field.' '.(isset($input['class']) ? $input['class'] : '').'"
		name="'.(isset($input['name']) ? $input['name'] : 'deadline['.$field.']').'"
		';
		if(isset($input['attrs']) && !empty($input['attrs'])){
			foreach ($input['attrs'] as $key=>$value){
				$html .= $key . '="'.$value.'" ';
			}
		}
		$html .= '>';
		for($i=0;$i<59;$i++){ 
			$html .= '<option '.(isset($input['value']) && $input['value']==$i ? 'selected' : '').' value="'.danhso($i,2,['allowNull'=>false]).'">'.danhso($i,2,['allowNull'=>false]).'</option>';
		}
		$html .= '</select>';
	}
	 
	
	$field = 'ext_time';
	$input = isset($o[$field]) ? $o[$field] : [];
	if(!empty($input)){
		$html .= '<select type="text" title="Sai số"
		class="form-control input-'.$field.' '.(isset($input['class']) ? $input['class'] : '').'"
		name="'.(isset($input['name']) ? $input['name'] : 'deadline['.$field.']').'"
		';
		if(isset($input['attrs']) && !empty($input['attrs'])){
			foreach ($input['attrs'] as $key=>$value){
				$html .= $key . '="'.$value.'" '; 
			}
		}
		$html .= '>';
		for($i=0;$i<121;$i++){
			$html .= '<option '.(isset($input['value']) && $input['value']==$i ? 'selected' : '').' value="'.$i.'">+'.$i.'</option>';
		}
		$html .= '</select>';
	}
	
	$field = 'ext_type';
	$input = isset($o[$field]) ? $o[$field] : [];
	if(!empty($input)){
		$html .= '<select
		class="form-control input-'.$field.' '.(isset($input['class']) ? $input['class'] : '').'"
		name="'.(isset($input['name']) ? $input['name'] : 'deadline['.$field.']').'" ';
		if(isset($input['attrs']) && !empty($input['attrs'])){
			foreach ($input['attrs'] as $key=>$value){
				$html .= $key . '="'.$value.'" ';
			}
		}
		$html .= '>';
		
		$html .= '
		<option '.(isset($input['value']) && $input['value'] == 1 ? 'selected' : '').' value="1">'.Yii::$app->t->translate('label_day',$lang).'</option>';
		$html .= isset($o['hour']) && !empty($o['hour']) ? '<option '.(isset($input['value']) && $input['value'] == 2 ? 'selected' : '').' value="2">'.Yii::$app->t->translate('label_hour',$lang).'</option>' : '';
		$html .= isset($o['minute']) && !empty($o['minute']) ? '<option '.(isset($input['value']) && $input['value'] == 3 ? 'selected' : '').' value="3">'.Yii::$app->t->translate('label_minute',$lang).'</option>' : '';
		$html .= isset($o['second']) && !empty($o['second']) ? '<option '.(isset($input['value']) && $input['value'] == 4 ? 'selected' : '').' value="4">'.Yii::$app->t->translate('label_second',$lang).'</option>' : '';
		
		$html .= '</select>';
	}
	
	
$html .= '</div>';
	
	return $html;
}



function readDeadlineExType($value, $type=1, $lang = __LANG__){
	$html = "±$value ";
	switch ($type){
		case 1: $html .= Yii::$app->t->translate('label_day',$lang); break;
		case 2: $html .= Yii::$app->t->translate('label_hour',$lang); break;
		case 3: $html .= Yii::$app->t->translate('label_minute',$lang); break;
		case 4: $html .= Yii::$app->t->translate('label_second',$lang); break;
	}
	return $html;
}



function replaceCode($code_rule, $regex = []){	 
	return str_replace(array_keys($regex), array_values($regex), $code_rule);
}
 
function showLocalSelect($o = []){
	
	$input_local_id = isset($o['input_local_id']) && is_array($o['input_local_id']) ? $o['input_local_id'] : [];
	$input_address = isset($o['input_address']) && is_array($o['input_address']) ? $o['input_address'] : [];
	$local_id = isset($o['local_id']) ? $o['local_id'] : 0; 
	$local = Yii::$app->zii->parseCountry($local_id,234);
	$label = isset($o['label']) ? $o['label'] : 'Vị trí địa lý'; 
	$group_class = isset($o['group_class']) ? $o['group_class'] : '';
	$country_class = isset($o['country_class']) ? $o['country_class'] : '';
	$ajax_action = isset($o['ajax_action']) ? $o['ajax_action'] : 'ajax';
	$select_class = isset($o['select_class']) ? $o['select_class'] : 'col-lg-2 col-sm-3 col-xs-6';
	
	$html = '';
	$respon = randString(8);
	$rs_address = randString(8);
	$target = randString(8);$target4= randString(8);
	
	$html .= '<fieldset class="f12px">
    <legend><b>'.$label.'</b></legend><div class="'.$group_class.'">';
	$html .= '<div class="'.$select_class.' mgb5 mgt5 '.$country_class.'">
<select data-allow_single_deselect="1" data-placeholder="Quốc gia" title="Quốc gia"
class="form-control chosen-select '.$respon.'"
data-selected="'.$local['country']['id'].'"
data-target-selected="'.$local['province']['id'].'"
data-respon=".'.$respon.'"
data-target=".'.$target.'"
data-target2=".'.$target4.'"
data-role="v2-show-local"
data-ajax-action="'.$ajax_action.'"
onchange="call_ajax_function(this)"
data-action="v2-load-local-country">';
	foreach (\app\modules\admin\models\Local::getAllCountry() as $k=>$v){
	//if($local['country']['id']>0){
		$html .= '<option '.($local['country']['id'] == $v['id'] ? 'selected' : '').' value="'.$v['id'].'">'.uh($v['title']).'</option>';
	//}else{
	//	$html .= '<option></option>';
	//}
	}
$html .= '</select></div>';

	$respon2 = randString(8);
	$target2 = randString(8);
	$html .= '<div class="'.$select_class.'  mgb5 mgt5">
<select data-allow_single_deselect="1" data-placeholder="Tỉnh / Thành phố" title="Tỉnh / Thành phố"
data-selected="'.$local['province']['id'].'"
data-target-selected="'.$local['district']['id'].'"
data-target=".'.$target2.'"
data-target2=".'.$target4.'"
data-role="v2-show-local"
data-ajax-action="'.$ajax_action.'"
onchange="call_ajax_function(this)"
data-action="v2-load-local-country"
class="form-control chosen-select '.$target.'">';
	if($local['province']['id']>0){
		$html .= '<option selected value="'.$local['province']['id'].'">'.showLocalName($local['province']['title'],$local['province']['type_id']).'</option>';
	}else{
		$html .= '<option></option>';
	}
$html .= '</select></div>';  

	$respon3 = randString(8);
	$target3 = randString(8);
	$html .= '<div class="'.$select_class.'  mgb5 mgt5">
<select data-allow_single_deselect="1" data-placeholder="Quận / Huyện" title="Quận / Huyện"
data-selected="'.$local['district']['id'].'" 
data-target-selected="'.$local['ward']['id'].'"
data-target=".'.$target3.'"
data-target2=".'.$target4.'"
data-ajax-action="'.$ajax_action.'"
onchange="call_ajax_function(this)"
data-action="v2-load-local-country"
class="form-control chosen-select '.$target2.'">';
	if($local['district']['id']>0){
		$html .= '<option selected value="'.$local['district']['id'].'">'.showLocalName($local['district']['title'],$local['district']['type_id']).'</option>';
	}else{
		$html .= '<option></option>';
	}
$html .= '</select></div>'; 
		 
	$html .= '<div class="'.$select_class.'  mgb5 mgt5">
<select data-allow_single_deselect="1" data-placeholder="Phường / Xã" title="Phường / Xã" 
data-selected="'.$local['ward']['id'].'"
data-target-selected="'.$local['ward']['id'].'"
data-target2=".'.$target4.'"
onchange="call_ajax_function(this)"
data-action="v2-load-local-country"
data-ajax-action="'.$ajax_action.'"
class="form-control chosen-select '.$target3.'">';
	if($local['ward']['id']>0){
		$html .= '<option selected value="'.$local['ward']['id'].'">'.showLocalName($local['ward']['title'],$local['ward']['type_id']).'</option>';
	}else{
		$html .= '<option></option>';
	}
$html .= '</select></div>'; 
	if(!empty($input_local_id)){
		$name = isset($input_local_id['name']) ? $input_local_id['name'] : 'f[local_id]';
		$value = isset($input_local_id['value']) ? $input_local_id['value'] : $local_id;
		
		$html .= '<input type="hidden" name="'.$name.'" value="'.$value.'" class="'.$target4.'"/>';
	}
	
	if(!empty($input_address)){
		$label = isset($input_address['label']) ? $input_address['label'] : '';
		$display_country = isset($input_address['display_country']) && !$input_address['display_country'] ? false : true;
		$name = isset($input_address['name']) ? $input_address['name'] : 'f[address]';
		$html .= $label != "" ? '<label class="col-sm-12 control-label">'.$label.'</label>' : "";
		$value = isset($input_address['value']) ? $input_address['value'] : '';
		$input_value = isset($input_address['input_value']) ? $input_address['input_value'] : '';
		//view($display_country);
		$full_address = Yii::$app->zii->showFullLocal($local_id, $input_value, ['display_country'=>$display_country]);
		$full_address_class = isset($o['full_address_class']) ? $o['full_address_class'] : '';
		$street_address_class = isset($o['street_address_class']) ? $o['street_address_class'] : '';
		$html .= '<div class="col-sm-12">
<input type="text" name="'.$input_address['input_name'].'"
onblur="call_ajax_function(this)"
data-action="address_street_change"
data-target=".'.$rs_address.'"
data-local_id="'.$local_id.'"
class="form-control ex_'.$target4.' '.$street_address_class.'" 
placeholder="'.$label.'" value="'.$input_value.'">
<input type="hidden" class="'.$rs_address.'" name="'.$name.'" value="'.$full_address.'"/>
<label class="control-label '.$full_address_class.'">Địa chỉ đầy đủ: <span class="green fulladdress_preview">'.$full_address.'</span></label>
</div>';
	
	}
	$html .= '<input type="hidden" class="auto_play_script_function" value="jQuery(\'.'.$respon.'\').change();"/>';
  	$html .= '</div></fieldset>';
	
	 
	
	return $html;
}

function showGenderInput($gender){
	$html = '<select name="f[gender]" class="form-control chosen-select">
<option value="1" '.($gender == 1 ? 'selected' : '').'>Ông</option>
<option value="0" '.($gender == 0 ? 'selected' : '').'>Bà</option>
<option value="2" '.($gender == 2 ? 'selected' : '').'>Khác</option>
</select>';
	return $html;
}

function showGenderName($gender){
	$html = '';
	switch ($gender){
		case 1:
			$html = 'Mr. ';
			break;
		case 0:
			$html = 'Ms. ';
			break;	
		case 3:
			$html = 'Mrs. ';
			break;		
		case 4:
			$html = 'Miss. ';
			break;		
			
	}
	return $html;
}

function getBeginTimeOfDate($date, $o = []){
	$format = isset($o['format']) ? $o['format'] : 'Y-m-d 00:00:00';
	return date($format,ctime(['string'=>$date,'return_type'=>1]));
}

function getEndTimeOfDate($date, $o = []){
	$format = isset($o['format']) ? $o['format'] : 'Y-m-d 23:59:59';
	return date($format,ctime(['string'=>$date,'return_type'=>1]));
}



function downloadFile($file_name, $file_type = 'excel', $deleteAfterDownload = false) {
	if (empty($file_name)) {
		//            return $this->goBack();
		return 0;
	}
	$baseRoot = Yii::getAlias('@webroot') . "/uploads/";
	$file_name = $baseRoot . $file_name;
	//echo $file_name,"<BR/>";
	if (!file_exists($file_name)) {
		//HzlUtil::setMsg("Error", "File not exist");
		return 0;
	}
	$fp = fopen($file_name, "r");
	$file_size = filesize($file_name);
	if ($file_type == 'excel') {
		header('Pragma: public');
		header('Expires: 0');
		header('Content-Encoding: none');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Description: File Transfer');
		Header("Content-Disposition: attachment; filename=" . basename($file_name));
		header('Content-Transfer-Encoding: binary');
		Header("Content-Length:" . $file_size);
	} else if ($file_type == 'picture') { //pictures
		Header("Content-Type:image/jpeg");
		Header("Accept-Ranges: bytes");
		Header("Content-Disposition: attachment; filename=" . basename($file_name));
		Header("Content-Length:" . $file_size);
	} else { //other files
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Content-Disposition: attachment; filename=" . basename($file_name));
		Header("Content-Length:" . $file_size);
	}
	
	$buffer = 1024;
	$file_count = 0;
	while (!feof($fp) && $file_count < $file_size) {
		$file_con = fread($fp, $buffer);
		$file_count+=$buffer;
		echo $file_con;
	}
	fclose($fp);
	if ($deleteAfterDownload) {
		unlink($file_name);
	}
	return 1;
}