<?php
	header('Content-Type: text/html; charset=utf-8');
	require("libs.php");
	$conn = new Connection();
	$conn->Connect();
	$view = new View();
	$id = htmlspecialchars($_GET["id"]);
?>

	<div class="column left " id="xml_list">
			<ul >
				<?php
					echo $view->getListFiles($conn, $id);
				?>
			</ul>
	</div>
	
	<div class="column right" id="node_list">
		<ul>
			<?php
				echo $view->getListNodes($conn);
			?>
	</div>
	<div style="clear: left"></div>
<?php
	$conn->Disconnect();
?>