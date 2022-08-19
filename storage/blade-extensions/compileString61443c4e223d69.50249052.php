Hi <?php echo e($booking->user->first_name); ?>,<br>
					<br>
					<p>your booking with the following details has been cancelled:</p>
					<br>
					<?php echo $booking->getBookingDetails(); ?>