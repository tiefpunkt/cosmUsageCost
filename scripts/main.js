$(document).ready(function() {
	$.ajax({
		url: 'ajax.php',
		success: function(data) {
			console.log(data);
			output = "";
			for (var datapoint in data.days) {
				output += "<tr><td>" + datapoint + "</td><td>" + data.days[datapoint].usage + "</td><td>" + data.days[datapoint].cost + "</td><td>" + data.days[datapoint].co2 + "</td></tr>";
			}
			output = "<table>" + output + "</table>";
			$("#output").html(output);
			$("#total_usage").html(data.summary.total.usage + " kWh");
			$("#total_cost").html(data.summary.total.cost + " €");
			$("#total_co2").html(data.summary.total.co2 + " g");
			$("#avg_usage").html(data.summary.avg.usage + " kWh");
			$("#avg_cost").html(data.summary.avg.cost + " €");
			$("#avg_co2").html(data.summary.avg.co2 + " g");
			$("#detail_price").html(data.detail.price + " €/kWh");
			$("#detail_co2").html(data.detail.co2equivalents + " g/kWh");
		}
	}).fail(function() {
		alert("Epic Error");
	});
 });
