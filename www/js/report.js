function updateDateRangeFrom() {
  var from = $('#from');
  var to = $('#to');
  to.datepicker('option', 'minDate', from.val());
  updateDateRange();
}

function updateDateRangeTo() {    
  updateDateRange();
}
