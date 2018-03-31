<script type="text/javascript">
jQuery(function(){
	$('.del_link').on('click', function() {
		if (confirm('is delete?')) {
			return true;
		}
		return false;
	});
});
</script>

<h3 class="introduction">global information</h3>
<span>全<?php echo $this->pagination->total_items;?>件</span>
<?php if ($this->pagination->total_pages > 1): ?>
<div class="pagination_div">
	<span class="pagination_previous">
	<?php if ($this->pagination->calculated_page > 1):?>
		<?php echo $this->pagination->previous();?>
	<?php endif;?>
	</span>
	<span class="pagination_render">
	<?php echo $this->pagination->pages_render(); ?>
	</span>
	<span class="pagination_next">
	<?php if ($this->pagination->calculated_page != $this->pagination->total_pages):?>
		<?php echo $this->pagination->next();?>
	<?php endif;?>
	</span>
</div>
<?php endif; ?>

<table>
<?php foreach ($this->arr_list as $i => $val):?>
<tr>
	<td><?php echo $val->date; ?></td>
	<td><?php echo $val->comment; ?></td>
	<td><?php echo Html::anchor(\Config::get('host.api_url'). '/cms/information/update/'. $val->id, '更新');?></td>
	<td><?php echo Html::anchor(\Config::get('host.api_url'). '/cms/information/delete/'. $val->id, '削除', array('class' => 'del_link'));?></td>
</tr>
<?php endforeach;?>
</table>

<?php if ($this->pagination->total_pages > 1): ?>
<div class="pagination_div">
	<span class="pagination_previous">
	<?php if ($this->pagination->calculated_page > 1):?>
		<?php echo $this->pagination->previous();?>
	<?php endif;?>
	</span>
	<span class="pagination_render">
	<?php echo $this->pagination->pages_render(); ?>
	</span>
	<span class="pagination_next">
	<?php if ($this->pagination->calculated_page != $this->pagination->total_pages):?>
		<?php echo $this->pagination->next();?>
	<?php endif;?>
	</span>
</div>
<?php endif; ?>

<div>
	<?php echo Html::anchor(\Config::get('host.api_url'). '/cms/information/create/', '新規登録');?>
</div>

