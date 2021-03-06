<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{lang}PILLORY{/lang}{config=TITLE_GLUE}{@pageTitle}</title>
<meta http-equiv="content-type" content="text/html; charset={@charset}" />
<link rel="shortcut icon" href="{const}BASE_URL{/const}favicon.ico" type="image/x-icon"/>
{if[{var=CSS_FILES} != ""]}<link rel="stylesheet" type="text/css" href="{const}BASE_URL{/const}css/?f={@CSS_FILES}"/>{/if}
{if[{var=JS_FILES} != ""]}<script type="text/javascript" src="{const}BASE_URL{/const}js/?f={@JS_FILES}"></script>{/if}
<link rel="alternate" type="application/rss+xml" title="Feed" href="{const}BASE_URL{/const}pillory/rss"/>
<link rel="alternate" type="application/atom+xml" title="Feed" href="{const}BASE_URL{/const}pillory/atom"/>
</head>
<body>
	{hook}FrontHtmlBegin{/hook}
	<br/>
	{@pagination}
	<table class="ntable center-table clear">
		<thead>
			<tr>
				<th>#</th>
				<th>{lang}USERNAME{/lang}</th>
				<th>{lang}PILLORY_FROM{/lang}</th>
				<th>{lang}PILLORY_TO{/lang}</th>
				<th>{lang}REASON{/lang}</th>
				<th>{lang}MODERATOR{/lang}</th>
			</tr>
		</thead>
		<tbody>
			{foreach[bans]}<tr>
				<td>{loop=counter}</td>
				<td>{loop=username}</td>
				<td>{loop=from}</td>
				<td>{loop=to}</td>
				<td>{loop=reason}</td>
				<td>{loop=moderator}</td>
			</tr>{/foreach}
		</tbody>
	</table>
	<br/>
	{@pagination}
	{hook}FrontHtmlEnd{/hook}
</body>
</html>