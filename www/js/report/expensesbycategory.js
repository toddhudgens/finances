$(document).ready(function() { plotExpensesByCategory();});
$(window).resize(function() { plotExpensesByCategory(); });

function runExpenseByCategoryReport() {   
  window.location =
   "/report/expenses-by-category?categoryId=" + $('#categoryId').val() +
     "&range=" + $('#range option:selected').val() +
     "&groupBy=" + $('#groupBy option:selected').val() +
     "&showAllTimePeriods=" + ($('#showAllTimePeriods').is(':checked') ? '1' : '0');
}

function plotExpensesByCategory() {
  if (!window.chartData || window.chartData.length == 0) { return; }
  $.plot($("#fullScreenChart"), [ window.chartData ],
    { series: { bars : { show: true, barWidth: 0.6, align: "center" } },
      grid: { hoverable:true },
      tooltip: true,
      tooltipOpts: { 
        content: function(label, xval, yval) { return xval + " - $" + yval; }, 
        shifts: { x:-40, y: 25 } 
      },
      xaxis: { mode: "categories", tickLength: 0 },
      tooltip: true
    });

  $.plot($("#expensesByEntityChart"), window.expensesByEntityData, { series: { pie: { show: true }}});

}