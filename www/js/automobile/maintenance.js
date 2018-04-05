$(document).ready(function() {
  $("#carSelector").change(function() {
    if ($('#view').val() == "maintenance") {
      window.location = '/automobile/maintenance?id='+this.value;
    }
    else {
      window.location = '/automobile/log?id='+this.value;
    }
  });
});