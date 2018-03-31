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
<form action="<?php echo \Config::get('host.api_url');?>/cms/information/update/<?php echo $this->arr_detail->id; ?>" method="post">
<table>
	<tr>
		<th>date</th>
		<td><input type="text" value="<?php echo \Input::param('date', $this->arr_detail->date); ?>" name="date"></td>
	</tr>
	<tr>
		<th>comment</th>
		<td><textarea name="comment" cols="50" rows="16"><?php echo \Input::param('comment', $this->arr_detail->comment);?></textarea></td>
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