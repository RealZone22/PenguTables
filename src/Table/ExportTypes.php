<?php

namespace RealZone22\PenguTables\Table;

enum ExportTypes: string
{
    case CSV_ALL = 'csv_all';
    case XLSX_ALL = 'xlsx_all';
    case CSV_SELECTED = 'csv_selected';
    case XLSX_SELECTED = 'xlsx_selected';
}
