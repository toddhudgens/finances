function populateMaintenanceInfo(transactionId) {
    var url = "/automobile/maintenance-info?transactionId=" + transactionId;
    jQuery.ajax({
        url: url,
        success: function(raw) {
            if (raw !== "") {		
              var data = jQuery.parseJSON(raw);
              for (var i = 0; i < data.length; i++) {
		var mlog = data[i];
                $('#maintenanceVehicle'+(i+1)).val(mlog.assetId);
                $('#maintenanceMileage'+(i+1)).val(mlog.mileage);
                $('#maintenanceCost'+(i+1)).val(mlog.amount);
                $('#maintenanceNotes'+(i+1)).val(mlog.notes);
                if (i < (data.length -1)) {
		  addMaintenanceLogEntry();
		}
	      }
           }
        }
    });
}

$(document).ready(function() { 

  $('body').on("categoryUpdated", function (event) { 
      console.log(event);
      updateVehicleMaintenanceFormVisibility();
      updateGasMileageFormVisibility();
      updateInsuranceFormVisibility();
  });


  $('body').on('addTransactionFormShown', function(event) {
    /*
    $('#vehicle').val('');
    $('#mileage').val('');
    $('#maintenanceNotes').val('');
    $('tr.vehicles').hide();

    $('#gasMileageVehicle').val('');
    $('#pricePerGallon').val('');
    $('#gallonsPumped').val('');
    $('#gasMileageOdometer').val('');
    */
    $('tr.gasMileage').hide();
  });


  $('body').on("editTransactionFormShown", function (event) { 
    $('tr#maintainenanceLogHeader').hide();

    var categoriesSelected = event.categoriesSelected;
    var transactionId = event.transactionId;

    resetMaintenanceLog();
    resetInsuranceLog();
    resetAutoTaxLog();

    for (var i = 0; i < categoriesSelected.length; i++) {
      if (categoriesSelected[i] == 11) { 
        $('tr#maintainenanceLogHeader').show(); 
        addMaintenanceLogEntry();
	populateMaintenanceInfo(transactionId);
      }
      else if (categoriesSelected[i] == 37) {
          $('tr#autoInsuranceLogHeader').show();
          addAutoInsuranceLogEntry();
          populateAutoInsuranceInfo(transactionId);
      }
      else if (categoriesSelected[i] == 99) {
         $('tr#autoTaxLogHeader').show();
         addAutoTaxLogEntry();
         populateAutoTaxInfo(transactionId);
      }
      else { continue; }
    }

      $('tr.gasMileage').hide();
      var categoriesSelected = event.categoriesSelected;
      var transactionId = event.transactionId;

      for (var i = 0; i < categoriesSelected.length; i++) {
	  
          if (categoriesSelected[i] == 8) {
	      
              $('tr.gasMileage').show();
              populateGasMileageInfo(transactionId);
          }
          else {
	       continue; }
     }
  });


  $('body').on('saveTransaction', function(event) { 

    var gmRowCount = $('#gasMileageRowCount').val();
    window.ajaxParams += '&gasMileageRowCount='+gmRowCount;
    for (var i = 1; i <= gmRowCount; i++) {	
      window.ajaxParams += '&gasMileageVehicle'+i+'=' + $('#gasMileageVehicle'+i).val() +
                        '&pricePerGallon'+i+'=' + $('#pricePerGallon'+i).val() +
                        '&gallonsPumped'+i+'=' + $('#gallonsPumped'+i).val() +
                        '&gasMileageOdometer'+i+'=' + $('#gasMileageOdometer'+i).val();
    }

    var rowCount = $('#maintenanceRowCount').val();
    window.ajaxParams += '&maintenanceRowCount='+rowCount;
    for (var i = 1; i <= rowCount; i++) {
      window.ajaxParams += '&maintenanceVehicle'+i+'='+$('#maintenanceVehicle'+i).val();
      window.ajaxParams += '&maintenanceMileage'+i+'='+$('#maintenanceMileage'+i).val();
      window.ajaxParams += '&maintenanceCost'+i+'='+$('#maintenanceCost'+i).val();
      window.ajaxParams += '&maintenanceNotes'+i+'='+$('#maintenanceNotes'+i).val();
    }

    var atRowCount = $('#autoTaxRowCount').val();
    window.ajaxParams += '&autoTaxRowCount='+atRowCount;
    for (var i = 1; i <= atRowCount; i++) {	
      window.ajaxParams += '&autoTaxVehicle'+i+'='+$('#autoTaxVehicle'+i).val();
      window.ajaxParams += '&autoTaxCost'+i+'='+$('#autoTaxCost'+i).val();
    }

    var aiRowCount = $('#autoInsuranceRowCount').val();
    window.ajaxParams += '&autoInsuranceRowCount='+aiRowCount;
    for (var i = 1; i <= aiRowCount; i++) {
      window.ajaxParams += '&autoInsuranceVehicle'+i+'='+$('#autoInsuranceVehicle'+i).val();
      window.ajaxParams += '&autoInsuranceCost'+i+'='+$('#autoInsuranceCost'+i).val();
    }
  });

});


