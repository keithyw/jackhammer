<?php
/**
 * Created by PhpStorm.
 * User: keithwatanabe
 * Date: 3/9/15
 * Time: 12:07 PM
 */

namespace Conark\Jackhammer\Models;

use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

/**
 * App\Models\BaseModel
 *
 */
class BaseModel extends Model
{
    use ValidatingTrait;
    protected $rules = [];
    protected $ruleset = [];

    public function isValidRule($type = null){
        if ($type){
            $this->rules = $this->ruleset[$type];
        }
        return $this->isValid();
    }

}

