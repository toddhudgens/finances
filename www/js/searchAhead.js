var ddMatchId = -1;
var ddMatchStr;
var ddResults;
var ddSelectedId;
var ddSelectedIndex = -1;
var ddMinIndex = 0;
var ddMaxIndex = -1;
var ddSearching = -1;
var lastSearch = '';

function addslashes (str) {
  str = (str + '').replace(/[\\"']/g, '\\$&');
  str = str.replace(/\u0000/g, '\\0');
  str = str.replace('&amp;', '&');
  return str;
}


function ddUpArrow(name) {
  var options = document.getElementById(name+"DD").getElementsByTagName("div");
  if (ddSelectedIndex > ddMinIndex){
    options[ddSelectedIndex].className = "ddOption";
    ddSelectedIndex--;
    ddMatchId = options[ddSelectedIndex].id;
    ddMatchStr = options[ddSelectedIndex].innerHTML;
    if (ddSelectedIndex >= ddMinIndex) {
      options[ddSelectedIndex].className = "ddOptionHover";
    }
  }
  else if (ddSelectedIndex === 0) {
	options[ddSelectedIndex].className = "ddOption";
	ddSelectedIndex = -1;
  }
}


function ddDownArrow(name) {
  var options = document.getElementById(name+"DD").getElementsByTagName("div");
  if (ddSelectedIndex < ddMaxIndex){
    if (ddSelectedIndex === -1) { ddSelectedIndex = 0; }
    else {
      options[ddSelectedIndex].className = "ddOption";
      ddSelectedIndex++;
    }
    ddMatchId = options[ddSelectedIndex].id;
    ddMatchStr = options[ddSelectedIndex].innerHTML;
  
    if (ddSelectedIndex <= ddMaxIndex) {
      options[ddSelectedIndex].className = "ddOptionHover";
    }
  }
}



function ddKeypressHandler(e, input) {
  //console.log("event = " + e);

  var name = input.id.substr(0, (input.id.length-6));
  e = e || window.event;

  //console.log("ddKeypressHandler(e, input), "+
  //            "keycode = "+e.keyCode+
  //            ", ddMatchId="+ddMatchId); 
  if (e.keyCode === 13) { // enter key
    //console.log("Enter key was pressed. ddMatchId="+ddMatchId+
    //              ", input.value= "+input.value);
    if (ddMatchId !== -1) {
      var ref = document.getElementById(ddMatchId);
      if (ref) { 
	  ddSelect(ref); 
      }
      else { 
        ddSelectNoMatch(name); 
      }
    }
    else if ((ddMatchId === -1) && (input.value !== "")) { 
      //console.log("Calling ddSelectNoMatch("+name+")");
      ddSelectNoMatch(name);
    }
    return false;
  }
  else if (e.keyCode === 27) {  // escape key
    ddHide(name); 
  }
}


function ddReset(name) {
  //console.log("ddReset("+name+")");
  ddMatchId = -1; 
  ddMatchStr = "";
  document.getElementById(name+"Search").value = "";
  var dropdown = document.getElementById(name+"DD");
  dropdown.style.display = "none";
  dropdown.innerHTML = "";
}



function ddBlur(event, input) { 
  var name = input.id.substr(0, (input.id.length-6));
  if (!ddSearching) { 
    setTimeout("ddHide('"+name+"')",250); 
  } 
}


function ddHide(name) { 
  $('#'+name+'DD').hide(); 
  ddMatchId = -1;
  ddMatchStr = '';
}


function ddCheck(name, oldValue) {
  var currentValue = $("#"+name+"Search").val();

  // have they stopped typing long enough for us to register the same value?
  if (oldValue === currentValue) { 

      // the check against lastSearch ensures that we don't search the same
      // value twice
      if (currentValue !== lastSearch) { 
	  lastSearch = currentValue;
	  ddSearch(name);   
      }
  }
}



function ddOver(ref) {
  var name = getNameFromId(ref.id);
  var options = document.getElementById(name+"DD").getElementsByTagName("div");
  for (var i = 0; i < options.length; i++) {
    options[i].className = "ddOption";
    if (options[i].id === ref.id) { ddSelectedIndex = i; }
  }
  ref.className = "ddOptionHover";
}



function ddQueue(e, input) {
  e = e || window.event;
  var name = input.id.substr(0, (input.id.length-6));
  if (e.keyCode===40){ddDownArrow(name);}
  else if(e.keyCode===38){ddUpArrow(name);}

  if ((e.keyCode !== 9)  && // tab
      (e.keyCode !== 13) && // enter
      (e.keyCode !== 16) && // shift
      (e.keyCode !== 17) && // ctrl
      (e.keyCode !== 18) && // alt
      (e.keyCode !== 19) && // pause/break
      (e.keyCode !== 20) && // caps lock
      (e.keyCode !== 27) && // escape
      (e.keyCode !== 35) && // end
      (e.keyCode !== 36) && // home
      (e.keyCode !== 37) && // left arrow
      (e.keyCode !== 38) && // up arrowUnited States of America
      (e.keyCode !== 39) && // right arrow
      (e.keyCode !== 40) && // down arrow
      (e.keyCode !== 45)) { // insert 
    var currentValue = document.getElementById(name+"Search").value;
    if (currentValue !== "") {
      ddSearching = 1;
      //console.log("called setTimeout(\"ddCheck("+name+","+currentValue + 
      //            "),250)");
      setTimeout("ddCheck('"+name+"','"+addslashes(currentValue)+"')", 250);
    }
    else { ddReset(name); }
  }
}



function ddSelect(ref) {
  var value = getValueFromId(ref.id)
  var name = getNameFromId(ref.id)
  ddMatchId = ref.id;
  ddMatchStr = ref.innerText;
  $('#'+name+'Search').val(ddMatchStr);
  $('#'+name+'Id').val(value);
  ddActionExecute(name);
}


function ddSelectNoMatch(name) {
}


function ddActionExecute(name) {
  if (name === "payee") { 
    var results = getDDSelection();
    console.log(results);
    $('#categorySearch').val(results['lastTransactionCategoryName']);
    $('#categoryId').val(results['lastTransactionCategoryId']);

    var event = jQuery.Event("categoryUpdated");
    //console.log("Firing event with categoryId = " + results['lastTransactionCategoryId']);
    event.categoryId = results['lastTransactionCategoryId'];
    $("body").trigger(event);
  } 

  else if (name === "category") { 
    var event = jQuery.Event("categoryUpdated");
    event.categoryId = $('#'+name+'Id').val();
    $("body").trigger(event);
  }

  else if (name === "assetCategory") { 
      var event = jQuery.Event(name + "Updated");
      event.categoryId = $('#' + name + 'Id').val();
      $('body').trigger(event);
  }

  else if (name == "tag") { 
    var event = jQuery.Event("tagSelected");
    event.tagId = $('#'+name + 'Id').val();
    event.tagName = $('#'+name + 'Search').val();
    $("body").trigger(event);
  }

  $('#'+name+'Confirm').hide();
  $('#'+name+'Confirm').fadeIn(500);
  ddHide(name);
}



function ddSearchURL(name) {
  var q = encodeURIComponent($('#' + name + 'Search').val());
  var get = '';
  var url = '';
  
  if (name === "payee") { return "/entity/search?q="+q; }
  else if (name === "category") { return "/category/search?q="+q; }
  else if (name === "assetCategory") { return "/category/search?q="+q; }
  else if (name === "asset") { return "/asset/search?q="+q; }
  else if (name === "tag") { return "/tag/search?q="+q; }
  else { alert(name); }
  return '';
}


function ddSearchAction(name, results) {
  ddResults = results;
}


function ddSearch(name) {
  //console.log("ddSearch("+name+")");

  $('#'+name+'Id').val('');
  $.ajax({
    url: ddSearchURL(name),
    success: function(data) {	     
      //console.log("data = "+data);
      //console.log("data[0] = "+data[0]);
      if ((data == '["no results"]') || (data == '["error"]')) { 
        //console.log("Search returned no results or error.About to ddHide()");
        ddHide(name);
      }
      else if (data !== "") {
        // set selected index to -1, clear dropdown
    	ddSelectedIndex = -1;        
	    var dropdown = document.getElementById(name + "DD");
        dropdown.innerHTML = '';
        
	    var results = jQuery.parseJSON(data);
	    ddSearchAction(name, results);
	    
	    var newHTML = "";
        for (var i = 0; i < (results.length); i++) {
          newHTML += 
            '<div id="' + name + '_' + results[i].id+'" '+
              'class="ddOption" '+
              'onmouseover="ddOver(this)" ' +
              'onclick="ddSelect(this)">' +
              results[i].name + 
            '</div>';
        }
        dropdown.innerHTML = newHTML;
        ddMinIndex = 0;
        ddMaxIndex = results.length - 1;
	    $('#'+name+'DD').show();
        ddSearching = 0;
      }
    }
  });
}



function getNameFromId(id) {
  var underscore = id.indexOf('_', 0);
  var name = id.substr(0,underscore);
  return name;
}


function getValueFromId(id) {
  if (id === -1) { return ''; }
  var underscore = id.indexOf('_', 0);
  var value = id.substr(underscore+1);
  //console.log("getValueFromId("+id+") = " + value);
  return value;	
}


function addSelection(name) {
  var selection = document.getElementById(ddMatchId+"Multi");
  if (selection !== null) { 
    alert("That selection ("+'#'+ddMatchId+'Multi'+") has already been added."); 
    return;
  }
  var newHTML = 
    '<span '+
      'id="'+ddMatchId+'Multi" ' +
      'class="multipleSelection">' +
      ddMatchStr + 
      '<a id="'+ddMatchId+'Del" class="del" '+
        'onclick="removeSelection(this)">[-]</a>' +
    '</span><br>';
  $('#'+name+'Container').append(newHTML);
  var newValue = $('#'+name+"Selections").val() + getValueFromId(ddMatchId) +';';
  $('#'+name+"Selections").val(newValue);
  $('#'+name+'Search').val('');
  ddMatchStr = '';
  ddMatchId = '';
}


function removeSelection(ref) {
  var id = ref.id.substr(0,(ref.id.length-3));
  var name = getNameFromId(id);
  var value = getValueFromId(id);
  $('#'+id+'Multi').remove();
  $('#'+name+'Selections').val($('#'+name+'Selections').val().replace(';'+value+';',';'));
}


function getDDSelection() { 
  //console.log("getDDSelection()");
  for (var i = 0; i < ddResults.length; i++) {
    if (getValueFromId(ddMatchId) == ddResults[i]['id']) { 
      return ddResults[i]; 
    }
  }
}


function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, 
                function(m,key,value) { vars[key] = value; });
  return vars;
}

