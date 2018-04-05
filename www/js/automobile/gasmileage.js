$(document).ready(function() {
  $("#carSelector").change(function() {
    window.location = '/automobile/gas-mileage?id='+this.value;
  });
});