<?php
	header("Error!");
?>

<html>
	<head>
		<title>Error In Page!</title>
	</head>
	
	<body>
		<h1>Oops! Error in page.</h1>
		<p>The requested URL <?php phpsec\printf(phpsec\HttpRequest::InternalPath())?> encountered an error.</p>
		<hr/>
		<address><?php phpsec\printf(phpsec\framework\whoami);?></address>
	</body>
</html>