//Group 2A - CSCI467 Spring 2025
//checkout_script.js - a JS file that handles data entry and tab navigation for the checkout page. Ensures that all fields are completed and contain valid inputs, then
//changes the current entry tab. Sequences: shipping -> billing -> payment -> final confirmation.


showTab(currentTab); // Display the current tab at script load


//EventListener - for use with auto-formatting the input fields or validating specific fields
//such as email addresses
document.addEventListener("DOMContentLoaded", function () {
  const shippingZipcodeInput = document.getElementById('zipcode');
  const shippingPhoneInput = document.getElementById('phone');

  const billingZipcodeInput = document.getElementById('billingZipcode');
  const billingPhoneInput = document.getElementById('billingPhone');

  const cardNumberInput = document.getElementById('cardNumber');

  const isMatchedInput = document.getElementById("matchShipping"); //used to auto-populate billing with shipping on click

  //
  //Shipping Formatting/Validation

  if(shippingZipcodeInput) //handles zipcode formatting for shipping
  {
    shippingZipcodeInput.addEventListener('input', function (e)
    {
      let value = e.target.value.replace(/\D/g, ''); //forces removal of non-digits
      let formatted = value;

      if (value.length > 5) 
      {
        formatted = `${value.slice(0, 5)}-${value.slice(5, 10)}`;
      }

      e.target.value = formatted;
    });
  }

  if(shippingPhoneInput) //handles phone formatting for shipping
  {
    shippingPhoneInput.addEventListener('input', function (e)
    {
      let value = e.target.value.replace(/\D/g, ''); //forces removal of non-digits
      let formatted = value;

      if (value.length >= 6)
      {
        formatted = `(${value.slice(0,3)}) ${value.slice(3,6)}-${value.slice(6)}`;
      } 
      else if (value.length >= 3) 
      {
        formatted = `(${value.slice(0,3)}) ${value.slice(3)}`;
      }

      e.target.value = formatted;
    });
  }

  //
  //Billing Formatting/Validation (same methodology as above)

  if(billingZipcodeInput) //handles zipcode formatting for shipping
  {
    billingZipcodeInput.addEventListener('input', function (e)
    {
      let value = e.target.value.replace(/\D/g, ''); //forces removal of non-digits
      let formatted = value;

      if (value.length > 5) 
      {
        formatted = `${value.slice(0, 5)}-${value.slice(5, 10)}`;
      }

      e.target.value = formatted;
    });
  }

  if(billingPhoneInput) //handles phone formatting for shipping
  {
    billingPhoneInput.addEventListener('input', function (e)
    {
      let value = e.target.value.replace(/\D/g, ''); //forces removal of non-digits
      let formatted = value;

      if (value.length >= 6)
      {
        formatted = `(${value.slice(0,3)}) ${value.slice(3,6)}-${value.slice(6)}`;
      } 
      else if (value.length >= 3) 
      {
        formatted = `(${value.slice(0,3)}) ${value.slice(3)}`;
      }

      e.target.value = formatted;
    });
  }

  //
  //Card Payment Formatting/Validation

  if(cardNumberInput) //handles card formatting (assummes a 16 digit card)
  {
    cardNumberInput.addEventListener('input', function (e)
    {
      let value = e.target.value.replace(/\D/g, ''); //forces removal of non-digits
      let formatted = value;

      if (value.length >= 12)
      {
        formatted = `${value.slice(0,4)}-${value.slice(4,8)}-${value.slice(8,12)}-${value.slice(12)}`;
      }
      else if (value.length >= 8)
      {
        formatted = `${value.slice(0,4)}-${value.slice(4,8)}-${value.slice(8)}`;
      }
      else if (value.length >= 4)
      {
        formatted = `${value.slice(0,4)}-${value.slice(4)}`;
      }
      else
      {
        formatted = `${value.slice(0)}`;
      }

      e.target.value = formatted;
    });
  }

  if(isMatchedInput)
  {
    isMatchedInput.addEventListener('input', function() 
    {
      if(currentTab == 1) //if the current tab is billing, proceed
      {
        if(isMatchedInput.checked) //if the checkbox is selected, call autofillBilling as true
        {
          autofillBilling(true);
        }
        else //else call autofillBilling as false
        {
          console.log("Calling autofill as false");
          autofillBilling(false);
        }
      }
    })
  }

});

