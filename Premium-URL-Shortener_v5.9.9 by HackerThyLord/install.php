<?php 
/**
 * ====================================================================================
 *                           PREMIUM URL SHORTENER (c) KBRmedia
 * ----------------------------------------------------------------------------------
 * @copyright This software is exclusively sold at CodeCanyon.net. If you have downloaded this
 *  from another site or received it from someone else than me, then you are engaged
 *  in an illegal activity. You must delete this software immediately or buy a proper
 *  license from http://gempixel.com/buy/short.
 *
 *  Thank you for your cooperation and don't hesitate to contact me if anything :)
 * ====================================================================================
 *
 * @author KBRmedia (http://gempixel.com)
 * @link http://gempixel.com 
 * @package Premium URL Shortener
 * @subpackage Application installer
 */
	if(!isset($_SESSION)) session_start();
	$error="";
	$message=(isset($_SESSION["msg"])?$_SESSION["msg"]:"");
	if(!isset($_GET["step"]) || $_GET["step"]=="1" || $_GET["step"] < "1"){
		$step = "1";
	}elseif($_GET["step"] > "1" && $_GET["step"]<="5"){
		$step = $_GET["step"];
	}else{
		die("Oups. Looks like you did not follow the instructions! Please follow the instructions otherwise you will not be able to install this script.");
	}
	switch ($step) {
		case '2':
			if(file_exists("includes/config.php")) $error='Configuration file already exists. Please delete or rename "config.php" and recopy "config_sample.php" from the original zip file. You cannot continue until you do this.';  

			if(isset($_POST["step2"])){
			if (empty($_POST["host"]))  $error.="<p>- You forgot to enter your host.</p>"; 
            if (empty($_POST["name"])) $error.="<p>- You forgot to enter your database name.</p>"; 
            if (empty($_POST["user"])) $error.="<p>- You forgot to enter your username.</p>"; 
	            if(empty($error)){
					 try{
					    $db = new PDO("mysql:host=".$_POST["host"].";dbname=".$_POST["name"]."", $_POST["user"], $_POST["pass"]);
						generate_config($_POST);
		                $query=get_query();
						foreach ($query as $q) {
						  $db->query($q);
						} 
						$_SESSION["msg"]="Database has been successfully imported and configuration file has been created.";
						header("Location: install.php?step=3");
					  }catch (PDOException $e){
					    $error = $e->getMessage();
					  }
          }							
			}
		break;
		case '3':
			if(!file_exists("includes/config.php")) die("<div class='error'>The file includes/config.php cannot be found. If the file includes/config_sample.php exists rename that to includes/config.php and refresh this page.</div>");			
					@include("includes/config.php");
					

					$_SESSION["msg"]="";

					if($db->get("user",["admin"=>"1"], ["limit" => "1"])){
						$error.="<div class='error'>You have already created an admin account! You can no longer use this form.</div>"; 
					}

			    if(isset($_POST["step3"])){
			            if (empty($_POST["email"]))  $error.="<div class='error'>You forgot to enter your email.</div>"; 
			            if (empty($_POST["pass"])) $error.="<div class='error'>You forgot to enter your password.</div>"; 
			            if (empty($_POST["url"])) $error.="<div class='error'>You forgot to enter the url.</div>"; 
			    	if(!$error){

			    	$data=array(
				    	":admin"=>"1",
				    	":email"=>$_POST["email"],
				    	":username"=>$_POST["username"],
				    	":password"=>Main::encode($_POST["pass"]),
				    	":date"=>"NOW()",
				    	":pro"=>"1",
				    	":auth_key"=>Main::encode(Main::strrand()),
				    	":last_payment" => date("Y-m-d H:i:s",time()),
				    	":expiration" => date("Y-m-d H:i:s",time()+315360000),
				    	":api" => Main::strrand(12)
			    	);

					  $db->insert("user",$data);					  
					  $db->update("settings",array("var"=>"?"),array("config"=>"?"),array($_POST["url"],"url"));
					  $db->update("settings",array("var"=>"?"),array("config"=>"?"),array($_POST["email"],"email"));
					  $_SESSION["msg"]="Your admin account has been successfully created.";
					  $_SESSION["site"]=$_POST["url"];
					  $_SESSION["username"]=$_POST["username"];
					  $_SESSION["email"]=$_POST["email"];
					  $_SESSION["password"]=$_POST["pass"];
					  header("Location: install.php?step=4"); 
			        }   
			    }		
		break;
		case '4':
			$_SESSION["msg"]="";
			@include("includes/config.php");
					if(!file_exists(ROOT."/.htaccess")){
					  	$content = "### Generated on ".date("d-m-Y H:i:s", strtotime("now"))."\n\nRewriteEngine On\n\n# Handle Authorization Header\n\n
	RewriteCond %{HTTP:Authorization} ^(.*)\n\nRewriteRule .* - [e=HTTP_AUTHORIZATION:%1]\n\n#Rewritebase /\n\n## Admin \n\nRewriteCond %{REQUEST_FILENAME} !-d\n\nRewriteCond %{REQUEST_FILENAME} !-f\n\nRewriteRule ^admin/(.*)?$ admin/index.php?a=$1 [QSA,NC,L]\n\nRewriteRule ^sitemap.xml$ sitemap.php\n\n## App \n\nRewriteCond %{REQUEST_FILENAME} !-d\n\nRewriteCond %{REQUEST_FILENAME} !-f\n\nRewriteRule ^(.*)?$ index.php?a=$1	[QSA,NC,L]\n\nErrorDocument 404 /index.php?a=404";
					  	$file = fopen(ROOT."/.htaccess", "w");
					  	fwrite($file, $content);
					  	fclose($file);						
					}
		break;
		case '5':
			header("Location: index.php"); 
			unset($_SESSION);
			unlink(__FILE__);
			
			if(file_exists("main.zip")){
				unlink('main.zip');
			}
			if(file_exists("updater.php")){
				unlink('updater.php');
			}
		break;
	}
 ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Premium URL Shortener Installation</title>
	<style type="text/css">
		body{background-color: rgb(237,242,247);font-family:Helvetica, Arial;width:860px;line-height:25px;font-size:13px;margin:0 auto;}a{color:#009ee4;font-weight:700;text-decoration:none;}a:hover{color:#000;text-decoration:none;}.container{background: #fff;box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06);border-radius: 10px;display: block;overflow: hidden;margin: 50px 0;}.container h1{font-size:20px;display:block;border-bottom:1px solid #eee;margin:0!important;padding:20px 10px;}.container h2{color:#999;font-size:18px;margin:10px;}.container h3{background:#fff;border-bottom:1px solid #eee;border-radius:3px 0 0 0;text-align:center;margin:0;padding:20px 0;}.left{float:left;width:258px;}.right{float:left;width:601px;border-left:1px solid #eee;}.form{width:90%;display:block; padding: 10px 20px;}.form label{font-size:15px;font-weight:700;margin:20px 0px 5px;display: block;}.form label a{float:right;color:#009ee4;font:bold 12px Helvetica, Arial; padding-top: 5px;}.form .input{display: block;width: 95%;height: 15px;border: 1px #ddd solid;font: bold 15px Helvetica, Arial;color: #aaa;border-radius: 3px;margin: 10px 0;padding: 10px 25px;}.form .input:focus{border:1px #73B9D9 solid;outline:none;color:#222;box-shadow:inset 1px 1px 3px #ccc,0 0 0 3px #DEF1FA;}.form .button{height:35px;}.button{background-color: #4f37ac;font-weight: 700;background-image: -moz-linear-gradient(0deg, #0854a9 0%, #4f37ac 100%);background-image: -webkit-linear-gradient(0deg, #0854a9 0%, #4f37ac 100%);width:90%;display:block;text-decoration:none;text-align:center;border-radius: 2px;color:#fff;font:15px Helvetica, Arial bold;cursor:pointer;border-radius:25px;margin:30px auto; padding:10px 0;border:0;}.button:active,.button:hover{opacity: 0.9; color: #fff;}.content{color:#999;display:block;border-top:1px solid #eee;margin:10px 0;padding:10px;}li{color:#999;}li.current{color:#000;font-weight:700;}li span{float:right;margin-right:10px;font-size:11px;font-weight:700;color:#00B300;}.left > p{border-top:1px solid #eee;color:#999;font-size:12px;margin:0;padding:10px;}.left > p >a{color:#777;}.content > p{color:#222;font-weight:700;}span.ok{float:right;border-radius:3px;background-color: #59d8c5;font-weight: 700;background-image: -moz-linear-gradient(0deg, #59d8c5 0%, #68b835 100%);background-image: -webkit-linear-gradient(0deg, #59d8c5 0%, #68b835 100%);background-image: -ms-linear-gradient(0deg, #59d8c5 0%, #68b835 100%);color:#fff;padding:2px 10px;}span.fail{float:right;border-radius:3px;background-color: #FF3146;font-weight: 700;background-image: -moz-linear-gradient(0deg, #f04c74 0%, #FF3146 100%);background-image: -webkit-linear-gradient(0deg, #f04c74 0%, #FF3146 100%);background-image: -ms-linear-gradient(0deg, #f04c74 0%, #FF3146 100%);color:#fff;padding:2px 10px;}span.warning{float:right;border-radius:3px;background:#D27900;color:#fff;padding:2px 10px;}.message{background:#1F800D;color:#fff;font:bold 15px Helvetica, Arial;border:1px solid #000;padding:10px;}.error{    background-color: #FF3146;background-image: -moz-linear-gradient(0deg, #f04c74 0%, #FF3146 100%);background-image: -webkit-linear-gradient(0deg, #f04c74 0%, #FF3146 100%);background-image: -ms-linear-gradient(0deg, #f04c74 0%, #FF3146 100%);color:#fff;font:bold 15px Helvetica, Arial;margin:0;padding:10px;}.inner,.right > p{margin:10px;}
	</style>
  </head>
  <body>
  	<div class="container">
  		<div class="left">
			<h3>Installation Process</h3>
			<ol>
				<li<?php echo ($step=="1")?" class='current'":""?>>Requirement Check <?php echo ($step>"1")?"<span>Completed</span>":"" ?></li>
				<li<?php echo ($step=="2")?" class='current'":""?>>Database Installation<?php echo ($step>"2")?"<span>Completed</span>":"" ?></li>
				<li<?php echo ($step=="3")?" class='current'":""?>>Admin Creation<?php echo ($step>"3")?"<span>Completed</span>":"" ?></li>
				<li<?php echo ($step=="4")?" class='current'":""?>>Installation Complete</li>
			</ol>
			<p>
				<a href="https://gempixel.com/" target="_blank">Home</a> | 
				<a href="https://gempixel.com/products" target="_blank">Products</a> | 
				<a href="https://support.gempixel.com/" target="_blank">Support</a>
				<p>2012-<?php echo date("Y") ?> &copy; <a href="https://gempixel.com" target="_blank">GemPixel</a><br>All Rights Reserved.</p>
			</p>
  		</div>
  		<div class="right">
					<h1>Installation of Premium URL Shortener</h1> 
					<?php if(!empty($message)) echo "<div class='message'>$message</div>"; ?>
					<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
					<?php if($step=="1"): ?>		
						<h2>1.0 Requirement Check</h2>
						<p>
							These are some of the important requirements for this software. "Red" means it is vital to this script, "Orange" means it is required but not vital and "Green" means it is good. If one of the checks is "Red", you will not be able to install this script because without that requirement, the script will not work.
						</p>
						<div class="content">
							<p>
							PHP Version (v7.2+)
							<?php echo check('version')?>
							</p>
							It is very important to have at least PHP Version 7.2. It is highly recommended that you use 7.2 or later for best performance.
						</div>
						<div class="content">
							<p>PDO Driver must be enabled 
								<?php echo check('pdo')?>
							</p>
							PDO driver is very important so it must enabled. Without this, the script will not connect to the database hence it will not work at all. If this check fails, you will need to contact your web host and ask them to either enable it or configure it properly.
						</div>					
						<div class="content">
							<p><i>config_sample.php</i> must be accessible. 
								<?php echo check('config')?>
							</p>
							This installation will open that file to put values in so it must be accessible. Make sure that file is there in the <b>includes</b> folder and is writable.
						</div>		
						<div class="content">
							<p><i>content/</i> folder must be writable. 
								<?php echo check('content')?>
							</p>
							Many things will be uploaded to that folder so please make sure it has the proper permission.
						</div>												
						<div class="content">
							<p><i>allow_url_fopen</i> Enabled
								<?php echo check('file')?>
							</p>
							The function <strong>file_get_contents</strong> is used to interact with external servers or APIs.
						</div>
						<div class="content">
							<p>cURL Enabled <?php echo check('curl')?></p>
							cURL is used to interact with external servers or APIs.
						</div>				
					<?php if(!$error) echo '<a href="?step=2" class="button">Requirements are met. You can now proceed.</a>'?>
					<?php elseif($step=="2"): ?>	
					<h2>2.0 Database Configuration</h2>
					<p>
						Now you have to set up your database by filling the following fields. Make sure you fill them correctly.
					</p>
					<form method="post" action="?step=2" class="form">
					    <label>Database Host <a>Usually it is localhost.</a></label>
					    <input type="text" name="host" class="input" required />
					    
					    <label>Database Name</label>
					    <input type="text" name="name" class="input" required />
					    
					    <label>Database User </label>
					    <input type="text" name="user" class="input" required />    
					    
					    <label>Database Password</label>
					    <input type="password" name="pass" class="input" />   

					    <label>Database Prefix <a>Prefix for your tables (Optional) e.g. short_</a></label>
					    <input type="text" name="prefix" class="input" value="" />       

					    <label>Security Key <a>Keep this secret!</a></label>
					    <input type="text" name="key" class="input" value="<?php echo "PUS".md5(rand(0,100000)).md5(rand(0,100000)) ?>" />   

					    <button type="submit" name="step2" class='button'>Create configuration file</button>    
					</form>
					<?php elseif($step=="3"): ?>
					<p>
						Now you have to create an admin account by filling the fields below. Make sure to add a valid email and a strong password. For the site URL, make sure to remove the last slash.
					</p>
					  <form method="post" action="?step=3" class="form">
					        <label>Admin Email</label>
					        <input type="email" name="email" class="input" required />

					        <label>Admin Username</label>
					        <input type="text" name="username" class="input" minlenght="3" required />	

					        <label>Admin Password (min 5 characters)</label>
					        <input type="password" name="pass" class="input" minlength="5" required />   

					        <label>Site URL <a>Including http:// but without the ending slash "/"</a></label>
					        <input type="text" name="url" class="input" value="<?php echo get_domain() ?>" placeholder="http://" required />  

					        <input type="submit" name="step3" value="Finish installation" class='button' />     
					  </form>		
					<?php elseif($step=="4"): ?>
				       <p>
			 				The script has been successfully installed and your admin account has been created. Please click "Delete Install" button below to attempt to delete this file. Please make sure that it has been successfully deleted. 
				       </p>
				       <p>
				       	  Once clicked, you may see a blank page otherwise you will be redirected to your main page. If you see a blank, don't worry it is normal. All you have to do is to go to your main site, login using the info below and configure your site by clicking the "Admin" menu and then "Settings". Thanks for your purchase and enjoy :)
				       </p>
				       <p>
				       <strong>Login URL: <a href="<?php get('site') ?>/user/login" target="_blank"><?php get('site') ?>/user/login</a></strong> <br />
				       <strong>Email: <?php get('email') ?></strong> <br />
				       <strong>Username: <?php get('username') ?></strong> <br />
				       <strong>Password: <?php get('password') ?></strong>
				       </p>	       
				       <a href="?step=5" class="button">Delete install.php</a>	       
					<?php endif; ?>					
  		</div>  		
  	</div>
  </body>
</html>
<?php 
function get_domain(){
	$http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
	$url = "{$http}://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$url = str_replace("/install.php?step=3", "", $url);
	return $url;
}
function get($what){
	if(isset($_SESSION[strip_tags(trim($what))])){
		echo $_SESSION[strip_tags(trim($what))];
	}
}
function check($what){
	switch ($what) {
		case 'version':
			if(PHP_VERSION >= "7.2"){
				return "<span class='ok'>You have ".PHP_VERSION."</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>You have ".PHP_VERSION."</span>";
			}
			break;
		case 'config':
			if(@file_get_contents('includes/config_sample.php') && is_writable('includes/config_sample.php')){
				return "<span class='ok'>Accessible</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>Not Accessible</span>";
			}
			break;
		case 'content':
			if(is_writable('content')){
				return "<span class='ok'>Accessible</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>Not Accessible</span>";
			}
			break;			
		case 'pdo':
			if(defined('PDO::ATTR_DRIVER_NAME') && class_exists("PDO")){
				return "<span class='ok'>Enabled</span>";
			}else{
				global $error;
				$error.=1;
				return "<span class='fail'>Disabled</span>";
			}
			break;
		case 'file':
			if(ini_get('allow_url_fopen')){
				return "<span class='ok'>Enabled</span>";
			}else{
				return "<span class='warning'>Disabled</span>";
			}
			break;	
		case 'curl':
			if(in_array('curl', get_loaded_extensions())){
				return "<span class='ok'>Enabled</span>";
			}else{
				return "<span class='warning'>Disabled</span>";
			}
			break;						
	}
}
function get_query(){

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `type` enum('728','468','300','resp','splash','frame') DEFAULT NULL,
  `code` text,
  `impression` int(12) DEFAULT '0',
  `enabled` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."bundle` (
  `id` int(11) AUTO_INCREMENT,
  `name` varchar(191) NULL,
  `slug` varchar(191) NULL,
  `userid` mediumint(9) NULL,
  `date` datetime NULL,
  `access` varchar(10) NOT NULL DEFAULT 'private',
  `view` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `description` text,
  `code` varchar(191) DEFAULT NULL,
  `discount` int(3) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used` int(9) NOT NULL DEFAULT '0',
  `validuntil` timestamp NULL DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `domain` varchar(191) DEFAULT NULL,
  `redirect` varchar(191) DEFAULT NULL,
  `redirect404` varchar(191) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."overlay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(9) DEFAULT NULL,
  `name` varchar(191) DEFAULT NULL,
  `type` varchar(191) NOT NULL DEFAULT 'message',
  `data` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."page` (
  `id` int(11) AUTO_INCREMENT,
  `name` varchar(191) NULL,
  `seo` varchar(191) NULL,
  `content` text NULL,
  `menu` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=2 ;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."payment` (
  `id` int(11) AUTO_INCREMENT,
  `tid` varchar(191) NULL,
  `userid` bigint(20) NULL,
  `status` varchar(191) NULL,
  `amount` decimal(10,2) NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry` datetime NULL,
  `trial_days` int(5) DEFAULT NULL,
  `data` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `description` text,
  `icon` varchar(191) DEFAULT NULL,
  `trial_days` int(11) DEFAULT NULL,
  `price_monthly` float NOT NULL DEFAULT '0',
  `price_yearly` float NOT NULL DEFAULT '0',
  `free` int(1) NOT NULL DEFAULT '0',
  `numclicks` int(9) DEFAULT NULL,
  `numurls` int(9) DEFAULT NULL,
  `permission` text,
  `status` int(1) NOT NULL DEFAULT '0',
  `stripeid` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(191) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `views` int(9) NOT NULL DEFAULT '0',
  `image` varchar(191) DEFAULT NULL,
  `meta_title` varchar(191) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `published` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `bannedlink` text,
  `type` varchar(191) DEFAULT NULL,
  `ip` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."settings` (
  `config` varchar(20),
  `var` text NULL,
  PRIMARY KEY (`config`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";


$query[] = "INSERT INTO `".trim($_POST["prefix"])."settings` (`config`, `var`) VALUES
('url', ''),
('title', ''),
('description', ''),
('api', '1'),
('user', '1'),
('sharing', '1'),
('geotarget', '1'),
('adult', '1'),
('maintenance', '0'),
('keywords', ''),
('theme', 'cleanex'),
('apikey', ''),
('ads', '1'),
('captcha', '0'),
('ad728', ''),
('ad468', ''),
('ad300', ''),
('frame', '0'),
('facebook', ''),
('twitter', ''),
('email', ''),
('fb_connect', '0'),
('analytic', ''),
('private', '0'),
('facebook_app_id', ''),
('facebook_secret', ''),
('twitter_key', ''),
('twitter_secret', ''),
('safe_browsing', ''),
('captcha_public', ''),
('captcha_private', ''),
('tw_connect', '0'),
('multiple_domains', '0'),
('domain_names', ''),
('tracking', '1'),
('update_notification', '1'),
('default_lang', ''),
('user_activate', '0'),
('domain_blacklist', ''),
('keyword_blacklist', ''),
('user_history', '0'),
('pro_yearly', ''),
('show_media', '0'),
('pro_monthly', ''),
('paypal_email', ''),
('logo', ''),
('timer', ''),
('smtp', ''),
('style', ''),
('font', ''),
('currency', 'USD'),
('news', '<strong>Installation successful</strong> Please go to the admin panel to configure important settings including this message!'),
('gl_connect', '0'),
('require_registration', '0'),
('phish_api', ''),
('phish_username', ''),
('aliases', ''),
('pro', '1'),
('google_cid', ''),
('google_cs', ''),
('public_dir', '0'),
('devicetarget', '1'),
('homepage_stats', '1'),
('home_redir', ''),
('detectadblock', '0'),
('timezone', ''),
('freeurls', '10'),
('allowdelete', '1'),
('serverip', ''),
('favicon', ''),
('advanced', '0'),
('purchasecode', ''),
('alias_length', '5'),
('theme_config', ''),
('schemes', 'https,ftp,http'),
('email.activated', '<p><b>Hello</b></p><p>Your account has been successfully activated at {site.title}.</p>'),
('email.activation', '<p><b>Hello!</b></p><p>You have been successfully registered at {site.title}. To login you will have to activate your account by clicking the URL below.</p><p><a href=\"http://{user.activation}\" target=\"_blank\">{user.activation}</a></p>'),
('email.registration', '<p><b>Hello!</b></p><p>You have been successfully registered at {site.title}. You can now login to our site at <a href=\"http://{site.link}\" target=\"_blank\">{site.link}</a>.</p>'),
('email.reset', '<p><b>A request to reset your password was made.</b> If you <b>didn\'t</b> make this request, please ignore and delete this email otherwise click the link below to reset your password.</p>\r\n		      <b><div style=\"text-align: center;\"><b><a href=\"http://{user.activation}\" class=\"link\">Click here to reset your password.</a></b></div></b></p><p>\r\n		      <p>If you cannot click on the link above, simply copy &amp; paste the following link into your browser.</p>\r\n		      <p><a href=\"http://{user.activation}\" target=\"_blank\">{user.activation}</a></p>\r\n		      <p><b>Note: This link is only valid for one day. If it expires, you can request another one.</b></p>'),
('email.invitation', '<p><b>Hello!</b></p><p>You have been invited to join our team at {site.title}. To accept the invitation, please click the link below.</p><p><a href=\"http://{user.invite}\" target=\"_blank\">{user.invite}</a></p>'),
('blog', '1'),
('root_domain', '1'),
('slackclientid', ''),
('slacksecretid', ''),
('slackcommand', ''),
('slacksigningsecret', ''),
('contact', '1'),
('report', '1'),
('customheader', ''),
('customfooter', ''),
('saleszapier', ''),
('pppublic', ''),
('ppprivate', ''),
('manualapproval', '0');";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."splash` (
  `id` int(11) AUTO_INCREMENT,
  `userid` bigint(12) NULL,
  `name` varchar(191) NULL,
  `data` text NULL,
  `date` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."stats` (
  `id` int(11) AUTO_INCREMENT,
  `short` varchar(191) NULL,
  `urlid` bigint(20) NULL,
  `urluserid` bigint(20) NOT NULL DEFAULT '0',
  `date` datetime NULL,
  `ip` varchar(191) NULL,
  `country` varchar(191) NULL,
  `domain` varchar(191) NULL,
  `referer` text NULL,
  `browser` text NULL,
  `os` text NULL,    
  PRIMARY KEY (`id`),
  KEY `short` (`short`),
  KEY `urlid` (`urlid`), 
  KEY `urluserid` (`urluserid`), 
  KEY `ip` (`ip`),
  KEY `country` (`country`),
  KEY `domain` (`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."url` (
  `id` int(20) AUTO_INCREMENT,
  `userid` int(16) NOT NULL DEFAULT '0',
  `alias` varchar(191) NULL,
  `custom` varchar(191) NULL,
  `url` text NULL,
  `location` text NULL,
  `devices` text NULL,
  `domain` text NULL,
  `description` text NULL,
  `date` datetime NULL,
  `pass` varchar(191) NULL,
  `click` bigint(20) NOT NULL DEFAULT '0',
  `uniqueclick` bigint(20) NOT NULL DEFAULT '0',
  `meta_title` varchar(191) NULL,
  `meta_description` text NULL,
  `ads` int(1) NOT NULL DEFAULT '1',
  `bundle` mediumint(9) NULL,
  `public` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `type` varchar(64) NULL,
  `pixels` varchar(191) NULL,
  `expiry` date NULL,
  `parameters` text NULL,
  `status` INT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`),
  KEY `custom` (`custom`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";


$query[] = "CREATE TABLE IF NOT EXISTS `".trim($_POST["prefix"])."user` (
  `id` int(11) AUTO_INCREMENT,
  `auth` text NULL,
  `auth_id` varchar(191) NULL,
  `admin` int(1) NOT NULL DEFAULT '0',
  `email` varchar(191) NULL,
  `name` varchar(191) NULL,
  `username` varchar(20) NULL,
  `password` varchar(191) NULL,
  `address` text NULL,
  `date` datetime NULL,
  `api` varchar(20) NULL,
  `ads` int(1) NOT NULL DEFAULT '1',
  `active` int(1) NOT NULL DEFAULT '1',
  `banned` int(1) NOT NULL DEFAULT '0',
  `public` int(1) NOT NULL DEFAULT '0',
  `domain` varchar(191) NULL,
  `media` int(1) NOT NULL DEFAULT '0',
  `splash_opt` int(1) NOT NULL DEFAULT '0',
  `splash` text NULL,
  `auth_key` varchar(191) NULL,
  `last_payment` datetime NULL,
  `expiration` datetime NULL,
  `pro` int(1) NOT NULL DEFAULT '0',
  `planid` int(9) DEFAULT NULL,
  `overlay` text NULL,
  `fbpixel` text NULL,
  `linkedinpixel` text NULL,
  `adwordspixel` text NULL,
  `twitterpixel` text NULL,
  `adrollpixel` text NULL,
  `quorapixel` text NULL,
  `gtmpixel` text,
  `defaulttype` varchar(191) NULL,
  `teamid` int(11) NULL,
  `teampermission` text NULL,
  `secret2fa` varchar(191) NULL,
  `slackid` varchar(191) DEFAULT NULL,
  `zapurl` varchar(191) DEFAULT NULL,
  `zapview` varchar(191) DEFAULT NULL,
  `trial` int(1) NOT NULL DEFAULT '0',
  `profiledata` text,
  `avatar` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api` (`api`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;";

$query[]=<<<QUERY
INSERT INTO `{$_POST["prefix"]}page` (`id`, `name`, `seo`, `content`, `menu`) VALUES
(1, 'Terms and Conditions', 'terms', 'Please edit me when you can. I am very important.', 1);
QUERY;
return $query;
}
function generate_config($array){
	if(!empty($array)){
	    $file = file_get_contents('includes/config_sample.php');
	    $file = str_replace("RHOST",trim($array["host"]),$file);
	    $file = str_replace("RDB",trim($array["name"]),$file);
	    $file = str_replace("RUSER",trim($array["user"]),$file);
	    $file = str_replace("RPASS",trim($array["pass"]),$file);
	    $file = str_replace("RPRE",trim($array["prefix"]),$file);
	    $file = str_replace("RPUB",trim(md5(api())),$file);
	    $file = str_replace("RKEY",trim($array["key"]),$file);
	    $fh = fopen('includes/config_sample.php', 'w') or die("Can't open config_sample.php. Make sure it is writable.");
	    fwrite($fh, $file);
	    fclose($fh);
	    rename("includes/config_sample.php", "includes/config.php");
	}
}
function api(){
  $l = "12";
  $api = "";
  $r = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  srand((double)microtime()*1000000);
  for($i = 0; $i < $l; $i++) { 
    $api .= $r[rand()%strlen($r)]; 
  }
  return $api;
}
?>