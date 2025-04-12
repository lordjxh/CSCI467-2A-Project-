showTab(currentTab); // Display the current tab

//showTab() - handles showing/hiding tabs during the checkout process
function showTab(n) {
    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";

    if (n == 0) 
    {
      document.getElementById("prevBtn").style.display = "none";
    } 
    else 
    {
      document.getElementById("prevBtn").style.display = "inline";
    }

    if (n == (x.length - 1))
    {
      document.getElementById("nextBtn").innerHTML = "Submit";
      printSummary();
    } 
    else 
    {
      document.getElementById("nextBtn").innerHTML = "Next";
    }

    fixStepIndicator(n);
}

//nextPrev() - handles navigation on the page between tabs
function nextPrev(n) 
{
  // This function will figure out which tab to display
  var x = document.getElementsByClassName("tab");

  // Exit the function if any field in the current tab is invalid:
  if (n == 1 && !validateForm())
  { 
    return false;
  }

  // Hide the current tab:
  x[currentTab].style.display = "none";

  // Increase or decrease the current tab by 1:
  currentTab = currentTab + n;

  // if you have reached the end of the form... :
  if (currentTab >= x.length) 
  {
    //...the form gets submitted:
    document.getElementById("regForm").submit();
    return false;
  }

  // Otherwise, display the correct tab:
  showTab(currentTab);
}

//validateForm() - ensures accuracy of data and that all required fields are filled
function validateForm()
{
  // This function deals with validation of the form fields
  var x, y, i, valid = true;
  x = document.getElementsByClassName("tab");
  y = x[currentTab].getElementsByTagName("input");

  // A loop that checks every input field in the current tab:
  for (i = 0; i < y.length; i++) {
    // If a field is empty...
    if (y[i].value == "") {
      // add an "invalid" class to the field:
      y[i].className += " invalid";
      // and set the current valid status to false:
      valid = false;
    }
  }

  // If the valid status is true, mark the step as finished and valid:
  if (valid) {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }

  return valid; // return the valid status
}

//fixStepIndicator() - updates the bottom bubbles based on which tab is active
function fixStepIndicator(n) {
  // This function removes the "active" class of all steps...
  var x = document.getElementsByClassName("step");

  for (var c = 0; c < x.length; c++)
  {
    x[c].className = x[c].className.replace(" active", "");
  }

  //... and adds the "active" class to the current step:
  x[n].className += " active";
}

//printShippingSummary() - changes <p> elements based on user-typed values on final checkout tab for review
function printSummary()
{
  //
  //Shipping Summary

  //variables to assign full name
  var firstName = document.getElementById("firstName").value;
  var lastName = document.getElementById("lastName").value;
  var fullName = firstName + " " + lastName;

  document.getElementById("fullNameOutput").textContent = fullName;

  //assigns street address
  document.getElementById("streetAddressOutput").textContent = document.getElementById("address").value;

  //variables to assign remaining address
  var city = document.getElementById("city").value;
  var state = document.getElementById("state").value;
  var zipcode = document.getElementById("zipcode").value;
  var fullAddr = city + ", " + state + ", " + zipcode;

  //assigns remaining address
  document.getElementById("fullAddressOutput").textContent = fullAddr;

  //variable to assign full contact info
  var phone = document.getElementById("phone").value;
  var email = document.getElementById("email").value;
  var fullContact = phone + " | " + email;

  //assigns full contact info
  document.getElementById("fullContactOutput").textContent = fullContact;

  //
  //Billing Summary

  var isMatched = document.getElementById("matchShipping");

  if(isMatched.checked) //if match shipping is selected, re-use fields from Shipping, otherwise populate new values
  {
    document.getElementById("billingAddressOutput").textContent = document.getElementById("address").value;
    document.getElementById("billingFullAddressOutput").textContent = fullAddr;
    document.getElementById("billingFullContactOutput").textContent = fullContact;
  }
  else
  {
    //assigns street address
    document.getElementById("billingAddressOutput").textContent = document.getElementById("billingAddress").value;

    //variables to assign remaining address
    var billingCity = document.getElementById("billingCity").value;
    var billingState = document.getElementById("billingState").value;
    var billingZipcode = document.getElementById("billingZipcode").value;
    var billingFullAddr = billingCity + ", " + billingState + ", " + billingZipcode;

    //assigns remaining address
    document.getElementById("billingFullAddressOutput").textContent = billingFullAddr;

    //variable to assign full contact info
    var billingPhone = document.getElementById("billingPhone").value;
    var billingEmail = document.getElementById("billingEmail").value;
    var billingFullContact = billingPhone + " | " + billingEmail;

    //assigns full contact info
    document.getElementById("billingFullContactOutput").textContent = billingFullContact;
  }

  //variables for card details
  var cardName = document.getElementById("cardName").value;
  var cardNum = document.getElementById("cardNumber").value;
  var partialCardNum;

  //assigns card detail info
  document.getElementById("cardNameOutput").textContent = cardName;

  partialCardNum = "****-****-****-" + cardNum.slice(12,15);
  document.getElementById("cardNumberOutput").textContent = partialCardNum;
}