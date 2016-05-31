<div class="buy-now-btn">
	<div class="buy-now-btn-text" style="text-align:center">
		COMMANDERHQ REPORTING
	</div>
</div>
<div class="customReporting">
	<div class="">
		Total New Customer : <?php echo $totalNewCustomers['TotalNewCustomers']; ?>
	</div>
</div>

<div class="customReporting">
	<div class="">Daily New Customers</div>
	<?php
		if($customersDaily != NULL)
		{
			foreach($customersDaily as $daily)
			{
				?>
					<div class="">Day <?php echo $daily['Day']; ?>) <?php echo $daily['Total']; ?></div>
				<?php
			}
		}
		else
			echo "No new customers";
	?>
</div>

<div class="customReporting">
	<div class="">New Customers Daily</div>
	<?php
		if($customersWeeklyDaily != NULL)
		{
			foreach($customersWeeklyDaily as $week => $data)
			{
				?>
					<div class="week-<?php echo $week; ?> weekly-container">
						<div class="main-week-header">Week <?php echo $week; ?></div>
						<?php
							foreach($data as $weekday => $total)
							{
								?>
									<div class=""><?php echo $weekday; ?> - <?php echo $total['Total']; ?></div>
								<?php
							}
						?>
					</div>
				<?php
			}
		}
		else
			echo "No new customers";
	?>
</div>

<div class="customReporting">
	<div class="">Weekly New Customers</div>
	<?php
		if($customersWeekly != NULL)
		{
			foreach($customersWeekly as $weekly)
			{
				?>
					<div class="">Week <?php echo $weekly['Week']; ?>) <?php echo $weekly['Total']; ?></div>
				<?php
			}
		}
		else
			echo "No new customers";
	?>
</div>