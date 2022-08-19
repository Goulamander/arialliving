<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

function csvToArray($filename = '', $delimiter = ',')
{
    if (!file_exists($filename) || !is_readable($filename))
        return false;

    $header = null;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== false)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
        {
            if (!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }

    return $data;
}

function split_full_name($name) {
	$name = trim($name);
	$last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
	$first_name = trim( preg_replace('#'.preg_quote($last_name,'#').'#', '', $name ) );
	return array($first_name, $last_name);
}

// 
function lineBreakToParagraph($string) {
	return preg_replace("/(\r\n)+|(\n|\r)+/", "</p><p>", $string);
}

// Add active to Navigation
// function setActive($path, $query = false)
// {
// 	if ($query && Request::getQueryString() == $query) {
// 		return ' class=active';
// 	} 
// 	else if ($query && Request::getQueryString() != $query) {
// 		return '';
// 	} 
// 	else {
// 		return Request::getRequestUri() == $path ? ' class="active open"' : '';
// 	}
// }

// Add active to Navigation
// function getActiveClass($path, $query = false)
// {
// 	if ($query && Request::getQueryString() == $query) {
// 		return 'active';
// 	} else if ($query && Request::getQueryString() != $query) {
// 		return '';
// 	} else {
// 		return Request::is($path) ? 'active' : '';
// 	}
// }

/**
 * Add active to Navigation
 *
 * @var string
 * @return string
 */

function isAdminEnd() {
	return strpos(Request::getRequestUri(), '/admin') !== false ? true : false;

}

function setActive($path)
{
	if (!is_array($path)) {
		return Request::getRequestUri() == $path ? ' class="active open"' : '';
	} 
	else {

		$true = 0;
		foreach ($path as $p) {
			if (strpos(Request::getRequestUri(), $p) !== false) {
				$true++;
			}
		}
		return ($true > 0) ? ' class="active open"' : '';
	}
}


/**
 * Create initials
 *
 * @var array
 */

function initials($name)
{
	if( ! trim($name) ) return '';

	$words = explode(" ", trim($name));
	$remove_chars = ['(',')','-', ':'];
	$inits = '';
	$i = 0;

	foreach($words as $word) {
		$char  = strtoupper(substr($word,0,1));
		$inits.= in_array($char, $remove_chars) ? null : $char;
		$i++;
		if($i == 3) break;
	}
	return $inits;	
}


/**
 * Dropdown selection
 *
 * @var array
 */

function dropdown($name, $options, $selected, $extra = '')
{
	$html = '';
	foreach ($options as $value => $text) {
		$set_selected = '';
		if ($value == $selected) {
		$set_selected = 'selected';
		}
		$html .= '<option value="' . $value . '" ' . $set_selected . '>' . $text . '</option>';
	}
	return '<select name="' . $name . '" ' . $extra . '>' . $html . '</select>';
}


/**
 * Format Price to default Currency
 *
 * @var array
 */
function priceFormat($price, $decimals = 2)
{

	if (!$price) {
		$price = 0;
	}

	return '$' . number_format(str_replace(',', '', $price), $decimals, '.', ',');
}


/**
 * Make Alias from String
 *
 * @var array
 */
function makeAlias($string)
{
	//Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )

	$string = str_replace("39", "", $string); //escaped '

	$string = strtolower($string);
	//Strip any unwanted characters
	$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
	//Clean multiple dashes or whitespaces
	$string = preg_replace("/[\s-]+/", " ", $string);
	//Convert whitespaces and underscore to dash
	$string = preg_replace("/[\s_]/", "-", $string);

	return $string;
}



/**
 * Date Format
 *
 * @var string
 * @var string
 */
function dateFormat($date, $format = 'M d, Y')
{
	try {
		if(empty($date) || $date == '0000-00-00') return null;
		return \Carbon\Carbon::parse($date)->format($format);
	} catch(Exception $e){
		return $date;
	}
}

/**
 * Time Format
 *
 * @var string
 * @var string
 */
function timeFormat($time, $format = 'h:i a')
{
	try {
		if(empty($time) || $time == '00:00:00') return null;
		return \Carbon\Carbon::parse($time)->format($format);
	} catch(Exception $e){
		return $time;
	}
}


/**
 * Time Length
 *
 * @var string
 * @var string
 */
function timeLength($start, $end)
{
	try {
		if(empty($start) || $start == '00:00:00') return null;
		if(empty($end) || $end == '00:00:00') return null;

		return Carbon::parse($start)->diffInHours(Carbon::parse($end), false).'hrs';


	} catch(Exception $e){
		return $time;
	}
}



function formatMilliseconds($milliseconds)
{
	$seconds = $milliseconds / 1000;
	$hours = 0;
	$milliseconds = str_replace("0.", '', $seconds - floor($seconds));

	if ($seconds >= 3600) {
		$hours = floor($seconds / 3600);
	}
	$seconds = $seconds % 3600;

	return str_pad($hours, 2, '0', STR_PAD_LEFT) . gmdate(':i', $seconds);
}




/**
 * Calculate Percentage
 *
 * @var decimal
 * @var decimal
 * @return decimal
 */
function calculatePercentage($percent, $number)
{
	if (!$percent || $percent == 0) {
		return 0;
	}
	return ($percent / 100) * $number;
}



function getSetting($settings, $code)
{
	if ($settings) {
		foreach ($settings as $setting) {
		if ($setting['code'] == $code) {
			return $setting['value'];
		}

		}
	}
}


// 
function modifyHtmlToBladeCode($replace, $context)
{
	
	if( ! $replace ) $context;

	/**
	 *  $replace := string
	 */
	$replace = json_decode($replace);

	foreach ($replace as $item) {
		$context = str_replace($item->html, $item->blade, $context);
	}
	
	return $context;
}




