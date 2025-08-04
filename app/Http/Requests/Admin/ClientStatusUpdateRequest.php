<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ClientStatusUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'in:active,inactive'
            ],
            'reason' => [
                'nullable',
                'string',
                'max:500',
                'min:3'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido. Deve ser "ativo" ou "inativo".',
            'reason.string' => 'O motivo deve ser um texto.',
            'reason.max' => 'O motivo não pode ter mais de 500 caracteres.',
            'reason.min' => 'O motivo deve ter pelo menos 3 caracteres.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'status' => 'status',
            'reason' => 'motivo'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitizar o motivo removendo tags HTML e caracteres especiais
        if ($this->has('reason')) {
            $this->merge([
                'reason' => strip_tags(trim($this->input('reason')))
            ]);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::warning('Validação falhou na atualização de status do cliente', [
            'client_id' => $this->route('id'),
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['_token']),
            'user_id' => auth()->id(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent()
        ]);

        parent::failedValidation($validator);
    }
}
