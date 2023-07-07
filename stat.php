<?php
	$source=$_GET["source"];	
	if ($source=="")
		$source="source.csv";
	echo $source;
	if (isset($_GET["refund"])) {
		$color="#3ecd95";
		$label="debit (refunds+cashouts)";
		$refund=true;
	} else {
		if (isset($_GET["refused"])) {
			$color="#cd953e";
			$label="refused";
			$refused=true;
		} else {
			$color="#3e95cd";
			$label="Uptime Ticks";
			$refused=false;
		}		
	}	

	$scale=isset($_GET["scale"]) ? 1 : 0;	
?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">	
	<style>
        html, body {
            height: 100%;
            margin: 0;
			overflow-y: hidden;
        }
		#line-chart {
		  display: block;
		  width: 99%;
		}
		#wrapper {
			display:flex;		
            flex-direction: column;
			flex-flow: column;
			height: 95%;
			overflow-y: hidden;
		}
		
		#end {
		  min-width: 120px;
		}
		#start {
		  min-width: 120px;
		}
		.buttonbar {
          //background-color:red;
		  display:flex;
          flex:1;
          //flex-direction: column;
          align-items: stretch;
		}
        .buttoncontainer1 {
			min-height:180px;
			//overflow-x: hidden;
			display:flex;		
            flex-direction: column;
			flex-flow: column;
			//height: 180px;
			overflow-y: hidden;			
        }		
        .chart {
			flex:1 1 auto;			
            align-items: stretch;
			overflow: auto;
			height: 95%;
			//overflow-y: hidden;			
        }		
		.buttonbar > * {
        	flex: 1 1 auto;
			align-items: stretch;		
      	}
		button, input {
		  font-size: 0.8em;
          justify-content: center; /* center the content horizontally */
          align-items: center; /* center the content vertically */
          align-items: stretch;
		}  		
	</style>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/5.7.0/d3.min.js"></script>
	<!--<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>-->
