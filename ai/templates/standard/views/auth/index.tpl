{include}"html_header"{/include}
	<div id="container">
		<form method="post" action="{@faction}">
		<fieldset>
			<legend>Recipe Admin Interface</legend>
			<ul>
				<li><label for="username">Username</label><input type="text" size="35" name="username" id="username" maxlength="{config=MAX_USER_CHARS}"/></li>
				<li><label for="password">Password</label><input type="password" size="35" name="password" id="password" maxlength="{config=MAX_PASSWORD_LENGTH}"/></li>
				<li><input type="submit" name="signin" value="Sign In" class="button" /></li>
			</ul>
			<?php if($errors = $this->get("loginErrors")): ?>
			<div class="error">
				<?php foreach($errors as $error): ?>
				<p><?php echo $error ?></p>
				<?php endforeach ?>
			</div>
			<?php endif ?>
		</fieldset>
		</form>
	</div>
{include}"html_footer"{/include}