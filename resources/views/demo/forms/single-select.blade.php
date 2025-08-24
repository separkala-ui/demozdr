<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <x-searchable-select
        name="city"
        label="City (Single Select)"
        :options="[
            'dhaka' => 'Dhaka',
            'ny' => 'New York',
            'london' => 'London'
        ]"
        placeholder="Select city"
        hint="Type to search city."
        :searchable="true"
        :multiple="false"
    />
</div>