</head>
<body>
	<div id="wrapper">
		<div class="chart">
			<canvas id="line-chart"></canvas>
		</div>
		<div class="buttoncontainer1">
				<div class="buttonbar">			  							   
					<button class="w3-button w3-black w3-hover-red" onclick="sda(-1);sea(-1);"><</button>
					<button class="w3-button w3-black w3-hover-red" style="background-color:rgb(120,120,255);" onclick="TodayDate()">Day</button>
					<button class="w3-button w3-black w3-hover-red" onclick="sda(1);sea(1);">></button>
					<button class="w3-button w3-black w3-hover-red" onclick="sda(-7);sea(-7);"><</button>
					<button class="w3-button w3-black w3-hover-red" style="background-color:rgb(120,120,255);" onclick="WeekDate()">Week</button>
					<button class="w3-button w3-black w3-hover-red" onclick="sda(7);sea(7);">></button>
					<button class="w3-button w3-black w3-hover-red" onclick="sda(-30);sea(-30);"><</button>
					<button class="w3-button w3-black w3-hover-red" style="background-color:rgb(120,120,255);" onclick="MonthDate()">Month</button>
					<button class="w3-button w3-black w3-hover-red" onclick="sda(30);sea(30);">></button>
				</div>
				<div class="buttonbar">		
					<button class="w3-button w3-white w3-hover-black" onclick="sdb(30)">|<</button>
					<button class="w3-button w3-white w3-hover-black" onclick="sdb(7)"><<</button>
					<button class="w3-button w3-white w3-hover-black" onclick="sdb(1)"><</button>
					<input class="w3-button w3-white w3-hover-black" id=start type="date" min="2022-01-01" value="<?=date("Y-m-d", strtotime("-7 day"));?>"> 
					<button class="w3-button w3-white w3-hover-black" onclick="sda(1)">></button>
					<button class="w3-button w3-white w3-hover-black" onclick="sda(7)">>></button>
					<button class="w3-button w3-white w3-hover-black" onclick="sda(30)">>|</button>
				</div>
				<div class="buttonbar">	
					<button class="w3-button w3-white w3-hover-black" onclick="seb(30)">|<</button>
					<button class="w3-button w3-white w3-hover-black" onclick="seb(7)"><<</button>
					<button class="w3-button w3-white w3-hover-black" onclick="seb(1)"><</button>
					<input class="w3-button w3-white w3-hover-black" id=end type="date" min="2022-01-01" value="2023-12-31">
					<button class="w3-button w3-white w3-hover-black" onclick="sea(1)">></button>
					<button class="w3-button w3-white w3-hover-black" onclick="sea(7)">>></button>
					<button class="w3-button w3-white w3-hover-black" onclick="sea(30)">>|</button>
				</div>
				<div class="buttonbar">
					<button class="w3-button w3-blue w3-hover-red" style="background-color:rgb(200,200,255);" onclick="changeScale()">Origine</button>
					<button class="w3-button w3-green w3-hover-red" style="background-color:lightgreen;" onclick="filterDate()">Filter</button>
					<button class="w3-button w3-orange w3-hover-red" style="background-color:rgb(255,90,90);" onclick="resetDate()">Reset</button>
				</div>
		</div>
	</div>
	<script>
		var scale=<?=$scale?>;
		const config = {
		  type: "line",
		  data: {
			labels: [],
			datasets: [{
			  data: [], // Set initially to empty data
			  label: "<?=$label?>",
			  borderColor: "<?=$color?>",
			  fill: false
			}]
		  },
		  options: {
		   responsive: true,
			maintainAspectRatio: false,
			scales: {
			  xAxes: [{
				type: "time",
				distribution: "linear",
                ticks: {
                    maxRotation: 90,
                    minRotation: 90
                }
			  }],
			  title: {
				display: false
			  }
			}
		  }
		};

		const ctx = document.querySelector("#line-chart").getContext("2d");
		const paymentChart = new Chart(ctx, config);
		//const convertedDates = [];

		const csvToChartData = csv => {
		  const lines = csv.trim().split("\n");
		  lines.shift(); // remove titles (first line)
		  return lines.map(line => {
			const [date, nombre] = line.split(",");
			return {
			  //x: new Date(date).setHours(0,0,0,0),
			  x: date,
			  y: nombre
			};
		  });
		};

		function filterDate(){
			fetchCSV();
		}

		function TodayDate(){
			var d = new Date();
			var datestring = d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
			console.log (datestring);
			document.getElementById('start').value = datestring;
			document.getElementById('end').value = datestring;
			fetchCSV();
		}
		
		function WeekDate(){
			const now = new Date();
			var ddeb = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
			var dfin = new Date();
			var debutstring = ddeb.getFullYear() + "-" + ("0"+(ddeb.getMonth()+1)).slice(-2) + "-" + ("0" + ddeb.getDate()).slice(-2);
			var finstring = dfin.getFullYear() + "-" + ("0"+(dfin.getMonth()+1)).slice(-2) + "-" + ("0" + dfin.getDate()).slice(-2);
			//console.log (datestring);
			document.getElementById('start').value = debutstring;
			document.getElementById('end').value = finstring;
			fetchCSV();
		}
		
		function MonthDate(){
			var d = new Date();
            var mois;
            var annee;
            annee=d.getFullYear();
            mois=d.getMonth();
            if (d.getMonth()==0){
                mois=12;
                annee=d.getFullYear()-1;
            }
			var debutstring = annee + "-" + ("0"+(mois)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
			var finstring = d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
			console.log (debutstring);
			console.log (finstring);
            console.log(d.getMonth());
			document.getElementById('start').value = debutstring;
			document.getElementById('end').value = finstring;
			fetchCSV();
		}

		function sdb(jour){
			const start = new Date(document.getElementById('start').value);
			var ddeb = new Date(start.getTime() - jour * 24 * 60 * 60 * 1000);
			var debutstring = ddeb.getFullYear() + "-" + ("0"+(ddeb.getMonth()+1)).slice(-2) + "-" + ("0" + ddeb.getDate()).slice(-2);
			//console.log (datestring);
			document.getElementById('start').value = debutstring;
			fetchCSV();
		}
		function sda(jour){
			const start = new Date(document.getElementById('start').value);
			var ddeb = new Date(start.getTime() + jour * 24 * 60 * 60 * 1000);
			var debutstring = ddeb.getFullYear() + "-" + ("0"+(ddeb.getMonth()+1)).slice(-2) + "-" + ("0" + ddeb.getDate()).slice(-2);
			//console.log (datestring);
			document.getElementById('start').value = debutstring;
			fetchCSV();
		}

		
		function seb(jour){
			const end = new Date(document.getElementById('end').value);
			var dfin = new Date(end.getTime() - jour * 24 * 60 * 60 * 1000);
			var finstring = dfin.getFullYear() + "-" + ("0"+(dfin.getMonth()+1)).slice(-2) + "-" + ("0" + dfin.getDate()).slice(-2);
			//console.log (datestring);
			document.getElementById('end').value = finstring;
			fetchCSV();
		}		

		function sea(jour){
			const end = new Date(document.getElementById('end').value);
			var dfin = new Date(end.getTime() + jour * 24 * 60 * 60 * 1000);
			var finstring = dfin.getFullYear() + "-" + ("0"+(dfin.getMonth()+1)).slice(-2) + "-" + ("0" + dfin.getDate()).slice(-2);
			//console.log (datestring);
			document.getElementById('end').value = finstring;
			fetchCSV();
		}		

		function resetDate(){
			document.getElementById('start').value = "2022-01-01";
			document.getElementById('end').value = "2023-12-31";
			fetchCSV();
		}

		function changeScale(){
			if (scale==true)
				scale=false;
			else
				scale=true;
			fetchCSV();
		}

		const fetchCSV = () => fetch("<?=$source?>")
		  .then(data => data.text())
		  .then(csv => {
			// Filter CSV JSON array before assiogniong to Graph
			const start1 = new Date(document.getElementById('start').value);
			const start = start1.setHours(0,0,0,0);
			console.log(start);
			const end1 = new Date(document.getElementById('end').value);
			const end = end1.setHours(0,0,0,0);
			var csvArray = csvToChartData(csv);
			/*var filteredCsvArray = csvArray.filter(function (a) {
				var hitDates = a.x || {};
				// extract all date strings
				hitDates = Object.keys(hitDates);
				// convert strings to Date objcts
				hitDates = hitDates.map(function(date) { return new Date(date); });
				// filter this dates by startDate and endDate
				var hitDateMatches = hitDates.filter(function(date) { return date >= start && date <= end });
				//console.log (hitDateMatches);
				// if there is more than 0 results keep it. if 0 then filter it away
				//return hitDateMatches.length > 0;
				return hitDateMatches;
        	});*/
			var filteredData = csvArray.filter(function(a){
				aDate = new Date(a.x).setHours(0,0,0,0);
				return aDate >= start && aDate <= end;
			});
			
			var displayedData 
				
	 
								
			
			if (scale) {		
				minCount = filteredData[0].y;			
				displayedData =filteredData.map(item => {
				return {
					  x: item.x,
					  y: item.y-minCount
					};
				});
			} else {
				
   
				displayedData =filteredData;
			}
	
			//paymentChart.data.datasets[0].data = filteredData;
			paymentChart.data.datasets[0].data = displayedData;
			console.log(paymentChart.data.datasets[0].data);
			paymentChart.update();
			setTimeout(fetchCSV, 300000); // Repeat every 5 sec
		  });

		fetchCSV(); // First fetch!
	</script>
</body>
</html>
