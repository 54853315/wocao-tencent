<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {

	protected $sdk_obj = null;	//接口对象实例
	protected $isOAuth = 0;	//授权程度，1=已授权，0=无
	protected $user = [];	//授权用户的信息
	protected $get = [];

	public function __construct() {
		parent::__construct();

		if(strpos($_SERVER['SERVER_NAME'], 'ali')){
			Alibaba::Xhprof()->start();
		}
		

		import('@.ORG.OAuth');
		import('@.ORG.Tencent');

		OAuth::init(C('client_id'), C('client_secret'));
		Tencent::$debug = C('debug');

		if ($_SESSION['t_access_token'] || ($_SESSION['t_openid'] && $_SESSION['t_openkey'])) {//用户已授权
			$this->isOAuth = 1;
			$this->_getUserInfo();
		}

		if($_GET)
			$this->get = $_GET ; 

		$this->assign('isOAuth',$this->isOAuth);
		$this->assign('user',$this->user);

	}

	public function __destruct(){
		if(strpos($_SERVER['SERVER_NAME'], 'ali')){
			Alibaba::Xhprof()->finish();
		}
		
	}

	private function _getUserInfo(){
		//获取用户信息
		$this->user = json_decode(Tencent::api('user/info'),true);
		if(c('debug')){
			print_r($this->user);
		}
	}

	private function _saveUserInfo(){

	}

	public function index(){
		$this->display();
	}

	//请求授权
	public function login(){
		$callback = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];//回调url
    if ($_GET['code']) {//已获得code
    	$code = $_GET['code'];
    	$openid = $_GET['openid'];
    	$openkey = $_GET['openkey'];
        //获取授权token
    	$url = OAuth::getAccessToken($code, $callback);
    	$r = Http::request($url);
    	parse_str($r, $out);
        //存储授权数据
    	if ($out['access_token']) {
    		$_SESSION['t_access_token'] = $out['access_token'];
    		$_SESSION['t_refresh_token'] = $out['refresh_token'];
    		$_SESSION['t_expire_in'] = $out['expires_in'];
    		$_SESSION['t_code'] = $code;
    		$_SESSION['t_openid'] = $openid;
    		$_SESSION['t_openkey'] = $openkey;

            //验证授权
    		$r = OAuth::checkOAuthValid();

    		if ($r) {
                header('Location: ' . U('index'));//刷新页面
            } else {
            	exit('<h3>授权失败,请重试</h3>');
            }
        } else {
        	exit($r);
        }
    } else {//获取授权code
        if ($_GET['openid'] && $_GET['openkey']){//应用频道
        	$_SESSION['t_openid'] = $_GET['openid'];
        	$_SESSION['t_openkey'] = $_GET['openkey'];
            //验证授权
        	$r = OAuth::checkOAuthValid();
        	if ($r) {
                header('Location: ' . $callback);//刷新页面
            } else {
            	exit('<h3>授权失败,请重试</h3>');
            }
        } else {
        	$url = OAuth::getAuthorizeURL($callback);
        	header('Location: ' . $url);
        }
    }
}


	//删除最后一天的所有微博
    public function lastday(){}

    //执行清理排行榜
    public function top(){}

    public function run(){
		//整理参数
		if(!$_POST['out_keywords']){	//排除关键字
			$out_keywords = explode(',', $_POST['out_keywords']);
		}

		//微博类型,//拉取类型（需填写十进制数字） 0x1：原创发表，0x2：转载。如需拉取多个类型请使用|，如(0x1|0x2)得到3，则type=3即可，填零表示拉取所有类型。
		if(count($_POST['type'])>1)
			$parames['type'] = $_POST['type'];
		elseif($_POST['type']!="")
			$parames['type'] = $_POST['type'];
		else
			$parames['type'] = 0;

		//微博时间 -- 无法控制
		// $start_time = strtotime($_POST['start_date']);
		// $end_time = strtotime($_POST['end_date']);
		// if(!$start_time || !$end_time){
		// 	$this->error("时间不正确哟！");
		// }

		$parames['format'] = 'json';
		$parames['pageflag'] = 0;	//分页方式，0： 第一页 1： 下一页 2： 上一页
		$parames['pagetime'] = 0;	//本页起始时间（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
		$parames['reqnum'] = '5';
		$parames['lastid'] = '0';
		$parames['contenttype'] = 0;	//过滤内容 0-表示所有类型，1-带文本，2-带链接，4-带图片，8-带视频，0x10-带音频
		$r = json_decode(Tencent::api('statuses/broadcast_timeline_ids',$parames),true);
		
		if($r['errcode'] > 0){
			$this->error($r['msg']);
		}

		while(true){
			//理论上从这里将各种参数记录好后，请求阿里云的执行脚本，不过我没做，呵呵

			$last = end($r['data']['info']);	//最后一条微博信息
			$parames['pageflag'] = 1;
			$parames['pagetime'] = $last['timestamp'];
			$parames['lastid'] = $last['id'];
			unset($r);
			$r = json_decode(Tencent::api('statuses/broadcast_timeline_ids',$parames),true);


			if($r['errcode'] == 0 && $r['msg']=='ok'){
				foreach($r['data']['info'] as $k=>$row){
					// echo "即将删除".date("Y-m-d H:i:s",$row['timestamp'])."分发布的一条微博,ID:{$row['id']}<br/>";
					if($this->delWB($row['id'])){
						// echo "删除成功！<br/>";
					}
				}
			}else{
				switch($r['errcode']){
					// $this->error($r['msg'])
				}
			}
			if($this->errArr){
				print_r($this->errArr);
			}
			if($r['msg'] == "have no tweet"){
				// echo '呵呵，没微博了，还删个毛~';
				die('200');
			}
		}//end while
	}

	//根据条件删除微博
	private function delWB($id){
		$parames['format'] = 'json';
		$parames['id'] = $id;
		$r = json_decode(Tencent::api('t/del',$parames,"POST"),true);
		if($r['errcode'] != 0){
			$this->errArr[$id]['msg'] = $r['msg'];
			$this->errArr[$id]['code'] = $r['errcode'];
		}else{
			return true;
		}
	}

	//根据条件获取微博
	private function getWB(){
		echo $this->user['seqid'];
		$parames['format'] = 'json';
		$parames['pageflag'] = '0';	//分页方式，0： 第一页 1： 下一页 2： 上一页
		$parames['pagetime'] = '0';	//本页起始时间（第一页：填0，向上翻页：填上一次请求返回的第一条记录时间，向下翻页：填上一次请求返回的最后一条记录时间）
		$parames['reqnum'] = '20';
		$parames['type'] = '20';
		return json_decode(Tencent::api('statuses/broadcast_timeline',$parames),true);
	}
}