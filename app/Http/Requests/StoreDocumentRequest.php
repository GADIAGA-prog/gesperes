<?php

namespace App\Http\Requests;

use App\Enums\TypeDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('documents.upload');
    }

    public function rules(): array
    {
        return [
            'type_document'   => ['required', new Enum(TypeDocument::class)],
            'reference'       => ['nullable', 'string', 'max:120'],
            'date_document'   => ['nullable', 'date'],
            'date_expiration' => ['nullable', 'date', 'after_or_equal:date_document'],
            'commentaire'     => ['nullable', 'string', 'max:1000'],
            'fichier'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return ['fichier' => 'fichier', 'type_document' => 'type de document'];
    }
}
