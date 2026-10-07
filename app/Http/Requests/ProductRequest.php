<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public const UNITS = ['unit', 'kg', 'g', 'l', 'ml', 'box', 'pack'];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:32', 'alpha_dash', Rule::unique('products')->ignore($this->route('product'))],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(self::UNITS)],
            'min_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sku')) {
            $this->merge(['sku' => strtoupper((string) $this->input('sku'))]);
        }
    }
}
