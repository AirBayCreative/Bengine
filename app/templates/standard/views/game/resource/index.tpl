<script type="text/javascript" src="{const=BASE_URL}js/?f=lib/wz_tooltip.js"></script>
<script type="text/javascript">
//<![CDATA[
var buildings = new Array();
{foreach[data]}{if[$row["level"] > 0]}
buildings.push({loop}id{/loop});
{/if}{/foreach}
{if[{var}sats{/var} > 0]}
buildings.push(39);
{/if}
//]]>
</script>
<form method="post" action="{@updateAction}">
<table class="ntable" class="center">
	<tr>
		<th colspan="6">{lang}RESOURCE_PRODUCTION_FOR_PLANET{/lang} {@planetName}</th>
	</tr>
	<tr>
		<td></td>
		<td><strong>{lang}METAL{/lang}</strong></td>
		<td><strong>{lang}SILICON{/lang}</strong></td>
		<td><strong>{lang}HYDROGEN{/lang}</strong></td>
		<td><strong>{lang}ENERGY{/lang}</strong></td>
		<td></td>
	</tr>
	<tr>
		<td><strong>{lang}BASIC_PRODUCTION{/lang}</strong></td>
		<td><span class="true">{@basicMetal}</span></td>
		<td><span class="true">{@basicSilicon}</span></td>
		<td>0</td>
		<td>0</td>
		<td></td>
	</tr>
	{foreach[data]}{if[$row["level"] > 0]}<tr>
		<td><strong>{loop}name{/loop} ({loop}level{/loop})</strong></td>
		<td>{if[$row["metal"] > 0]}<span class="true">{loop}metal{/loop}</span>{else if[$row["metalCons"] > 0]}<span class="false">{loop}metalCons{/loop}</span>{else}0{/if}</td>
		<td>{if[$row["silicon"] > 0]}<span class="true">{loop}silicon{/loop}</span>{else if[$row["siliconCons"] > 0]}<span class="false">{loop}siliconCons{/loop}</span>{else}0{/if}</td>
		<td>{if[$row["hydrogen"] > 0]}<span class="true">{loop}hydrogen{/loop}</span>{else if[$row["hydrogenCons"] > 0]}<span class="false">{loop}hydrogenCons{/loop}</span>{else}0{/if}</td>
		<td>{if[$row["energy"] > 0]}<span class="true">{loop}energy{/loop}</span>{else if[$row["energyCons"] > 0]}<span class="false">{loop}energyCons{/loop}</span>{else}0{/if}</td>
		<td><input type="text" name="{loop}id{/loop}" id="factor_{loop}id{/loop}" value="{loop}factor{/loop}" maxlength="3" size="3" onblur="checkNumberInput(this, 0, 100);" class="center"/>% <select onchange="setFromSelect('factor_{loop}id{/loop}', this)"><option value="none" class="center">-</option>{@selectProd}</select></td>
	</tr>{/if}{/foreach}
	{if[{var}sats{/var} > 0]}<tr>
		<td><strong class="helptip" onmouseover="Tip('<span class=true>{lang}PROD_ONE_SOLAR_SATELLITE{/lang}</span>', FADEIN, 500);" onmouseout="UnTip();">{lang}SOLAR_SATELLITE{/lang} ({@satsNum})</strong></td>
		<td>0</td>
		<td>0</td>
		<td>0</td>
		<td><span class="true">{@satsProd}</span></td>
		<td><input type="text" name="39" id="factor_39" value="{@solar_satellite_prod}" maxlength="3" size="3" onblur="checkNumberInput(this, 0, 100);" class="center"/>% <select onchange="setFromSelect('factor_39', this)"><option value="none" class="center">-</option>{@selectProd}</select></td>
	</tr>{/if}
	<tr>
		<td class="strongBorderTop"><strong>{lang}STORAGE_CAPICITY{/lang}</strong></td>
		<td class="strongBorderTop"><span class="true">{@storageMetal}</span></td>
		<td class="strongBorderTop"><span class="true">{@storageSilicon}</span></td>
		<td class="strongBorderTop"><span class="true">{@sotrageHydrogen}</span></td>
		<td class="strongBorderTop">-</td>
		<td class="strongBorderTop"><input type="submit" name="update" value="{lang}COMMIT{/lang}" class="button" /></td>
	</tr>
	<tr>
		<td><strong>{lang}SUM{/lang}</strong></td>
		<td><span class="true">{@totalMetal}</span></td>
		<td><span class="true">{@totalSilicon}</span></td>
		<td><span class="true">{@totalHydrogen}</span></td>
		<td><span class="{if[{var}totalEnergy{/var} <= 0]}false{else}true{/if}">{@totalEnergy}</span></td>
		<td><input type="button" class="button" value="{lang}SHUT_DOWN{/lang}" onclick="javascript:setProdTo0();" /></td>
	</tr>
	<tr>
		<td class="strongBorderTop"><strong>{lang}DAILY_PRDOUCTION{/lang}</strong></td>
		<td class="strongBorderTop"><span class="true">{@dailyMetal}</span></td>
		<td class="strongBorderTop"><span class="true">{@dailySilicon}</span></td>
		<td class="strongBorderTop"><span class="true">{@dailyHydrogen}</span></td>
		<td class="strongBorderTop"><span class="{if[{var}totalEnergy{/var} <= 0]}false{else}true{/if}">{@totalEnergy}</span></td>
		<td class="strongBorderTop"><input type="button" class="button" value="{lang}START_UP{/lang}" onclick="javascript:setProdTo100();" /></td>
	</tr>
	<tr>
		<td><strong>{lang}WEEKLY_PRDOUCTION{/lang}</strong></td>
		<td><span class="true">{@weeklyMetal}</span></td>
		<td><span class="true">{@weeklySilicon}</span></td>
		<td><span class="true">{@weeklyHydrogen}</span></td>
		<td><span class="{if[{var}totalEnergy{/var} <= 0]}false{else}true{/if}">{@totalEnergy}</span></td>
		<td></td>
	</tr>
	<tr>
		<td style="margin: 20px;"><strong>{lang}MONTHLY_PRDOUCTION{/lang}</strong></td>
		<td><span class="true">{@monthlyMetal}</span></td>
		<td><span class="true">{@monthlySilicon}</span></td>
		<td><span class="true">{@monthlyHydrogen}</span></td>
		<td><span class="{if[{var}totalEnergy{/var} <= 0]}false{else}true{/if}">{@totalEnergy}</span></td>
		<td></td>
	</tr>
</table>
</form>
