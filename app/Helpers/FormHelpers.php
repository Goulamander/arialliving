<?php

namespace App\Helpers;

use App\Models\RecurringEvent as RecurringEvent;
use Carbon\Carbon;


class FormHelper {

	/**
	 * Forms: Get Field Validation
	 */
	public static function _getFieldsValidation($validations) {

		if(!$validations) {
			return "";
		}
		
		$validations = explode('|', $validations);

		if(!$validations) {
			return "";
		}

		$validation_str = "";

		foreach($validations as $validation) {

			switch($validation) 
			{
				// required
				case "required":
					$validation_str .= " required";
					break;
				
				// Max value
				case strpos($validation, 'maxvalue'):
					$validation_str .= ' max="'.str_replace('maxvalue:', '', trim($validation)).'"';
					break;

				// Min value
				case strpos($validation, 'minvalue'):
					$validation_str .= ' min="'.str_replace('minvalue:', '', trim($validation)).'"';
					break;

				// Max length
				case strpos($validation, 'max'):
					$validation_str .= ' maxlength="'.str_replace('max:', '', trim($validation)).'"';
					break;

				// Min length
				case strpos($validation, 'min'):
					$validation_str .= ' minlength="'.str_replace('min:', '', trim($validation)).'"';
					break;

				// Digits [num]
				case strpos($validation, 'digits'):
					$digits = str_replace('digits:', '', trim($validation));
					$validation_str .= ' data-parsley-length="['.$digits.','.$digits.']" data-parsley-length-message="This value should be 4 digits long"';
					break;
			}
		}
		return $validation_str;
	}


