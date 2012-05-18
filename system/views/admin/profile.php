<a href="<?=BASEURL?>admin/"><span class="icon-previous"></span></a> 
<div class="login-box">
<h2>Change Password</h2>
<form action="" method="post">
<input name="send" type="hidden" value="1" />
<div><label for="login">Login:</label> <input id="login" name="login" type="text" value="<?=$login?>" disabled /></div>
<div><label for="password">Current password:</label> <input id="password" name="password" type="password" <?php if ( ! $writable ) echo 'disabled '?>/></div>
<div><label for="password">New password:</label> <input id="npassword" name="npassword" type="password" <?php if ( ! $writable ) echo 'disabled '?>/></div>
<div><label for="password">Again new password:</label> <input id="npassword2" name="npassword2" type="password" <?php if ( ! $writable ) echo 'disabled '?>/></div>
<?php Load::Msg()->show(); ?>
<div><input name="" type="submit" value="Save" /></div>
</form>
</div>