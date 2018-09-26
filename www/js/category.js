function showCategoryActions(id) {
 $('#edit'+id).show();
 $('#delete'+id).show();
}

function hideCategoryActions(id) {
 $('#edit'+id).hide();
 $('#delete'+id).hide();
}

function showNewCategoryForm() {    
  $('#name').val('');
  $('#categoryId').val('');
  $('#addCategoryBtn').val("Add");

  $('#editCategoryForm').dialog({
    modal: true,
    title: "Add Category",
    width: 450
  });

  var event = jQuery.Event("addCategoryFormShown");
  $("body").trigger(event);
}

function showCategoryEditForm(id) {
  $('#addCategoryBtn').val("Edit");
  $('#categoryId').val(id);
  $('#name').val($('#categoryName'+id).html());

  $('#editCategoryForm').dialog({
    modal: true,
    title: "Edit Category",
    width: 450
  });
  var event = jQuery.Event("editCategoryFormShown");
  $("body").trigger(event);
}

function saveCategory() {
  var categoryId = $('#categoryId').val();
  var name = $('#name').val();
  var url = '/category/save?name='+encodeURI(name)+'&id='+encodeURI(categoryId);
  jQuery.ajax({
    url: url,
    success: function(data) {
      console.log(data);
      var results = jQuery.parseJSON(data);
      if (results[0] === "success") {
        $('editCategoryForm').dialog('close');
        window.location.reload();
      }
      else { alert(data); }
    }
  });
}

function deleteCategory(id) {
  var name = $('#categoryName'+id).html();

  if (confirm("Are you sure you want to delete " + name + "?")) {
    var url = '/category/delete?id='+id;
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