	/**
	 * Forms: Get Field
	 * 
	 * @param str $key - the field key 
	 * @param mixed $field - the field object or string
	 * @param mixed $data - data source to fill_in
	 */
	public static function getFields($key, $field, $data = null) {

		if($data) {
			$default_data = $data;
			$data = $data->toArray();
		}

		if( gettype($field) == "string") {

			switch($field) 
			{
				case "row_start":
					return '<div class="row">';
					break;

				case "row_end":
				case "div_end":
					return '</div>';
					break;

				case strpos($field, "title:"):
					return '<h4 class="mt-4">'.str_replace("title:", "", $field).'</h4>';
					break;

				default:
					$a = explode('|class:', $field);
					if($a[0] == 'div_start') {
						return '<div class="'.$a[1].'">';
					}
			}
		}

		$available_types = [
			// input
			'text', 
			'email',
			'number',
			'password',
			'hidden',
			// other
			'select', 
			'radio', 
			'checkbox', 
			'multi-select', 
			'textarea',
			'color',
			// Custom types
			'date',
			'datetime',
			'time',
			'time24',
			'office-hours',
			'repeating-options',
			'add-more-fee',
		];

		// some basic validation...
		// dd($field);
		if( !in_array($field->type ?? '', $available_types) ) {
			return '';
		}

		// Read the hidden_on Option
		if( isset($field->hidden_on) ) {

			if($field->hidden_on == 'create' && !$data) {
				return;
			}
			if($field->hidden_on == 'edit' && $data) {
				return;
			}
		}


		// Create the Object key. This is used to locate the field's value in the nested data array.
		$object_key = isset($field->object_key) ? $field->object_key : $key;

		/**
		 * todo: tooltips, 
		*/

		// Attach data attributes
		$data_attribute_list = "";

		if( isset($field->data) && $field->data) 
		{
			foreach($field->data as $k => $v) {
				$data_attribute_list .= " data-{$k}='{$v}'";
			}
		}

		// Attach data conditions
		if( isset($field->conditions) && $field->conditions) {
			$data_attribute_list = $data_attribute_list . " data-conditions=".utf8_encode(json_encode($field->conditions));
		}


		// other attributes
		$other_attribute_list = "";

		if( isset($field->other_attr) && $field->other_attr) 
		{
			foreach($field->other_attr as $k => $v) {
				$other_attribute_list .= " {$k}='{$v}'";
			}
		}
		
		// get the field by its type
		switch($field->type) 
		{
			case 'text':
			case 'email':
			case 'number':
			case 'color':
			case 'password':
			case 'hidden':

				// grab the default value that is passed along in the field object
				$value = isset($field->value) ? $field->value : "";
				
				if($data) {
					$value = array_get($data, $object_key, '');
				}
				$value = isset($value) ? " value='{$value}'" : "";

				// placeholder
				$placeholder = isset($field->placeholder) ? " placeholder='{$field->placeholder}'" : "";
				// generate the input
				$input = "<input type='{$field->type}' name='{$key}' class='form-control {$field->class->input}'{$data_attribute_list}{$other_attribute_list}{$value}{$placeholder}".self::_getFieldsValidation($field->validation).">";
				break;


			// Date / Time Pickers
			case 'date':
			case 'datetime':
			case 'time24':
			case 'time':
				// grab the default value that is passed along in the field object
				$value = isset($field->value) ? $field->value : "";
				
				if($data) {
					$value = array_get($data, $object_key, '');
				}
				$value = $value ? " value='{$value}'" : "";

				// generate the input
				$input = "<input type='text' name='{$key}' class='form-control {$field->type}Pickr {$field->class->input}'{$data_attribute_list}{$value}".self::_getFieldsValidation($field->validation).">";
				break;

			// select dropdown
			case 'select':
			case 'multi-select':

				$is_multiple = $field->type == 'multi-select' ? ' multiple' : '';

				$options = array_map(function ($k, $v) use($field, $data, $object_key) {
					// default
					$selected = isset($field->value) && $field->value == $k ? ' selected' : '';
					// from data object
					if($data) {
						$selected = array_get($data, $object_key, '') == $k ? ' selected' : '';
						if($field->type == 'multi-select') {
							$selected = is_int(array_search($k, $data[$object_key])) ? ' selected' : '';
						}
					}
					return "<option value='{$k}'{$selected}>{$v}</option>";
				}, array_keys((array) $field->options), (array) $field->options);
			

				// Pre-select option for Select2 fields (with remote sources)
				if(!$options && $data) {

					// keys provided in the form def. array
					if(isset($field->s2_selected_opt_keys) && $field->s2_selected_opt_keys) {

						$object_key = explode('.', $object_key);

						$object_group = $object_key[0];
						$object_id = $object_key[1];

						$selected_options = [];
						
						// When the group is an array (buildings)
						if( gettype(array_get($data, $object_group, '')) == 'array') { 

							// loop all selected items in the object group (buildings)
							$group_length = count(array_get($data, $object_group, ''));

							for($i = 1; $i <= $group_length; $i++) {
								
								$is_iteration = true;

								// Create the array ID = (object_group.iteration.object_id) => (buildings.0.id)
								$id = array_get($data, $object_group.'.'.($i-1).'.'.$object_id, '');

								// The item ID could not be found with iteration number so try to find item on a level up without the iteration
								if(!$id) {
									$id = array_get($data, $object_group.'.'.$object_id, '');
									$is_iteration = false;
								}

								foreach($field->s2_selected_opt_keys as $k => $v) {
									// Array Values = (object_group.iteration.key) => (buildings.0.name)
									$selected_options[$id][$k] = array_get($data, self::_createKeyPath($i-1, $v, $is_iteration), '');
								}
							}
						}
						// the group is a string
						else {
							foreach($field->s2_selected_opt_keys as $k => $v) {
								// Create the array ID = (object_group.object_id) => (building.id)
								$id = array_get($data, $object_key, '');
								// Array Values = (object_group.key) => (buildings.name)
								$selected_options[$id][$k] = array_get($data, $v, '');
							}
						}

						// convert to html
						$options = [];
						foreach($selected_options as $id => $values) {
							$options[] = "<option value='{$id}' data-selected='".base64_encode(json_encode($values))."'></option>";
						}

					}
				}

				$input = "<select name='{$key}' class='form-control {$field->class->input}' {$data_attribute_list}".self::_getFieldsValidation($field->validation)."{$is_multiple}>".implode('',$options)."</select>";
				break;


			// radio
			case 'radio':

				$input = "";
				$n = 1;

				// data object
				if($data) {
					$data_value = array_get($data, $object_key, '');
				}

				foreach($field->options as $opt_value => $opt_label) {
					// set value from default
					$checked = isset($field->value) && $field->value == $opt_value ? ' checked' : '';
					// set value form data object
					if($data) {
						$checked = $opt_value == $data_value ? ' checked' : '';
					}
					$unique_key = str_random(8);

					$input .= "
					<div class='radio'>
						<input type='radio' id='".$unique_key."_{$key}' name='{$key}' class='{$field->class->input}' value='{$opt_value}'{$checked}>
						<label for='".$unique_key."_{$key}'>{$opt_label}</label>
					</div>";
					$n++;
				}
				break;


			// checkbox	
			case 'checkbox':

				$input = "";
				$n = 1;

				// data object
				if($data) {
					$data_value = array_get($data, $object_key, '');
				}

				foreach($field->options as $opt_value => $opt_label) {
					// set value from default
					$checked = isset($field->value) && $field->value == $opt_value ? ' checked' : '';
					// set value form data object
					if($data) {
						if(gettype($data_value) == 'array') {
							$checked = in_array($opt_value, $data_value) ? ' checked' : '';
						}
						$checked = $opt_value == $data_value ? ' checked' : '';
					}
					$input .= "
					<div class='checkbox'>
						<input type='checkbox' id='{$n}_{$opt_value}_{$key}' name='{$key}' class='{$field->class->input}' value='{$opt_value}' {$data_attribute_list}{$checked}>
						<label for='{$n}_{$opt_value}_{$key}'>{$opt_label}</label>
					</div>";
					// $input .= "
					// <div class='checkbox'>
					// 	<input type='checkbox' id='{$n}_{$key}' name='{$key}' class='{$field->class->input}' value='{$opt_value}' {$data_attribute_list}{$checked}>
					// 	<label for='{$n}_{$key}'>{$opt_label}</label>
					// </div>";
					$n++;
				}
				break;


			// textarea
			case 'textarea':
				$value = isset($field->value) ? $field->value : "";
				if($data) {
					$value = array_get($data, $object_key, '');
				}

				$placeholder = isset($field->placeholder) ? " placeholder='{$field->placeholder}'" : "";
				$input = "<textarea name='{$key}' rows='4' class='form-control pl-0 pr-0 {$field->class->input}'{$data_attribute_list}{$placeholder}".self::_getFieldsValidation($field->validation).">{$value}</textarea>";
				break;

			// Build office Hours	
			case 'office-hours':
				if($data) {
					$data = array_get($data, $object_key, '');
				}
				return self::officeHours($field, $data, $key);
			// Build Repeating Options
			case 'repeating-options':
				return self::repeatingOptions($field, $data && isset($data['recurring']) ? (object) $data['recurring'] : null, $data && isset($data['event']) ? (object) $data['event'] : null);

			// Build add more fee
			case 'add-more-fee':
				$defaultData = null;
				if(isset($default_data) && $default_data->bookableItemFees()->exists()) {
					$defaultData = $default_data->bookableItemFees()->get();
				}
				return self::addMoreFee($field, $defaultData);
		}

		if( !$input ) {
			return '';
		}

		$output = "
			<div class='form-group _{$key} {$field->class->group}'>";
			if($field->label) {
				$output .= "
				<label class='control-label'>{$field->label}</label>";
			}
			$output .= "
				{$input}";
				if( isset($field->description) && $field->description ) {
					$output .= "<small>{$field->description}</small>";	
				}
			$output .= "</div>";

		return trim(str_replace("'", "\"", $output));
	}


