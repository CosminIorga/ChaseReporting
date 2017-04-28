<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 21/04/17
 * Time: 15:58
 */

namespace App\Transformers;


use App\Definitions\Data;
use App\Models\ColumnModel;

class TransformConfigPivotColumns
{

    /**
     * Transform config pivot columns to match an array used by ColumnModel
     * @param array $configPivotColumn
     * @return array
     */
    public function transform(array $configPivotColumn): array
    {
        $columnData = [];

        $this->addIfExists(Data::PIVOT_NAME, $configPivotColumn, ColumnModel::COLUMN_NAME, $columnData);
        $this->addIfExists(Data::PIVOT_DATA_TYPE, $configPivotColumn, ColumnModel::COLUMN_DATA_TYPE, $columnData);
        $this->addIfExists(Data::PIVOT_EXTRA_INDEX, $configPivotColumn, ColumnModel::COLUMN_INDEX, $columnData);


        if (
            array_key_exists(Data::PIVOT_EXTRA, $configPivotColumn) &&
            is_array($configPivotColumn[Data::PIVOT_EXTRA])
        ) {
            $columnData[ColumnModel::COLUMN_EXTRA_PARAMETERS] = [];

            $this->addIfExists(
                Data::PIVOT_EXTRA_DATA_TYPE_LENGTH,
                $configPivotColumn[Data::PIVOT_EXTRA],
                ColumnModel::COLUMN_DATA_TYPE_LENGTH,
                $columnData[ColumnModel::COLUMN_EXTRA_PARAMETERS]
            );
        }

        return $columnData;
    }

    /**
     * Search for $search in $haystack and add it to $carry with key $key
     * @param string $search
     * @param array $haystack
     * @param string $key
     * @param array $carry
     */
    protected function addIfExists($search, $haystack, $key, &$carry)
    {
        if (array_key_exists($search, $haystack)) {
            $carry[$key] = $haystack[$search];
        }
    }
}