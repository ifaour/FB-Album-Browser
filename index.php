<?php
require 'path/to/FB/SDK/facebook.php';
$facebook = new Facebook(array(
	'appId'  => 'APP_ID',
	'secret' => 'APP_SECRET',
	'cookie' => true,
));

$user = $facebook->getUser();
if ($user) {
	try {
		$user_albums = $facebook->api('/me/albums');
		$albums = array();
		if(!empty($user_albums['data'])) {
			foreach($user_albums['data'] as $album) {
				$temp = array();
				$temp['id'] = $album['id'];
				$temp['name'] = $album['name'];
				$temp['thumb'] = "https://graph.facebook.com/{$album['id']}/picture?type=album&access_token={$facebook->getAccessToken()}";
				$temp['count'] = (!empty($album['count'])) ? $album['count']:0;
				if($temp['count']>1 || $temp['count'] == 0)
					$temp['count'] = $temp['count'] . " photos";
				else
					$temp['count'] = $temp['count'] . " photo";
				$albums[] = $temp;
			}
		}
	} catch (FacebookApiException $e) {
		error_log($e);
		var_dump($e);
		$user = null;
	}
}

if ($user) {
	$logoutUrl = $facebook->getLogoutUrl();
} else {
	$loginUrl = $facebook->getLoginUrl(array(
		'scope' => 'user_photos'
	));
}
?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<head>

<title>My Facebook Albums Browser</title>
<link href="css/style.css" media="screen" type="text/css" rel="stylesheet">

</head>
<body>
<div id="wrapper">
	<div id="header">
		<h1><a href="index.php">My Facebook Albums Browser</a></h1>
		<div class="links">
			<?php if ($user): ?>
			<a class="login" href="<?php echo $logoutUrl; ?>">Logout</a>
			<?php else: ?>
			<a class="login" href="<?php echo $loginUrl; ?>">Login with Facebook</a>
			<?php endif ?>
		</div>
	</div>
	<div id="content">
	<?php if(!empty($albums)) { ?>
	<table id="albums">
	<tr>
	<?php
	$count = 1;
	foreach($albums as $album) {
		if( $count%6 == 0 )
			echo "</tr><tr>";
		echo	"<td>" .
				"<a href=\"album.php?id={$album['id']}\">" .
				"<div class=\"thumb\" style=\"background: url({$album['thumb']}) no-repeat 50% 50%\"></div>" .
				"<p>{$album['name']}</p>" .
				"<p>{$album['count']}</p>" .
				"</a></td>";
		$count++;
	}
	?>
	</tr>
	</table>
	<?php } ?>
	</div>
	<div id="footer">
		<p>&copy; 2011 <a href="http://www.masteringapi.com/">MasteringAPI.com</a></p>
	</div>
</div>
</body>
</html>