	/**
	 * Add the iteration into the key-path to get values in array
	 * 
	 * @param $i - iteration number
	 * @param $key - array_key
	 * @param $is_iteration - include the iteration number in a key? 
	 * @return array_key
	 */
	private static function _createKeyPath($i, $key, $is_iteration = true) {

		$key_parts = explode('.', $key);

		if($is_iteration) {
			return $key_parts[0] . '.' . $i . '.' . $key_parts[1];
		}
		return $key_parts[0] . '.' . $key_parts[1];
	}



	/***********************************************************************/
	/******************************   Templates   **************************/
	/***********************************************************************/

	
	/**
	 * Line items template
	 * 
	 * @param Object $field
	 * @param Object $data
	 * 
	 * @return HTML 
	 */
	public static function lineItems($field, $data) {

		$template = "
			<div class='row'>
				<ul class='line-items col-12'>";

					if($data) {
	
						foreach($data as $key => $itm) {
							
							$itm = (object) $itm;
							$num = $key + 1;

							$template .= "
							<li class='line-item'>
								<span class='remove_line_item_btn'><i class='material-icons'>close</i></span>
								<h4 class='num'>{$num}</h4>
								<input type='hidden' name='{$num}_id' value='0'/>
								<div class='row'>
									<div class='col-3'>
										<div class='inline-uploader' data-name='{$num}_thumb'></div>
									</div>
									<div class='col-7'>
										<div class='row'>
											<div class='col-sm-8'>
												<div class='form-group'>
													<input type='text' name='{$num}_name' class='form-control' placeholder='Name' maxlength='255' value='{$itm->name}' required>
												</div>
											</div>
											<div class='col-sm-4'>
												<div class='form-group'>
													<input type='number' name='{$num}_price' class='form-control' placeholder='Price' value='{$itm->price}' required>
												</div>
											</div>
										</div>
										<div class='form-group'>
											<textarea name='{$num}_desc' class='form-control' placeholder='Description' maxlength='600'>{$itm->desc}</textarea>
										</div>
									</div>
								</div>
							</li>";
						}
					}
					else
					{
						$template .= "
						<li class='line-item'>
							<h4 class='num'>1</h4>
							<input type='hidden' name='1_id' value='0'/>
							<div class='row'>
								<div class='col-3'>
									<div class='inline-uploader' data-name='1_thumb'></div>
								</div>
								<div class='col-9'>
									<div class='row'>
										<div class='col-sm-8'>
											<div class='form-group'>
												<input type='text' name='1_name' class='form-control' placeholder='Name' maxlength='255' required>
											</div>
										</div>
										<div class='col-sm-4'>
											<div class='form-group'>
												<input type='number' name='1_price' class='form-control' placeholder='Price' required>
											</div>
										</div>
									</div>
									<div class='form-group'>
										<textarea name='1_desc' class='form-control' placeholder='Description' maxlength='600'></textarea>
									</div>
								</div>
							</div>
						</li>";
					}
					$template .= "
					<li class='new'>
						<button type='button' name='add_line_item_btn' class='btn btn-sm'>+ New item</button>
					</li>
				</ul>
			</div>";
		
		return trim(str_replace("'", "\"", $template));
	}


