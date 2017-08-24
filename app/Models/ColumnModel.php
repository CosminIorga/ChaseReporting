<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 18/04/17
 * Time: 14:10
 */

namespace App\Models;

use App\Definitions\Columns;
use App\Models\BaseModels\NonPersistentModel;
use Illuminate\Validation\Rule;

/**
 * Class ColumnModel
 * @package Models
 * @property string name
 * @property string type
 * @property string dataType
 * @property array extra
 * @property string index
 * @property string generatedAs
 * @property boolean allow_null
 */
class ColumnModel extends NonPersistentModel
{

    const COLUMN_NAME = 'name';
    const COLUMN_DATA_TYPE = 'dataType';
    const COLUMN_DATA_TYPE_LENGTH = 'length';
    const COLUMN_EXTRA_PARAMETERS = 'extra';
    const COLUMN_INDEX = 'index';
    const COLUMN_ALLOW_NULL = 'allow_null';

    protected $defaultAttributeValues = [
        self::COLUMN_INDEX => Columns::COLUMN_SIMPLE_INDEX,
        self::COLUMN_EXTRA_PARAMETERS => [],
        self::COLUMN_ALLOW_NULL => false,
    ];

    /**
     * Call initBefore to create the validation rules
     * @param array $attributes
     */
    protected function initBefore(array $attributes)
    {
        $this->rules = [
            self::COLUMN_NAME => [
                'required',
                'string',
            ],
            self::COLUMN_DATA_TYPE => [
                'required',
                Rule::in(Columns::AVAILABLE_COLUMN_DATA_TYPES),
            ],
            self::COLUMN_INDEX => [
                'present',
                Rule::in(Columns::AVAILABLE_COLUMN_INDEXES),
            ],
        ];
    }


}