<?php
	header('Content-Type: text/html; charset=utf-8');
	require("libs.php");
	$conn = new Connection();
	$conn->Connect();
	$id = htmlspecialchars($_GET["id"]);

	$nodes = new Nodes();
	echo $nodes->getListAttrs($conn, $id);
	echo $nodes->getChildsAndValue($conn, $id);
?>
