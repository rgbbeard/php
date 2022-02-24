<?php
require_once "../lnxutils.php";

$active_processes = @empty($_GET["pn"]) ? get_active_processes(true) : find_process(base64_decode($_GET["pn"]));
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Task Manager</title>
		<style type="text/css">
			* {
				box-sizing: border-box;
				font-size: 14px;
				font-family: "Arial", sans-serif;
			}

			input {
				appearance: none;
				border: solid 1px #ccc;
				padding: 4px 8px;
				outline: none !important;
			}

			input:focus {
				background-color: #0055ff22;
			}

			table {
				border-collapse: collapse;
			}

			table tr td {
				padding: 4px 8px;
			}

			table tr:hover {
				background-color: #0055ff44;
			}

			table tr input:hover {
				background-color: #c00;
				color: #fff;
			}
		</style>
	</head>
	<form action="" method="post">
		<table width="100%">
			<input type="text" name="search-process" placeholder="Find process" value="<?php echo @empty($_GET["pn"]) ? "" : base64_decode($_GET["pn"]);?>">
			<input type="submit" name="search" value="Find process">
			<?php
			if(@!empty($_GET["pn"])) {
				echo "<div>
					<a href=\"http://localhost:9010/taskmanager.php\">Clear filters</a>
				</div>";
			}

			if(isset($_POST["search"]) && !empty(trim($_POST["search-process"]))) {
				header("Location: http://localhost:9010/taskmanager.php?pn=" . base64_encode($_POST["search-process"]));
			}
			?>
			<br>
			<br>
			<thead>
				<tr>
					<td>PID</td>
					<td>TTY</td>
					<td>TIME</td>
					<td>CMD</td>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($active_processes as $process) {
					$pid = $process[0];
					$tty = $process[1];
					$time = $process[2];
					$cmd = $process[3];
					$input_name = "kpid-$pid";
					$row_color = "";

					if(process_is_daemon($process)) {
						$row_color = "color:#fff;background-color:purple;";
					}

					echo "<tr style=\"$row_color\">
						<td>$pid</td>
						<td>$tty</td>
						<td>$time</td>
						<td>$cmd</td>
						<td><input type=\"submit\" name=\"$input_name\" value=\"Kill process\"></td>
					</tr>";

					if(isset($_POST[$input_name])) {
						kill_process(intval($pid));
						header("Location: " . $_SERVER["PHP_SELF"]);
					}
				}
				?>
			<tbody>
		</table>
	</form>
</tbody>