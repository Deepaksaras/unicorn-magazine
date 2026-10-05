@props(['name' => 'status', 'value' => 1, 'labels' => ['1' => 'Active', '0' => 'Inactive'], 'label' => 'Status'])
<x-admin.select :name="$name" :label="$label" :value="(string) $value" :options="$labels" />