	/**
	 * Office Hours template
	 * 
	 * @param Object $field
	 * @return HTML 
	 */
	public static function officeHours($field, $data, $key = '') {

		$hours = [
			// am
			"6:00 am",
			"6:30 am",
			"7:00 am",
			"7:30 am",
			"8:00 am",
			"8:30 am",
			"9:00 am",
			"9:30 am",
			"10:00 am",
			"10:30 am",
			"11:00 am",
			"11:30 am",
			"12:00 am",
			// pm
			"12:30 pm",
			"1:00 pm",
			"1:30 pm",
			"2:00 pm",
			"2:30 pm",
			"3:00 pm",
			"3:30 pm",
			"4:00 pm",
			"4:30 pm",
			"5:00 pm",
			"5:30 pm",
			"6:00 pm",
			"6:30 pm",
			"7:00 pm",
			"7:30 pm",
			"8:00 pm",
			"8:30 pm",
			"9:00 pm",
			"9:30 pm",
			"10:00 pm",
			"10:30 pm",
			"11:00 pm",
			"11:30 pm"
		];

		$days = [
			"Monday",
			"Tuesday",
			"Wednesday",
			"Thursday",
			"Friday",
			"Saturday",
			"Sunday"
		];

		$template = "
		<div class='form-group _{$key} {$field->class->group}'>
			<h4>{$field->label}</h4>
			<div class='row'>
				<ul class='office-hours col-12'>";
				foreach($days as $day) 
				{
					
					$rand = str_random(6);

					// default state
					$checked = in_array($day, ["Saturday", "Sunday"]) ? '' : ' checked';

					// load the stored value
					if($data && $data[$day]) {

						$checked = (int) $data[$day]['status'] == 1 ? ' checked' : '';
					}

					$is_hidden = $checked ? '' : ' hidden';

					$template .= "
						<li class='--item ".strtolower($day)."'>
							<div class='row'>
								<div class='col-3 align-self-center'>
									<h4>{$day}</h4>
								</div>
								<div class='col-2 align-self-center'>
									<div class='form-group'>
										<div class='custom-control custom-switch'>
											<input type='checkbox' id='{$rand}_status' name='oh__{$day}_status' value='1' class='office-hours-switch custom-control-input' {$checked}>
											<label class='custom-control-label' for='{$rand}_status'>Open</label>
										</div>
									</div>
								</div>
								<div class='col-3 from{$is_hidden}'>
									<div class='form-group'>
										<select name='oh__{$day}_from' class='form-control' required>
											<option disabled>From</option>";
											foreach($hours as $hour) {
												// default 9:00 am
												$selected = ($hour == '9:00 am') ? ' selected' : '';
												// load the stored value
												if($data && $data[$day]) {
													$selected = $data[$day]['from'] == $hour ? ' selected' : '';
												}
												$template .= "<option value='{$hour}'{$selected}>{$hour}</option>";
											}
										$template .= "
										</select>
									</div>
								</div>
								<div class='col-3 to{$is_hidden}'>
									<div class='form-group'>
										<select name='oh__{$day}_to' class='form-control'>
											<option disabled>To</option>";
											foreach($hours as $hour) {
												// default 5:00 pm
												$selected = ($hour == '5:00 pm') ? ' selected' : '';
												// load the stored value
												if($data && $data[$day]) {
													$selected = $data[$day]['to'] == $hour ? ' selected' : '';
												}
												$template .= "<option value='{$hour}'{$selected}>{$hour}</option>";
											}
										$template .= "
										</select>
									</div>
								</div>
							</div>
						</li>";
				}
				$template .= "
				</ul>
			</div>
		</div>";
		
		return trim(str_replace("'", "\"", $template));
	}

