<?php
/**
 * Spidanet main transactions page callback function.
 */
function spidanet_transactions_cb() {
	?>
	<div class="wrap woocommerce">
	<div class="icon32" id="icon-woocommerce-importer"><br></div>
	<h2><?php _e('Spidanet eWallet', 'spidanet-yfo'); ?></h2>
	<p class="description"><?php _e('Spidanet credit and debit transactions for restaurants'); ?></p>
	<div class="restaurants_wrap">
	<?php if( $restaurant_list = spidanet_get_restaurant_dropdown() ) {
		echo $restaurant_list;
		$selected = ( isset($_GET['restaurant']) ) ? intval($_GET['restaurant']) : "";
		if( !empty($selected) ) {
		?>
			<div class="tool-box">
				<!-- <h3 class="title"><?php #_e('Merchant Transactions', 'spidanet-yfo'); ?></h3>
				<p class="description"><?php #_e('Merchant credit and debit amount transactions.', 'woocommerce-yfo'); ?></p> -->
				<?php
				// Check if date filter is used by administrator.
				$query_cond = "";
				if ( isset( $_POST['spidanet_dt_fltr'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'spidanet-date-fltr' ) ) {
					$show_dt_fltr = $show_type_fltr = $show_mode_fltr = $show_strnxtype_fltr = "";
					$show_fltrs_label = "<span class='show_fltrs_label'>" . __( 'Selected Filters', 'spidanet-yfo' ) . "</span>: ";
					$frm_dt = sanitize_text_field( $_POST['trnx_from_dt'] );
					$to_dt = sanitize_text_field( $_POST['trnx_to_dt'] );
					$trnx_type = sanitize_text_field( $_POST['trnx_type'] );
					$trnx_pay_mode = sanitize_text_field( $_POST['trnx_pay_mode'] );
					$strnx_type = sanitize_text_field( $_POST['strnx_type'] );
					$frm_dt_db = date( 'Y-m-d H:i:s', strtotime( $frm_dt, current_time('timestamp', 0) ) );
					$to_dt_db = date( 'Y-m-d H:i:s', strtotime( $to_dt, current_time('timestamp', 0) ) );
					if( $frm_dt && $to_dt ) {
						$query_cond .= "  AND date BETWEEN '$frm_dt_db' AND '$to_dt_db'";
						$show_dt_fltr = "<span class='spidanet_show_fltrs'>$frm_dt - $to_dt</span>";
					}
					if( in_array($trnx_type, array("pickup", "delivery")) ) {
						$query_cond .= "  AND order_type = '$trnx_type'";
						$show_type_fltr = "<span class='spidanet_show_fltrs'>$trnx_type</span>";
					}
					if( in_array($trnx_pay_mode, array("online", "cash")) ) {
						$show_mode_fltr = "<span class='spidanet_show_fltrs'>$trnx_pay_mode</span>";
						if( $trnx_pay_mode == "cash" )
							$query_cond .= "  AND status IN(3, 4)";
						if( $trnx_pay_mode == "online" )
							$query_cond .= "  AND status IN(0, 1)";
					}
					if( in_array($strnx_type, array("credit", "debit")) ) {
						$show_strnxtype_fltr = "<span class='spidanet_show_fltrs'>$strnx_type</span>";
						if( $strnx_type == "credit" )
							$query_cond .= "  AND status != 2";
						if( $strnx_type == "debit" )
							$query_cond .= "  AND status = 2";
					}
				} else {
					$show_dt_fltr = $show_type_fltr = $show_mode_fltr = $show_fltrs_label = $show_strnxtype_fltr = "";
				}
				global $wpdb;
				$yesterday_date = date( 'Y-m-d H:i:s', strtotime( '-1 day', current_time('timestamp', 0) ) );
				$strx_tot = $wpdb->get_var( "SELECT SUM(gross_total) FROM $wpdb->prefix" . "stransactions WHERE status IN(0, 1) AND merchant_id = $selected" );
				$strx_comm_tot = $wpdb->get_var( "SELECT SUM(commission) FROM $wpdb->prefix" . "stransactions WHERE status IN(0, 1) AND merchant_id = $selected" );
				$strx_cod_comm_tot = $wpdb->get_var( "SELECT SUM(commission) FROM $wpdb->prefix" . "stransactions WHERE status = 4 AND merchant_id = $selected" );
				$strx_cod_pending_comm_tot = $wpdb->get_var( "SELECT SUM(commission) FROM $wpdb->prefix" . "stransactions WHERE status = 3 AND merchant_id = $selected AND date < '$yesterday_date'" );
				$mtrx_tot_release = $wpdb->get_var( "SELECT SUM(merchant_total) FROM $wpdb->prefix" . "stransactions WHERE status = 0 AND merchant_id = $selected AND date < '$yesterday_date'" );
				$mtrx_tot_release_comm = $wpdb->get_var( "SELECT SUM(commission) FROM $wpdb->prefix" . "stransactions WHERE status = 0 AND merchant_id = $selected AND date < '$yesterday_date'" );
				$mtrx_tot_released = $wpdb->get_var( "SELECT SUM(merchant_total) FROM $wpdb->prefix" . "stransactions WHERE status = 2 AND merchant_id = $selected" );
				/* Pagination Params */
				$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
				$strxs_tot = $wpdb->get_var( "SELECT COUNT(`id`) FROM $wpdb->prefix" . "stransactions WHERE merchant_id = $selected $query_cond" );
				$limit = 10; // number of records per page
				$offset = ( $pagenum - 1 ) * $limit;
				$num_of_pages = ceil( $strxs_tot / $limit );
				/* Pagination Params */

				$strxs = $wpdb->get_results( "SELECT * FROM $wpdb->prefix" . "stransactions WHERE merchant_id = $selected $query_cond ORDER BY id DESC LIMIT $offset, $limit" );
				?>
				<h3 class="spidanet_heading"><?php _e("Earnings", "woocommerce-yfo"); ?></h3>
				<table class="form-table wp-list-table widefat fixed spidanet_summary">
					<thead>
						<tr>
							<th><?php _e("Total Earnings", "woocommerce-yfo"); ?><p class="description"><?php _e("PayPal", "woocommerce-yfo"); ?></p></th>
							<th><?php _e("Total Commission", "woocommerce-yfo"); ?><p class="description"><?php _e("Online", "woocommerce-yfo"); ?></p></th>
							<th><?php _e("Total Commission", "woocommerce-yfo"); ?><p class="description"><?php _e("Cash", "woocommerce-yfo"); ?></p></th>
							<th><?php _e("Total Paid", "woocommerce-yfo"); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="spidanet_credit"><?php echo wc_price( $strx_tot ); ?></td>
							<td class="spidanet_credit"><?php echo wc_price( $strx_comm_tot ); ?></td>
							<td class="spidanet_credit"><?php echo wc_price( $strx_cod_comm_tot ); ?></td>
							<td class="spidanet_debit"><?php echo wc_price( $mtrx_tot_released ); ?></td>
						</tr>
					</tbody>
				</table>
				<!-- <p><?php #echo "<strong>" . __("Spidanet Total Earned Amount(Online)", "woocommerce-yfo") . " : " . wc_price( $strx_tot ) . "</strong>"; ?></p>
				<p><?php #echo "<strong>" . __("Spidanet Total Earned Commission Amount(Online)", "woocommerce-yfo") . " : " . wc_price( $strx_comm_tot ) . "</strong>"; ?></p>
				<p><?php #echo "<strong>" . __("Spidanet Total Earned Commission Amount(Cash)", "woocommerce-yfo") . " : " . wc_price( $strx_cod_comm_tot ) . "</strong>"; ?></p>
				<p><?php #echo "<strong>" . __("Merchant Total Released Amount", "woocommerce-yfo") . " : " . wc_price( $mtrx_tot_released ) . "</strong>"; ?></p> -->
				<!-- <p><?php #echo "<strong>" . __("Merchant Pending Amount", "woocommerce-yfo") . " : " . wc_price( $mtrx_tot_release ) . "</strong>"; ?></p>
				<p><?php #echo "<strong>" . __("Spidanet Pending Commission Amount At Merchant", "woocommerce-yfo") . " : " . wc_price( $strx_cod_pending_comm_tot ) . "</strong>"; ?></p>
				<p><?php #echo "<strong>" . __("Total Merchant Pending Amount", "woocommerce-yfo") . " : " . wc_price( $mtrx_tot_release-$strx_cod_pending_comm_tot ) . "</strong>"; ?></p> -->
				<div id="spidanet-pay-form" title="<?php _e("Send Money", "spidanet-yfo"); ?>">
				  	<form>
				    	<fieldset>
				      		<textarea rows="4" cols="30" id="send_amt_desc" placeholder="Amount Description" class="text ui-widget-content ui-corner-all"></textarea>
				      		<!-- Allow form submission with keyboard without duplicating the dialog button -->
				      		<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
				    	</fieldset>
				  	</form>
				</div>
				<h3 class="spidanet_heading"><?php _e("Send Money", "woocommerce-yfo"); ?></h3>
				<?php $mtrnx_release_amt = number_format($mtrx_tot_release-$strx_cod_pending_comm_tot, 2); ?>
				<table class="form-table wp-list-table widefat fixed spidanet_summary">
					<thead>
						<tr>
							<th><?php _e("Pending Merchant", "woocommerce-yfo"); ?></th>
							<th><?php _e("Pending Commission", "woocommerce-yfo"); ?><p class="description"><?php _e("Cash at merchant", "woocommerce-yfo"); ?></p></th>
							<th><?php _e("Total Earnings", "woocommerce-yfo"); ?></th>
							<th><?php _e("Total Pending Merchant", "woocommerce-yfo"); ?><p class="description"><?php _e("Deduct pending commission", "woocommerce-yfo"); ?></p></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="spidanet_debit"><?php echo wc_price( $mtrx_tot_release ); ?><sup>*</sup></td>
							<td class="spidanet_credit"><?php echo wc_price( $strx_cod_pending_comm_tot ); ?><sup>*</sup></td>
							<td class="spidanet_credit"><?php echo wc_price( $mtrx_tot_release_comm + $strx_cod_pending_comm_tot ); ?><sup>*</sup></td>
							<td class="spidanet_debit"><?php echo wc_price( $mtrnx_release_amt ); ?><sup>*</sup>
							<?php if( $mtrnx_release_amt > 0 ) { ?>
								<input type="hidden" id="mtxa" value="<?php echo $mtrnx_release_amt; ?>">
								<input type="hidden" id="mid" value="<?php echo $selected; ?>">
								<?php wp_nonce_field( 'spidanet-merchant-pay' ); ?>
								<input type="button" class="" id="spidanet_pay_popup" value="Pay" /"><span class="spinner"></span></td>
							<?php } ?>
						</tr>
					</tbody>
				</table>
				<p class="description"><sup>*</sup><?php _e("Amounts are calculated based on customer transactions before 24 hours", "woocommerce-yfo"); ?></p>
				<form method="post" action="<?php echo admin_url('options-general.php?page=spidanet-transactions&restaurant='.$selected); ?>">
					<?php wp_nonce_field( 'spidanet-date-fltr' ); ?>
					<table class="form-table">
						<tr>
							<td>
								<label><strong><?php _e( 'Filters', 'spidanet-yfo' ); ?></strong></label>
							</td>
							<td><input type="text" id="trnx_from_dt" name="trnx_from_dt" readonly="readonly"><span class="sep"><?php _e('To', 'woocommerce-yfo'); ?></span><input type="text" id="trnx_to_dt" name="trnx_to_dt" readonly="readonly"><span class="sep"></span><select name="trnx_type"><option value=""><?php _e('Order Type', 'woocommerce-yfo'); ?></option><option value="pickup"><?php _e('Pickup', 'woocommerce-yfo'); ?></option><option value="delivery"><?php _e('Delivery', 'woocommerce-yfo'); ?></option></select><span class="sep"></span><select name="trnx_pay_mode"><option  value=""><?php _e('Payment Mode', 'woocommerce-yfo'); ?></option><option value="online"><?php _e('Online', 'woocommerce-yfo'); ?></option><option value="cash"><?php _e('Cash', 'woocommerce-yfo'); ?></option></select><span class="sep"></span><select name="strnx_type"><option  value=""><?php _e('Entry', 'woocommerce-yfo'); ?></option><option value="credit"><?php _e('Credit', 'woocommerce-yfo'); ?></option><option value="debit"><?php _e('Debit', 'woocommerce-yfo'); ?></option></select><span class="sep"></span><input type="submit" class="button-primary" name="spidanet_dt_fltr" value="Go" /"><span class="sep"></span></td>
						</tr>
					</table>
				</form>
				<div class="show_fltrs_wrap"><?php echo $show_fltrs_label . " " . $show_dt_fltr ." ". $show_type_fltr . " " . $show_mode_fltr . " " . $show_strnxtype_fltr; ?></div>
				<div id="spidanet-tabs">
					<ul>
						<li><a href="#spidanet-1"><?php _e('Transactions', 'spidanet-yfo'); ?></a></li>
						<!-- <li><a href="#spidanet-2"><?php #_e('Cash Payments', 'spidanet-yfo'); ?></a></li> -->
					</ul>
					<div id="spidanet-1">
						<table class="strnx form-table wp-list-table widefat fixed">
							<thead>
								<tr><td><?php _e( 'Gross Total', 'woocommerce-yfo' ); ?> (<?php echo get_woocommerce_currency(); ?>)</td><td><?php _e( 'Commission', 'woocommerce-yfo' ); ?></td><td><?php _e( 'Merchant Total', 'woocommerce-yfo' ); ?></td><td><?php _e( 'Delivery Total', 'woocommerce-yfo' ); ?></td><td><?php _e( 'Fee', 'woocommerce-yfo' ); ?></td><td><?php _e( 'Description', 'woocommerce-yfo' ); ?></td><td><?php _e( 'Date', 'woocommerce-yfo' ); ?></td></tr>
							</thead>
							<tbody>
								<?php
								if( !empty($strxs) ) {
									foreach( $strxs as $strx ) {
										if( $strx->status == 0 || $strx->status == 1 || $strx->status == 3 || $strx->status == 4 ) {
											$order_type = ($strx->order_type) ? "spidanet_" . $strx->order_type : "spidanet_pending";
											echo "<tr class='" . $order_type . "'><td>" . $strx->gross_total . "</td><td>" . $strx->commission . "</td><td>" . $strx->merchant_total . "</td><td>" . $strx->delivery_total . "</td><td>" . $strx->fee . "</td><td>" . $strx->desc . "</td><td>" . $strx->date . "</td></tr>";
										}
										else
											echo "<tr class='spidanet_debit_row'><td colspan='5'><strong>" . __( 'Paid', 'woocommerce-yfo' ) . ": " . wc_price($strx->merchant_total) . "</strong></td><td>" . $strx->desc
											 . "</td><td>" . $strx->date . "</td></tr>";
									}
								} else {
									echo "<tr><td colspan='7'>".__("No records found.", "woocommerce-yfo")."</td></tr>";
								}
								?>
							</tbody>
						</table>
						<?php
						$page_links = paginate_links( array(
						    'base' => add_query_arg( 'pagenum', '%#%' ),
						    'format' => '',
						    'prev_text' => __( '&laquo;', 'woocommerce-yfo' ),
						    'next_text' => __( '&raquo;', 'woocommerce-yfo' ),
						    'total' => $num_of_pages,
						    'current' => $pagenum
						) );
						if ( $page_links ) {
						    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
						}
						?>
					</div> <!-- /#spidanet-1 -->
				</div> <!-- /#spidanet-tabs -->
			</div> <!-- /.tool-box -->
		<?php
		}
	 } else {
		echo '<div class="notice-error – error"><p>' . __( 'No subscriber available.', 'spidanet-yfo' ) . '</p></div>';
	} ?>
	</div>
	</div> <!-- /.wrap -->
	<?php
}

/**
 * This function create a dropdown of all subscribed members.
 */
function spidanet_get_restaurant_dropdown() {
	global $spidanet_restaurants;
	if( !empty($spidanet_restaurants) ) {
		$otpt = '<select id="restaurant_list">';
		$otpt .= '<option>' . __('Select Subscriber', 'spidanet-yfo') . '</option>';
		$selected = ( isset($_GET['restaurant']) ) ? $_GET['restaurant'] : "";
		foreach($spidanet_restaurants as $restaurant) {
			if( $selected && $selected == $restaurant['id'] )
				$otpt .= "<option value='" . $restaurant['id'] . "' selected>" . $restaurant['name'] . "</option>";
			else
				$otpt .= "<option value='" . $restaurant['id'] . "'>" . $restaurant['name'] . "</option>";
		}
		$otpt .= '</select>';
		return $otpt;
	} else {
		return false;
	}
}

/**
 * Send money to merchant AJAX callback
 */
function spidanet_send_merchant_amt_cb() {
	check_ajax_referer( 'spidanet-merchant-pay', 'sec_inf' );
	$merchant_id = intval( $_POST['mid'] );
	$merchant_amt = floatval( $_POST['tot_amt'] );
	$desc = sanitize_text_field( $_POST['amt_desc'] );
	$yesterday_date = date( 'Y-m-d H:i:s', strtotime( '-1 day', current_time('timestamp', 0) ) );
	$curr_time = current_time( 'mysql' );
	if( !empty($merchant_id) && !empty($merchant_amt) ) {
		global $wpdb;
		global $spidanet_restaurants;
		// Get selected merchant database details
		foreach($spidanet_restaurants as $restaurant) {
			if( $merchant_id == $restaurant['id'] ) {
				$merchant_db_name = $restaurant['db_name'];
				$merchant_db_user = $restaurant['db_user'];
				$merchant_db_pass = $restaurant['db_pass'];
				$merchant_db_host = $restaurant['db_host'];
				$merchant_email = $restaurant['email'];
			}
		}
		// Add transaction into merchant and spidanet database
		$merchant_con = new mysqli($merchant_db_host, $merchant_db_user, $merchant_db_pass, $merchant_db_name);
		if( !$merchant_con->connect_error ) {
			$merchant_con->query("INSERT INTO yfo_mtransactions(`amount`, `desc`, `date`, `status`) VALUES ($merchant_amt, '$desc', '$curr_time', 2)");
			$merchant_con->query("UPDATE yfo_mtransactions SET status = 1 WHERE date < '$yesterday_date' AND status = 0");
			$merchant_con->query("UPDATE yfo_mtransactions SET status = 4 WHERE date < '$yesterday_date' AND status = 3");
			$merchant_con->close();
			// Add spidanet transaction
			$table_name = $wpdb->prefix . 'stransactions';
			$wpdb->insert(
				$table_name,
				array(
					'merchant_id' => $merchant_id,
					'merchant_total' => $merchant_amt,
					'desc' => $desc,
					'date' => $curr_time,
					'status' => 2
				),
				array( 
					'%d',
					'%f',
					'%s',
					'%s',
					'%d'
				)
			);
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET status = %d WHERE date < %s AND status = %d", 1, $yesterday_date, 0) );
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET status = %d WHERE date < %s AND status = %d", 4, $yesterday_date, 3) );
			/* Merchant Email Alert */
			$subject = __('Amount Received', 'spidanet-yfo');
			$body = "<p>".__( 'Hello', 'spidanet-yfo' )."</p>";
			$body .= "<p>".__( 'Your pending amount has been released by the Spidanet. You will receive in next couple of working days.', 'spidanet-yfo' )."</p>";
			$body .= "<br><br><br><p>".__( 'Best regards', 'spidanet-yfo' ).",</p>";
			$body .= "<p>".__( 'Team Spidanet', 'spidanet-yfo' )."</p>";
			$body .= "<p style='font-size:9pt;font-style:italic;'>".__( 'This is a system generated email, please do not reply.', 'spidanet-yfo' )."</p>";
			$headers = array('Content-Type: text/html; charset=UTF-8');
			wp_mail( $merchant_email, $subject, $body, $headers );
			wp_mail( "sapangupta@techinfini.com", $subject, $body, $headers );
			echo json_encode( array("msg" => __( 'Authorized', 'spidanet-yfo')) );
		} else {
			echo json_encode( array("msg" => __( 'Please contact administrator', 'spidanet-yfo')) );
		}
	} else {
		echo json_encode( array("msg" => __( 'Invalid Request', 'spidanet-yfo')) );
	}
	wp_die();
}