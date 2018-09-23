$(document).ready(function() {
  $('#autoCalculate').click(function(event){

    if (event.target.checked) {
      $('#amountRow').hide();
      $('#categoryRow').show();
      $('#lookBehindRow').show();
    }
    else {
      $('#categoryRow').hide();
      $('#lookBehindRow').hide();
      $('#amountRow').show();
    }
  }); 

  $('#addBudgetItemBtn').click(function(event) {
    var budgetItemId = $('#budgetItemId').val();
    if (budgetItemId === undefined) { budgetItemId = ''; }
    var budgetItemType = $('#budgetItemType').val();
    var label = $('#label').val();
    var amount = $('#amount').val();
    var categoryId = $('#categoryId').val();
    var lookBehind = $('#lookBehind').val();
    var autoCalculate = 0; 
    if ($('#autoCalculate')[0].checked) { autoCalculate = 1; }

    var url = '/monthly-budget/save-item?';
    var params =
     'itemId=' + budgetItemId + '&' +
     'type=' + budgetItemType + '&' +
     'label=' + encodeURIComponent(label) + '&' +
     'amount=' + amount + '&' +
     'categoryId=' + categoryId + '&' +
     'autoCalculate='+ autoCalculate + '&' +
     'lookBehind='+lookBehind;
    jQuery.ajax({
      url: url + params,
      success: function(data) {
        var results = jQuery.parseJSON(data);
        if (results[0] === "success") {
          $('#addBudgetItemForm').dialog('close');
          window.location.reload();
        }
        else { alert(data); }
      }
    });
  });


  $('#deleteBudgetItemBtn').click(function(event) {
    $('#deleteConfirm').show();
  });

});

function showNewBudgetItem() {
  $('#budgetItemId').val('')
  $('#label').val('');
  $('#budgetItemType').val('');
  $('#amount').val('');
  $('#autoCalculate').prop('checked', false);
  $('#categorySearch').val('');
  $('#categoryId').val('');
  $('#categoryRow').hide();
  $('#lookBehindRow').hide();
  $('#amountRow').show();
  $('#addBudgetItemBtn').val('Add Item');
  $('#deleteBudgetItemBtn').hide();

  $('#addBudgetItemForm').dialog({ 
   title: 'Add Budget Item',
   height:350,
   width:400
  });
}

function editBudgetItem(id) {
 var item = window.items[id];
 $('#budgetItemId').val(id)
 $('#label').val(item.name);
 $('#budgetItemType').val(item.type);
 $('#amount').val(item.amount);
 $('#lookBehind').val(item.variableAmountLookBehind);
 $('#categoryId').val(item.variableAmountCategoryId);
 $('#categorySearch').val(item.categoryName);
 $('#deleteBudgetItemBtn').show();

 if (item.variableAmount == "1") {
  $('#autoCalculate').prop('checked', true);
  $('#amountRow').hide();
  $('#categoryRow').show();
  $('#lookBehindRow').show();
 }
 else {
   $('#autoCalculate').prop('checked', false);
   $('#amountRow').show();
   $('#categoryRow').hide();
   $('#lookBehindRow').hide();
 }

 $('#addBudgetItemBtn').val('Edit Item');

 $('#addBudgetItemForm').dialog({
   title: 'Edit Budget Item',
   height:350,
   width:400
 });
}


function deleteBudgetItemConfirmed() {
  var itemId = $('#budgetItemId').val();
  var url = '/monthly-budget/delete-item?id=' + itemId;
  window.location = url;  
}


function deleteBudgetItemCancelled() {
  $('#deleteConfirm').hide();
}