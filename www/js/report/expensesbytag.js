$(document).ready(function() { plotExpensesByTag(); });
$(window).resize(function() { plotExpensesByTag(); });

function runExpensesByTagReport() {   
  window.location =
   "/report/expenses-by-tag?tagId=" + $('#tagId').val() +
     "&range=" + $('#range option:selected').val() +
     "&groupBy=" + $('#groupBy option:selected').val() +
     "&showAllTimePeriods=" + ($('#showAllTimePeriods').is(':checked') ? '1' : '0');
}


function plotExpensesByTag() {   
  $.plot($("#fullScreenChart"), [ window.chartData ],
    { series: { bars : { show: true, barWidth: 0.6, align: "center" } },
      grid: { hoverable:true },
      tooltip: true,
      tooltipOpts: { content: "%x - $%y", shifts: { x:-40, y: 25 } },
      xaxis: { mode: "categories", tickLength: 0 },
      tooltip: true
    });
}