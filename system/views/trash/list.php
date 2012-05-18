<div class="fr">
	<a href="<?=BASEURL?>admin/c/m/trash/list/restore/all"><span class="button"><span class="icon-restore">Восстановить все</span></span></a>
	<a href="<?=BASEURL?>admin/c/m/trash/list/delete/all"><span class="button"><span class="icon-delete">Очистить корзину</span></span></a>
</div>
<h2><a href="<?=BASEURL?>admin/"><span class="icon-previous"></span></a> Удаленные материалы</h2>
<div class="clearfix"></div>
<?php Load::Msg()->show(); ?>
<table class="result_table" id="pages">
<thead>
	<th class="numbering">#</th>
	<th>Модель</th>
	<th>Заголовок</th>
	<th>Дата удаления</th>
	<th></th>
	
</thead>
<tbody>
<?php if ( isset($data) AND is_array($data) ) foreach ( $data as $row )
{
//	pr($row);
	?><tr>
		<td class="numbering"><?=$row['id']?></td>
		<td>Model_<?=ucfirst(strtolower($row['model']))?></td>
		<td><?=$row['title']?></td>
		<td><?=$row['date']?></td>
		<td>
			<a href="<?=BASEURL?>admin/c/m/trash/list/restore/<?=$row['id']?>"><span class="button"><span class="icon-restore">Восстановить</span></span></a>
			<a href="<?=BASEURL?>admin/c/m/trash/list/delete/<?=$row['id']?>"><span class="button"><span class="icon-delete">Окончательно удалить</span></span></a>
		</td>
	  </tr><?php
}
?>
</tbody>
</table>
<?=( isset($paginator) ? $paginator : '' )?>