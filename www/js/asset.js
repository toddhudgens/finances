var mode = '';
var assetId = 0;

function showAssetActions(id) { 
 $('#edit'+id).show(); 
 $('#delete'+id).show();
}

function hideAssetActions(id) { 
 $('#edit'+id).hide(); 
 $('#delete'+id).hide();
}

function showAssetEditForm(id) {
  assetId = id;
  mode = 'edit';

  $('input#assetName').val($('#assetName'+assetId).val());

  $('input#assetCategorySearch').val($('#categoryName'+assetId).val());
  $('input#assetCategoryId').val($('#categoryId'+assetId).val());
  $('input#currentValue').val($('#currentValue'+assetId).val());
  $('input#purchasePrice').val($('#purchasePrice'+assetId).val());
  $('#madeIn').val($('#madeIn'+assetId).val());
  $('input#picture').val($('#picture'+assetId).val());
  $('textarea#notes').html($('#notes'+assetId).html());

  $('#editAssetForm').dialog({
    modal: true,
    title: "Edit Asset",
    width:600
  });

  // trigger event for plugins to update view if needed
  var event = jQuery.Event("editAssetFormShown");
  event.assetId = id;
  event.categoryId = $('#categoryId'+assetId).val();

  $("body").trigger(event);
}

function showNewAssetForm() {
  $('#assetName').val('');
  $('#assetCategorySearch').val('');
  $('#assetCategoryId').val('');
  $('#purchasePrice').val('');
  $('#currentValue').val('');
  $('#notes').html('');
  $('#picture').val('');
  $('#madeIn').val('');

  assetId = 0;
  mode = 'new';
  $('#editAssetForm').dialog({
    modal: true,
    title: "Add New Asset",
    width:600
  });

  var event = jQuery.Event("addAssetFormShown");
  $("body").trigger(event);
} 


function saveAsset() {
  window.ajaxURL = '/asset/save?';
  window.ajaxParams = 
     'mode=' + mode + '&' +
     'assetId=' + assetId + '&' + 
     'name=' + encodeURIComponent($('input#assetName').val()) + '&' + 
     'category=' + $('input#assetCategoryId').val() + '&' + 
     'categoryName='+encodeURIComponent($('input#assetCategorySearch').val())+'&' + 
     'madeIn='+$('#madeIn').val() + '&' + 
     'picture='+encodeURIComponent($('input#picture').val()) + '&' + 
     'currentValue=' + $('input#currentValue').val() + '&' + 
     'purchasePrice=' + $('input#purchasePrice').val() + '&' + 
     'notes=' + $('textarea#notes').val();

  // trigger event for plugins to append additional GET vars if needed 
  var event = jQuery.Event("saveAsset");
  $("body").trigger(event);

  jQuery.ajax({
    url: window.ajaxURL + window.ajaxParams,
    success: function(data) {
      var results = jQuery.parseJSON(data);
      if (results[0] === "success") {
        $('editAssetForm').dialog('close');
	window.location.reload();
      }
      else { alert(data); }
    }
  });
}


function deleteAsset(id) {
  if (confirm("Are you sure you want to delete this asset?")) {
    var url = '/asset/delete?id='+id;
    jQuery.ajax({
      url: url,
      success: function(data) {
        var results = jQuery.parseJSON(data);
        if (results[0] === "success") { window.location.reload(); }
        else { alert(data); }
      }
    });
  }
}