/**
 * Get the Job ID as Number
 *
 * @return string
 */
function getBookingNumber($id) {
	if(!$id) return "-";
	return sprintf('B%04d', $id);
}



/**
 * Process Office Hours in request.
 * 
 * @param  Illuminate\Http\Request $request
 * @return {json} office_hours
 */
function _jsonOfficeHours($request) {

	$office_hours = [];

	foreach($request as $key => $val) {

		if( strpos($key, 'oh__') !== false ) {

			$key = explode('_', str_replace('oh__', '', $key));
			$day = $key[0]; $key = $key[1];

			if(gettype($val) == 'array') {
				$val = isset($val[0]) ? (int) $val[0] : 0;
			}

			$office_hours[$day][$key] = $val;
		}
	}

	return json_encode($office_hours);
}


/**
 * Convert filesize to human readable
 */
function convert_filesize($bytes, $decimals = 2){
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}


/**
 * Format the Booking dates
 */
function bookingDate($start, $end)
{
	$format = 'M d, Y';

	$start = ($start == '0000-00-00 00:00:00') ? null : $start;
	$end   = ($end   == '0000-00-00 00:00:00') ? null : $end;

	if( ! $start && ! $end ) {
		return '';
	}
	
	$start = Carbon::parse($start);
	
	try {
		$end = Carbon::parse($end);
	} 
	catch(Exception $e){
		$end = $start;
	}

	$TOD = Carbon::today();
	$TOM = Carbon::tomorrow();
	$YES = Carbon::yesterday();

	// Same day dates
	if ($start == $end) {
		return $start->format($format);
	}

	// Multi day dates
	$start_format = $format;
	$end_format = $format;

	// same year
	if ($start->year == $end->year) {
		
		$start_format = 'M d';

		// same month
		if ($start->month == $end->month) {

			$end_format = 'd, Y';
			
			// same day
			if ($start->day == $end->day) {
				return $start->format($format);
			}
		}
	}

	return $start->format($start_format) . ' - ' . $end->format($end_format);
}


function bookingTime($start, $end) {

	$format = 'h:i a';

	$start = ($start == '0000-00-00 00:00:00') ? null : $start;
	$end   = ($end   == '0000-00-00 00:00:00') ? null : $end;

	if( ! $start && ! $end ) {
		return '';
	}

	return Carbon::parse($start)->format($format).' - '.Carbon::parse($end)->format($format);
}



/**
 *
 */
function cleanHtmlToExport($str) {

	$p = [
		'#<small(.*?)data-exclude="true"(.*?)>(.*?)</small>#',
		'#<span(.*?)data-exclude="true"(.*?)>(.*?)</span>#',
	];
	$str = preg_replace($p, '', $str);

	return trim(strip_tags($str));
}



/**
 * Export DataList to CSV
 * 
 */
function exportToCSV($data, $file_name) {

	if(!$data) {
		return redirect()->back()->withErrors('No data to export');
	}

	$columns = array_keys($data[0]);

	$columns = array_map(function($key) {
		return ucwords(str_replace('_', ' ', $key));
	}, $columns);

	$rows = array_values($data);

	$handle = fopen($file_name, 'w+');
    fputcsv($handle, $columns);

    foreach($rows as $row) {
        fputcsv($handle, array_values($row));
    }

    fclose($handle);

    $headers = array(
		"Content-type" => "text/csv",
		"Content-Disposition" => "attachment; filename=".$file_name.".csv",
		"Pragma" => "no-cache",
		"Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
		"Expires" => "0"
		
    );

	return Response::download($file_name, $file_name, $headers)->deleteFileAfterSend(true);

}


/**
 * 
 */
function implodeArraysInRequest($data) {

	return array_map(function($n) {
		return gettype($n) == 'array' && count($n) <= 1 ? 
			$n ? implode(',', $n) : 0
			: $n;
	}, $data);
}


/**
 * 
 * Build the @mentions
 * 
 * @param 	object 	$Comment instance
 * @return 	string 	HTML comment body
 */
function convertMarkdown($commentObj) {

	$markdown = Markdown::convertToHtml($commentObj->comment);
	$markdown = _linkify($markdown);

	return _fixPreLineBreaks($markdown);
}


/**
 * Linkify content
 */
function _linkify($content) {
	return preg_replace(
		"~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~",
		"<a href=\"\\0\" target='_blank'>\\0</a>", 
		$content);
}

function _fixPreLineBreaks($string) {

	$a = preg_replace_callback("/<pre>(.*?)<\/pre>/s", // https://regexr.com/
		function (Array $matches) {
			foreach($matches as $match) {
				return nl2br($match); 
			}
		}, 
		$string
	);
	return $a;
}



function luminance($hexcolor, $percent)
{
  if ( strlen( $hexcolor ) < 6 ) {
    $hexcolor = $hexcolor[0] . $hexcolor[0] . $hexcolor[1] . $hexcolor[1] . $hexcolor[2] . $hexcolor[2];
  }
  $hexcolor = array_map('hexdec', str_split( str_pad( str_replace('#', '', $hexcolor), 6, '0' ), 2 ) );

  foreach ($hexcolor as $i => $color) {
    $from = $percent < 0 ? 0 : $color;
    $to = $percent < 0 ? $color : 255;
    $pvalue = ceil( ($to - $from) * $percent );
    $hexcolor[$i] = str_pad( dechex($color + $pvalue), 2, '0', STR_PAD_LEFT);
  }

  return '#' . implode($hexcolor);
}


/** 
 * Grab the first sentence only from content.
 */
function first_sentence($content) {
    $pos = strpos($content, '.');
    return substr($content, 0, $pos+1);
}
