<?php
	ini_set('session.gc_maxlifetime', 28800);
	ini_set('session.cookie_lifetime', 28800);
	session_set_cookie_params(28800);

	try
	{
    	$pdo = new PDO("pgsql:host=localhost;dbname=atendeai", "postgres", "91lS!&*Ke");
    	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e)
    {
    	echo "Erro: " . $e->getMessage();
	}
?>