function updateDateRangeFrom() {
  var from = $('#from');
  var to = $('#to');
  to.datepicker('option', 'minDate', from.val());
  updateDateRange();
}

function updateDateRangeTo() {    
  updateDateRange();
}


function pad(n, width, z) {   
  z = z || '0';
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}