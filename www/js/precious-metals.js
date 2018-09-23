$(document).ready(function() { 

  $('body').on("categoryUpdated", function (event) { 
      var id = event.categorySelectId;
      var categoryId = $('#categorySelect' + id).val();

      if ((categoryId == 61) || (categoryId == 128)) {
	  alert("You selected a precious metal category!");
      }
  });

  $('body').on("addAssetFormShown", function(event) {
    hidePmRows();
  });

  $('body').on("editAssetFormShown", function (event) {
    updatePmRowVis(event.categoryId);
    if ((event.categoryId == 61) || (event.categoryId == 128)) {
      populatePreciousMetalInfo(event.assetId);
    }
  });

  $('body').on('assetCategoryUpdated', function (event) { 
    updatePmRowVis(event.categoryId);
  });

  $('body').on('saveAsset', function (event) {
    var category = $('#assetCategoryId').val();
    if (category == 61) { 
      window.ajaxParams += '&pmAutoPricing='+$('#pmAutoPricing').is(':checked');
      window.ajaxParams += '&pmQty='+$('#pmQty').val();
      window.ajaxParams += '&pmType='+$('#silverType').val();
    }
    else if (category == 128) {
      window.ajaxParams += '&pmAutoPricing='+$('#pmAutoPricing').is(':checked');
      window.ajaxParams += '&pmQty='+$('#pmQty').val();
      window.ajaxParams += '&pmType='+$('#goldType').val();
    }
  });
});



function hidePmRows() {
    $('.silverAssetInfo').hide();
    $('.goldAssetInfo').hide();
    $('.pmAssetInfo').hide();
    $('#silverType').val('');
    $('#goldType').val('');
    $('#pmQty').val('1');
    $('#pmAutoPricing').prop('checked', false);
}


function updatePmRowVis(categoryId) {
  if (categoryId == 61) {
    $('.silverAssetInfo').show();
    $('.pmAssetInfo').show();
  }
  else if (categoryId == 128) {
    $('.goldAssetInfo').show();
    $('.pmAssetInfo').show();
  }
  else {
    hidePmRows();
  }
}


function populatePreciousMetalInfo(assetId) {   
  var url = "/precious-metal-asset-info?id=" + assetId;
  jQuery.ajax({
    url: url,
    success: function(raw) {
      var data = jQuery.parseJSON(raw);
      console.log(data);
      if (data.metal == "Silver") {
	$('#silverType').val(data.id);
      }
      if (data.metal == "Gold") {
	$('#goldType').val(data.id);
      }
      if (data.automaticPricing === "1") { $('#pmAutoPricing').prop('checked', true); }
      else { 
        console.log("TRYING TO UNCHECK");
        $('#pmAutoPricing').prop('checked', false); 
      }
      $('#pmQty').val(data.quantity);
    }
  });
}