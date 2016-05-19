<div class="payfast-form" style="display:none;">
	<form action="https://<?php echo $payfast_host; ?>/eng/process" method="post" name="payfastPaymentForm" id="paymentForm">
		<input type="hidden" name="merchant_id" value="<?php echo MERCHANT_ID_TEST; ?>" />
		<input type="hidden" name="merchant_key" value="<?php echo MERCHANT_KEY_TEST; ?>" />
		<input type="hidden" name="return_url" value="<?php echo SITE_URL.RETURN_URL; ?>" />
		<input type="hidden" name="cancel_url" value="<?php echo SITE_URL.CANCEL_URL; ?>" />
		<input type="hidden" name="notify_url" value="<?php echo SITE_URL.PROCESS_URL; ?>" />
		<input type="hidden" name="amount" id="subscription_amount" value="50" />
		<input type="hidden" name="item_name" id="subscription_name" value="Membership Payment" />
		<input type="hidden" name="item_description" value="" />
		<input type="hidden" name="custom_int1" id="subscription_period" value="" />
		<input type="submit" name="payfasy-submit" value="Membership" />
	</form>
</div>