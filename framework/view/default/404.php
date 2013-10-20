<?php
header("404 Not Found");
?>
<html>
<head>
<title>404 Not Found</title>
</head>
<body>
<h1>Not Found</h1>
<p>The requested URL <?php phpsec\printf(phpsec\HttpRequest::InternalPath())?> was not found on this server.</p>
<hr/>
<address><?php phpsec\printf(phpsec\framework\whoami);?></address>
</body>
</html>
