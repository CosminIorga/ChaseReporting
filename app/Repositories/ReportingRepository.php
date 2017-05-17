<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 18/04/17
 * Time: 12:49
 */

namespace App\Repositories;


use App\Definitions\Columns;
use App\Models\ColumnModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class ReportingRepository
{
    /**
     * Function used to check if tableName exists
     * @param string $tableName
     * @return bool
     */
    public function tableExists(string $tableName): bool
    {
        return \Schema::hasTable($tableName);
    }

    /**
     * Function used to create a reporting table
     * @param string $tableName
     * @param Collection $columnDefinitions
     * @return array
     */
    public function createTable(string $tableName, Collection $columnDefinitions): array
    {
        try {
            \Schema::create($tableName, function (Blueprint $table) use ($columnDefinitions) {
                $columnDefinitions->each(function (ColumnModel $columnModel) use (&$table) {

                    /* Create the column */
                    $column = $table->addColumn(
                        $columnModel->dataType,
                        $columnModel->name,
                        $columnModel->extra ?? []
                    );

                    if ($columnModel->allow_null) {
                        /* @noinspection PhpUndefinedMethodInspection */
                        $column->nullable();
                    }

                    /* Add index to column */
                    switch ($columnModel->index) {
                        case Columns::COLUMN_SIMPLE_INDEX:
                            $table->index($columnModel->name);
                            break;
                        case Columns::COLUMN_UNIQUE_INDEX:
                            $table->unique($columnModel->name);
                            break;
                        case Columns::COLUMN_PRIMARY_INDEX:
                            $table->primary($columnModel->name);
                            break;
                        default:
                            /* Add no index */
                            break;
                    }


                });

                $table->engine = 'InnoDB';
            });
        } catch (\Exception $exception) {
            return [
                false,
                $exception->getMessage()
            ];
        }

        return [
            /* Create table success status */
            true,
            /* Create table message if it failed */
            null
        ];
    }

}