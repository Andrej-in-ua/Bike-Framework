<div class="login-box">
<h2>Control panel</h2>
<form action="" method="post">
<input name="send" type="hidden" value="1" />
<div><label for="login">Name:</label> <input id="login" name="login" type="text" /></div>
<div><label for="password">Password:</label> <input id="password" name="password" type="password" /></div>
<?php Load::Msg()->show(); ?>
<div><input name="" type="submit" value="Login" /></div>
</form>
</div>