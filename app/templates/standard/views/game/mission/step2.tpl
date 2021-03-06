<script type="text/javascript">
//<![CDATA[
var now = new Date();
var delay = {const}TIME{/const} - Math.round(now.getTime() / 1000);
var oGalaxy = {@oGalaxy};
var oSystem = {@oSystem};
var oPos = {@oPos};
var gamespeed = {config}GAMESPEED{/config};
var maxspeed = {@maxspeedVar};
var basicConsumption = {@basicConsumption};
var capicity = {@capacity_raw};
var decPoint = '{lang}DECIMAL_POINT{/lang}';
var thousandsSep = '{lang}THOUSANDS_SEPERATOR{/lang}';
var maxGalaxy = {config}GALAXYS{/config};
var maxSystem = {config}SYSTEMS{/config};
var maxPos = 15;
$(document).ready(function() {
	{foreach[invitations]}
	$('#timer_{loop=eventid}').countdown({until: {loop=time_r}, compact: true, onExpiry: function() {
		$('#timer_{loop=eventid}').text('-');
	}});
	{/foreach}
	rebuild();
});
//]]>
</script>
<form method="post" action="{@formaction}" class="form-sec">
<table class="ntable">
	<colgroup>
		<col />
		<col width="250" />
	</colgroup>
	<tr>
		<th colspan="2">{lang}SELECT_TARGET{/lang}</th>
	</tr>
	<tr>
		<td>{lang}TARGET{/lang}</td>
		<td>
			<input type="text" name="galaxy" id="galaxy" value="{@galaxy}" size="3" maxlength="2" onblur="javascript:rebuild();"/>
			<input type="text" name="system" id="system" value="{@system}" size="3" maxlength="3" onblur="javascript:rebuild();"/>
			<input type="text" name="position" id="position" value="{@position}" size="3" maxlength="2" onblur="javascript:rebuild();"/>
			<select name="targetType" onchange="javascript:rebuild();" id="targetType">
				<option value="planet"{if[{var=targetType} == "planet"]} selected="selected"{/if}>{lang}PLANET{/lang}</option>
				<option value="tf"{if[{var=targetType} == "tf"]} selected="selected"{/if}>{lang}TF{/lang}</option>
				<option value="moon"{if[{var=targetType} == "moon"]} selected="selected"{/if}>{lang}MOON{/lang}</option>
			</select>
			<input type="hidden" name="code" value="{@code}"/>
			<input type="hidden" name="formation" value=""/>
		</td>
	</tr>
	<tr>
		<td>{lang}DISTANCE{/lang}</td>
		<td><span id="distance">{@distance}</span></td>
	</tr>
	<tr>
		<td>{lang}SPEED{/lang}</td>
		<td><input type="text" name="speed" size="3" maxlength="3" value="100" id="speed" onkeyup="javascript:rebuild();" />% <select onchange="setFromSelect('speed', this); rebuild();">{@speedFromSelectBox}</select></td>
	</tr>
	<tr>
		<td>{lang}TIME{/lang}</td>
		<td><span id="time">{@time}</span></td>
	</tr>
	<tr>
		<td>{lang=OUTBOUND_FLIGHT}</td>
		<td><span id="outbound-flight"></span></td>
	</tr>
	<tr>
		<td>{lang=RETURN_FLIGHT}</td>
		<td><span id="return-flight"></span></td>
	</tr>
	<tr>
		<td>{lang}FUEL{/lang}</td>
		<td><span id="fuel">{@fuel}</span></td>
	</tr>
	<tr>
		<td>{lang}MAX_SPEED{/lang}</td>
		<td>{@maxspeed}</td>
	</tr>
	<tr>
		<td>{lang}CAPICITY{/lang}</td>
		<td><span id="capicity">{@capacity}</span></td>
	</tr>
	<?php if(count($this->getLoop("shortlinks"))): ?>
	<tr>
		<th colspan="2">{lang}SHORTLINKS{/lang}</th>
	</tr>
	<?php $count = count($this->getLoop("shortlinks")) ?>
	<?php foreach($this->getLoop("shortlinks") as $key => $row): ?>
	<?php if($key % 2 == 0): ?><tr><?php endif ?>
		<td>
			<a class="pointer" onclick="javascript:setCoordinates({loop}galaxy{/loop}, {loop}system{/loop}, {loop}position{/loop}, {loop}type{/loop});"><strong>{loop}planetname{/loop} [{loop}galaxy{/loop}:{loop}system{/loop}:{loop}position{/loop}]</strong></a>
		</td>
		<?php if($count == $key+1 && $key % 2 == 0): ?>
		<td></td>
		<?php endif ?>
		<?php if($key % 2 != 0 || $count == $key+1): ?>
		</tr>
		<?php endif ?>
	<?php endforeach ?>
	<?php endif ?>
	<?php if(count($this->getLoop("invitations"))): ?>
	<tr>
		<th colspan="2">{lang=FORMATION_INVATATIONS}</th>
	</tr>
	<tr>
		<td colspan="2">{foreach[invitations]}
			<span id="timer_{loop=eventid}">{loop=formatted_time}</span>
			&ndash; <a href="javascript:void(0);" onclick="setFormation({loop=eventid}, {loop=galaxy}, {loop=system}, {loop=position}, {loop=type});" class="true pointer">[{loop=galaxy}:{loop=system}:{loop=position}] | {loop=name}</a><br />
		{/foreach}</td>
	</tr>
	<?php endif ?>
	<tr>
		<td colspan="2" class="center">
			<input type="hidden" name="step3" value="1"/>
			<input type="submit" value="{lang}NEXT{/lang}" class="button" />
		</td>
	</tr>
</table>
</form>