//showTab() - handles showing/hiding tabs during the checkout process
//Inputs -
  //tabNum - a number passed that indexes the current tab to show
//Output - changes the visibility of HTML elements
function showTab(tabNum) {
    var x = document.getElementsByClassName("tab");
    x[tabNum].style.display = "block";

    if (tabNum == 0) //if on the first tab, disable seeing the previous button
    {
      document.getElementById("prevBtn").style.display = "none";
    } 
    else //else enable seeing the previous button
    {
      document.getElementById("prevBtn").style.display = "inline";
    }

    if (tabNum == (x.length - 1)) //if the tab is the last, swap "next" with "submit"
    {
      document.getElementById("nextBtn").innerHTML = "Submit";
      printSummary(); //prints a summary of the user's inputs
    } 
    else //else the next button should be visible
    {
      document.getElementById("nextBtn").innerHTML = "Next";
    }

    fixStepIndicator(tabNum); //sets the bottom navigation to the current tab
}

//nextPrev() - handles navigation on the page between tabs and the next/prev button's actions
//Inputs -
  //tabNum - a number passed for the tab to change to (less or greater than currentTab)
//Output - calls showTab(), or submits form if on the last tab
function nextPrev(tabNum) 
{
  var tabGroup = document.getElementsByClassName("tab");

  //ensure the form is valid before navigating to the next, returns false if invalid
  if (tabNum == 1 && !validateForm())
  { 
    return false;
  }

  tabGroup[currentTab].style.display = "none"; //hide the current tab:
  currentTab = currentTab + tabNum; //increments or decrements the currentTab by tabNum

  if (currentTab >= tabGroup.length) //if the current tab is the final tab, call submit on the form elements
  {
    document.getElementById("regForm").submit();
    return false; //prevents any form changes while submitting
  }

  showTab(currentTab); //calls showTab() with the new currentTab passed
}

//fixStepIndicator() - updates the bottom bubbles based on which tab is active
//Inputs -
  //n: 
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

//validateForm() - ensures accuracy of data and that all required fields are filled
function validateForm()
{
  let shippingError = document.getElementById('shippingError');
  let billingError = document.getElementById('billingError');
  let paymentError = document.getElementById('paymentError');

  let valid = true;

  //Step 1
  //Validate Shipping Details
  if(currentTab == 0)
  {
    valid = validateShippingInputs();

    if(valid)
    {
      shippingError.textContent = "";
    }
    else
    {
      shippingError.textContent = "⚠️ One or more entry is invalid.";
    }
  }

  //Step 2
  //Validate Billing Details
  if(currentTab == 1)
  {
    valid = validateBillingInputs();

    if(valid)
    {
      billingError.textContent = "";
    }
    else
    {
      billingError.textContent = "⚠️ One or more entry is invalid.";
    }
  }

  //Step 3
  //Validate Card Details (Exclusive from CC API)
  if(currentTab == 2)
  {
    valid = validatePaymentInputs();
  
    if(valid)
    {
      paymentError.textContent = "";
    }
    else
    {
      paymentError.textContent = "⚠️ One or more entry is invalid.";
    }
  }

  // If the valid status is true, mark the step as finished and valid:
  if (valid)
  {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }

  return valid; // return the valid status
}

