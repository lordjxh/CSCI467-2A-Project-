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