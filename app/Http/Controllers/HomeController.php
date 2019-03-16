<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller;
use App\Models\Activity;
use App\Models\Reward;
use App\Models\Signup;
use App\Models\Zhulip;
use App\Models\Zhuli;
use App\Models\Zhuan;
use App\Models\Brand;
use App\Models\Scorep;
use App\Models\Scoreuser;
use App\Models\Scorerule;
use App\Models\Titletype;
use App\Models\Danmu;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{


	public function pre($uid=0,$aid=0){
		$this->common("index/{$uid}/{$aid}");
	}

	public function common($c = ''){
        $openid = session('openid');
        $token = session('token');
        if(empty($openid) || empty($token)){
            $url ="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxa07701e1b107027c&redirect_uri=https://742.fmxcx0513.com/".$c."&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
            header("Location:".$url);die;
        }else{
            return redirect('/'.$c);
        }
    }

	public function index(Request $request,$uid=0,$aid=0){
		$aid = $aid!=0?$aid:1;
		$openid = session('openid');
        $token = session('token');
        $code = !empty($request->code)?$request->code:'';
        if(!empty($code)) {
            $userinfo = $this->getUserInfoAll($code);
        }else{
            $userinfo = $this->getUserInfo($token,$openid);
        }
        if(empty($userinfo['openid'])) return redirect("/{$uid}/{$aid}");
        $user = User::where(['openid'=>$userinfo['openid']])->first();
        $fromuser = [];
        if($uid>0) $fromuser = User::find($uid);
        if(!$user){
            $id = User::insertGetId([
                'openid'=>$userinfo['openid'],
                'name'=>$this->removeEmoji($userinfo['nickname']),
                'avatar'=>$userinfo['headimgurl'],
                'reg_time'=>date('Y-m-d H:i:s')
                ]);

            $srule = Scorerule::where(['type'=>3])->first();

            $fen = mt_rand($srule['min'],$srule['max']);
            Scoreuser::insertGetId([
                'uid'=>$id,
                'type'=>3,
                'fen'=>$fen,
                'content'=>'阅读注册',
                'aid'=>$aid,
                'at'=>date('Y-m-d H:i:s')
                ]);
            if($uid>0 && $uid!=$id){
            	Scoreuser::insertGetId([
	                'uid'=>$uid,
	                'type'=>3,
	                'fen'=>$fen,
	                'content'=>"id为{$id}的".$userinfo['nickname'].'阅读了',
	                'aid'=>$aid,
	                'at'=>date('Y-m-d H:i:s')
	                ]);
            }
        }
        $myuid = !$user?$id:$user->id;
        $openid = isset($userinfo['openid'])?$userinfo['openid']:'';


		
		$act = Activity::where(['id'=>$aid])->first();
		$titletype = [];
		if(!empty($act->titletype)){
			$titletype = Titletype::whereIn('id',$act->titletype)->get()->toArray();
		}
		$reward1 = [];
		if(!empty($act->reward1)){
			$reward1 = Reward::whereIn('id',$act->reward1)->get()->toArray();
		}

		$reward2 = [];
		if(!empty($act->reward2)){
			$reward2 = Reward::whereIn('id',$act->reward2)->get()->toArray();
		}
		
		$jiang = [1 => '第一名', 2 => '第二名', 3 => '第三名',4=>'第四名',5=>'第五名',6=>'第六名',7=>'第七名'];
		$danmu = [];
		if(!empty($act->danmu)){
			$danmu = Danmu::whereIn('id',$act->danmu)->get()->toArray();
		}

		$brand = [];
		if(!empty($act->brand)){
			$brand = Brand::whereIn('id',$act->brand)->get()->toArray();
		}

		$scorerule = [];
		if(!empty($act->scorerule)){
			$scorerule = Scorerule::whereIn('id',$act->scorerule)->get()->toArray();
		}

		$signup = Signup::where(['aid'=>$aid])->orderBy('sign_at','desc')->limit(20)->get()->toArray();
		$signup = $this->toFormat($signup);

		$zhulip = Zhulip::where(['aid'=>$aid])->orderBy('zhu','desc')->limit(20)->get()->toArray();
		$zhulip = $this->toFormat($zhulip);
		$scorep = Scorep::where(['aid'=>$aid])->orderBy('score','desc')->limit(20)->get()->toArray();
		$scorep = $this->toFormat($scorep);

		// dd($signup);
		// $act->pics = json_decode($act->pics,1); 
		
		$mytongji = [
			'score'=>0,
			'scorep'=>0,
			'zhuli'=>0,
			'zhulip'=>0,
			'zhuan'=>0
		];
		$mytongji['score'] = Scoreuser::where(['uid'=>$myuid,'aid'=>$aid])->sum('fen');

		$res = Scorep::where(['uid'=>$myuid,'aid'=>$aid])->first();
		$mytongji['scorep'] = $res?$res->id:0;

		$mytongji['zhuli'] = Zhuli::where(['uid'=>$myuid,'aid'=>$aid])->count();

		$res = Zhulip::where(['uid'=>$myuid,'aid'=>$aid])->first();
		$mytongji['zhulip'] = $res?$res->id:0;

		$mytongji['zhuan'] = Zhuan::where(['uid'=>$myuid])->orWhere(['zhuanid'=>$myuid])->count();

		return view('home',['act'=>$act,'reward1'=>$reward1,'reward2'=>$reward2,'jiang'=>$jiang,'danmu'=>$danmu,'signup'=>$signup,'zhulip'=>$zhulip,'scorep'=>$scorep,'brand'=>$brand,'fromuser'=>$fromuser,'myuid'=>$myuid,'openid'=>$openid,'user'=>$user,'mytongji'=>$mytongji,'titletype'=>$titletype,'scorerule'=>$scorerule]);
		
	}


	public function dianzan(Request $request){

		$fromuserid  = $request->input('fromuserid');
		$myuid  = $request->input('myuid');
		$aid  = $request->input('aid');
		if($fromuserid == $myuid) return ['code'=>1000,'msg'=>'不可以为自己助力'];
		$res = Zhuli::where(['aid'=>$aid,'uid'=>$fromuserid,'zhuid'=>$myuid])->first();
		if($res) return ['code'=>1000,'msg'=>'您已经助力过了'];
		Zhuli::insertGetId([
			'aid'=>$aid,
			'uid'=>$fromuserid,
			'zhuid'=>$myuid,
			'at'=>date('Y-m-d H:i:s')
		]);
		$srule = Scorerule::where(['type'=>2])->first();
		$fen = mt_rand($srule['min'],$srule['max']);
		Scoreuser::insertGetId([
            'uid'=>$fromuserid,
            'type'=>2,
            'fen'=>$fen,
            'content'=>'id为'.$myuid.'的用户助力了',
            'aid'=>$aid,
            'at'=>date('Y-m-d H:i:s')
        ]);
        return ['code'=>200,'msg'=>'助力成功'];

	}
	public function moreBm(Request $request){
		 $res = Signup::where([])->paginate(10);
		 return $this->toFormat($res);
	}

	public function moreZhulip(Request $request){
		$res = Zhulip::where([])->paginate(10);
		return $this->toFormat($res);
	}

	public function moreScorep(Request $request){
		$res = Scorep::where([])->paginate(10);
		return $this->toFormat($res);
	}


	// 格式化
	private function toFormat($signup){
		foreach ($signup as $k => $v) {

			$signup[$k]['phones'] = substr_replace($v['phone'],'****',3,4);

			if(!isset($signup[$k]['sign_at'])) continue;
			$signup[$k]['shi'] = '';
			$time = strtotime($signup[$k]['sign_at']);
			$cha = time()-$time;
			if($cha<=60) $signup[$k]['shi'] = $cha.'秒前';
			if($cha<=3600 && $cha>60) $signup[$k]['shi'] = floor($cha/60).'分钟前';
			if($cha<=3600*24 && $cha>3600) $signup[$k]['shi'] = floor($cha/3600).'小时前';
			if($cha>60*24*60) $signup[$k]['shi'] = floor($cha/(3600*24)).'天前';

		}
		return $signup;
	}


	public function getUserInfoAll($code)
    {
        $appid = 'wxa07701e1b107027c';
        $secret = '7ced7c1127360c24a130122219650646';
     
        //第一步:取全局access_token $token =  $global_token;
        
        //第二步:取得openid
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        $oauth2 = $this->getJson($oauth2Url);
        if(empty($oauth2['openid'])) return '';
        $access_token = $oauth2['access_token'];

        //第三步:根据全局access_token和openid查询用户信息    $access_token = $token; 
        $openid = $oauth2['openid'];
        session('token',$access_token);
        session('openid',$openid);
        $get_user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";//有subscribe
        $userinfo = $this->getJson($get_user_info_url);   
        return $userinfo;
    }

    public function getUserInfo($access_token='',$openid='')
    {   
        $get_user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";//有subscribe
        $userinfo = $this->getJson($get_user_info_url);
        return $userinfo;

    }

     // 去除emoji
    public  function removeEmoji($text) {
        $clean_text = "";
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return $clean_text;
    }

     function getJson($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    // 分享
    public function jsToken(){
        echo file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxa07701e1b107027c&secret=7ced7c1127360c24a130122219650646");
    }
    public function get_jsapi_ticket(Request $request){
        $token = $request->access_token;
        echo file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi");
    }

    // 分享积分
    public function addSharefen(Request $request){
        $openid = $request->openid;
        $aid = $request->aid;
        $fromuserid = $request->fromuserid;
        $user = User::where(['openid'=>$openid])->first();
        if(!$user) return;
        $count = Zhuan::where(['aid'=>$aid,'uid'=>$user->id])->where('at','>',date('Y-m-d'))->where('at','<=',date('Y-m-d').' 23:59:59')->count();
        if($count<1){
            Zhuan::insertGetId([
                'uid'=>$user->id,
                'aid'=>$aid,
                'zhuanid'=>$fromuserid,
                'at'=>date('Y-m-d H:i:s')
                ]);
            $srule = Scorerule::where(['type'=>4])->first();
			$fen = mt_rand($srule['min'],$srule['max']);
             Scoreuser::insertGetId([
                'uid'=>$user->id,
                'type'=>4,
                'fen'=>$fen,
                'content'=>'转发',
                'aid'=>$aid,
                'at'=>date('Y-m-d H:i:s')
             ]);
        }
    }


    // 处理报名表单
    public function formsign(Request $request){

        $all = $request->all();
        if(!isset($all['bid']) || empty($all['bid'])) return ['code'=>1000,'msg'=>'请选择意向品牌'];
        if(empty($all['name'])) return ['code'=>1000,'msg'=>'请填写您的姓名'];
        if( !preg_match( '/^1[3|4|5|6|7|8][0-9]\d{8}$/', $all['phone'] )) return ['code'=>1000,'msg'=>'请正确填写您的手机'];
        if(empty($all['code'])) return ['code'=>1000,'msg'=>'验证码未填写'];

        $res = Signup::where(['uid'=>$all['uid'],'aid'=>$all['aid']])->first();
        if($res) return ['code'=>1000,'msg'=>'您已经报名过了'];
        if($all['code']!=session('code')) return ['code'=>1000,'msg'=>'验证码不正确'];

        Signup::insertGetId([
        	'bid'=>$all['bid'],
        	'name'=>$all['name'],
        	'phone'=>$all['phone'],
        	'uid'=>$all['uid'],
        	'aid'=>$all['aid'],
        	'avator'=>$all['avator'],
        	'sign_at'=>date('Y-m-d H:i:s'),
        ]);
        // 报名送积分
        $srule = Scorerule::where(['type'=>1])->first();
        $fen = mt_rand($srule['min'],$srule['max']);
        Scoreuser::insertGetId([
                'uid'=>$all['uid'],
                'type'=>1,
                'fen'=>$fen,
                'content'=>'报名',
                'aid'=>$all['aid'],
                'at'=>date('Y-m-d H:i:s')
        ]);


        // 邀请
        if(!empty($all['who'])){
        	$who = User::where(['name'=>$all['who']])->first();
        	if(!$who) return;
        	$srule = Scorerule::where(['type'=>5])->first();
            $fen = mt_rand($srule['min'],$srule['max']);
        	Scoreuser::insertGetId([
                'uid'=>$who->id,
                'type'=>5,
                'fen'=>$fen,
                'content'=>'邀请了id为'.$all['uid'].'的用户',
                'aid'=>$all['aid'],
                'at'=>date('Y-m-d H:i:s')
                ]);
        }

        return ['code'=>200,'msg'=>'报名成功'];
        
    }


    public function sendYzm(Request $request){
    	$all = $request->all();
    	if( !preg_match( '/^1[3|4|5|6|7|8][0-9]\d{8}$/', $all['phone'] )) return ['code'=>1000,'msg'=>'请正确填写您的手机'];
    	$code = mt_rand(1000,9999);
    	session(['code' => $code]);
    	$content = "【飞米网络】您的报名验证码:{$code} 有效期5分钟";
    	$result = $this->sendCode($all['phone'],$content);
    	if($result==0) return ['code'=>200,'msg'=>'发送成功'];
    	return ['code'=>1000,'msg'=>'多次发送或发送失败'];
    }

    // 发送短信
	function sendCode($phone='',$content=''){
	    $statusStr = array(
	        "0" => "短信发送成功",
	        "-1" => "参数不全",
	        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
	        "30" => "密码错误",
	        "40" => "账号不存在",
	        "41" => "余额不足",
	        "42" => "帐户已过期",
	        "43" => "IP地址限制",
	        "50" => "内容含有敏感词"
	    );
	    if(is_array($phone)) $phone = implode(',', $phone);
	    $smsapi = "http://api.smsbao.com/";
	    $user = "sx1989"; //短信平台帐号
	    $pass = md5("pwdsx1989"); //短信平台密码
	    $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
	    $result =file_get_contents($sendurl) ;
	    return $result;
	}

}