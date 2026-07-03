<?php

namespace App\Enums;

enum ExportFormat: string
{
    case CSV = 'csv';
    case EXCEL = 'excel';
    case PDF = 'pdf';

    public static function options(): array
    {
        return [
            self::CSV->value => 'CSV',
            self::EXCEL->value => 'Excel',
            self::PDF->value => 'PDF',
        ];
    }

    public static function mimeType(self $format): string
    {
        return match ($format) {
            self::CSV => 'text/csv',
            self::EXCEL => 'application/vnd.ms-excel',
            self::PDF => 'application/pdf',
        };
    }

    public static function fileExtension(self $format): string
    {
        return match ($format) {
            self::CSV => 'csv',
            self::EXCEL => 'xls',
            self::PDF => 'pdf',
        };
    }
}
