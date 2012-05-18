<h2><a href="<?=BASEURL?>admin/"><span class="icon-previous"></span></a>  Добавить новую страницу</h2>
<?php Load::Msg()->show(); ?>
<p>Для начала процедуры добавления новой страницы введите её будующий адрес:</p>
<form action="<?=BASEURL?>pages/add/" method="POST"><?=BASEURL?>pages/<input name="uri" type="text" value="" /> <input type="submit" value="Добавить" /></form>