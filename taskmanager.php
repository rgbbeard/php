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
				font-size: 13px;
				font-family: "Calibri", sans-serif;
			}

			html, body {
				margin: 0;
				padding: 0;
				scroll-behavior: smooth;
			}

			#legenda {
				position: sticky;
				height: auto;
				width: 100%;
				top: 0;
				left: 0;
				background-color: #fff;
				margin-bottom: 10px;
				padding: 5px;
			}

			#legenda span {
				display: inline-block;
				width: 10%;
				height: 20px;
				text-align: center;
				line-height: 20px;
				user-select: none;
				-moz-user-select: none;
				-webkit-user-select: none;
			}

			#scrolltop {
				display: inline-block;
				position: fixed;
				right: 30px;
				bottom: 30px;
				padding: 8px 10px;
				background-color: #c00;
				color: #fff;
				cursor: pointer;
				font-size: 16px;
			}

			a {
				text-decoration: none;
			}

			input,
			a {
				cursor: pointer;
			}

			input:not([type="checkbox"]),
			a {
				display: inline-block;
				appearance: none;
				border: solid 1px #ccc;
				padding: 4px 8px;
				outline: none !important;
			}

			input:not([type="checkbox"]):focus {
				background-color: #0055ff22;
			}

			input[type="submit"],
			a {
				background-color: #efefef;
				color: #000;
			}

			table thead {
				position: sticky;
				top: 30px;
				background-color: #940;
				color: #fff;
			}

			table {
				border-collapse: collapse;
			}

			table tr {
				margin: 2px 0;
			}

			table tr td {
				padding: 3px 7px;
			}

			table tr:hover {
				background-color: #0055ff44;
			}

			table tr input:hover {
				background-color: #c00;
				color: #fff;
			}

			.is_daemon {
				color: #111;
				background-color: #66666644;
			}

			.is_system {
				color: #111;
				background-color: #6600aa44;
			}

			.is_vital {
				color: #111;
				background-color: #aa550044;
			}

			.is_ordinary {
				color: #111;
				background-color: #fff;
			}
		</style>
	</head>
	<body>
		<form action="" method="post">
			<div id="legenda">
				<span class="is_vital">Vital</span>
				<span class="is_daemon">Daemons</span>
				<span class="is_system">System</span>
				<span class="is_ordinary">Others</span>
				<span>
					<b>Processes found: <?php echo sizeof($active_processes);?></b>
				</span>
				<span>
					<b>Refreshing in: <i id="refresh-display">60</i>s</b>
				</span>
			</div>
			<table width="100%">
				<input type="text" name="search-process" placeholder="Find process" value="<?php echo @empty($_GET["pn"]) ? "" : base64_decode($_GET["pn"]);?>">
				<br>
				<br>
				<div>
					<input id="filter_vital" type="checkbox" name="filter_vital" value="0">
					<label for="filter_vital">Filter vital processes</label>
					<input id="filter_daemon" type="checkbox" name="filter_daemon" value="1">
					<label for="filter_daemon">Filter daemon processes</label>
					<input id="filter_system" type="checkbox" name="filter_system" value="2">
					<label for="filter_system">Filter system processes</label>
				</div>
				<br>
				<br>
				<input type="submit" name="search" value="Filter processes">
				<br>
				<br>
				<div>
					<a href="<?php echo $_SERVER["REQUEST_URI"];?>">Refresh</a>
				</div>
				<?php
				if(@!empty($_GET)) {
					echo "<br>
					<div>
						<a href=\"http://localhost:9010/taskmanager.php\">Clear filters</a>
					</div>";
				}

				if(isset($_POST["search"])) {
					if(!empty(trim($_POST["search-process"]))) {
						$params[] = "pn=" . base64_encode($_POST["search-process"]);
					}

					if(isset($_POST["filter_vital"]) || isset($_POST["filter_system"]) || isset($_POST["filter_daemon"])) {
						$filters = [];

						if(!is_null($_POST["filter_vital"])) {
							$filters[] = $_POST["filter_vital"];
						}

						if(!empty($_POST["filter_daemon"])) {
							$filters[] = $_POST["filter_daemon"];
						}

						if(!empty($_POST["filter_system"])) {
							$filters[] = $_POST["filter_system"];
						}

						$params[] = "filters=" . base64_encode(implode(";", $filters));
					}

					header("Location: http://localhost:9010/taskmanager.php?" . implode("&", $params));
				}
				?>
				<br>
				<br>
				<thead>
					<tr>
						<td>PROCESS ID</td>
						<td>PPID</td>
						<td>TIME DD:HH:MM</td>
						<td>COMMAND</td>
						<td>ACTIONS</td>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($active_processes as $process) {
						$pid = $process[3];
						$ppid = $process[4];
						$time = $process[12];
						$cmd = $process[13];
						$input_name = "kpid-$pid";
						$row_color = "";

						$process_classes = [];

						if(process_is_vital($process)) {
							$process_classes[] = "is_vital";
						}elseif(process_is_daemon($process)) {
							$process_classes[] = "is_daemon";
						} elseif(process_is_system($process)) {
							$process_classes[] = "is_system";
						} else {
							$process_classes[] = "is_ordinary";
						}

						if(@!empty($_GET["filters"])) {
							$processes_filters = explode(";", base64_decode($_GET["filters"])); 
							if(!in_array($ppid, $processes_filters)) {
								continue; # Just don't display this process
							}

						}

						$process_classes = implode(" ", $process_classes);

						echo "<tr class=\"$process_classes\">
							<td>$pid</td>
							<td>$ppid</td>
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
		<span id="scrolltop" title="Return to top">&uarr;</span>
	</body>
</html>
<script type="text/javascript">
	window.onload = function() {
		let timeout = 59, display = document.getElementById("refresh-display");
		setInterval(function() {
			display.textContent = timeout;
			timeout--;
		}, 1000);
		setTimeout(function() {
			window.location.href = "http://localhost:9010/taskmanager.php";
		}, timeout*1000);
		document.getElementById("scrolltop").onclick = function() {
			window.scrollTo(0, 0);
		};
	};
</script>