function resetMaintenanceLog() {
  $('.autoMaintenance').hide();
  var rowCount = $('#autoMaintenanceRowCount').val();
  for (var i = 1; i <= rowCount; i++) { 
    $('#autoMaintenanceVehicle'+i).val('');
    $('#autoMaintenanceCost'+i).val('');
  }
  $('#autoMaintenanceRowCount').val(0);
}

function resetInsuranceLog() {
  $('.autoInsurance').hide();
  var rowCount = $('#autoInsuranceRowCount').val();
  for (var i = 1; i <= rowCount; i++) {
    $('#autoInsuranceVehicle'+i).val('');
    $('#autoInsuranceCost'+i).val('');
  }
  $('#autoInsuranceRowCount').val(0);
}

function resetAutoTaxLog() {
    
}


function updateVehicleMaintenanceFormVisibility() {
    if ($('#categoryRow:hidden').length == 0) { 
      if ($('#categoryId').val() == 11) { addMaintenanceLogEntry(); }
      else { $('tr.maintenance').hide(); }
    }
    else {
        var found = 0;
	for (var i = 1; i < 9; i++) {
          if ($('#categorySelect'+i).val() == 11) { found = 1; }
        }
        if (found) { addMaintenanceLogEntry(); }
        else { $('tr.maintenance').hide(); }
    }
}


function populateGasMileageInfo(transactionId) {
    var url = "/automobile/gas-mileage-info?transactionId=" + transactionId;
    jQuery.ajax({
        url: url,
        success: function(raw) {
            var data = jQuery.parseJSON(raw);
            for (var i = 0; i < data.length; i++) {		 
              console.log(data);
              var log = data[i];
              console.log(log);
              addGasMileageLogEntry();
              $('#gasMileageVehicle'+(i+1)).val(log['assetId']);
    	      $('#gallonsPumped'+(i+1)).val(log['gasPumped']);
 	      $('#pricePerGallon'+(i+1)).val(log['gasPrice']);
              $('#gasMileageOdometer'+(i+1)).val(log['mileage']);
              //if (i < (data.length -1)) { addGasMileageLogEntry(); }
            }
        }
    });
}


function populateAutoTaxInfo(transactionInfo) {
    var url = "/automobile/tax-info?transactionId=" + transactionId;
    jQuery.ajax({
        url: url,
        success: function(raw) {
            if (raw !== "") {
              var data = jQuery.parseJSON(raw);
              for (var i = 0; i < data.length; i++) {
                var tlog = data[i];
                $('#autoTaxVehicle'+(i+1)).val(tlog.assetId);
                $('#autoTaxCost'+(i+1)).val(tlog.amount);
                if (i < (data.length -1)) {
                  addAutoTaxLogEntry();
                }
              }
           }
        }
    });
}


function populateAutoInsuranceInfo(transactionInfo) {
    var url = "/automobile/insurance-info?transactionId=" + transactionId;
    jQuery.ajax({
        url: url,
        success: function(raw) {
            if (raw !== "") {
              var data = jQuery.parseJSON(raw);
              for (var i = 0; i < data.length; i++) {
                var log = data[i];
                $('#autoInsuranceVehicle'+(i+1)).val(log.assetId);
                $('#autoInsuranceCost'+(i+1)).val(log.amount);
                if (i < (data.length -1)) {
                  addAutoInsuranceLogEntry();
                }
              }
           }
        }
    });
}



function updateGasMileageFormVisibility() {
    if ($('#categoryRow:hidden').length == 0) {
      if ($('#categoryId').val() == 8) { 
        $('tr.gasMileage').show(); 
      }
      else { $('tr.gasMileage').hide(); }
    }
    else {	
        var found = 0;
        for (var i = 1; i < 9; i++) {
          if ($('#categorySelect'+i).val() == 8) { found = 1; }
        }
        if (found) { $('tr.gasMileage').show(); }
        else { $('tr.gasMileage').hide(); }
    }
}


