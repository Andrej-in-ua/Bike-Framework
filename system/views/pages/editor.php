<?php
	$meta =& Core::getKey('meta');
	$meta['js'][] = 'nicEdit';
	$meta['js'][] = 'pagesEditor';
?>
<script language="javascript">
$(document).ready(function(){
	pagesEditor.init('<?=$version?>', '<?=$version?>', '<?=$id?>', '<?=( $currentUser['moderator'] ? 'true' : 'false' )?>');
});
</script>