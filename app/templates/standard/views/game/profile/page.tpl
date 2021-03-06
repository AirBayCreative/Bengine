<?php if($this->get("canEdit")): ?>
<script type="text/javascript" src="{const=BASE_URL}js/lib/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
//<![CDATA[
$(function() {
	$('.profile-top').mouseover(function() {
		$('#edit-info').css('visibility', 'visible');
	});
	$('.profile-top').mouseout(function() {
		$('#edit-info').css('visibility', 'hidden');
	});
	$('.profile-about').mouseover(function() {
		$('#edit-about').css('visibility', 'visible');
	});
	$('.profile-about').mouseout(function() {
		$('#edit-about').css('visibility', 'hidden');
	});
});

var saveAbout = true;
var editorOpened = false;

tinyMCE_GZ.init({
	language: "{@langcode}",theme:"advanced",disk_cache:true,debug:false,
	plugins: "save,style,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,xhtmlxtras"
});

tinyMCE.init({
	language: "{@langcode}",forced_root_block:"div",theme:"advanced",skin:"alliancetext",mode:"none",elements:"about-texteditor",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align : "left",theme_advanced_disable: "anchor,styleselect",theme_advanced_statusbar_location: "bottom",theme_advanced_resizing: true,theme_advanced_resize_horizontal: false,width: 505,height: 370,relative_urls: false,remove_script_host: false,
	theme_advanced_buttons1 : "save,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,insertdate,inserttime,preview",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,forecolor,backcolor",
	theme_advanced_buttons4 : "styleprops,cite,abbr,acronym,|,link,unlink,anchor,image,cleanup,code,fullscreen,|,charmap,emotions,iespell,media,advhr,|,sub,sup",
	plugins: "save,style,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,xhtmlxtras",
	save_callback: function(element_id, html, body) {
		if(saveAbout && typeof(html) != "undefined")
		{
			$.post("{const=BASE_URL}game.php/{const=SID}/Profile/SaveAbout/{request[get]}1{/request}", { text: html },
				function(data) {
					if(data != "")
					{
						alert(data);
					}
					else
					{
						editorOpened = false;
						saveAbout = false;
						tinyMCE.execCommand('mceRemoveControl', false, 'about-texteditor');
						$('#text-editor').html(html);
					}
				});
		}
	}
});

function openEditor()
{
	if(!editorOpened)
	{
		saveAbout = true;
		editorOpened = true;
		var text = $('#text-editor').html();
		$('#text-editor').html('<textarea cols="75" rows="15" id="about-texteditor">'+text+'</textarea>');
		tinyMCE.execCommand('mceAddControl', false, 'about-texteditor');
	}
	else
	{
		editorOpened = false;
		$('#text-editor').html(tinyMCE.get('about-texteditor').getContent());
		tinyMCE.execCommand('mceRemoveControl', false, 'about-texteditor');
	}
}
//]]>
</script>
<?php endif; ?>
<script type="text/javascript">
//<![CDATA[
function statusTooltip()
{
	var legend = '<p class="legend"><cite><span>i</span> = {lang}LOWER_INACTIVE{/lang}</cite><cite><span>I</span> = {lang}UPPER_INACTIVE{/lang}</cite><cite><span class="banned">b</span> = {lang}BANNED{/lang}</cite><cite><span class="strong-player">s</span> = {lang}STRONG_PLAYER{/lang}</cite><cite><span class="weak-player">n</span> = {lang}NEWBIE{/lang}</cite><cite><span class="vacation-mode">v</span> = {lang}VACATION_MODE{/lang}</cite></p>';
	legend += '';
	Tip(legend, FADEIN, 500);
}
//]]>
</script>
<table class="ntable">
	<tr>
		<th>{lang=USER_PROFILE}</th>
	</tr>
	<tr>
		<td>
			<div class="profile">
				<div class="profile-top">
					<div class="profile-avatar">
						<img src="<?php if($this->get("avatar") == ""): ?>{const=BASE_URL}img/avatar.png<?php else: ?>{@avatar}<?php endif; ?>" alt="{lang=USERS_AVATAR}" title="{lang=USERS_AVATAR}"/>
					</div>
					<div class="profile-info">
						<ul>
							<li class="even"><strong>{@username}</strong>{@regdate}</li>
							<li class="odd"><?php if($this->get("user")->getUserid() != Core::getUser()->get("userid")) { ?><strong>{lang=CONTACT}</strong> {@pm} {@addToBuddylist} {perm[CAN_MODERATE_USER]}{@moderate}{/perm}<?php } else { ?>&nbsp;<?php } ?></li>
							<li class="even"><strong>{lang=POINTS}</strong> {@points}</li>
							<li class="odd"><strong>{lang=RANK}</strong> {@rank}</li>
							<?php if($this->get("status") != ""): ?><li class="even helptip" onmouseover="statusTooltip()" onmouseout="UnTip();"><strong>{lang=STATUS}</strong> {@status}</li><?php else: ?><li class="even">&nbsp;</li><?php endif; ?>
							<li class="odd"><strong>{lang=ALLIANCE}</strong> {@allianceName}</li>
							<?php $i = 0; ?>
							<?php foreach($this->getLoop("profile") as $field): ?>
							<li class="<?php echo ($i % 2 == 0) ? "even" : "odd"; ?>"><strong><?php echo $field->getTranslatedName() ?></strong> <?php echo ($field->getData()) ? $field->getData() : "&nbsp;"; ?></li>
							<?php $i++; ?>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="edit-profile" id="edit-info">
						{@editLink}
					</div>
				</div>
				<div class="profile-about">
					<div class="profile-text">
						<form action="{const=BASE_URL}game.php/{const=SID}/Profile/SaveAbout/{request[get]}1{/request}" method="post" onsubmit="return false;">
							<div class="text" id="text-editor">
								<?php if($this->get("aboutMe") == ""): ?>
								{lang=DEFAULT_ABOUT_ME}
								<?php else: ?>
								{@aboutMe}
								<?php endif; ?>
							</div>
						</form>
					</div>
					<div class="edit-profile" id="edit-about">
						<a href="javascript:void(0);" onclick="openEditor();">{lang=EDIT_TEXT}</a>
					</div>
				</div>
			</div>
		</td>
	</tr>
</table>
<script type="text/javascript" src="{const=BASE_URL}js/?f=lib/wz_tooltip.js"></script>