<form action="https://pay.cashlog.com/dynamicpe/flow?flow=subscribe" name="cashlog" method="post" id="mpayButton">
	<input type="hidden" name="countryCode" value="ZA">
	<input type="hidden" name="siteCode" value="STCom1555214">
	<input type="hidden" name="productName" value="CommanderHQ">
	<input type="hidden" name="price" value="50.00">
	<input type="hidden" name="currencyCode" value="ZAR">
	<input type="hidden" name="callbackUrl" value="<?php echo SITE_URL; ?>/registration/processCashlogDetails">
	<input type="hidden" name="stayInFrame" value="false">
	<input type="hidden" name="customParams" value='{"Banner":"http://membership.commanderstore.com/img/site-images/desktop-version-header.jpg", "BackgroundColour": "#FFFFFF", "TextColour": "#000000" }'>
	<input type="hidden" name="mpaySubmit" value="true">
	<input type="submit" name="submitBtn" value="GO">
</form>
<!--  -->
<script language="JavaScript">
	document.forms["cashlog"].submit();
</script>