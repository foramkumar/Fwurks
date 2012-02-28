<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>{$__globals->META.administration_title}</title>
	<link rel="stylesheet" href="{url for='/back/styles/styles.php'}" type="text/css" media="screen" />
	<link rel="stylesheet" href="{url for="/back/styles/browser_`$__browser`.css"}" type="text/css" media="screen" charset="utf-8" />
	<script type="text/javascript">
		window.onload = function(){ document.getElementById('id_username').focus(); }
	</script>
</head>
<body id="login">
	<div class="panel">
		<h2>test</h2>
		<!-- <h1>{$__globals->META.login_title}</h1> -->
		<h1 class="header">Admin</h1>
		<div class="cleaner">&nbsp;</div>
			<form method="post">
				<div class="col w2"><div class="inner">{html_field type=text name=username value=$username}</div></div>
				<div class="col w2">{html_field type=password name=password}</div>
				<div class="cleaner">&nbsp;</div>
				<div class="buttons {if $error}errors{/if}">
					{if $error}
						<p>Username and Password does not match.</p>
					{/if}
					{html_field type=submit name=login class=login}
					<div class="cleaner">&nbsp;</div>
				</div>
			</form>		
		<div class="footer">
			<a href="http://antipodes.bg" class="logo" title="Antipodes ltd">Antipodes.bg</a>
			<div class="cleaner"></div>
		</div>
		
	</div>
</body>
</html>