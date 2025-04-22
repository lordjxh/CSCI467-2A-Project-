var checkout_button = document.getElementById("checkout");
var checkoutValid;

checkoutValid = setCheckoutButton();

checkout_button.addEventListener('click', () => {
    if(checkoutValid)
    {
        window.location.href = "checkout.php";
    }
});

function setCheckoutButton() 
{
    let cartItemCount = document.getElementsByClassName("cart-item").length;
    let invalidItemCount = document.getElementsByClassName("invalid-item").length;
    let checkoutMessage = document.getElementById("checkoutMessage");

    console.log("Got " + cartItemCount + " items and " + invalidItemCount + " invalid");

    if(cartItemCount == 0 || invalidItemCount > 0)
    {
        checkout_button.disabled = true;
        checkout_button.style.color = "LightGray";
        checkout_button.style.backgroundColor = "Gray";

        checkoutMessage.textContent = "⚠️ One or more items is no longer available. Check your cart quantity or remove the item to proceed.";

        return false;
    }

    return true;
}