<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
</head>
<body>
<h3 class="introduction">global information create</h3>
<?php echo Html::anchor(\Config::get('host.api_url'). '/cms/information/index/', '←一覧へ');?>
<br />
<br />
<form action="<?php echo \Config::get('host.api_url'); ?>/cms/information/create/" method="post">
<table>
	<tr>
		<th>date</th>
		<td><input type="text" value="<?php echo \Input::param('date', \Date::forge()->format("%Y-%m-%d")); ?>" name="date"></td>
	</tr>
	<tr>
		<th>comment</th>
		<td><textarea name="comment" cols="50" rows="16"><?php echo \Input::param('comment');?></textarea></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<input type="submit" value="送信" style="width: 84%;">
		</td>
	</tr>
</table>
</form>
</body>
</html>