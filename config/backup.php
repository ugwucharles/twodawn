<?php

return [
    'backup' => [
        'name' => env('APP_NAME', 'laravel-backup'),
        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    storage_path('app/backups'),
                    storage_path('framework/cache'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => true,
                'relative_path' => true,
            ],
            'databases' => [
                env('DB_CONNECTION', 'sqlite'),
            ],
        ],
        'database_dump_compressor' => null,
        'database_dump_file_extension' => '',
        'destination' => [
            'filename_prefix' => '',
            'disks' => [
                env('BACKUP_DISK', 'backups'),
            ],
        ],
        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_PASSWORD'),
        'encryption' => 'default',
    ],

    'backup_monitor' => [
        'backups' => [
            [
                'name' => env('APP_NAME', 'laravel-backup'),
                'disks' => [env('BACKUP_DISK', 'backups')],
                'health_checks' => [
                    Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 2,
                    Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5120,
                ],
            ],
        ],
        'notifications' => [
            'notifications' => [
                Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => ['log'],
                Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['log'],
                Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => ['log'],
                Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => ['log'],
                Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => ['log'],
                Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => ['log'],
            ],
            'notifiable' => Spatie\Backup\Notifications\Notifiable::class,
            'mail' => [
                'to' => env('BACKUP_MAIL_TO'),
            ],
            'slack' => [
                'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL'),
                'username' => 'Backup bot',
                'icon' => ':package:',
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 2,
            'keep_daily_backups_for_days' => 7,
            'keep_weekly_backups_for_weeks' => 4,
            'keep_monthly_backups_for_months' => 6,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5120,
        ],
    ],
];