	/**
	 * Add more fee template
	 * 
	 * @param Object $field
	 * @return HTML 
	 */
	public static function addMoreFee($field, $data) {
		$template = '
			<div class="col-12 add-more-fee">
				<label class="control-label">Cleaning Fee</label>
				<div class="add-more-fee__items">
		';

		if ($data && count($data) > 0) {
			$template .= '<input type="hidden" value="'.count($data).'" id="clearing_fee_count"/>';
			foreach ($data as $key => $item) {
				$template .= '
					<div class="row mb-2 add-more-fee__item">
						<div class="col">
							<input type="text" name="clearing_fee['.$key.'].name" class="form-control" value="'.$item->name.'" placeholder="Name">
						</div>
						<div class="col">
							<input type="number" name="clearing_fee['.$key.'].fee" min="0" class="form-control" value="'.$item->fee.'" placeholder="Fee">
						</div>
						<div class="col-1">
							<button type="button" class="btn btn-primary p-1 remove"><i class="material-icons">remove</i></button>
						</div>
					</div>
				';
			}
		} else {
			$template .= '<input type="hidden" value="0" id="clearing_fee_count"/>';
			// $template .= '<input type="hidden" value="1" id="clearing_fee_count"/>';
			// $template .= '
			// 	<div class="row mb-2 add-more-fee__item">
			// 		<div class="col">
			// 			<input type="text" name="clearing_fee[0].name" class="form-control" placeholder="Name" value="No Cleaning">
			// 		</div>
			// 		<div class="col">
			// 			<input type="number" name="clearing_fee[0].fee" min="0" class="form-control" placeholder="Fee" value="0">
			// 		</div>
			// 	</div>
			// 	';
		}
			

		$template .='
				</div>
				<button type="button" class="btn btn-primary p-1 add-more"><i class="material-icons">add</i></button>
			</div>
		';
		
		return trim(str_replace("'", "\"", $template));
	}


