<?php

if (!defined('THINK_PATH'))
    exit();

/**
  数据查验类，用在表单验证上
  +------------------------------------------
 * php自身的检测函数
 * ctype_digit 检查是否为数字字符串
 * bool is_numeric ( mixed var)
 * bool is_bool ( mixed var)
 * bool is_null ( mixed var)
 * bool is_float ( mixed var)
 * bool is_int ( mixed var)
 * bool is_string ( mixed var)
 * bool is_object ( mixed var)
 * bool is_array ( mixed var)
 * bool is_scalar ( mixed var)
 * string gettype ( mixed var)
  +---------------------------------------------
 * 新添加的检测函数
  isDate()     日期反省
  isTime()     工夫反省
  isInt()      整数反省
  isNum()      数字反省
  isEmail()    邮件反省
  isUrl()      url反省
  isPost()     邮政编码反省
  isPhone()    电话号码反省
  isMobile()   移动电话反省
  isLen()      长度反省
  isIdCard()   身份证反省
  isEnglish() 英文反省
  isGB2312()   简体中文反省
  isIP()       IP反省
  isQQ()       QQ反省
  checkFileType() 文件后缀名反省
 */
class Validator {
     function __construct(){
    
      }

    /* 函数名：CheckLengthBetween($C_char, $I_len1, $I_len2=100)
      // 作 用：判断是否为指定长度内字符串
      // 参 数：$C_char（待检测的字符串）
      // $I_len1 （目标字符串长度的下限）
      // $I_len2 （目标字符串长度的上限）
      // 返回值：布尔值
      // 备 注：无
     */

    static public function checkLengthBetween($C_cahr, $I_len1, $I_len2=100) {
        $C_cahr = trim($C_cahr);
        if (strlen($C_cahr) < $I_len1)
            return false;
        if (strlen($C_cahr) > $I_len2)
            return false;
        return true;
    }

    /* 函数名：isUserName($C_user)
     * 作 用：判断是否为合法用户名 
     * 参 数：$C_user（待检测的用户名） 
     * 返回值：布尔值 
     */

    static public function isUserName($C_user) {

        if (!self::CheckLengthBetween($C_user, 4, 20))
            return false; //宽度检验
 if (is_numeric($C_user))
            return false; //不能为纯数字
 if (!preg_match("/^[_a-zA-Z0-9\x80-\xff]*$/", $C_user))
            return false; //特殊字符检验
 return true;
    }

    /* ValueBetween($N_var, $N_val1, $N_val2)
      作 用：判断是否是某一范围内的合法值
      参 数：$N_var 待检测的值
      $N_var1 待检测值的上限
      $N_var2 待检测值的下限
      返回值：布尔值
     */

    static public function checkValueBetween($N_var, $N_val1, $N_val2) {
        if (($N_var < $N_val1) || ($N_var > $N_val2)) {
            return false;
        }
        return true;
    }

    /* 函数名：CheckMoney($C_Money)
      作 用：检查数据是否是99999.99格式
      参 数：$C_Money（待检测的数字）
      返回值：布尔值
     */

    static public function ISMoney($Money) {  
       if (  preg_match("/^[0-9]*\.?[0-9]{0,2}$/", $Money) ){
          return $Money ;
          }
        return false;
    }

    /*
     * 行动 ：bool isDate($str,$format="")
     * 作用：查验日期的合法 性
     * 阐发 ：默认的合法 日期技俩是以"-"支解的"年-月-日"
     *         当参数$format设置技俩时则服从这个技俩查验
     * 例子：isDate("2006-12-1");isDate("2006-12-1","Y-m-d h:i:s")
     */

