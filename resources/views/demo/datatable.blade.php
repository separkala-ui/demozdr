<h3 class="text-lg mb-3 font-bold p-3 pl-0">
    {{ __('Datatable Components') }}
</h3>

<div class="flex flex-col gap-5">
    <x-demo.preview-component
        title="{{ __('Simple Datatable') }}"
        description="{{ __('A basic datatable with only body content.') }}"
        path="views/demo/datatable/datatable.blade.php"
        include="demo.datatable.datatable"
    />
</div>