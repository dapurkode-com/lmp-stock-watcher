<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SymbolRequest
 *
 * @property int|null $id
 * @property string|null $symbol
 * @property string|null $name
 * @property float|null $amount
 * @property int|null $unit
 *
 */
class SymbolHoldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id'        => 'required_without:symbol|nullable|numeric',
            'symbol'    => 'required_without:id|nullable|string',
            'name'      => 'required_with:symbol|nullable|string',
            'amount'    => 'required|numeric'
        ];
    }
}
