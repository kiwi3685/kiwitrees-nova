<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

 if (!defined('KT_KIWITREES')) {
 	header('HTTP/1.0 403 Forbidden');
 	exit;
 }

global $KT_STATS_CHART_COLOR1, $KT_STATS_CHART_COLOR2, $KT_STATS_CHART_COLOR3;
?>

<script>
	google.charts.load(
	    "current",
	    {
	        "packages": [
	            "corechart",
				"bar",
	        ]
	    }
	);

	//INDIVIDUALS CHARTS
	// chartSex
	google.charts.setOnLoadCallback(drawChartSex);
	function drawChartSex() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->chartSex(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			colors: ["<?php echo $KT_STATS_CHART_COLOR1; ?>", "<?php echo $KT_STATS_CHART_COLOR2; ?>", "<?php echo $KT_STATS_CHART_COLOR3; ?>"],
			legend: {
				position: "top",
				alignment: "center",
				maxLines: 3,
			},
			sliceVisibilityThreshold: 0,
		 };
		 var chart = new google.visualization.PieChart(document.getElementById("chartSex"));
		 chart.draw(data, options);
	}

	// chartMortality
	google.charts.setOnLoadCallback(drawChartMortality);
	function drawChartMortality() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->chartMortality(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			legend: {
				position: "top",
				alignment: "center",
				maxLines: 3,
			},
			sliceVisibilityThreshold: 0,
		};
		var chart = new google.visualization.PieChart(document.getElementById("chartMortality"));
		chart.draw(data, options);
	}

	//chartStatsBirth by century
	google.charts.setOnLoadCallback(drawStatsBirth);
	function drawStatsBirth() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->statsBirth(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			legend: {position: "none"},
			vAxis: {format: "decimal"},
		};
		var chart = new google.visualization.ColumnChart(document.getElementById("chartStatsBirth"));
		chart.draw(data, options);
	}

	//chartStatsDeath by century
	google.charts.setOnLoadCallback(drawStatsDeath);
	function drawStatsDeath() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->statsDeath(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			legend: {position: "none"},
			vAxis: {format: "decimal"},
		};
		var chart = new google.visualization.ColumnChart(document.getElementById("chartStatsDeath"));
		chart.draw(data, options);
	}

	//chartStatsAge
	google.charts.setOnLoadCallback(drawStatsAge);
	function drawStatsAge() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->statsAge(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			colors: ["<?php echo $KT_STATS_CHART_COLOR1; ?>", "<?php echo $KT_STATS_CHART_COLOR2; ?>", "<?php echo $KT_STATS_CHART_COLOR3; ?>"],
			legend: {
				position: "top",
				alignment: "center",
				maxLines: 3,
			},
			seriesType: 'bars',
	  		series: {2: {type: "line", color: "#ff0000"}},
		};

		var chart = new google.visualization.ComboChart(document.getElementById('chartStatsAge'));
		chart.draw(data, options);
	}

	//chartCommonSurnames
	google.charts.setOnLoadCallback(drawStatsSurn);
	function drawStatsSurn() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->chartCommonSurnames(array(0,5)); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			legend: {
				position: "top",
				alignment: "center",
				maxLines: 3,
			},
		};
		var chart = new google.visualization.PieChart(document.getElementById("chartCommonSurnames"));
		chart.draw(data, options);
	}

	//chartCommonGiven
	google.charts.setOnLoadCallback(drawStatsGivn);
	function drawStatsGivn() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->chartCommonGiven(array(0,5)); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			legend: {
				position: "top",
				alignment: "center",
				maxLines: 3,
			},
		};
		var chart = new google.visualization.PieChart(document.getElementById("chartCommonGiven"));
		chart.draw(data, options);
	}

	// FAMILIES CHARTS
	//chartStatsMarr by century
	google.charts.setOnLoadCallback(drawStatsMarr);
	function drawStatsMarr() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->statsMarr(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			height: 300,
			legend: {position: "none"},
			vAxis: {format: "decimal"},
		};
		var chart = new google.visualization.ColumnChart(document.getElementById("chartStatsMarr"));
		chart.draw(data, options);
	}

	//chartStatsDiv by century
	google.charts.setOnLoadCallback(drawStatsDiv);
	function drawStatsDiv() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->statsDiv(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			height: 300,
			legend: {position: "none"},
			vAxis: {format: "decimal"},
		};
		var chart = new google.visualization.ColumnChart(document.getElementById("chartStatsDiv"));
		chart.draw(data, options);
	}

	//chartStatsMarrAge by century
	google.charts.setOnLoadCallback(drawStatsMarrAge);
	function drawStatsMarrAge() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->statsMarrAge(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			colors: ["<?php echo $KT_STATS_CHART_COLOR1; ?>", "<?php echo $KT_STATS_CHART_COLOR2; ?>", "<?php echo $KT_STATS_CHART_COLOR3; ?>"],
			legend: {
				position: "top",
				alignment: "center",
				maxLines: 3,
			},
			seriesType: 'bars',
	  		series: {2: {type: "line", color: "#ff0000"}},
		};

		var chart = new google.visualization.ComboChart(document.getElementById('chartStatsMarrAge'));
		chart.draw(data, options);
	}

	//chartStatsChildren age by century
	google.charts.setOnLoadCallback(drawStatsChildren);
	function drawStatsChildren() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->statsChildren(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			legend: {position: "none"},
			vAxis: {gridlines:{count: 6}},
		};

		var chart = new google.visualization.ColumnChart(document.getElementById('chartStatsChildren'));
		chart.draw(data, options);
	}

	//chartStatsChildren age by century
	google.charts.setOnLoadCallback(drawStatsNoChildren);
	function drawStatsNoChildren() {
		var data = google.visualization.arrayToDataTable(<?php echo $stats->chartNoChildrenFamilies(); ?>);
		var options = {
			backgroundColor: {fill: "transparent"},
			legend: {position: "none"},
			vAxis: {gridlines:{count: 6}},
		};

		var chart = new google.visualization.ColumnChart(document.getElementById('chartNoChildrenFamilies'));
		chart.draw(data, options);
	}


</script>
<?php