//validateShippingInputs() - called as part of validateForm(), verifies shipping inputs by lengths.
//Inputs - none; uses user input values
//Output - boolean for validity of inputs
function validateShippingInputs()
{
  let shippingVars = [], valid = true;

  //gets each indviidual element part of shipping
  shippingVars[0] = document.getElementById("firstName");
  shippingVars[1] = document.getElementById("lastName");
  shippingVars[2] = document.getElementById("address");
  shippingVars[3] = document.getElementById("city");
  shippingVars[4] = document.getElementById("state");
  shippingVars[5] = document.getElementById("zipcode");
  shippingVars[6] = document.getElementById("email");
  shippingVars[7] = document.getElementById("phone");

  //checks that no elements are left empty
  for(c = 0; c < 8; c++)
  {
    if(shippingVars[c].value == "") //if the element is empty, set element to invalid, and valid to false
    {
      shippingVars[c].className += " invalid";
      valid = false;
    }
  }

  //checks state is 2 characters exact, otherwise sets element to invalid, and valid to false
  if(shippingVars[4].value.length != 2)
  {
    shippingVars[4].className += " invalid";
    valid = false;
  }

  //checks zip code is five digits or 10 digits exact, otherwise sets element to invalid, and valid to false
  if(shippingVars[5].value.length != 10)
  {
    if(shippingVars[5].value.length != 5)
    {
      shippingVars[5].className += " invalid";
      valid = false;
    }
  }

  //checks shipping email is valid, otherwise sets element to invalid, and valid to false
  const email = shippingVars[6].value.trim();
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!regex.test(email))
  {
    shippingVars[6].className += " invalid";
    valid = false;
  } 

  //checks phone number is valid (all digits), otherwise sets element to invalid, and valid to false
  if(shippingVars[7].value.length != 14)
  {
    shippingVars[7].className += " invalid";
    valid = false;
  }

  return valid;
}


//validateBillingInputs() - called as part of validateForm(), verifies billing inputs by lengths.
//Follows same methodology as validateShippingInputs().
//Inputs - none; uses user input values
//Output - boolean for validity of inputs
function validateBillingInputs()
{
  let billingVars = [], valid = true;

  //gets each indviidual element part of shipping
  billingVars[0] = document.getElementById("billingFirstName");
  billingVars[1] = document.getElementById("billingLastName");
  billingVars[2] = document.getElementById("billingAddress");
  billingVars[3] = document.getElementById("billingCity");
  billingVars[4] = document.getElementById("billingState");
  billingVars[5] = document.getElementById("billingZipcode");
  billingVars[6] = document.getElementById("billingEmail");
  billingVars[7] = document.getElementById("billingPhone");

  //checks that no elements are left empty
  for(c = 0; c < 8; c++)
  {
    if(billingVars[c].value == "") //if the element is empty, set element to invalid, and valid to false
    {
      billingVars[c].className += " invalid";
      valid = false;
    }
  }

  //checks state is 2 characters exact, otherwise sets element to invalid, and valid to false
  if(billingVars[4].value.length != 2)
  {
    billingVars[4].className += " invalid";
    valid = false;
  }

  //checks zip code is five digits or 10 digits exact, otherwise sets element to invalid, and valid to false
  if(billingVars[5].value.length != 10)
  {
    if(billingVars[5].value.length != 5)
    {
      billingVars[5].className += " invalid";
      valid = false;
    }
  }

  //checks billing email is valid, otherwise sets element to invalid, and valid to false
  const email = billingVars[6].value.trim();
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!regex.test(email))
  {
    shippingVars[6].className += " invalid";
    valid = false;
  } 

  //checks phone number is valid (all digits), otherwise sets element to invalid, and valid to false
  if(billingVars[7].value.length != 14)
  {
    billingVars[7].className += " invalid";
    valid = false;
  }

  return valid;
}

//validatePaymentInputs() - called as part of validateForm(), verifies payment inputs by lengths.
//Follows same methodology as validateShippingInputs() and validateBillingInputs().
//Inputs - none; uses user input values
//Output - boolean for validity of inputs
function validatePaymentInputs()
{
  let paymentVars = [], valid = true;

  paymentVars[0] = document.getElementById("cardNumber");
  paymentVars[1] = document.getElementById("cardMonth");
  paymentVars[2] = document.getElementById("cardYear");
  paymentVars[3] = document.getElementById("cardSecurity");

  //checks that no elements are left empty
  for(c = 0; c < 4; c++)
  {
    if(paymentVars[c].value == "") //if the element is empty, set element to invalid, and valid to false
    {
      paymentVars[c].className += " invalid";
      valid = false;
    }
  }

  //checks card number is 19 digits exact (based on live formatting), otherwise sets element to invalid, and valid to false
  if(paymentVars[0].value.length != 19)
  {
    paymentVars[0].className += " invalid";
    valid = false;
  }

  return valid;
}


