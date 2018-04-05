//var pollInterval = 60000;
var pollInterval = 15000;
var expirationMinutes = 15;
var warningMinutes = 13;
var intervalId;
var lastActivity;
var dialogShown = 0;

function initSessionMonitor() {
  pingServer();
  lastActivity = new Date();
  sessionSetInterval();
  $(document).bind('keypress.session', function(ed, e) { sessionKeyPressed(ed, e); });
}

function sessionSetInterval() {
  intervalId = setInterval('sessionInterval()', pollInterval);
}

function sessionClearInterval() {
  clearInterval(intervalId);
}

function sessionKeyPressed(ed, e) {
  now = new Date();
  if ((now - lastActivity) > 30000) {
    console.log("now - lastActivity = " + (now - lastActivity));
    pingServer();
  }
  lastActivity = now;
}

function pingServer() {
  // ping server
  var url = '/session-update';
  $.ajax({
         url: url, 
         success: function() {
         }}
  );
}

function logout() {
  window.location.href = '/user/logout';
}

function stayLoggedIn() {
  pingServer();
  lastActivity = new Date();
  $('#timeoutDialog').dialog('close');
  dialogShown = 0;
}

function sessionInterval() {
  var now = new Date();
  var diff = now - lastActivity;
  var diffMins = (diff / 1000 / 60);
  //console.log("Diff seconds = " + (diff / 1000) + ", Diff minutes = " + diffMins);

  if (diffMins >= warningMinutes) {
    console.log("showSessionTimeoutDialog()");
    if (!dialogShown) {
      var minutesRemaining = expirationMinutes - warningMinutes;
      showSessionTimeoutDialog(minutesRemaining);
      dialogShown = 1;
    }
  }
  if (diffMins >= expirationMinutes) {
    logout();
  }
}


function showSessionTimeoutDialog(minutesRemaining) {
  $('#minutesRemaining').html(minutesRemaining);
  $('#timeoutDialog').dialog({
    height:200,
    width:550,
    modal:true,
    buttons: {
      "Log Out": logout,
      "Stay Logged In": stayLoggedIn
    }
  });
}