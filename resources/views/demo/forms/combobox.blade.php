<x-inputs.combobox
    name="city"
    label="City"
    :options="[
        ['value' => 'dhaka', 'label' => 'Dhaka'],
        ['value' => 'ny', 'label' => 'New York'],
        ['value' => 'london', 'label' => 'London']
    ]"
    placeholder="Select city"
    hint="Type to search city."
    :searchable="true"
/>