function autofillBilling(wasSelected)
{
  let shippingVars = [], billingVars = [];

  //gets each indviidual element part of shipping
  shippingVars[0] = document.getElementById("firstName");
  shippingVars[1] = document.getElementById("lastName");
  shippingVars[2] = document.getElementById("address");
  shippingVars[3] = document.getElementById("city");
  shippingVars[4] = document.getElementById("state");
  shippingVars[5] = document.getElementById("zipcode");
  shippingVars[6] = document.getElementById("email");
  shippingVars[7] = document.getElementById("phone");

  //gets each indviidual element part of shipping
  billingVars[0] = document.getElementById("billingFirstName");
  billingVars[1] = document.getElementById("billingLastName");
  billingVars[2] = document.getElementById("billingAddress");
  billingVars[3] = document.getElementById("billingCity");
  billingVars[4] = document.getElementById("billingState");
  billingVars[5] = document.getElementById("billingZipcode");
  billingVars[6] = document.getElementById("billingEmail");
  billingVars[7] = document.getElementById("billingPhone");

  if(wasSelected == true) //if this value is true, proceed with auto-populating variables
  {
    for(c = 0; c < 8; c++)
    {
      billingVars[c].value  = shippingVars[c].value;
      billingVars[c].readOnly = true;
    }
  }
  else //else reset variables to empty
  {
    for(c = 0; c < 8; c++)
    {
      billingVars[c].value  = "";
      billingVars[c].readOnly = false;
    }
  }
}

//printShippingSummary() - changes <p> elements based on user-typed values on final checkout tab for review
//Inputs - none
//Output - copies values to summary tab
function printSummary()
{
  //
  //Shipping Summary

  //variables to assign full name
  let firstName = document.getElementById("firstName").value;
  let lastName = document.getElementById("lastName").value;
  let fullName = firstName + " " + lastName;

  document.getElementById("fullNameOutput").textContent = fullName;

  //assigns street address
  document.getElementById("streetAddressOutput").textContent = document.getElementById("address").value;

  //variables to assign remaining address
  let city = document.getElementById("city").value;
  let state = document.getElementById("state").value;
  let zipcode = document.getElementById("zipcode").value;
  let fullAddr = city + ", " + state + ", " + zipcode;

  //assigns remaining address
  document.getElementById("fullAddressOutput").textContent = fullAddr;

  //variable to assign full contact info
  let phone = document.getElementById("phone").value;
  let email = document.getElementById("email").value;
  let fullContact = phone + " | " + email;

  //assigns full contact info
  document.getElementById("fullContactOutput").textContent = fullContact;

  //
  //Billing Summary

  let isMatched = document.getElementById("matchShipping");

  if(isMatched.checked) //if match shipping is selected, re-use fields from Shipping, otherwise populate new values
  {
    document.getElementById("cardNameOutput").textContent = fullName;
    document.getElementById("billingAddressOutput").textContent = document.getElementById("address").value;
    document.getElementById("billingFullAddressOutput").textContent = fullAddr;
    document.getElementById("billingFullContactOutput").textContent = fullContact;
  }
  else
  {
    //assigns billing name
    let billingFirstName = document.getElementById("billingFirstName").value;
    let billingLastName = document.getElementById("billingLastName").value;
    let billingFullName = billingFirstName + " " + billingLastName;

    //assigns card detail info
    document.getElementById("cardNameOutput").textContent = billingFullName;

    //assigns street address
    document.getElementById("billingAddressOutput").textContent = document.getElementById("billingAddress").value;

    //variables to assign remaining address
    let billingCity = document.getElementById("billingCity").value;
    let billingState = document.getElementById("billingState").value;
    let billingZipcode = document.getElementById("billingZipcode").value;
    let billingFullAddr = billingCity + ", " + billingState + ", " + billingZipcode;

    //assigns remaining address
    document.getElementById("billingFullAddressOutput").textContent = billingFullAddr;

    //variable to assign full contact info
    let billingPhone = document.getElementById("billingPhone").value;
    let billingEmail = document.getElementById("billingEmail").value;
    let billingFullContact = billingPhone + " | " + billingEmail;

    //assigns full contact info
    document.getElementById("billingFullContactOutput").textContent = billingFullContact;
  }

  //variables for card details
  let cardNum = document.getElementById("cardNumber").value;
  let partialCardNum;

  //assigns card number details
  partialCardNum = "****-****-****-" + cardNum.slice(15,19);
  document.getElementById("cardNumberOutput").textContent = partialCardNum;
}