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
    console.log(event);
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
    console.log(url+params);
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

  $('#addBudgetItemForm').dialog({ 
   title: 'Add Budget Item',
   height:300,
   width:400
  });
}

function editBudgetItem(id) {
 var item = window.items[id];
  console.log(item);

 $('#budgetItemId').val(id)
 $('#label').val(item.name);
 $('#budgetItemType').val(item.type);
 $('#amount').val(item.amount);
 $('#lookBehind').val(item.variableAmountLookBehind);
 $('#categoryId').val(item.variableAmountCategoryId);
 $('#categorySearch').val(item.categoryName);

 if (item.variableAmount) {
  $('#autoCalculate').prop('checked', true);
  $('#amount').prop("disabled", true);
  $('#categoryRow').show();
  $('#lookBehindRow').show();
 }
 else {
   $('#autoCalculate').prop('checked', false);
   $('#amount').prop("disabled", false);
 }

 $('#addBudgetItemBtn').val('Edit Item');

 $('#addBudgetItemForm').dialog({
   title: 'Edit Budget Item',
   height:300,
   width:400
 });
}
