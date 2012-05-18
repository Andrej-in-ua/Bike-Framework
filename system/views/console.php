<script type="text/javascript" src="<?=BASEURL?>js/console.js"></script>

<div id="console">
<?php if ( isset($console) ): ?>
<?php if ( ! isset($time_total) ) $time_total = 0; ?>
<div class="left">Total: <strong><?=sprintf('%.5f', $console[(count($console)-1)][0])?> c.</strong></div>
<div class="left">MySQL: <strong><?=sprintf('%.5f', $time_total)?> c.</strong></div>
<div class="left">PHP: <strong><?=sprintf('%.5f', ($console[(count($console)-1)][0] - $time_total))?> c.</strong></div>
<?php endif; ?>

<div class="clearfix"></div>

<?php if ( isset($errors) ): ?>
	<div class="box-title">Errors</div>
	<div class="box">
	<?php foreach ( $errors as $code => $msg ):
		$msg = each($msg);
	?>	
	<div>[<?=$msg[0]?>] <?=$msg[1]?></div>

	<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( isset($console) ): ?>
	<div class="box-title">System log (<?=count($console)?>)</div>
	<div class="box" id="box-console">
	<?php foreach ( $console as $num => $val ): ?>
		
	<div>
	[<?=$val[0]?>] [<?=sprintf('%.2f',($val[1]/1042/1024))?> Mb] <?=$val[3]?>
	<span class="right">[<?=$val[2].($val[4]?' ('.$val[4].')':'')?>]</span>
	</div>

	<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( isset($query) ): ?>
	<div class="box-title">Querys (<?=count($query)?>)</div>
	<div class="box" id="box-query">
	<?php foreach ( $query as $num => $val ): ?>
		
	<div>
	[<?=sprintf('%.5f', $val[0])?>] <?=$val[1]?>
	</div>

	<?php endforeach; ?>
	</div>
<?php endif; ?>
<?php if ( isset($core) ): ?>	
	<div class="box-title">$_core</div>
	<div class="box" id="box-core">
	<pre><?=print_r($core, 1)?></pre>
	</div>
<?php endif; ?>

<?php if ( isset($_SESSION) ): ?>	
	<div class="box-title">$_SESSION (<?=count($_SESSION)?>)</div>
	<div class="box" id="box-session">
	<pre><?=print_r($_SESSION, 1)?></pre>
	</div>
<?php endif; ?>


<?php if ( isset($_COOKIE) ): ?>	
	<div class="box-title">$_COOKIE (<?=count($_COOKIE)?>)</div>
	<div class="box" id="box-cookie">
	<pre><?=print_r($_COOKIE, 1)?></pre>
	</div>
<?php endif; ?>

<?php if ( isset($_POST) ): ?>	
	<div class="box-title">$_POST (<?=count($_POST)?>)</div>
	<div class="box" id="box-post">
	<pre><?=print_r($_POST, 1)?></pre>
	</div>
<?php endif; ?>

<?php if ( isset($_GET) ): ?>	
	<div class="box-title">$_GET (<?=count($_GET)?>)</div>
	<div class="box" id="box-get">
	<pre><?=print_r($_GET, 1)?></pre>
	</div>
<?php endif; ?>


	<div class="box-title">Local Storage (<span id="storage-count"></span>)</div>
	<div class="box" id="box-local">
	</div>


<div class="clearfix"></div>
</div>