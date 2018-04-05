function updateOptions() {
  var url = '/mortgage-calculator?' + 
    'id=' + $('#accountId').val() + '&' + 
    'rate=' + $('#interest-rate').val() + '&' + 
    'extra=' + $('#extra-payment').val();
  window.location = url;
}


function resetOptions() {
  var url = '/mortgage-calculator?id='+$('#accountId').val();
  window.location = url;
}


function accountUpdated() {
  console.log($('#account').val());
  var accountId = $('#account').val();
 
  if (accountId == 'custom') {
    window.location = '/mortgage-calculator';
  }
  else {
    window.location = '/mortgage-calculator?id='+accountId;
  }
}


function updateLoanTerm() {
  $('#paymentCount').val($('#loanterm').val()*12);
}


function currencyFormat(x) {
  var x = x.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return '$ ' + x;
  //return parseFloat(x).toFixed(2);
}


function parseCurrency(num) {
  if (num === undefined) { return 0; }
  if (typeof num === "string") { return parseFloat(num.replace(',', '')); }
  else { return parseFloat(num); }
}



function recalculateMortgage() {
  var accountId = $('#account').val();

  if (accountId == 'custom') {
    var purchasePrice = parseCurrency($('#purchasePrice').val());
    var downPayment = parseCurrency($('#downPayment').val());
    var term = $('#loanterm').val();
  }
  else {
    var purchasePrice = parseCurrency($('#purchasePrice').html());
    var downPayment = parseCurrency($('#downPayment').html());
    var term = $('#loanterm').html();
  }

  var loanAmount = purchasePrice - downPayment;
  var payments = term * 12;
  var rate = $('#interest-rate').val();
  var monthlyRate = rate / 100 / 12;
  var extraPayment = parseCurrency($('#extra-payment').val());

  var principal = parseCurrency(loanAmount);
  var x = Math.pow(1 + monthlyRate, payments);
  var monthly = (principal * x * monthlyRate) / (x - 1);

  var balance = loanAmount;
  var startingBalance = balance;


  $('#loanAmount').html(currencyFormat(loanAmount));
  $('#paymentCount').html(payments);
  $('#mortgageCalculator .odd').remove();
  $('#mortgageCalculator .even').remove();

  var now = Date.today();
  var totalPaid = 0; var totalInterest = 0;
  var i = 0;
  var newHTML = '';
  var totalPossibleInterest = 0;


  for (i = 0; i < payments; i++) {
    if (balance < 0) { break; }
    var monthlyInterest = monthlyRate * balance;
    var monthlyPrincipal = monthly - monthlyInterest;    
    balance -= (monthlyPrincipal);
    totalPossibleInterest += monthlyInterest;
  }

  balance = loanAmount;
  var lastMonth = ''; 
  for (i = 0; i < payments; i++) {
    if (balance < 0) { break; }

    var monthlyInterest = monthlyRate * balance;
    var monthlyPrincipal = monthly - monthlyInterest;
    startingBalance = balance;
    balance -= (monthlyPrincipal + extraPayment);
    totalPaid += (monthlyPrincipal + monthlyInterest + extraPayment);
    totalInterest += monthlyInterest;
    var extra = 0;
    var month = (i).months().fromNow().toString('MMMM-yyyy');
    lastMonth = month;

    var rowClass = 'odd';
    if (i % 2) { rowClass = 'even'; }
    newHTML += '<tr id="'+(i+1)+'" class="'+rowClass+'">';
    newHTML += '<td class="paymentNumber">'+(i+1)+'</th>';
    newHTML += '<td class="month">'+month+'</th>';
    newHTML += '<td class="balance">' + currencyFormat(startingBalance) + '</th>';
    newHTML += '<td class="payment">' + currencyFormat(monthly) + '</th>';
    newHTML += '<td class="principal">' + currencyFormat(monthlyPrincipal) + '</th>';
    newHTML += '<td class="interest">' + currencyFormat(monthlyInterest) + '</th>';
    newHTML += '<td class="extra">' + currencyFormat(extraPayment) + '</th>';
    newHTML += '<td class="principal">' + currencyFormat(monthly + extra) + '</th>';
    newHTML += '<td class="endingBalance">' + currencyFormat(balance) + '</th>';
    newHTML += '</tr>';
  }
  $('#mortgageCalculator').append(newHTML);

  $('#totalPaid').html(currencyFormat(totalPaid));
  $('#totalInterest').html(currencyFormat(totalInterest));
  $('#maxInterest').html(currencyFormat(totalPossibleInterest));

  var interestSavings = totalPossibleInterest - totalInterest;
  $('#interestSavings').html(currencyFormat(interestSavings));

  var percentInterestPaid = (totalInterest / totalPossibleInterest) * 100;
  $('#percentInterestPaid').html(percentInterestPaid.toFixed(1));
  $('#payoffDate').html(lastMonth);
  $('#actualPaymentCount').html(i);
}