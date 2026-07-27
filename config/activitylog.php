<?php

return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'table_name' => 'activity_log',
    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),
    
    // IMPORTANTE: Desativar se não quiseres logs de utilizadores
    'subject_returns_soft_deleted_models' => false,
    
    // Configurar para usar UUID
    'default_log_name' => 'default',
];