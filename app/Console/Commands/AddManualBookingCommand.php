<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\CarbonInterval;
use Carbon\Carbon;

use App\Models\BookableItem;
use App\Models\Building;
use App\Models\Booking;

class AddManualBookingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:add-manual {path_to_file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add manual booking: {path_to_file} include(Status, Mobile, Item Category, Title, User, Building, Start, End, Is Cleaning)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path_to_file = $this->argument('path_to_file');
        $csvFile = public_path($path_to_file);
        $datas = $this->arrayFromCSV($csvFile, true);
        if (isset($datas) && count($datas) > 0) {
            $bar = $this->output->createProgressBar(count($datas));
            $bar->start();

            foreach ($datas as $key => $data) {
                if ($data['Id']) {
                    $this->update($key, $data);
                } else {
                    $this->create($key, $data);
                }

                $bar->advance();
                sleep(1);
            }
            $bar->finish();
        }
        return 0;
    }

    public function update($key, $data)
    {
        $booking = Booking::where('id', $data['Id'])->first();
        if ($booking) {
            $update_data = [
                'booking_comments'=>$data['Resident Comments']
            ];
            $booking->update($update_data);
            $this->info(' Update successfully: Booking(#' . $booking->id . ')');
        } else {
            $this->error(' Can not Update: Line(' . $key . ')');
        }

    }

    public function create($key, $data)
    {
        $save_data = [
            'user_id' => $data['User ID'],
            'start' => $data['Start'],
            'end' => $data['End'],
            'qty' => 1,
            'cleaning_required' => $data['Is Cleaning'] ? 1 : 0,
            'type' => $this->getType($data['Item Category']),
            'length_str' => $this->_getBookingLengthStr($data['Start'], $data['End']),
            'status' => $data['Status'] == 'Active' ? 1 : 0,
        ];

        //========================
        $building = Building::where('name', $data['Building'])->first();
        $save_data['building_id'] = $building->id;

        //===================
        $bookable_item = BookableItem::where('title', $data['Title'])->first();
        $save_data['bookable_item_id'] = $bookable_item->id;
        $save_data['cancellation_cutoff_date'] = $bookable_item->getCutOffDate($data['Start']);

        //====================
        $save_booking = Booking::create($save_data);
        if ($save_booking) {
            $this->info(' Save successfully: Booking(#' . $save_booking->id . ')');
        } else {
            $this->error(' Can not save: Line(' . $key . ')');
        }
    }

    public function arrayFromCSV($file, $hasFieldNames = false, $delimiter = ',')
    {
        $result = array();
        $size = filesize($file) + 1;
        $file = fopen($file, 'r');
        #TO DO: There must be a better way of finding out the size of the longest row... until then
        if ($hasFieldNames) $keys = fgetcsv($file, $size, $delimiter);
        while ($row = fgetcsv($file, $size, $delimiter)) {
            $n = count($row);
            $res = array();
            for ($i = 0; $i < $n; $i++) {
                $idx = ($hasFieldNames) ? $keys[$i] : $i;
                $res[$idx] = $row[$i];
            }
            $result[] = $res;
        }
        fclose($file);
        return $result;
    }

    function _getBookingLengthStr($start, $end)
    {

        if (!$start || !$end) {
            return null;
        }

        $length = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
        return CarbonInterval::minutes($length)->cascade()->forHumans(['short' => true]);
    }

    public function getType($type)
    {
        switch ($type) {
            case 'Room/Area':
                return Booking::$TYPE_ROOM;
            case 'Hire':
                return Booking::$TYPE_HIRE;
            case 'Event':
                return Booking::$TYPE_EVENT;
            case 'Service':
                return Booking::$TYPE_SERVICE;
            default:
                return 0;
        }
    }
}
