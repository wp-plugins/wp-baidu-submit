<?php
/*
Plugin Name: WP BaiDu Submit
Description: WP BaiDu Submit帮助获得百度站长Sitemap权限的用户自动提交最新文章，加速百度收录。
Version: 1.0
Plugin URI: https://wordpress.org/plugins/wp-baidu-submit/
Author: Include
Author URI: http://www.170mv.com/
*/
/*
Publish Date: 2015-05-09
*/
add_action('publish_post', 'publish_bd_submit', 0);
function publish_bd_submit($post_ID){
	global $post;
	$bd_submit_site = get_option('bd_submit_site');
	$bd_submit_token = get_option('bd_submit_token');
	$bd_submit_enabled = get_option('bd_submit_enabled');
	if($bd_submit_enabled){
		if( empty($post_ID) || empty($bd_submit_site) || empty($bd_submit_token) ) return;
		$api = 'http://data.zz.baidu.com/urls?site='.$bd_submit_site.'&token='.$bd_submit_token;
		if( $post->post_status != "publish" ){
			$url = get_permalink($post_id);
			$ch = curl_init();
			$options =  array(
				CURLOPT_URL => $api,
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => $url,
				CURLOPT_HTTPHEADER => array('Content-Type: text/plain')
			);
			curl_setopt_array($ch, $options);
			$result = curl_exec($ch);
			$result = json_decode($result, true);
			if($result['error']){
				update_option('bd_submit_error',$result['message']);
			}else{
				delete_option('bd_submit_error');
			}
		}
	}
}

add_action('admin_menu', 'bd_submit_add_page');
function bd_submit_add_page() {
	add_options_page('WP BaiDu Submit选项', 'WP BaiDu Submit', 'manage_options', 'bd_submit', 'bd_submit_do_page');
}
function bd_submit_do_page() {
	if($_POST['submit']){
		$bd_submit_site = $_POST['bd_submit_site'];
		$bd_submit_token = $_POST['bd_submit_token'];
		$bd_submit_enabled = $_POST['bd_submit_enabled'];
		if( empty($bd_submit_site) || empty($bd_submit_token) ){
			$bd_submit_enabled = 0;
		}
		update_option('bd_submit_site',$bd_submit_site);
		update_option('bd_submit_token',$bd_submit_token);
		update_option('bd_submit_enabled',$bd_submit_enabled);
	}else{
		$bd_submit_site = get_option('bd_submit_site');
		$bd_submit_token = get_option('bd_submit_token');
		$bd_submit_enabled = get_option('bd_submit_enabled');
		$bd_submit_error = get_option('bd_submit_error');
	}
?>
	<div class="wrap">
		<h2>百度站长平台链接自动提交选项</h2>
		<form method="post" action="#">
			<table class="form-table">
				<tr valign="top"><th scope="row"><label for="bd_submit_site">验证站点域名</label></th>
					<td><input type="text" name="bd_submit_site" id="bd_submit_site" value="<?php echo $bd_submit_site; ?>" class="regular-text" /><br />
					 	在站长平台验证的站点，比如www.example.com
					</td>
				</tr>
				<tr valign="top"><th scope="row"><label for="bd_submit_token">站点准入密钥</label></th>
					<td><input type="text" name="bd_submit_token" id="bd_submit_token" value="<?php echo $bd_submit_token; ?>" class="regular-text" /><br />
						在站长平台申请的推送用的准入密钥，比如：3sM2Wity6fP8TbR0
					</td>
				</tr>
				<tr valign="top"><th scope="row">开启自动提交？</th>
					<td>
					<label for="bd_submit_enabled">
					<input name="bd_submit_enabled" type="checkbox" <?php if($bd_submit_enabled) echo "checked"; ?> id="bd_submit_enabled" value="1" />
					是否开启自动提交，勾选开启，仅对新发布文章有效
					</label>
					</td>
				</tr>
				<tr valign="top"><th scope="row">自动提交使用建议</th>
					<td>
					建议：在发布高质量文章前开启，大量自动提交垃圾文章可能导致失去权限。
					<a href="http://zhanzhang.baidu.com/sitemap/pingindex?site=<?php echo empty($bd_submit_site)?'':'http://'.$bd_submit_site.'/'; ?>" target="_blank">查看主动推送效果</a>
					</td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			<h2>提交返回错误信息记录</h2>
			<table class="form-table">
				<tr valign="top"><th scope="row">提交返回错误</th>
					<td>
					错误信息：<?php if($bd_submit_error){  echo $bd_submit_error; }else{ echo '恭喜，目前没有错误信息'; } ?>
					</td>
				</tr>
			</table>
		</form>
	</div>
	<?php	
}