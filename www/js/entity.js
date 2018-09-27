function showEntityActions(id) {
 $('#edit'+id).show();
 $('#delete'+id).show();
}

function hideEntityActions(id) {
 $('#edit'+id).hide();
 $('#delete'+id).hide();
}

function showNewEntityForm() {    
  $('#name').val('');
  $('#entityId').val('');
  $('#addEntityBtn').val("Add");

  $('#editEntityForm').dialog({
    modal: true,
    title: "Add Entity",
    width: 450
  });

  var event = jQuery.Event("addEntityFormShown");
  $("body").trigger(event);
}

function showEntityEditForm(id) {
  $('#addEntityBtn').val("Edit");
  $('#entityId').val(id);
  $('#name').val($('#entityName'+id).html());

  $('#editEntityForm').dialog({
    modal: true,
    title: "Edit Entity",
    width: 450
  });
  var event = jQuery.Event("editEntityFormShown");
  $("body").trigger(event);
}

function saveEntity() {
  var entityId = $('#entityId').val();
  var name = $('#name').val();
  var url = '/entity/save?name='+encodeURI(name)+'&id='+encodeURI(entityId);
  jQuery.ajax({
    url: url,
    success: function(data) {
      console.log(data);
      var results = jQuery.parseJSON(data);
      if (results[0] === "success") {
        $('editEntityForm').dialog('close');
        window.location.reload();
      }
      else { alert(data); }
    }
  });
}

function deleteEntity(id) {
  var name = $('#entityName'+id).html();

  if (confirm("Are you sure you want to delete " + name + "?")) {
    var url = '/entity/delete?id='+id;
    jQuery.ajax({
      url: url,
      success: function(data) {
        var results = jQuery.parseJSON(data);
        if (results[0] === "success") { window.location.reload(); }
        else { alert(data); }
      }    
    });
  }
}