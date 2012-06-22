"use strict";
$(document).ready(function() {
	$.ajax({
		url: 'ajax.php',
		success: function(data) {
			var output = "";
			var date;
			for (var datapoint in data.days) {
				date = moment(datapoint, "YYYY-MM-DD").format("ddd, MMM Do");
				output += "<tr><td>" + date + "</td><td>" + data.days[datapoint].usage + "</td><td>" + data.days[datapoint].cost + "</td><td>" + $.format.number(data.days[datapoint].co2, "#,##0") + "</td></tr>";
			}
			output = "<table>" + output + "</table>";
			$("#output").html(output);
			$("#total_usage").html(data.summary.total.usage + " kWh");
			$("#total_cost").html(data.summary.total.cost + " €");
			$("#total_co2").html($.format.number(data.summary.total.co2, "#,##0") + " g");
			$("#avg_usage").html(data.summary.avg.usage + " kWh");
			$("#avg_cost").html(data.summary.avg.cost + " €");
			$("#avg_co2").html($.format.number(data.summary.avg.co2, "#,##0") + " g");
			$("#detail_price").html(data.detail.price + " €/kWh");
			$("#detail_co2").html(data.detail.co2equivalents + " g/kWh");
		}
	}).fail(function() {
		alert("Something went wrong. Please try again.");
	});
 });
