 <?php
	/*
		*
		* OpenGL ES hardware capability database server implementation
		*
		* Copyright (C) 2013-2018 by Sascha Willems (www.saschawillems.de)
		*
		* This code is free software, you can redistribute it and/or
		* modify it under the terms of the GNU Affero General Public
		* License version 3 as published by the Free Software Foundation.
		*
		* Please review the following information to ensure the GNU Lesser
		* General Public License version 3 requirements will be met:
		* http://www.gnu.org/licenses/agpl-3.0.de.html
		*
		* The code is distributed WITHOUT ANY WARRANTY; without even the
		* implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
		* PURPOSE.  See the GNU AGPL 3.0 for more details.
		*
	*/ 
 
	include 'header.html';
	include 'dbconfig.php';	
	
	$name = null;
	$esversion = 2;
	if (isset($_GET['name'])) {
		$name = $_GET['name'];
	}
	if (isset($_GET['esversion'])) {
		$esversion = $_GET['esversion'];
	}
	$tablename = "reports_es20caps";
	if ($esversion == "3") {
		$tablename = "reports_es30caps";
	}														

	// Check if capability as valid and part of the selected table
	DB::connect();
	$result = DB::$connection->prepare("SELECT * from information_schema.columns where TABLE_NAME= :tablename and column_name = :columnname");
	$result->execute([":columnname" => $name, ":tablename" => $tablename]);
	DB::disconnect();
	if ($result->rowCount() == 0) {
		echo "<center>";
		?>
			<div class="alert alert-danger error">
			<strong>This is not the <strike>droid</strike> capability you are looking for!</strong><br><br>
			You may have passed a wrong capability name or selected the wrong OpenGL ES target version.
			</div>				
		<?php
		include "footer.html";
		echo "</center>";
		die();		
	}

?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>	
	<script>
		$(document).ready(function() {
			var table = $('#extensions').DataTable({
				"pageLength" : -1,
				"paging" : false,
				"stateSave": false, 
				"searchHighlight" : true,	
				"dom": '',			
				"bInfo": false,	
				"order": [[ 0, "asc" ]]	
			});
		} );	
	</script>

	<div class='header'>
		<h4 class='headercaption'>Value distribution for <?php echo $name ?> (OpenGL ES <?php echo $esversion ?>.0)</h4>
	</div>

	<center>	
		<div class='parentdiv'>
			<div id="chart"></div>
			<div class='tablediv' style='width:auto; display: inline-block;'>	
				<table id="extensions" class="table table-striped table-bordered table-hover reporttable" >
					<thead>
						<tr>				
							<th>Value</th>
							<th>Reports</th>
						</tr>
					</thead>
					<tbody>				
						<?php		
							DB::connect();			
							// TODO: Check if name is valid column name (security!)
							$result = DB::$connection->prepare("SELECT $name as value, count(0) as reports from $tablename where $name > 0 group by 1 order by 1");
							$result->execute();
							$rows = $result->fetchAll(PDO::FETCH_ASSOC);
							foreach ($rows as $cap) {
								$link ="listreports.php?capability=$name&esversion=".$esversion."&value=".$cap["value"];
								echo "<tr>";						
								echo "<td>".$cap["value"]."</td>";
								echo "<td><a href='$link'>".$cap["reports"]."</a></td>";
								echo "</tr>";	    
							}     
							DB::disconnect();       			
						?>   					
					</tbody>
				</table> 

			</div>
		</div>
	</center>
	
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Value', 'Reports'],
			<?php 
				DB::connect();			
				// TODO: Check if name is valid column name (security!)
				$result = DB::$connection->prepare("SELECT $name as value, count(0) as reports from $tablename where $name > 0 group by 1 order by 2 desc");
				$result->execute();
				$rows = $result->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					echo "['".$row['value']."',".$row['reports']."],";
				}     
				DB::disconnect();
			?>		
		]);

        var options = {
			legend: { position: 'bottom' },
			chartArea: { width:"80%", height:"80%" },
			height: 500,
			width: 500		  
        };

        var chart = new google.visualization.PieChart(document.getElementById('chart'));

        chart.draw(data, options);
	  }
	</script>

	<?php 
		include "footer.html";
	?>

</body>
</html>