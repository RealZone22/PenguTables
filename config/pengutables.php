<?php

return [
    'table_view' => 'pengutables::livewire.components.table',
    'export_filename' => 'export_'.now()->format('Ymd_Hi'),
    'export_route' => 'pengutables.export',
    'export_token_ttl' => 60,
];
