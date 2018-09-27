$(document).ready(function() { 

  $('body').on('addTransactionFormShown', function(event) {
    resetAll();
  });


  $('body').on("editTransactionFormShown", function (event) { 
    resetAll();

    categoriesById = getCategoriesById();
    var cats = [];
    for (var i = 0; i < event.categoriesSelected.length; i++) {
	cats.push(categoriesById[event.categoriesSelected[i]]);
    }
    updateVehicleMaintenanceFormVisibility(cats);
    updateGasMileageFormVisibility(cats);
    updateInsuranceFormVisibility(cats);
    updateVehicleTaxFormVisibility(cats);


    var txId = event.transactionId;
    for (var i = 0; i < cats.length; i++) {
      if (cats[i] == "Auto Maintenance / Repair") { populateMaintenanceInfo(txId); }
      else if (cats[i] == "Auto Insurance") { populateAutoInsuranceInfo(txId); }
      else if (cats[i] == "Auto Registration") { populateAutoTaxInfo(txId); }
      else if (cats[i] == "Gasoline") { populateGasMileageInfo(txId); }
      else { continue; }
    }
  });


  $('body').on("categoryUpdated", function (event) {
    categoriesById = getCategoriesById();
    var cats = [];
    if ($('#categoryRow:hidden').length == 0) {
      cats.push(categoriesById[$('#categoryId').val()]);
    }
    else {
      for (var i = 1; i < 9; i++) {
        if ($('#categorySelect'+i).val() > 0) {
          cats.push(categoriesById[$('#categorySelect'+i).val()])
        }
      }
    }
    updateVehicleMaintenanceFormVisibility(cats);
    updateGasMileageFormVisibility(cats);
    updateInsuranceFormVisibility(cats);
    updateVehicleTaxFormVisibility(cats);
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



function getCategoriesById() {
  var categoriesById = {};
  if (window.categories !== undefined) {
    for (var i = 0; i < window.categories.length; i++) {	
      var cat = window.categories[i];
      categoriesById[cat.id] = cat.name;
    }
  }
  return categoriesById;
}



function resetAll() {
  $('.maintenanceInput').remove();
  $('.maintenance').hide();
  $('#maintenanceRowCount').val(0);
  $('.autoInsuranceInput').remove();
  $('.autoInsurance').hide();
  $('#autoInsuranceRowCount').val(0);
  $('.autoTaxInput').remove();
  $('.autoTax').hide();
  $('#autoTaxRowCount').val(0);
  $('.gasMileageInput').remove();
  $('.gasMileage').hide();
  $('#gasMileageRowCount').val(0);
}



function populateGasMileageInfo(transactionId) {
    var url = "/automobile/gas-mileage-info?transactionId=" + transactionId;
    jQuery.ajax({
        url: url,
        success: function(raw) {
            var data = jQuery.parseJSON(raw);
            for (var i = 0; i < data.length; i++) {
              var log = data[i];
              addGasMileageLogEntry();
              $('#gasMileageVehicle'+(i+1)).val(log['assetId']);
    	      $('#gallonsPumped'+(i+1)).val(log['gasPumped']);
 	      $('#pricePerGallon'+(i+1)).val(log['gasPrice']);
              $('#gasMileageOdometer'+(i+1)).val(log['mileage']);
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
                addAutoTaxLogEntry();
                $('#autoTaxVehicle'+(i+1)).val(tlog.assetId);
                $('#autoTaxCost'+(i+1)).val(tlog.amount);
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
                addAutoInsuranceLogEntry();
                $('#autoInsuranceVehicle'+(i+1)).val(log.assetId);
                $('#autoInsuranceCost'+(i+1)).val(log.amount);
              }
           }
        }
    });
}


function populateMaintenanceInfo(transactionId) {   
  var url = "/automobile/maintenance-info?transactionId=" + transactionId;
  jQuery.ajax({
    url: url,
    success: function(raw) {
      if (raw !== "") {
        var data = jQuery.parseJSON(raw);
        for (var i = 0; i < data.length; i++) {
          var mlog = data[i];
	  addMaintenanceLogEntry();
          $('#maintenanceVehicle'+(i+1)).val(mlog.assetId);
	  $('#maintenanceMileage'+(i+1)).val(mlog.mileage);
          $('#maintenanceCost'+(i+1)).val(mlog.amount);
          $('#maintenanceNotes'+(i+1)).val(mlog.notes);
        }
      }
    }
  });
}


function updateVehicleMaintenanceFormVisibility(cats) {
  if ($.inArray("Auto Maintenance / Repair", cats) != -1) { $('#maintainenanceLogHeader').show(); }
  else { $('tr.maintenance').hide(); }
}


function updateGasMileageFormVisibility(cats) {
  if (($.inArray("Gasoline", cats) != -1) || 
      ($.inArray("Diesel", cats) != -1)) { 
    $('#gasMileageHeader').show();
  }
  else { $('tr.gasMileage').hide(); }
}


function updateInsuranceFormVisibility(cats) {
  if ($.inArray("Auto Insurance", cats) != -1) { $('#autoInsuranceLogHeader').show(); }
  else { $('tr.insurance').hide(); }
}

function updateVehicleTaxFormVisibility(cats) {
  if ($.inArray("Auto Registration", cats) != -1) { $('#autoTaxLogHeader').show(); }
  else { $('tr.vehicleTax').hide(); }
}


function addMaintenanceLogEntry() {
  var i = $('#maintenanceRowCount').val();
  i++;

  var html = '<tr class="maintenance maintenanceInput mlVehicle"><td class="label">Vehicle</td><td>';
  html += '<select id="maintenanceVehicle'+i+'"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  // build select
  html += '</select></td></tr>';
  html += '<tr class="maintenance maintenanceInput"><td class="label">Mileage:</td>';
  html += '<td><input type="text" id="maintenanceMileage'+i+'" class="mileage" value=""></td></tr>'; 
  html += '<tr class="maintenance maintenanceInput"><td class="label">Cost:</td>';
  html += '<td><input type="text" id="maintenanceCost'+i+'" class="maintenanceCost" value=""></td></tr>';
  html += '<tr class="maintenance mlNotes maintenanceInput">';
  html += '<td class="label">Maintenance<br>Notes:</td>';
  html += '<td><textarea id="maintenanceNotes'+i+'" class="maintenanceNotes"></textarea>';
  html += '</td></tr>';
  $(html).insertAfter($('#maintainenanceLogHeader'));
  $('#maintenanceRowCount').val(i);
}


function addGasMileageLogEntry() {
  var i = $('#gasMileageRowCount').val();
  i++;

  var html = '<tr class="gasMileage gasMileageInput"><td class="label">Vehicle</td><td>';
  html += '<select id="gasMileageVehicle'+i+'"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  html += '</select></td></tr>';
  html += '<tr class="gasMileage gasMileageInput"><td class="label">Price / Gal:</td>';
  html += '<td><input type="text" id="pricePerGallon'+i+'" class="mileage" value=""></td></tr>';
  html += '<tr class="gasMileage gasMileageInput"><td class="label"># of Gallons:</td>';
  html += '<td><input type="text" id="gallonsPumped'+i+'" class="mileage" value=""></td></tr>';
  html += '<tr class="gasMileage gasMileageInput"><td class="label">Odometer:</td>';
  html += '<td><input type="text" id="gasMileageOdometer'+i+'" class="mileage">';
  html += '</td></tr>';
  html += '<tr class="spacer gasMileage gasMileageInput"><td colspan="2"><br></td></tr>';

  $(html).insertAfter($('#gasMileageHeader'));
  $('#gasMileageRowCount').val(i);
}


function addAutoTaxLogEntry() {
  var i = $('#autoTaxRowCount').val();
  i++;
  var html = '<tr class="autoTax autoTaxInput"><td class="label">Vehicle</td><td>';
  html += '<select id="autoTaxVehicle'+i+'" class="autoTaxVehicle"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  html += '</select></td></tr>';
  html += '<tr class="autoTax autoTaxInput"><td class="label">Cost:</td>';
  html += '<td><input type="text" id="autoTaxCost'+i+'" class="autoTaxCost" value=""></td></tr>';
  $(html).insertAfter($('#autoTaxLogHeader'));
  $('#autoTaxRowCount').val(i);
}


function addAutoInsuranceLogEntry() {
  var i = $('#autoInsuranceRowCount').val();
  i++;
  var html = '<tr class="autoInsurance autoInsuranceInput">';
  html += '<td class="label">Vehicle</td><td>';
  html += '<select id="autoInsuranceVehicle'+i+'"><option value=""></option>';
  var autos = jQuery.parseJSON(window.automobiles);
  for (var j = 0; j < autos.length; j++) {
    var car = autos[j];
    html += '<option value="'+car.id+'">'+car.name+'</option>';
  }
  html += '</select></td></tr>';
  html += '<tr class="autoInsurance autoInsuranceInput"><td class="label">Cost:</td>';
  html += '<td><input type="text" id="autoInsuranceCost'+i+'" class="autoInsuranceCost" value="">';
  html += '</td></tr>';

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