    static public function isDate($str, $format="") {
        if (empty($format)) {
            $str = explode("-", $str);
            return @checkdate($str[1], $str[2], $str[0]);
        } else {
            //按规定 的技俩查验
            $unixTime = strtotime($str); //转为时间戳
            $checkDate = date($format, $unixTime);
            return ($checkDate == $str);
        }
    }

//检测时间合法性 
    static public function isTime($str, $format="") {
        if (empty($format)) {
            $str = explode(":", $str);
            if (count($str) != 3)
                return false;
            if ($str[0] >= 24)
                return false;
            if ($str[1] > 60)
                return false;
            if ($str[2] > 60)
                return false;
        }
        else {  //按format来验证
            $unixTime = strtotime($str); //转为时间戳
            $checkDate = date($format, $unixTime);
            if ($checkDate != $str)
                return false;
        }
        return true;
    }

//验证表单数据是否是整数(用is_int()验证不了的)
//前面可以加上可选的标记（- 可能 +）
    static public function isInt($str) {
        $pattern = "/^[-|+]?(([0-9]{1})|([1-9]{1}[0-9]+))$/";
        if (@preg_match($pattern, $str)) {
            return true;
        }
        return false;
    }

//验证是否由纯数字构成 ,is_numeric()验证包孕整数和浮点数	
    static public function isNum($str) {
        $pattern = '/^\d+$/';
        if ( preg_match($pattern, $str) ){
            return $str;
        }
        return false;
    }

//email检测
    static public function isEmail($str) {
        $pattern = '/^[_0-9a-z]+@[0-9a-z-]+(\.[0-9a-z-]+)*\.[a-z]+$/i';
        if (@preg_match($pattern, $str)) {
            return true;
        }
        return false;
    }

//URL检测，只反省 http形式
    static public function isUrl($str) {
        $pattern = '/^http:\/\/[A-Za-z0-9\-]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/';
        if (@preg_match($pattern, $str)) {
            return true;
        }
        return false;
    }

//邮政编码,中国邮政编码是6位数字构成 
    static public function isPost($str, $pattern="") {
        if ($pattern) {
            if (@preg_match($pattern, $str)) {
                return true;
            }
        } else {
            if (@preg_match('/^\d{6}$/', $str)) {
                return true;
            }
        }
        return false;
    }

//电话号码 区号-号码 或 号码 或区号号码
//0751-8120917||07518120917||8120917
    static public function isPhone($str, $pattern="") {
        if ($pattern) {
            if (@preg_match($pattern, $str)) {
                return true;
            }
        } else {
            $pattern = '/^((\(\d{3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}$/';
            if (@preg_match($pattern, $str)) {
                return true;
            }
        }
        return false;
    }

//手机号码区号-号码 或 号码 或区号号码
    static public function isMobile($str, $pattern="") {
        if ($pattern) {
            if (@preg_match($pattern, $str)) {
                return true;
            }
        } else {
            $pattern = '/^1[3-9]\d{9}$/';
            if (@preg_match($pattern, $str)) {
                return true;
            }
        }
        return false;
    }

//字符串长度是否在l1和l2之间,即l1<$str<l2
    static public function isLen($str, $l1, $l2) {
        if (strlen($str) > $l1 && strlen($str) < $l2) {
            return true;
        }
        return false;
    }

//身份证号码
//可以验证15和18位的身份证号码
    static public function isIdCard($str) {
        
    }

//字符串是否整个是英文
    static public function isEnglish($str) {
        $pattern = "/^[a-z]+$/i";
        if (@preg_match($pattern, $str)) {
            return true;
        }
        return false;
    }

//是否是ip
    static public function isIP($str) {
        $s = explode(".", $str);
        if (count($s) != 4)
            return false;
        foreach ($s as $v) {
            if (!is_numeric($v))
                return false;
//if(!is_int($v)) return false;
//不消反省是否带小数,由于小数带小数点"."
//正则检测是否存在192.168.01.02 以0开头的小数
            if (preg_match("/^0[0-9]+$/", $v))
                return false;
            if ($v < 0 || $v > 255)
                return false;
        }
        return false;
    }

//是否是QQ 4-15位
    static public function isQQ($str, $pattern="") {
        if ($pattern) {
            if (@preg_match($pattern, $str)) {
                return true;
            }
        } else {
            $pattern = '/^[1-9]\d{4,15}$/';
            if (@preg_match($pattern, $str)) {
                return true;
            }
        }
        return false;
    }

//反省文件的扩张名是否切合 ,$filetype可以是字符串或数组,并且转达 的数据要整个为小写
//应用 1:checkFileType("gif|rar|jpeg|jpg","lsflkjs.jpeg") return true
//应用 2:checkFileType(array("rar","bmp","gif"),"lsflkjs.jpeg") return false
    static public function checkFileType($filetype, $file) {
        $f = (is_array($filetype)) ? $filetype : explode("|", $filetype);
        $n = strrchr($file, '.'); //截取字符串，找不到则返回""
        $n = (!$n) ? "" : strtolower(substr($n, 1));
//in_array(),是区分巨细写的
        return in_array($n, $f);
    }

//反省数据提交，有post,get两种行动，是否全部都不为空
//留意 :检测不了多选项.没有应用 trim()去掉首尾空格
    static public function checkRequest($post_or_get, $emptyitems="") {
        if (!is_array($post_or_get)) {
            return false;
        }
        if (count($post_or_get) == 0) {
            return false;
        }
        $items = explode("|", $emptyitems);
        foreach ($post_or_get as $key => $value) {
            if ($_REQUEST[$key] == "") {
                if (in_array($key, $items)) {
                    continue;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

//表现全部经受 到的数据列表
    static public function showRequest($_request, $leftnum, $post_or_get="\$_POST") {
        if (!$this->isInt($leftnum) || !$_request

            )return false;
        if (!is_array($_request)) {
            echo $_request;
            return;
        }
        foreach ($_request as $key => $value) {
            $str = "\$" . $key;
            if ($leftnum > strlen($key)) {
                for ($i = 0; $i <= $leftnum - strlen($key); $i++) {
                    $str.="&nbsp;";
                }
            }
            $str.="=&nbsp;&nbsp;" . $post_or_get . "['" . $key . "']";
            if (is_array($_REQUEST[$key]))
                $str.="&nbsp;&nbsp;<b>数组</b>";
            echo $str . "\n";
        }
    }

}

?>