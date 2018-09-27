function showTagActions(id) {
 $('#edit'+id).show();
 $('#delete'+id).show();
}

function hideTagActions(id) {
 $('#edit'+id).hide();
 $('#delete'+id).hide();
}

function showNewTagForm() {    
  $('#name').val('');
  $('#tagId').val('');
  $('#addTagBtn').val("Add");

  $('#editTagForm').dialog({
    modal: true,
    title: "Add Tag",
    width: 450
  });

  var event = jQuery.Event("addTagFormShown");
  $("body").trigger(event);
}

function showTagEditForm(id) {
  $('#addTagBtn').val("Edit");
  $('#tagId').val(id);
  $('#name').val($('#tagName'+id).html());

  $('#editTagForm').dialog({
    modal: true,
    title: "Edit Tag",
    width: 450
  });
  var event = jQuery.Event("editTagFormShown");
  $("body").trigger(event);
}

function saveTag() {
  var tagId = $('#tagId').val();
  var name = $('#name').val();
  var url = '/tag/save?name='+encodeURI(name)+'&id='+encodeURI(tagId);
  jQuery.ajax({
    url: url,
    success: function(data) {
      console.log(data);
      var results = jQuery.parseJSON(data);
      if (results[0] === "success") {
        $('editTagForm').dialog('close');
        window.location.reload();
      }
      else { alert(data); }
    }
  });
}

function deleteTag(id) {
  var name = $('#tagName'+id).html();
  var msg = "Are you sure you want to delete " + name + "?\n\n" + 
            "Transactions with this tag will be untagged.";
  if (confirm(msg)) {
    var url = '/tag/delete?id='+id;
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