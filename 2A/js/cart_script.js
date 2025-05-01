//Group 2A - CSCI467 Spring 2025
//cart_script.js - a JS file used for the cart page, specifically for the checkout button.


//Global variables
var checkout_button = document.getElementById("checkout");
var checkoutValid;

checkoutValid = setCheckoutButton(); //calls setCheckoutButton() at script load

//EventListener - used for checkout button, will add an href value if able to proceed with checkout
checkout_button.addEventListener('click', () => {
    if(checkoutValid)
    {
        window.location.href = "checkout.php";
    }
});

//setCheckoutButton() - handles enabling/disabling the checkout button based on a user's cart items
//Inputs - none
//Output - alters the checkout button element and enables/disables based on validity
function setCheckoutButton()
{
    let cartItemCount = document.getElementsByClassName("cart-item").length;
    let invalidItemCount = document.getElementsByClassName("invalid-item").length;
    let checkoutMessage = document.getElementById("checkoutMessage");

    let setCondition = true;

    console.log("Got " + cartItemCount + " items and " + invalidItemCount + " invalid");

    if(invalidItemCount > 0)
    {
        checkoutMessage.textContent = "⚠️ One or more items is no longer available. Check your cart quantity or remove the item to proceed.";
        setCondition = false;
    }
    else if (cartItemCount == 0)
    {
        setCondition = false;
    }

    if(setCondition == false)
    {
        checkout_button.disabled = true;
        checkout_button.style.color = "LightGray";
        checkout_button.style.backgroundColor = "Gray";

        return false;
    }

    return true;
}