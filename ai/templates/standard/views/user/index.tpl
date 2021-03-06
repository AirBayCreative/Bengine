<h1>{lang}User_Manager{/lang}</h1>
<div class="draggable">
	<form method="post">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Add_New_User{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_username">{lang}Username{/lang}</label></td>
				<td><input type="text" name="username" maxlength="128" id="f_username" /></td>
			</tr>
			<tr>
				<td><label for="f_password">{lang}Password{/lang}</label></td>
				<td><input type="text" name="password" maxlength="128" id="f_password" /></td>
			</tr>
			<tr>
				<td><label for="f_email">{lang}E_Mail{/lang}</label></td>
				<td><input type="text" name="email" maxlength="128" id="f_email" /></td>
			</tr>
			<tr>
				<td><label for="f_language">{lang}Language{/lang}</label></td>
				<td><select name="languageid" id="f_language">{while[langs]}<option value="{loop}languageid{/loop}">{loop}title{/loop}</option>{/while}</select></td>
			</tr>
			<tr>
				<td><label for="f_template">{lang}Template_Package{/lang}</label></td>
				<td><select name="templatepackage" id="f_template">{foreach[templatepacks]}<option value="{loop}template{/loop}">{loop}template{/loop}</option>{/foreach}</select></td>
			</tr>
			<tr>
				<td><label for="f_ipcheck">{lang}IP_Check{/lang}</label></td>
				<td><select name="ipcheck" id="f_ipcheck"><option value="1">{lang}Yes{/lang}</option><option value="0">{lang}No{/lang}</option></select></td>
			</tr>
			<tr>
				<td><label for="f_usergroup">{lang}User_Group{/lang}</label></td>
				<td><select name="usergroup[]" id="f_usergroup" size="5" multiple="multiple"><option value="0">{lang}No_User_Group{/lang}</option>{while[groups]}<option value="{loop}usergroupid{/loop}">{loop}grouptitle{/loop}</option>{/while}</select></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="add_user" value="{lang}Commit{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div class="draggable">
	<form method="post" action="{const=BASE_URL}user/seek">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Quick_Search{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="s_username">{lang}Username{/lang}</label><br />[{link[Advanced_Search]}"user/seek"{/link}]</td>
				<td><input type="text" name="username" maxlength="128" id="s_username" /></td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="search_user" value="{lang}Go{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>