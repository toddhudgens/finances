var mode = '';
window.transactionId = 0;


$(document).ready(function() {

    $('body').on("tagSelected", function (event) {
	console.log(event);

	// check to see if the tag has already been selected
	var tagIds = $('#tagIds').val();
	if (tagIds.indexOf("|" + event.tagId + "|") !== -1) { return; }

	var addition = '<span id="tag' + event.tagId + '">';
	if ($('#tagsDiv').html() !== "") { addition += ', '; }
	addition += (event.tagName + ' <a class="removeTag" onclick="removeTag('+event.tagId+')">[-]</a></span>');
    
	$('#tagsLabel').show();
	$('#tagsDiv').append(addition);
	$('#tagIds').val(tagIds + "|" + event.tagId + "|");
	$('#tagSearch').val('');
    });


  $('#toAccount').change(function() {
    updateInterestVisibility();
  });
  
});


function updateInterestVisibility() {
  var aId = $('#toAccount').val();
  var aType = $('#transactionType').val();
  $('#interestRow').hide();
  if ((aType == "Transfer") && 
      ((window.accounts[aId].accountType == 4) ||
      (window.accounts[aId].accountType == 6))) {
    $('#interestRow').show();
  }
}



function roundNumber(num, dec) { return Math.round(num*Math.pow(10,dec))/Math.pow(10,dec); }


function currencyFormat(num) { return num.toFixed(2); }

function clearAndHideCategorySelects() {
  //clear out category selects and hide them all                                                                           
  for (var i = 1; i < 9; i++) {
    $('#categorySelect' + i).val(0);
    $('#categoryAmount' + i).val('');
    $('#categorySelectRow' + i).hide();
  }
}

function showNewTransactionForm() {
  window.transactionId = 0;
  mode = 'new';

  $('#transactionType').val("Withdrawal");
  updateTxType();

  $('#date').val($('#today').val());
  $('#transactionNumber').val($('#nextTransactionNumber').val());
  $('#payeeSearch').val('');
  $('#payeeId').val('');

  clearAndHideCategorySelects();

  // clear out the category search ahead and reset the values
  $('#categorySearch').val('');
  $('#categoryId').val('');
  $('#categoryRow').show();

  $('#total').val('');
  $('#tax').val('');
  $('#notes').val('');
  $('#tagSearch').val('');
  $('#tagsDiv').html('');
  $('#tagIds').val('');

  $('tr.plugin').remove();

  // trigger event for plugins to update view if needed  
  var event = jQuery.Event("addTransactionFormShown");
  $("body").trigger(event);

  $('#editTransactionForm').dialog({
    modal: true,
    title: "Add New Transaction",
    width:450
  }
  );
}


function getCategoryName(id) {
  var cats = window.categories;
  for (var j = 0; j < cats.length; j++) {    
    if (cats[j].id == id){ return cats[j].name; }
  }
}


function editT(id) {
  var data = window.tdata[id];
  
  $('select#transactionType').val(data.type);
  $('input#transactionNumber').val(data.num);
  $('input#date').val(data.date);
  $('#total').val(currencyFormat(Math.abs(data.total)));
  $('input#tax').val(currencyFormat(Math.abs(data.tax)));
  $('input#interest').val(currencyFormat(Math.abs(data.interest)));
  $('textarea#notes').html('').html(data.notes);

  updateTxType();
  if (data.type == "Transfer") {
    $('#fromAccount').val(data.fromAccount);
    $('#toAccount').val(data.toAccount);
  }
  else if ((data.type == "Stock Purchase") || (data.type == "Stock Sale")) {
    $('#ticker').val(data.ticker);
    $('#sharePrice').val(data.sharePrice);
    $('#shares').val(data.shares);
    $('#adminFees').val(data.txFees);
  }
  else {
    $('input#payeeSearch').val(data.pName);
    $('input#payeeId').val(data.p);
    $('tr#categoryRow').show();
   }

  $('tr.plugin').remove();

  updateInterestVisibility();
  clearAndHideCategorySelects();

  // this transaction only has one category, so show the category search
  if (data.type != "Transfer") {
    if (data.cat === null || data.cat.indexOf(',') == -1) {  
      $('select.categorySelect').val('');
      $('input#categorySearch').val(getCategoryName(data.cat));
      $('input#categoryId').val(data.cat);
      $('input.categoryAmount').val('');
    }
 
    // this transaction has multiple categories, show the list of selects
    else { 
      var categories = data.cat.split(',');
      var categoryAmounts = data.catAmt.split(',');
      $('#categoryRow').hide();
      for (var j = 0; j < categories.length; j++) { 
        populateCategorySelect(j+1);
        $('select#categorySelect'+(j+1)).val(categories[j]);
        $('input#categoryAmount'+(j+1)).val(Math.abs(categoryAmounts[j]));
        $('#categorySelectRow'+(j+1)).show();
        $('select#categorySelect'+(j+1)).attr('onchange', 'categorySelectUpdated(' + (j+1) + ')');
        if ((j+1) == categories.length) { $('#addNextCategory'+(j+1)).show(); }
      }
    }
  }

  $('#tagsDiv').html('');
  if (data.tagIds !== undefined) {
    var tagIds = data.tagIds.split("|");
    var tags = data.tagNames.split("|");
    var tagContent = ''; var tagIdStr = ''; var comma = ''; 
    for (var i = 0; i < tagIds.length; i++) { 
        if (tagIds[i] == "") { continue; }
        if (i != 0) { comma = ', '; }  
        tagContent += 
          '<span id="tag'+tagIds[i]+'">' + comma + tags[i] + 
            '&nbsp;<a class="removeTag" onclick="removeTag(' + tagIds[i] + ')">[-]</a>' + 
          '</span>';
        tagIdStr += '|' + tagIds[i] + '|';
    }
    $('#tagsDiv').html(tagContent);
    $('#tagIds').val(tagIdStr);
  }


  mode = 'edit';
    $('#editTransactionForm').dialog({
	modal: true,
	title: "Edit Transaction",
	width:450
    }
 );

  // trigger event for plugins to update view if needed                        
  var event = jQuery.Event("editTransactionFormShown");
  event.categoriesSelected = categoriesSelected();
  event.transactionId = id;
  window.transactionId = id;
  $("body").trigger(event);
}



