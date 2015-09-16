StartCheckout.config({
	key: WooCommerceStartParams.key,
	complete: function(params) {
		submitFormWithToken(params); // params.token.id, params.email
	}
});
