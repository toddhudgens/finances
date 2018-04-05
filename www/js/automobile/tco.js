$(document).ready(function() { 
  plotAutoTco(); 

  $("#carSelector").change(function() {
    window.location = '/automobile/tco?id='+this.value;
  });

});
$(window).resize(function() { plotAutoTco(); });

function plotAutoTco() {
  $.plot($("#fullScreenChart"), window.chartData, { series: { pie: { show: true }}});
}