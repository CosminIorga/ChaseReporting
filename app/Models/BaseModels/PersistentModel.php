<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 09/05/17
 * Time: 16:38
 */

namespace App\Models\BaseModels;


use App\Traits\ModelValidator;
use Illuminate\Database\Eloquent\Model;

class PersistentModel extends Model
{
    use ModelValidator;

    public function __construct(array $attributes = [])
    {
        $this->initBefore($attributes);

        parent::__construct($attributes);

        $this->validate($attributes);

        $this->initAfter($attributes);
    }

    /**
     * Function called before validating input data and filling attributes
     * @param array $attributes
     */
    protected function initBefore(array $attributes)
    {
    }

    /**
     * Function called after validating input data and filling attributes
     * @param array $attributes
     */
    protected function initAfter(array $attributes)
    {
    }
}