<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>土豆视频播放器</title>
</head>

<body>
<?php
/*
* 土豆视频解析下载工具
* Author: Tekin QQ:932256355
* Site: http://dev.tekin.cn
*/
header("Content-Type:text/html;charset=utf-8");
$url = trim($_REQUEST['url']);
$type = trim($_REQUEST['type']);

// preg_match("#view\/(.*?)\/#", $url, $getid); //id里可以有_下划线=号 - 链接符号
preg_match("/view\/(.*?)\//i", $url, $getid);
$id = trim($getid[1]) ? trim($getid[1]) :'';

function getcontent($weburl)
{
	/**
	 * 调用方式比较简单: echo getcontent($url); 可以抓取网页/也可以抓取图片
批量抓取的时候用file_get_contents可能会飙升服务器cpu和内存, 而 curl方式则不会, 而且还可以伪装马甲防止批量抓取被发现封ip.
	 */
	$user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
	$user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_HEADER, true); // 过滤HTTP头
	curl_setopt($curl, CURLOPT_TIMEOUT, 40);
	curl_setopt($curl, CURLOPT_URL, $weburl);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . $user_IP, 'CLIENT-IP:' . $user_IP)); //伪装IP为用户IP
	curl_setopt($curl, CURLOPT_REFERER, 'http%3A%2F%2Fwww.tudou.com'); //伪装一个来路
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //当前浏览器
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //抓取转跳
	curl_setopt($curl, CURLOPT_BINARYTRANSFER, true) ;
	curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); //gzip解压
	//curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2031.2 Safari/537.36'); //Chrome浏览器
	//curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko'); //IE11浏览器
	
	$data = curl_exec($curl);
	// $infos = (curl_getinfo($curl));//返回抓取网页参数的值(数组);
	curl_close($curl);
	return $data;
}

if (!empty($id))
{ 
	// 通过CURL获取当前连接跳转后的URL地址
	$surl = 'http://www.tudou.com/v/' . $id . '/&resourceId=0_04_05_99/v.swf';

	$content = getcontent($surl);

	preg_match("/Location:\s([^\s]*)/i", $content, $getswf);
	$swfurl = $getswf[1];

	preg_match("/iid=(\d+)(.*?)\&title=([^\/]*)\&mediaType=(\w+)\&totalTime=(\d+)\&hdType=(\d+)(.*?)adSourceId=(\d+)/i", $content, $matches);
	$iid = $matches[1];
	$title = urldecode($matches[3]);
	$hdType = (!empty($type)) ? $type : $matches[6];
	$sid = $matches[8];
	$vxml = 'http://v2.tudou.com/v.action?mt=0&sid=' . $sid . '&refurl=http://www.tudou.com/programs/view/' . $id . '/&st=2&hd=' . $hdType . '&noCache=5406&si=' . $sid . '&vn=02&it=' . $iid . '&pw=&ui=0&retc=1'; 
	
    //通过上面获取的视频XML地址获取FLV视频播放地址
	$flvc = getcontent($vxml);
	preg_match("/http:([^\s].*)<\/f>/i", $flvc, $fcarra);
	$flvurl = $fcarra[1];
	if (!empty($flvurl)) // 单个视频文件
		{ ?>
        <div id="a1"></div>
<script type="text/javascript" src="ckplayer.js" charset="utf-8"></script>
<script type="text/javascript">
	var flashvars={
		f:'<?php echo 'http:'.$flvurl; ?>',
		c:0,
		b:1
		};
	var params={bgcolor:'#FFF',allowFullScreen:true,allowScriptAccess:'always',wmode:'transparent'};
	CKobject.embedSWF('ckplayer.swf','a1','ckplayer_a1','720','576',flashvars,params);
</script>
        
     <?php
      }else{
			echo '视频加载失败,请刷新重试!';
			}
	print_r($ckxml);	
}
else
{
echo '播放地址错误! 请确认你的土豆URL地址, 如  http://www.tudou.com/programs/view/jNCCgpZr0Rg/ ';  
}
?>
</body>
</html>