function saveTransaction() {
  var txType = $('select#transactionType').val();

  if ((txType == "Transfer") && 
      ($('select#fromAccount').val() == $('select#toAccount').val())) { 
    alert("You have selected the same account in both 'From' and 'To'");
    return;
  }

  var accountId = $('#accountId').val();
  if (window.tdata[window.transactionId] !== undefined) {
    var data = window.tdata[window.transactionId];
    accountId = data.accountId;
  }

  window.ajaxURL = '/transaction/save?';

  window.ajaxParams = 
      'mode=' + mode + '&' +
      'accountId=' + accountId + '&' + 
      'fromAccount=' + $('select#fromAccount').val() + '&' + 
      'toAccount=' + $('select#toAccount').val() + '&' + 
      'transactionId=' + window.transactionId + '&' + 
      'transactionType='+ txType + '&' + 
      'payeeName=' + encodeURIComponent($('input#payeeSearch').val()) + '&' + 
      'payee=' + $('#payeeId').val() + '&' + 
      'date=' + $('input#date').val() + '&' + 
      'transactionNumber=' + $('input#transactionNumber').val()+'&'+
      'total=' + $('input#total').val() + '&' +
      'tax=' + $('input#tax').val() + '&' + 
      'interest=' + $('input#interest').val() + '&' + 
      'tagIds=' + $('#tagIds').val() + '&' + 
      'notes=' + encodeURIComponent($('textarea#notes').val());

  if ($('input#categorySearch').is(":visible")) { 
    window.ajaxParams += 
      '&categoryName='+encodeURIComponent($('input#categorySearch').val()) +
      '&category=' + $('input#categoryId').val();
  }
  else { 
    window.ajaxParams +=
      '&category1=' + $('select#categorySelect1').val() + '&categoryAmount1=' + $('input#categoryAmount1').val() + 
      '&category2=' + $('select#categorySelect2').val() + '&categoryAmount2=' + $('input#categoryAmount2').val() +
      '&category3=' + $('select#categorySelect3').val() + '&categoryAmount3=' + $('input#categoryAmount3').val() +
      '&category4=' + $('select#categorySelect4').val() + '&categoryAmount4=' + $('input#categoryAmount4').val() +
      '&category5=' + $('select#categorySelect5').val() + '&categoryAmount5=' + $('input#categoryAmount5').val() + 
      '&category6=' + $('select#categorySelect6').val() + '&categoryAmount6=' + $('input#categoryAmount6').val() +
      '&category7=' + $('select#categorySelect7').val() + '&categoryAmount7=' + $('input#categoryAmount7').val() +
      '&category8=' + $('select#categorySelect8').val() + '&categoryAmount8=' + $('input#categoryAmount8').val();

  }

  if ((txType == "Stock Purchase") || (txType == "Stock Sale")) {
    window.ajaxParams += 
     '&ticker=' + $('#ticker').val() + 
     '&shares=' + $('#shares').val() + 
     '&sharePrice=' + $('#sharePrice').val() +
     '&adminFees=' + $('#adminFees').val();
  }

  // trigger event for plugins to append additional GET vars if needed
  var event = jQuery.Event("saveTransaction");
  $("body").trigger(event);
  
  jQuery.ajax({
    type: 'POST',
    url: window.ajaxURL,
    data: window.ajaxParams,
    success: function(data) { 
      var response = jQuery.parseJSON(data);
      if (response.result === "success") { 
        $('#editTransactionForm').dialog('close');
        window.location.reload();
      }
      else { 
        alert(data);
      }
    }
  });
}


