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

class TransformConfigColumn
{

    /**
     * Transform config pivot columns to match an array used by ColumnModel
     * @param array $configPivotColumn
     * @return array
     */
    public function toColumnModelData(array $configPivotColumn): array
    {
        $columnData = [
            ColumnModel::COLUMN_NAME => $configPivotColumn[Data::CONFIG_COLUMN_NAME],
            ColumnModel::COLUMN_DATA_TYPE => $configPivotColumn[Data::CONFIG_COLUMN_DATA_TYPE],
            ColumnModel::COLUMN_INDEX => $configPivotColumn[Data::CONFIG_COLUMN_INDEX],
            ColumnModel::COLUMN_ALLOW_NULL => $configPivotColumn[Data::CONFIG_COLUMN_ALLOW_NULL],
            ColumnModel::COLUMN_EXTRA_PARAMETERS => [
                ColumnModel::COLUMN_DATA_TYPE_LENGTH => $configPivotColumn[Data::CONFIG_COLUMN_DATA_TYPE_LENGTH] ?? null,
            ],
        ];

        return $columnData;
    }

    /**
     * Transform pivots config to a set of laravel validation rules
     * @param array $pivotsConfig
     * @return array
     */
    public function toRules(array $pivotsConfig): array
    {
        $rules = collect([]);

        /* Process the pivots config */
        array_walk($pivotsConfig, function ($pivotConfig) use ($rules) {
            $rule = [];

            /* Check if pivot is required */
            $allowNull = $pivotConfig[Data::CONFIG_COLUMN_ALLOW_NULL] ?? false;
            if (!$allowNull) {
                $rule[] = "required";
            }

            /* Enforce data type */
            $rule[] = $pivotConfig[Data::CONFIG_COLUMN_DATA_TYPE];

            /* Enforce data type length if necessary */
            $dataTypeLength = $pivotConfig[Data::CONFIG_COLUMN_DATA_TYPE_LENGTH] ?? null;
            if (!is_null($dataTypeLength)) {
                $rule[] = "max:{$dataTypeLength}";
            }

            /* Add rule to rules */
            $rules->put($pivotConfig[Data::CONFIG_COLUMN_NAME], $rule);
        });

        return $rules->toArray();
    }

}