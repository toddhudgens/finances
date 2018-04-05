$( document ).ready(function() {
  $('input#searchText').keypress(function (e) {
    if(e.keyCode == 13) { transactionSearch(); }
  });
});

function showAccountActions(id) {
  $('#editAccount'+id).show();
}

function hideAccountActions(id) {
  $('#editAccount'+id).hide();
}


function clearAccountInputs() {
  $('#accountName').val('');
  $('#initialBalance').val('');
  $('#accountType').val('');
  $('#assetSearch').val('');
  $('#associatedAssetRow').hide();
  $('#liquid').attr('checked', false);
  $('#accountActive').attr('checked', false);
}

function showNewAccountForm() {
  clearAccountInputs();

  $('#accountId').val('');

  $('#editAccountForm').dialog({
    modal: true,
    title: "Add New Account",
    width:600
  });
}

function editAccount(id) {
  clearAccountInputs();
  $('#accountId').val(id);
  $('#accountName').val($('#accountName'+id).val());
  $('#initialBalance').val($('#initialBalance'+id).val());

  var accountType = $('#accountType'+id).val();
  $('#accountType').val(accountType);

  if ((accountType == "4") || (accountType == "6")) { 
    $('#associatedAssetRow').show();
  }

  var liquid = false;
  if ($('#liquid'+id).val() == 1) { liquid = true; }
  $('#liquid').prop('checked', liquid);

  var active = false;
  if ($('#accountActive'+id).val() == 1) { active = true; }
  $('#accountActive').prop('checked', active);

  $('#assetSearch').val($('#assetName'+id).val());
  $('#assetId').val($('#assetId'+id).val());

  $('#notes').val($('#notes'+id).val());

  $('#editAccountForm').dialog({
    modal: true,
    title: "Edit Account",
    width:600
  });
}


function updateAccountType() {
  var accountType = $('#accountType').val();
  if ((accountType == "Loan") || (accountType == "Mortgage")) { 
    $('#associatedAssetRow').show();
  }
  else { $('#associatedAssetRow').hide(); }
}


function saveAccount() {
  var liquid = 0; var active = 0;
  if ($('#liquid').is(":checked")) { liquid = 1; }
  if ($('#accountActive').is(":checked")) { active = 1; }

  var url = 
   '/account/save?' + 
    'accountId=' + $('#accountId').val() + '&' +
    'name=' + encodeURIComponent($('#accountName').val()) + '&' + 
    'initialBalance=' + $('#initialBalance').val() + '&' +
    'accountType=' + $('#accountType').val() + '&' + 
    'notes=' + encodeURIComponent($('#notes').val()) + '&' + 
    'assetId=' + $('#assetId').val() + '&' +
    'liquid=' + liquid + '&' +  
    'active=' + active;
  jQuery.ajax({
    url: url,
    success: function(data) { 
      var results = jQuery.parseJSON(data);
      if (results[0] === "success") {
        $('editAccountForm').dialog('close');
        window.location.reload();
      }
      else { alert(data); }
    }
  });
}


function transactionSearch() {
  var search = $('#searchText').val();
  window.location = '/transaction/search?s='+search;
}