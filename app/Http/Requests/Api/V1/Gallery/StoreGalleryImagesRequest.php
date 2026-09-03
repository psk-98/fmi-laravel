<?php

namespace App\Http\Requests\Api\V1\Gallery;


use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryImagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('gallery')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            // 'description' => ['nullable', 'string', 'max:2000'],
            // 'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
