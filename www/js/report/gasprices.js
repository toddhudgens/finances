$(document).ready(function() {
  $(".dateInput").datepicker({dateFormat:'yy-mm-dd'});
  drawGasPriceChart();
});

$(window).resize(function() { drawGasPriceChart(); });


function updateDateRange() {
  drawGasPriceChart();
}
		       
function drawGasPriceChart() {  
  var offset = new Date().getTimezoneOffset();
  var dataToPlot = [];

  var from = $('#from').val();
  var fromTS = new Date(from).getTime();
  var to = $('#to').val();
  var toTS = new Date(to).getTime();


  for (var i = 0; i < window.gasPriceLog.length; i++) {
    var wl = [];

    wl[0] = window.gasPriceLog[i][0] - (offset*60000); 
    wl[1] = window.gasPriceLog[i][1];
    console.log("Comparing " + wl[0] + " and " + fromTS + " and " + toTS);
    if ((wl[0] > fromTS) && (wl[0] < toTS)) {
      dataToPlot.push(wl);
    }
  }

  var now = new Date();
  var rangeEnd = new Date(now.getTime() - (offset*60000));
  var rangeStart = $('#startDate').val();

  var options = {
    xaxis: {
      mode: "time",
      min: rangeStart,
      max: rangeEnd
    }
  };
  console.log(options);
  console.log(dataToPlot);
  $('#fullScreenChart').html('');
  $.plot("#fullScreenChart", [dataToPlot], options);
}