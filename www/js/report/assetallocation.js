$(document).ready(function() { plotAssetAllocation(); });
$(window).resize(function() { plotAssetAllocation(); });

function plotAssetAllocation() {   
  $.plot($("#fullScreenChart"), window.assetAllocationData, { series: { pie: { show: true }}});
}