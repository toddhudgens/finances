$(document).ready(function() {
  $(".dateInput").datepicker({dateFormat:'yy-mm-dd'});
  drawNetworthChart();

  $('.timePeriod').click(function() { 
    updateTimePeriod(this.id);
  });
});

$(window).resize(function() { drawNetworthChart(); });




function updateDateRange() {
  drawNetworthChart();
}

function updateTimePeriod(id) {
  var now = new Date();
  var thisYear = now.getFullYear();
  var lastYear = thisYear - 1;
  var thisDate = pad(now.getMonth(),2) + '-' + pad(now.getDay(),2);
  var today = thisYear + "-" + thisDate;

  if (id == "thisYear") {
    $('#from').val(thisYear + "-01-01");
    $('#to').val(today);
  }
  else if (id == "lastYear") {
    $('#from').val(lastYear + "-01-01");
    $('#to').val(lastYear + "-12-31");
  }
  else if (id == "last3") {
    $('#from').val((thisYear-3) + '-' + thisDate)
    $('#to').val(today);
  }
  else if (id == "last5") {
    $('#from').val((thisYear-5)+ '-' + thisDate)
    $('#to').val(today);
  }
  else if (id == "all") {
    $('#from').val(window.startingDate);
    $('$#to').val(today);
  }
  drawNetworthChart();
}
		       
function drawNetworthChart() {  
  var offset = new Date().getTimezoneOffset();
  var dataToPlot = [];

  var from = $('#from').val();
  var fromTS = new Date(from).getTime();
  var to = $('#to').val();
  var toTS = new Date(to+' 23:59:59').getTime();


  for (var i = 0; i < window.networthLog.length; i++) {
    var wl = [];

    wl[0] = window.networthLog[i][0] - (offset*60000); 
    wl[1] = window.networthLog[i][1];
    //console.log("Comparing " + wl[0] + " and " + fromTS + " and " + toTS);
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