	/**
	 * Repeating Event template
	 * @param Object $field
	 * @param Object $item -> event->recurring as item
	 * @return HTML 
	 */
	public static function repeatingOptions($field, $item = null, $event = null) {

		$active = $item ? 'active' : '';

		$repeat_next = $item && $item->repeat_next ? $item->repeat_next : '';

		$repeat_every_value = $item->repeat_every ?? 1;
		$is_all_day = $event && $event->all_day ? 1 : 0;
		$is_all_day_checked = $event && $event->all_day ? 'checked' : '';
		$recurring_event_from = $event ? $event->event_from : '';
		$recurring_event_to = $event ? $event->event_to : '';

		$template = "
		<div class='form-group recurring_options _recurring_group {$active}'>
			<input type='hidden' name='repeat_next' value='{$repeat_next}'>
			<div class='row'>
				<div class='col-6'>
					<label class='control-label'>Repeat Start</label>
					<div class='form-group'>";
						$repeat_start = $item && $item->repeat_start ? $item->repeat_start : '';
						$template .= "<input type='text' name='repeat_start' class='form-control datePickr' value='{$repeat_start}' data-min-date='today'>";
					$template .= "
					</div>
				</div>
				<div class='col-6'>
					<label class='control-label'>Repeat End</label>
					<div class='form-group'>";
						$repeat_end = $item && $item->repeat_end ? $item->repeat_end : '';
						$template .= "<input type='text' name='repeat_end' class='form-control datePickr' value='{$repeat_end}' placeholder='Never'>";
					$template .= "
					</div>
				</div>
			</div>
			<div class='row'>
				<div class='form-group _event_all_day col-6 align-self-center'>
					<label class='control-label'></label>
					<div class='checkbox'>
						<input type='checkbox' id='rec_event_all_day' name='recurring_all_day' class='' value='{$is_all_day}' {$is_all_day_checked} data-conditions=[{\"fields\":\"event_recurring_from|event_recurring_to\",\"if_value\":\"null\"}]>
						<label for='rec_event_all_day'>All day Event</label>
					</div>
				</div>

				<div class='form-group _event_recurring_from col-3'>
					<label class='control-label'>From</label>
					<input type='text' name='recurring_event_from' class='form-control timePickr' value='{$recurring_event_from}'>
				</div>
				<div class='form-group _event_recurring_to col-3'>
					<label class='control-label'>To</label>
					<input type='text' name='recurring_event_to' class='form-control timePickr' value='{$recurring_event_to}'>
				</div>
			</div>

			<div class='row'>
				<div class='col-6'>
					<label class='control-label'>Repeat every</label>
					<div class='row'>
						<div class='col-5'>
							<input type='number' name='repeat_every' class='form-control' value='{$repeat_every_value}' min='1' max='100'>
						</div>
						<div class='col-7'>
							<select name='frequency' class='form-control'>";
							foreach (RecurringEvent::$frequency_array as $opt_id => $opt_name) 
							{
								if($item && $item->frequency == $opt_id) {
									$template .= "<option value='{$opt_id}' selected>{$opt_name}</option>";
								}
								else {
									$template .= "<option value='{$opt_id}'>{$opt_name}</option>";
								}
							}
							$template .= "
							</select>
						</div>
					</div>
				</div>";

				$active = ! $item || ($item && $item->frequency == 7) ? 'active' : '';

				$template .= "
				<div class='col-6 freq_option freq_7 {$active}'>
					<label class='control-label'>Repeat on</label>";

					foreach (RecurringEvent::$days_array as $opt_id => $opt_name) {
						
                        $is_primary = '';

                        if($item && $item->repeat_start) {
                            $is_primary = Carbon::parse($item->repeat_start)->dayOfWeek == $opt_id ? 'class="primary"' : '';
						}

						$checked = $item && in_array($opt_id, explode(',',$item->frequency_week_days)) ? 'checked' : '';
						
						$template .= "
						<div class='days-checkbox'>
							<input id='_week_days_{$opt_id}' type='checkbox' {$is_primary} name='frequency_week_days' value='{$opt_id}' {$checked}>
							<label for='_week_days_{$opt_id}'>".RecurringEvent::$days_array[$opt_id]."</label>
						</div>";
					}

				$template .= "
				</div>
			</div>
			<div class='row'>
				<div class='col-sm'>";
					if($item) {
						$template .= "          
						<div class='recurring_settings_preview'>
							<small>Next</small>";
							if($repeat_next) {
								$template .= "<strong>". dateFormat($item->repeat_next, '(D) d M Y') ."</strong>";
							}	
							else {
								$template .= "<strong style='color:#ca0000'>Repeating ended</strong>";
							}
						$template .= "
						</div>";
					}
					else {
						$template .= "<div class='recurring_settings_preview'></div>";
					}
				$template .= "
				</div>
			</div>
		</div>";

		return trim(str_replace("'", "\"", $template));
	}



}
