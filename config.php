<?php
    date_default_timezone_set('America/Sao_Paulo');
	//ini_set('session.gc_maxlifetime', 28800);
	//ini_set('session.cookie_lifetime', 28800);
	//session_set_cookie_params(28800);

	try
	{
    	$pdo = new PDO("pgsql:host=45.224.128.87;dbname=atendeai", "postgres", "91lS!&*Ke");
    	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e)
    {
    	echo "Erro: " . $e->getMessage();
	}
?>