$(document).ready(function() {
  $(".dateInput").datepicker({dateFormat:'yy-mm-dd'});
  drawNetworthChart();
});

$(window).resize(function() { drawNetworthChart(); });


function updateDateRange() {
  drawNetworthChart();
}
		       
function drawNetworthChart() {  
  var offset = new Date().getTimezoneOffset();
  var dataToPlot = [];

  var from = $('#from').val();
  var fromTS = new Date(from).getTime();
  var to = $('#to').val();
  var toTS = new Date(to).getTime();


  for (var i = 0; i < window.networthLog.length; i++) {
    var wl = [];

    wl[0] = window.networthLog[i][0] - (offset*60000); 
    wl[1] = window.networthLog[i][1];
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