function updateInsuranceFormVisibility() {
  if ($('#categoryRow:hidden').length == 0) {
    if ($('#categoryId').val() == 37) {
      $('tr#autoInsuranceLogHeader').show(); 
    }
    else {
      $('tr#autoInsuranceLogHeader').hide(); 
    }
  }
  else {
    var found = 0;
    for (var i = 1; i < 9; i++) {
      if ($('#categorySelect'+i).val() == 37) { found = 1; }
    }
    if (found) {
      $('tr#autoInsuranceLogHeader').show(); 
    }
    else {
      $('tr#autoInsuranceLogHeader').hide(); 
    }
  }
}


function addMaintenanceLogEntry() {
  var i = $('#maintenanceRowCount').val();
  i++;

  var html = '<tr class="plugin maintenance mlVehicle"><td class="label">Vehicle</td><td>';
  html += '<select id="maintenanceVehicle'+i+'"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  // build select
  html += '</select></td></tr>';
  html += '<tr class="plugin maintenance"><td class="label">Mileage:</td>';
  html += '<td><input type="text" id="maintenanceMileage'+i+'" class="mileage" value=""></td></tr>'; 
  html += '<tr class="plugin maintenance"><td class="label">Cost:</td>';
  html += '<td><input type="text" id="maintenanceCost'+i+'" class="maintenanceCost" value=""></td></tr>';
  html += '<tr class="plugin maintenance mlNotes"><td class="label">Maintenance<br>Notes:</td>';
  html += '<td><textarea id="maintenanceNotes'+i+'" class="maintenanceNotes"></textarea>';
  html += '</td></tr>';
  $(html).insertAfter($('#maintainenanceLogHeader'));
  $('#maintenanceRowCount').val(i);
}

function addGasMileageLogEntry() {
  var i = $('#gasMileageRowCount').val();
  i++;

  var html = '<tr class="plugin gasMileage"><td class="label">Vehicle</td><td>';
  html += '<select id="gasMileageVehicle'+i+'"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  html += '</select></td></tr>';

  html += '<tr class="plugin gasMileage"><td class="label">Price / Gal:</td>';
  html += '<td><input type="text" id="pricePerGallon'+i+'" class="mileage" value=""></td></tr>';

  html += '<tr class="plugin gasMileage"><td class="label"># of Gallons:</td>';
  html += '<td><input type="text" id="gallonsPumped'+i+'" class="mileage" value=""></td></tr>';

  html += '<tr class="plugin gasMileage"><td class="label">Odometer:</td>';
  html += '<td><input type="text" id="gasMileageOdometer'+i+'" class="mileage">';

  html += '</td></tr>';

  html += '<tr class="spacer gasMileage"><td colspan="2"><br></td></tr>';

  $(html).insertAfter($('#gasMileageHeader'));
  $('#gasMileageRowCount').val(i);
}

function addAutoTaxLogEntry() {
  var i = $('#autoTaxRowCount').val();
  i++;
  var html = '<tr class="plugin autoTax atVehicle"><td class="label">Vehicle</td><td>';
  html += '<select id="autoTaxVehicle'+i+'" class="autoTaxVehicle"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  html += '</select></td></tr>';
  html += '<tr class="plugin autoTax atCost"><td class="label">Cost:</td>';
  html += '<td><input type="text" id="autoTaxCost'+i+'" class="autoTaxCost" value=""></td></tr>';
  $(html).insertAfter($('#autoTaxLogHeader'));
  $('#autoTaxRowCount').val(i);
}

function addAutoInsuranceLogEntry() {
  var i = $('#autoInsuranceRowCount').val();
  i++;
  var html = '<tr class="plugin autoInsurance aiVehicle"><td class="label">Vehicle</td><td>';
  html += '<select id="autoInsuranceVehicle'+i+'"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  html += '</select></td></tr>';
  html += '<tr class="plugin autoInsurance"><td class="label">Cost:</td>';
  html += '<td><input type="text" id="autoInsuranceCost'+i+'" class="autoInsuranceCost" value=""></td></tr>';
  $(html).insertAfter($('#autoInsuranceLogHeader'));
  $('#autoInsuranceRowCount').val(i);
}


function updateMaintenanceNotes() {
    var url = '/automobile/update-maintenance-notes';
    var params = 'notes='+encodeURIComponent($('#maintenanceNotes').val());
    params += '&id='+$('#assetId').val();
    $.ajax({
      type: "POST",
      url: url,
      data: params, 
      success: function(data) {
      }
    });
}