<script type="text/javascript" src="{@APP_ROOT_DIR}js/lib/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
//<![CDATA[
tinyMCE_GZ.init({
language: "{@langcode}",theme: "advanced",plugins: "emotions",disk_cache : true,debug : false
});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
tinyMCE.init({
language: "{@langcode}",mode: "exact",skin : "message",elements: "message",theme: "advanced",theme_advanced_toolbar_location: "top",theme_advanced_toolbar_align : "left",theme_advanced_disable: "anchor,styleselect",width: 436,height: 250,plugins: "emotions",theme_advanced_buttons1_add: "forecolor,backcolor",theme_advanced_buttons3_add: "emotions",relative_urls: false,remove_script_host: false
});
//]]>
</script>
<h1>{lang}Global_Mail{/lang}</h1>
<div class="draggable">
	<form method="post" action="{@formaction}">
		<table class="ntable" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th colspan="2">{lang}Send_Global_Mail{/lang}</th>
				</tr>
			</thead>
			<tr>
				<td><label for="f_mode">{lang}Mode{/lang}</label></td>
				<td><select name="mode" id="f_mode"><option value="pm">{lang}PM{/lang}</option></select></td>
			</tr>
			<tr>
				<td><label for="f_subject">{lang}Subject{/lang}</label></td>
				<td><input type="text" name="subject" maxlength="128" id="f_subject" />{if[{var=subjectError}]}<br />{@subjectError}{/if}</td>
			</tr>
			<tr>
				<td><label for="f_message">{lang}Message{/lang}</label></td>
				<td><textarea cols="75" rows="15" name="message" id="message"></textarea>{if[{var=messageError}]}<br />{@messageError}{/if}</td>
			</tr>
			<tfoot>
				<tr>
					<td colspan="2"><input type="submit" name="send" value="{lang}Send{/lang}" class="button" /></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>