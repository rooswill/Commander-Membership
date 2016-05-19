<div class="buy-now-btn">
	<div class="buy-now-btn-text">
		JOIN NOW & GET YOUR ACTIVE GEAR AT UNBEATABLE PRICES ALL THE TIME
		<span>ONLY R50 PER MONTH</span>
	</div>
</div>
<div class="main-registration-container">
	<form method="post" action="/registration" id="registration-form" />
		<div class="main-user-form">

			<?php
				echo $this->element('cashlog-form');
				echo $this->element('payfast-form');
				echo $this->element('snapscan-form');
			?>

			<div class="info-block-header padded-header">
				Renew Membership
			</div>
			<div class="desktop-left">
				<div class="info-block">
					<div class="left-icon desktop-display"><img src="/img/site-images/step-one.jpg" /></div>
					<div class="info-block-header">
						ENTER YOUR DETAILS BELOW
					</div>
				</div>
				<br />
				<div class="form-field">
					<div class="form-input"><input type="text" value="" placeholder="EMAIL:" name="email" id="email" /></div>
				</div>
			</div>

			<div class="desktop-right">
				<div class="right-icon desktop-display"><img src="/img/site-images/step-two.jpg" /></div>
				<div class="info-block">
					<div class="info-block-header">
						SELECT A PAYMENT METHOD
					</div>
					<div class="info-block-text">
						R50 will be billed to your prefered payment method 
						monthly - you can cancel at anytime.
					</div>
				</div>
				<div class="form-submit">
					<div class="payment-btns">
						<div class="payment-btn cashlog-btn"><a href="#" onclick="submitFormData('cashlog');"><img src="/img/site-images/cashlog-btn.jpg" /><!--</a>--></div>
						<div class="payment-btn payfast-btn"><a href="#" onclick="submitFormData('payfast');"><img src="/img/site-images/payfast-btn.jpg" /></a></div>
						<div class="payment-btn snapscan-btn"><a href="#" onclick="submitFormDataSnapScan();"><img src="/img/site-images/snapscan-btn.jpg" /></a></div>
					</div>
				</div>

				<div class="form-field">
					<div class="form-input">
						<div class="checkbox">
							<div class="checkbox-input"><input id="terms" type="checkbox" name="terms" value="terms"></div>
							<div class="checkbox-label"><label for="terms">I have read, understood and agree to these <a href="">Terms &amp; Conditions</a></label></div>			
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="clear"></div>