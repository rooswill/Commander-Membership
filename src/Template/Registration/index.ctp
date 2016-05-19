<div class="buy-now-btn">
	<div class="buy-now-btn-text">
		JOIN NOW & GET YOUR ACTIVE GEAR AT UNBEATABLE PRICES ALL THE TIME
		<span>ONLY R50 PER MONTH</span>
	</div>
</div>
<div class="main-registration-container">
	<div class="main-user-form">

		<?php
			if(isset($paymentType))
				echo $this->element($paymentType.'-form');
			else
			{
				$paymentType = 'cashlog';
				echo $this->element('cashlog-form');
			}
				
		?>

		<form method="post" action="/registration" id="registration-form" />

			<div class="desktop-left">

				<div class="info-block">
					<div class="info-block-header">
						ENTER YOUR DETAILS BELOW
					</div>
				</div>

				<div class="left-form-elements">

					<div class="form-field">
						<div class="form-input"><input type="text" value="" placeholder="FIRST NAME:" name="first_name" id="first-name" /></div>
					</div>
					<div class="form-field">
						<div class="form-input"><input type="text" value="" placeholder="LAST NAME:" name="last_name" id="last-name" /></div>
					</div>
					<div class="form-field">
						<div class="form-input"><input type="text" value="" placeholder="EMAIL:" name="email" id="email" /></div>
					</div>
					<div class="form-field">
						<div class="form-input"><input type="password" value="" placeholder="PASSWORD:" name="password" id="password" /></div>
					</div>
					<div class="form-field">
						<div class="form-input"><input type="password" value="" placeholder="CONFIRM PASSWORD:" name="password_confirmation" id="password_confirmation" /></div>
					</div>

				</div>

			</div>

			<div class="desktop-right">
			
				<div class="right-form-elements aligned-bottom">
					<div class="form-submit sticky-bottom">
						<div class="submit-btn">
							<?php 
								if($submitButton)
									echo '<input type="submit" value="JOIN NOW" />';
								else
									echo '<input type="button" value="JOIN NOW" onclick="submitFormData(\''.$paymentType.'\');" />';
							?>
						</div>
					</div>
				</div>

			</div>

			<div class="clear"></div>

			<div class="form-field">
				<div class="form-input">
					<div class="checkbox">
						<div class="checkbox-input"><input id="terms" type="checkbox" name="terms" value="terms"></div>
						<div class="checkbox-label"><label for="terms">I have read, understood and agree to these <a href="">Terms &amp; Conditions</a></label></div>			
						<div class="clear"></div>
					</div>
				</div>
			</div>

		</form>
	</div>
</div>
<div class="clear"></div>