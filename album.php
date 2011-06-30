<?php
if( !isset($_GET['id']) )
	die("No direct access allowed!");
require 'path/to/FB/SDK/facebook.php';
$facebook = new Facebook(array(
	'appId'  => 'APP_ID',
	'secret' => 'APP_SECRET',
	'cookie' => true,
));

$user = $facebook->getUser();
if ($user) {
	try {
		$logoutUrl = $facebook->getLogoutUrl();
		
		$params = array();
		if( isset($_GET['offset']) )
			$params['offset'] = $_GET['offset'];
		if( isset($_GET['limit']) )
			$params['limit'] = $_GET['limit'];
		$params['fields'] = 'name,source,images';
		$params = http_build_query($params, null, '&');
		$album_photos = $facebook->api("/{$_GET['id']}/photos?$params");
		if( isset($album_photos['paging']) ) {
			if( isset($album_photos['paging']['next']) ) {
				$next_url = parse_url($album_photos['paging']['next'], PHP_URL_QUERY) . "&id=" . $_GET['id'];
			}
			if( isset($album_photos['paging']['previous']) ) {
				$pre_url = parse_url($album_photos['paging']['previous'], PHP_URL_QUERY) . "&id=" . $_GET['id'];
			}
		}
		$photos = array();
		if(!empty($album_photos['data'])) {
			foreach($album_photos['data'] as $photo) {
				$temp = array();
				$temp['id'] = $photo['id'];
				$temp['name'] = (isset($photo['name'])) ? $photo['name']:'';
				$temp['picture'] = $photo['images'][1]['source'];
				$temp['source'] = $photo['source'];
				$photos[] = $temp;
			}
		}
	} catch (FacebookApiException $e) {
		error_log($e);
		$user = null;
	}
} else {
	header("Location: index.php");
}
?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
<head>

<title>My Facebook Album</title>
<link href="css/style.css" media="screen" type="text/css" rel="stylesheet">
<link href="css/jquery.fancybox-1.3.4.css" media="screen" type="text/css" rel="stylesheet">

</head>
<body>
<div id="wrapper">
	<div id="header">
		<h1><a href="index.php">My Facebook Album</a></h1>
		<div class="links">
			<?php if ($user): ?>
			<a class="login" href="<?php echo $logoutUrl; ?>">Logout</a>
			<?php endif ?>
		</div>
	</div>
	<div id="content">
	<?php if(!empty($photos)) { ?>
	<table id="album">
	<tr>
	<?php
	$count = 0;
	foreach($photos as $photo) {
		$lastChild = "";
		if( $count%5 == 0 && $count != 0 )
			echo "</tr><tr>";
		$count++;
		echo	"<td>" .
				"<a href=\"{$photo['source']}\" title=\"{$photo['name']}\" rel=\"pic_gallery\">" .
				"<div class=\"thumb\" style=\"background-image: url({$photo['picture']})\"></div>" .
				"</a></td>";
	}
	?>
	</tr>
	</table>
	<?php if(isset($album_photos['paging'])) { ?>
	<div class="paging">
		<?php if(isset($next_url)) { echo "<a class='next' href='album.php?$next_url'>Next</a>"; } ?>
		<?php if(isset($pre_url)) { echo "<a class='prev' href='album.php?$pre_url'>Previous</a>"; } ?>
	</div>
	<?php } ?>
	<?php } ?>
	</div>
	<div id="footer">
		<p>&copy; 2011 <a href="http://www.masteringapi.com/">MasteringAPI.com</a></p>
	</div>
</div>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.js"></script>
<script type="text/javascript" src="js/jquery.fancybox-1.3.4.pack.js"></script>
<script>
$(function() {
	$("a[rel=pic_gallery]").fancybox({
		'titlePosition' 	: 'over',
		'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
			return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
		}
	});
});
</script>
</body>
</html>
