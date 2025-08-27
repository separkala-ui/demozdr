<div>
    <table class="table">
        <thead class="table-thead">
            <tr class="table-tr">
                <th width="3%" class="table-thead-th"><div class="flex items-center"><div class="h-4 w-4 bg-gray-300 dark:bg-gray-700 rounded"></div></div></th>
                <th width="20%" class="table-thead-th"><div class="flex items-center"><div class="h-4 w-24 bg-gray-300 dark:bg-gray-700 rounded mb-1"></div></div></th>
                <th width="10%" class="table-thead-th"><div class="flex items-center"><div class="h-4 w-32 bg-gray-300 dark:bg-gray-700 rounded"></div></div></th>
                <th width="30%" class="table-thead-th"><div class="h-4 w-20 bg-gray-300 dark:bg-gray-700 rounded"></div></th>
                <th width="15%" class="table-thead-th table-thead-th-last"><div class="h-8 w-20 bg-gray-300 dark:bg-gray-700 rounded"></div></th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 8; $i++)
                <tr class="animate-pulse bg-gray-100 dark:bg-gray-800">
                    <td class="table-td table-td-checkbox"><div class="h-4 w-4 bg-gray-300 dark:bg-gray-700 rounded"></div></td>
                    <td class="table-td flex items-center md:min-w-[200px]">
                        <div class="w-10 h-10 bg-gray-300 dark:bg-gray-700 rounded-full mr-3"></div>
                        <div class="flex-1 min-w-0">
                            <div class="h-4 w-24 bg-gray-300 dark:bg-gray-700 rounded mb-1"></div>
                            <div class="h-3 w-16 bg-gray-200 dark:bg-gray-600 rounded"></div>
                        </div>
                    </td>
                    <td class="table-td"><div class="h-4 w-32 bg-gray-300 dark:bg-gray-700 rounded"></div></td>
                    <td class="table-td"><div class="h-4 w-20 bg-gray-300 dark:bg-gray-700 rounded"></div></td>
                    <td class="table-td flex justify-center"><div class="h-8 w-20 bg-gray-300 dark:bg-gray-700 rounded"></div></td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
