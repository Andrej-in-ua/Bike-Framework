<?php $node = $nodes[0] ?>
	<div class="node node-num<?=$num?>">
		<h3 class="node-title">
			<?php if ( isset($node['term_title']) AND $node['term_uri'] != $dictionary['name'] ): ?>
				<a href="<?=BASEURL?>blog/<?=$node['term_uri']?>" class="term-uri"><?=$node['term_title']?></a> → 
			<?php endif; ?>
			<a href="<?=BASEURL?>blog/<?=$node['term_uri']?>/<?=$node['uri']?>" class="node-uri"><?=$node['title']?></a>
		</h4>
		<div class="node-content">
		<?=preg_replace('#<!--break-->#','', $node['content'])?>
		</div>
		<div class="tags">
<?php
if ( isset($node['tags']) ){
	$first = false;
	foreach ( $node['tags'] as $num => $tag ):
		if ( $first ){ echo ', '; }else{$first = true;}
?><a href="<?=BASEURL?>blog/tag-<?=$tag[0]?>/"><?=$tag[1]?></a><?php 
	endforeach;
}
?>
			
		</div>
		<div class="meta">
			<div class="left">
			<?=date("Y-m-d | H:i", strtotime($node['date_add']))?>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="clearfix"></div>
	</div>