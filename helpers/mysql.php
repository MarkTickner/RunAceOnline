<?php
	$dbHost		= getenv('OPENSHIFT_MYSQL_DB_HOST') . ':' . getenv('OPENSHIFT_MYSQL_DB_PORT');
	$dbUsername	= getenv('OPENSHIFT_MYSQL_DB_USERNAME');
	$dbPassword	= getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
	$dbName		= getenv('OPENSHIFT_APP_NAME');
?>