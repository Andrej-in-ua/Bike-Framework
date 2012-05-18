<script>
</script>

<h2>Страницы сайта</h2>
<?php Load::Msg()->show(); ?>
<table class="result_table" id="pages">
<thead>
	<th class="numbering">#</th>
	<th>URI</th>
	<th>Владелец</th>
	<th>Версия</th>
	<th>Заголовок</th>
	<th>Статус</th>
	<th>Блокировка</th>
</thead>
<tbody>
<?php if ( isset($pages) AND is_array($pages) ) foreach ( $pages as $page )
{
	$meta = unserialize($page['meta']);
	?><tr>
		<td class="numbering"><?=$page['id']?></td>
		<td><a href="<?=BASEURL?>pages/<?=$page['uri']?>" target="_blank"><?=$page['uri']?></a></td>
		<td><?=$page['owner_login']?> <small>(ID:<?=$page['owner_id']?>)</small></td>
		<td><?=$page['version']?> <small>(<?=$page['date']?>)</small></td>
		<td><?=$meta['title']?></td>
		<td><?=(
			$page['status'] == 1
				? 'Опубликована'
				: ( $page['status'] == 3 ? '<em>Требуется модерация</em>' : 'Черновик' )
				)?></span></td>
		<td><?=(
			strtotime($page['locked_time']) + $lockTime >= microtime(true) == 1
				? 'Заблокирована: '.$page['locked_login'].' <small>(ID:'.$page['locked_id'].')</small>'
				: '')?></span></td>
	  </tr><?php
}
?>
</tbody>
</table>
<?=( isset($paginator) ? $paginator : '' )?>