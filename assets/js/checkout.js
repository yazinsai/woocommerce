jQuery( 'form.checkout' ).on( 'checkout_place_order_payfort', function () {
	return startFormHandler();
});

jQuery('input[name=payment_method]').click(function() {
	resetStartForm();
});

jQuery( 'form#order_review' ).on( 'submit', function () {
	return startFormHandler();
});


function resetStartForm() {
	var form  = jQuery( 'form.checkout, form#order_review' );
	jQuery('input[name=payfortToken], input[name=payfortEmail]').remove();
	jQuery('#payfortCardDetails').hide().html("");
	jQuery('#changeCardBtn').hide();
}

function createToken() {
	StartCheckout.open({
		amount: WooCommerceStartParams.amount,
		currency: WooCommerceStartParams.currency,
		email: jQuery("#billing_email").val()
	});
}

function startFormHandler() {
	var form = jQuery( 'form.checkout, form#order_review' );

	if ( jQuery( '#payment_method_payfort' ).is( ':checked' ) ) {
		if ( 0 === jQuery( 'input[name=payfortToken]' ).size() ) {
			createToken();
			return false;
		}
	}

	return true;
}
/**
 * This method is called after a token is returned when the form is submitted.
 * We add the token + email to the form, and then submit the form.
 */
function submitFormWithToken(params) {
	var form  = jQuery( 'form.checkout, form#order_review' );
	// params.token.id, params.email

	// remove old values if any
	jQuery('input[name=payfortToken], input[name=payfortEmail]').remove();

	// Append the params to the form
	frmCheckout = jQuery("form[name=checkout]");
	frmCheckout.append("<input type='hidden' name='payfortToken' value='" + params.token.id + "'>");
	frmCheckout.append("<input type='hidden' name='payfortEmail' value='" + params.email + "'>");

	jQuery('#payfortCardDetails').show().html("<p>Pay with Card: xxxx-xxxx-xxxx-<b>" + params.token.card.last4 + "</b></p>");

	jQuery('#addCardBtn').hide();
	jQuery('#changeCardBtn').show();

	form.submit();
}

