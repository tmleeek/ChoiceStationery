//*****************************************************************************************************
//
function requestQuoteForProduct(button)
{
    //change target url for form
    document.getElementById('product_addtocart_form').action = quoteRequestUrl;

    //submit form
    productAddToCartForm.submit(button);
}