function delT(id) {
  if (confirm("Are you sure you want to delete this transaction?")) {
    var url = '/transaction/delete?transactionId='+id;
      jQuery.ajax({
        url: url,
	success: function(data) { 
          window.location.reload();
	}
      });
  }
}



function updateTxType() {
  var txType = $('select#transactionType').val();
  $('tr#categoryRow').hide();
  $('#taxRow').hide();
  $('tr#payeeRow').hide();
  $('tr#fromAccountRow').hide();
  $('tr#toAccountRow').hide();
  $('tr#taxRow').hide();
  $('tr#categoryRow').hide();
  $('tr#tickerRow').hide();
  $('tr#sharePriceRow').hide();
  $('tr#sharesPurchasedRow').hide();
  $('tr#feeRow').hide();
  $('tr#tagRow').hide();
  $('tr#tagArea').hide();

  if (txType == "Transfer") { 
    $('tr#fromAccountRow').show();
    $('#fromAccount').val($('#accountId').val());
    $('tr#toAccountRow').show();
    updateInterestVisibility();
  }
  else { 
    if ((txType == "Deposit") || (txType == "Withdrawal")) {
      $('tr#payeeRow').show();
      $('tr#taxRow').show();
      $('tr#categoryRow').show();
      $('tr#tagRow').show();
      $('tr#tagArea').show();
    }
    else if ((txType == "Stock Purchase") || (txType == "Stock Sale")) {
      $('tr#tickerRow').show();
      $('tr#sharePriceRow').show();
      $('tr#sharesPurchasedRow').show();
      $('tr#feeRow').show();
    }
  }
}


function updateDateRange() {
  var accountId = getUrlVars()['id'];
  var categoryId = getUrlVars()['categoryId'];
  var entityId = getUrlVars()['entityId'];
  var dateRange = $('#dateRange').val();
  var url = '/account/show-transactions?dateRange=' + dateRange;
  if (accountId != undefined) { url += "&id=" + accountId; }
  else if (categoryId != undefined) { url += "&categoryId=" + categoryId; }
  else if (entityId != undefined) { url += "&entityId=" + entityId; }
  window.location = url;
}



function switchToCategorySelect() {
  $('#categoryRow').hide();
  populateCategorySelect(1); 
  populateCategorySelect(2);
  $('#categorySelect1').val($('#categoryId').val());
  $('#categoryAmount1').val($('#total').val());
  $('#categorySelectRow1').show();
  $('#addNextCategory2').show();
  $('#categorySelectRow2').show();
}

function addNextCategorySelect(id) { 
  $('#addNextCategory'+id).fadeOut(500);
  populateCategorySelect(id+1);
  if (id < 8) { $('#addNextCategory'+(id+1)).show(); }
  $('#categorySelectRow'+(id+1)).show();
}


function populateCategorySelect(i) {
  var output = ['<option value="0"></option>'];
  var cats = window.categories;
  for (var j = 0; j < cats.length; j++) {
    var cat = cats[j];
    output.push('<option value="'+ cat.id +'">'+ cat.name +'</option>');
  }
  $('#categorySelect'+i).html(output.join(''));
}

function categoriesSelected() {

  if ($('input#categorySearch').is(":visible")) {
    return [$('input#categoryId').val()]
  }
  else {
    return [$('select#categorySelect1').val(), 
	    $('select#categorySelect2').val(),
	    $('select#categorySelect3').val(),
	    $('select#categorySelect4').val(),
	    $('select#categorySelect5').val(),
	    $('select#categorySelect6').val(),
	    $('select#categorySelect7').val(),
	    $('select#categorySelect8').val()];
    }

}

function categorySelectUpdated(id) {
    var event = jQuery.Event("categoryUpdated");    
    event.categoryId = $("#categorySelect"+id).val();
    $("body").trigger(event);
}


function addTag() { 
    jQuery.ajax({
	url: '/tag/add?name='+$('#tagSearch').val(),
	success: function(data) {
	    var results = jQuery.parseJSON(data);           
	    var tagId = results[0];
	    $('#tagId').val(tagId);
	    ddActionExecute('tag');
        }
    });    
}

function removeTag(id) {
    $('#tag'+id).remove();
    var tagIds = $('#tagIds').val();
    $('#tagIds').val(tagIds.replace('|' + id + '|', ''));
}
