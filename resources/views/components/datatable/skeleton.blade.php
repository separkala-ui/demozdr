@props([
    'columns' => 5,
    'rows' => 8,
    'enableCheckbox' => true
])

<div class="table-responsive">
    <table class="table">
        <thead class="table-thead">
            <tr class="table-tr">
                @if($enableCheckbox)
                <th width="3%" class="table-thead-th"><div class="flex items-center"><div class="h-4 w-4 bg-gray-300 dark:bg-gray-700 rounded"></div></div></th>
                @endif
                @foreach(range(1, $columns) as $column)
                    <th class="table-thead-th">
                        <div class="flex items-center">
                            <div class="h-4 w-24 bg-gray-300 dark:bg-gray-700 rounded mb-1"></div>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach(range(1, $rows) as $row)
                <tr class="animate-pulse bg-gray-100 dark:bg-gray-800">
                    @if($enableCheckbox)
                    <td class="table-td">
                        <div class="h-4 w-4 bg-gray-300 dark:bg-gray-700 rounded"></div>
                    </td>
                    @endif

                    @foreach(range(1, $columns) as $column)
                        <td class="table-td">
                            <div class="h-4 w-24 bg-gray-300 dark:bg-gray-700 rounded mb-1"></div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
