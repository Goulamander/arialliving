<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class PaginationService
{
    /**
     * The number of rows displayed on every page
     *
     * @var int
     */
    const PER_PAGE = 3;

    /**
     * Prepares the paginated data
     *
     * @param Illuminate\Database\Eloquent\Builder
     * @param int  $current_page
     * @return Illuminate\Support\Collection  $paginated_data
     */
    public function getPaginatedData($data, $current_page) {
        // Paginate the data
        $paginated_data = $data->paginate(self::PER_PAGE);
        // Get the last page
        $last_page = $paginated_data->total();
        // Check that the current page doesn't exceed the last page
        if ($current_page > $last_page) {
            $paginated_data = $data
                        ->paginate(self::PER_PAGE, ['*'], 'page', $last_page);
        }
        return $paginated_data;
    }
}
